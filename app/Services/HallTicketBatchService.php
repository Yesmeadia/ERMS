<?php

namespace App\Services;

use App\Models\HallTicketBatch;
use App\Models\HallTicketPdfPart;
use App\Models\Student;
use App\Jobs\GenerateHallTicketPdfPart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class HallTicketBatchService
{
    /**
     * Create or retrieve an existing active batch and dispatch PDF generation jobs.
     * Uses atomic locking and DB transactions with afterCommit queue dispatching.
     */
    public function createAndDispatchBatch(
        int $schoolId,
        int $examinationId,
        int $requestedByUserId,
        array $filters = [],
        ?array $specificStudentIds = null
    ): HallTicketBatch {
        $lockKey = "create_ht_batch_{$schoolId}_{$examinationId}";

        // Acquire lock for up to 10 seconds to prevent concurrent duplicate batch creations
        return Cache::lock($lockKey, 10)->block(5, function () use (
            $schoolId,
            $examinationId,
            $requestedByUserId,
            $filters,
            $specificStudentIds
        ) {
            // Check for ANY currently active batch (pending or processing) for this school and exam
            $existingActiveBatch = HallTicketBatch::where('school_id', $schoolId)
                ->where('examination_id', $examinationId)
                ->whereIn('status', ['pending', 'processing'])
                ->latest()
                ->first();

            if ($existingActiveBatch) {
                return $existingActiveBatch;
            }

            // Build student query: SELECT IDs ONLY to prevent unbounded memory consumption
            $query = Student::where('school_id', $schoolId)
                ->where('examination_id', $examinationId)
                ->where('status', 'Hall Ticket Issued');

            if (!empty($specificStudentIds)) {
                $query->whereIn('id', $specificStudentIds);
            }

            if (!empty($filters['gender'])) {
                $query->where('gender', $filters['gender']);
            }
            if (!empty($filters['centre_id'])) {
                $query->where('centre_id', $filters['centre_id']);
            }
            if (!empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }
            if (!empty($filters['search'])) {
                $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $filters['search']);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('hall_ticket_number', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                });
            }

            // Deterministic ordering: registration_number, name, id
            $studentIds = $query->orderBy('registration_number', 'asc')
                ->orderBy('name', 'asc')
                ->orderBy('id', 'asc')
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->all();

            if (empty($studentIds)) {
                throw ValidationException::withMessages([
                    'examination_id' => ['No issued hall tickets found matching the selected criteria.'],
                ]);
            }

            // HARD SAFETY LIMIT: Enforce configured maximum (default 50/100, hard-capped at 100)
            $maxPerPdf = min(100, max(1, (int) config('hallticket.max_per_pdf', 100)));
            $chunks = array_chunk($studentIds, $maxPerPdf);
            $totalStudents = count($studentIds);
            $totalParts = count($chunks);
            $expirationHours = (int) config('hallticket.expiration_hours', 24);

            return DB::transaction(function () use (
                $schoolId,
                $examinationId,
                $requestedByUserId,
                $filters,
                $totalStudents,
                $totalParts,
                $chunks,
                $expirationHours
            ) {
                $batch = HallTicketBatch::create([
                    'school_id' => $schoolId,
                    'examination_id' => $examinationId,
                    'requested_by' => $requestedByUserId,
                    'total_students' => $totalStudents,
                    'total_parts' => $totalParts,
                    'completed_parts' => 0,
                    'failed_parts' => 0,
                    'completed_students' => 0,
                    'failed_students' => 0,
                    'status' => 'pending',
                    'filter_criteria' => $filters,
                    'expires_at' => now()->addHours($expirationHours),
                ]);

                $parts = [];
                foreach ($chunks as $index => $chunk) {
                    $partNumber = $index + 1;
                    $part = HallTicketPdfPart::create([
                        'batch_id' => $batch->id,
                        'part_number' => $partNumber,
                        'student_ids' => array_values($chunk),
                        'total_students' => count($chunk),
                        'completed_students' => 0,
                        'status' => 'pending',
                    ]);

                    $parts[] = $part;
                }

                // Dispatch jobs to dedicated pdf queue ONLY AFTER the transaction successfully commits
                foreach ($parts as $part) {
                    GenerateHallTicketPdfPart::dispatch($part->id)
                        ->onQueue(config('hallticket.queue', 'pdf'))
                        ->afterCommit();
                }

                $batch->update([
                    'status' => 'processing',
                    'started_at' => now(),
                ]);

                Log::info("Created and dispatched Hall Ticket Batch #{$batch->id} for School ID: {$schoolId}, Exam ID: {$examinationId} ({$totalStudents} students in {$totalParts} parts).");

                return $batch;
            });
        });
    }

    /**
     * Retry failed parts in a batch with atomic state transition to prevent duplicate concurrent dispatches.
     */
    public function retryFailedParts(HallTicketBatch $batch): void
    {
        $failedParts = $batch->parts()->where('status', 'failed')->get();

        foreach ($failedParts as $part) {
            // Atomic update: only claim if currently in 'failed' status
            $updated = HallTicketPdfPart::where('id', $part->id)
                ->where('status', 'failed')
                ->update([
                    'status' => 'pending',
                    'error_message' => null,
                    'started_at' => null,
                    'completed_at' => null,
                ]);

            if ($updated > 0) {
                GenerateHallTicketPdfPart::dispatch($part->id)
                    ->onQueue(config('hallticket.queue', 'pdf'))
                    ->afterCommit();
            }
        }

        $batch->recalculateProgress();
    }
}
