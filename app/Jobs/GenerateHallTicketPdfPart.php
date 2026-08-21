<?php

namespace App\Jobs;

use App\Models\HallTicketPdfPart;
use App\Models\Student;
use App\Services\HallTicketPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GenerateHallTicketPdfPart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $partId;
    public int $tries;
    public int $timeout;
    public ?string $processingToken = null;
    public array $backoff = [10, 30];

    /**
     * Create a new job instance.
     */
    public function __construct(int $partId)
    {
        $this->partId = $partId;
        $this->tries = (int) config('hallticket.job_tries', 3);
        $this->timeout = (int) config('hallticket.job_timeout', 300);
        $this->onQueue(config('hallticket.queue', 'pdf'));
    }

    /**
     * Execute the job.
     */
    public function handle(HallTicketPdfService $pdfService): void
    {
        $part = HallTicketPdfPart::with(['batch.school', 'batch.examination'])->find($this->partId);

        if (!$part || !$part->batch) {
            Log::warning("GenerateHallTicketPdfPart: Part #{$this->partId} or its parent batch not found.");
            return;
        }

        $disk = Storage::disk(config('hallticket.disk', 'local'));

        // Handle completed part: verify physical file exists on storage disk
        if ($part->status === 'completed') {
            if (!empty($part->pdf_path) && $disk->exists($part->pdf_path)) {
                return;
            }

            // Completed in DB but file is missing or purged from disk:
            // Safely reset to 'pending' so it can be claimed and regenerated cleanly
            $reset = HallTicketPdfPart::where('id', $part->id)
                ->where('status', 'completed')
                ->update([
                    'status' => 'pending',
                    'pdf_path' => null,
                    'file_size' => null,
                    'completed_students' => 0,
                    'completed_at' => null,
                    'processing_token' => null,
                    'error_message' => 'PDF file was missing from storage; part was queued for regeneration.',
                ]);

            if ($reset > 0) {
                $part->refresh();
                $part->batch?->recalculateProgress();
            }
        }

        $this->processingToken = (string) Str::uuid();
        $token = $this->processingToken;

        // Atomic lock transition: Claim ownership of the part with unique processing token
        $claimed = HallTicketPdfPart::where('id', $this->partId)
            ->whereIn('status', ['pending', 'failed'])
            ->update([
                'status' => 'processing',
                'processing_token' => $token,
                'started_at' => now(),
            ]);

        if ($claimed === 0) {
            $currentPart = HallTicketPdfPart::find($this->partId);

            // If another worker is processing it, check for stale crash recovery atomically
            if ($currentPart && $currentPart->status === 'processing') {
                $staleThresholdSeconds = (int) config('hallticket.stale_after', 600);
                $staleCutoff = now()->subSeconds($staleThresholdSeconds);

                // Atomic stale recovery: only reclaim if started_at is strictly older than stale cutoff
                $reclaimed = HallTicketPdfPart::where('id', $this->partId)
                    ->where('status', 'processing')
                    ->where('started_at', '<=', $staleCutoff)
                    ->update([
                        'started_at' => now(),
                        'processing_token' => $token,
                    ]);

                if ($reclaimed === 0) {
                    Log::info("GenerateHallTicketPdfPart: Part #{$this->partId} is currently being processed by another active worker. Exiting.");
                    return;
                }
            } elseif ($currentPart && $currentPart->status === 'completed') {
                return;
            }
        }

        $part->refresh();
        $part->batch->recalculateProgress();

        $finalStoragePath = null;

        try {
            $studentIds = $part->student_ids ?? [];
            
            // Empty students safety branch protected by atomic processing token
            if (empty($studentIds)) {
                $updated = HallTicketPdfPart::where('id', $this->partId)
                    ->where('status', 'processing')
                    ->where('processing_token', $token)
                    ->update([
                        'status' => 'completed',
                        'completed_students' => 0,
                        'completed_at' => now(),
                        'processing_token' => null,
                        'error_message' => null,
                    ]);

                if ($updated === 0) {
                    Log::warning("GenerateHallTicketPdfPart: Lost ownership of empty Part #{$this->partId}.");
                    return;
                }

                $part->batch->recalculateProgress();
                return;
            }

            // Detect duplicate student IDs in data payload
            if (count($studentIds) !== count(array_unique($studentIds))) {
                throw new RuntimeException("Data consistency error: Part #{$this->partId} contains duplicate student IDs.");
            }

            // Enforce maximum allowed students per PDF part from configuration
            $maxAllowed = (int) config('hallticket.max_per_pdf', 100);
            if (count($studentIds) > $maxAllowed) {
                throw new RuntimeException("Safety violation: Part #{$this->partId} contains " . count($studentIds) . " students, which exceeds the configured maximum limit of {$maxAllowed} students per PDF.");
            }

            // Eager-load students with strict school and examination isolation boundaries
            $students = Student::query()
                ->whereIn('id', $studentIds)
                ->where('school_id', $part->batch->school_id)
                ->where('examination_id', $part->batch->examination_id)
                ->with(['school', 'class', 'category', 'examination', 'hallTicket', 'centre'])
                ->get();

            // Strict Data Consistency: Check for deleted/missing student records or cross-school tampering
            if ($students->count() !== count($studentIds)) {
                $foundIds = $students->pluck('id')->all();
                $missingIds = array_diff($studentIds, $foundIds);
                throw new RuntimeException("Student data consistency error: Expected " . count($studentIds) . " students for School #{$part->batch->school_id}, but found " . $students->count() . ". Missing or invalid Student IDs: " . implode(', ', $missingIds));
            }

            // Retain exact deterministic ordering
            $studentMap = $students->keyBy('id');
            $orderedStudents = collect($studentIds)->map(fn($id) => $studentMap->get($id))->filter();

            // Render PDF via centralized HallTicketPdfService
            $pdf = $pdfService->generateBulkPdf($orderedStudents);
            $output = $pdf->output();

            // Validate PDF binary output
            if (empty($output) || !str_starts_with($output, '%PDF-')) {
                throw new RuntimeException("PDF generation failed: Generated output is empty or not a valid PDF binary.");
            }

            $academicYear = $part->batch->examination?->academic_year;
            if (empty($academicYear)) {
                throw new RuntimeException("Examination academic year is missing for Batch #{$part->batch_id}, Part #{$part->part_number}.");
            }
            $year = Str::slug($academicYear);

            $schoolId = $part->batch->school_id;
            $batchId = $part->batch_id;
            
            // Unique immutable filename with processing token to prevent race condition overwrites
            $filename = "Hall_Tickets_Part_{$part->part_number}_{$token}.pdf";
            $finalStoragePath = config('hallticket.storage_directory', 'hall-tickets') . "/{$year}/school-{$schoolId}/batch-{$batchId}/{$filename}";

            // Write PDF directly to immutable tokenized path
            if (!$disk->put($finalStoragePath, $output)) {
                throw new RuntimeException("Failed to write PDF file for Part #{$part->part_number}.");
            }

            if (!$disk->exists($finalStoragePath)) {
                throw new RuntimeException("PDF was written but could not be verified in storage for Part #{$part->part_number}.");
            }

            $fileSize = strlen($output);

            // Token-protected database completion update: only succeeds if this worker STILL owns the part
            $updated = HallTicketPdfPart::where('id', $this->partId)
                ->where('status', 'processing')
                ->where('processing_token', $token)
                ->update([
                    'status' => 'completed',
                    'completed_students' => $orderedStudents->count(),
                    'pdf_path' => $finalStoragePath,
                    'file_size' => $fileSize,
                    'completed_at' => now(),
                    'error_message' => null,
                    'processing_token' => null,
                ]);

            if ($updated === 0) {
                Log::warning("GenerateHallTicketPdfPart: Lost ownership for Part #{$this->partId} (reclaimed by another worker). Cleaning up orphaned file.");
                if ($finalStoragePath && $disk->exists($finalStoragePath)) {
                    $disk->delete($finalStoragePath);
                }
                return;
            }

            $part->batch->recalculateProgress();

            Log::info("GenerateHallTicketPdfPart: Successfully completed Part #{$part->part_number} for Batch #{$batchId} ({$part->completed_students} students, {$fileSize} bytes).");

            // Explicit resource and memory cleanup
            unset($pdf, $output, $orderedStudents, $students, $studentMap);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

        } catch (Throwable $e) {
            // Detailed internal error logging
            Log::error("GenerateHallTicketPdfPart attempt {$this->attempts()} failed for Part #{$this->partId}", [
                'part_id' => $this->partId,
                'batch_id' => $part->batch_id ?? null,
                'attempt' => $this->attempts(),
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            // Delete partial or orphan file on failure
            if ($finalStoragePath && $disk->exists($finalStoragePath)) {
                $disk->delete($finalStoragePath);
            }

            // User-facing sanitized error message
            $userFacingMessage = $this->attempts() >= $this->tries
                ? 'PDF generation failed after maximum retry attempts. Please click Retry to regenerate this part.'
                : "PDF generation encountered an error (Attempt {$this->attempts()} of {$this->tries}). Retrying in background...";

            if ($this->attempts() >= $this->tries) {
                HallTicketPdfPart::where('id', $this->partId)
                    ->where('processing_token', $token)
                    ->update([
                        'status' => 'failed',
                        'error_message' => $userFacingMessage,
                        'processing_token' => null,
                    ]);
                $part->batch->recalculateProgress();
            } else {
                HallTicketPdfPart::where('id', $this->partId)
                    ->where('processing_token', $token)
                    ->update([
                        'error_message' => $userFacingMessage,
                    ]);
            }

            throw $e;
        }
    }

    /**
     * Handle job permanent failure when all attempts are exhausted.
     * Guarded by token check to prevent overwriting newer workers or completed parts.
     */
    public function failed(?Throwable $exception): void
    {
        if (!$this->processingToken) {
            Log::error("GenerateHallTicketPdfPart: Permanently failed for Part #{$this->partId}, but no processing token was available.");
            return;
        }

        $affected = HallTicketPdfPart::where('id', $this->partId)
            ->where('status', 'processing')
            ->where('processing_token', $this->processingToken)
            ->update([
                'status' => 'failed',
                'error_message' => 'PDF generation timed out or failed permanently. Please retry this part.',
                'processing_token' => null,
            ]);

        if ($affected > 0) {
            $part = HallTicketPdfPart::with('batch')->find($this->partId);
            $part?->batch?->recalculateProgress();
        }

        Log::error("GenerateHallTicketPdfPart: Permanently failed for Part #{$this->partId}.");
    }
}
