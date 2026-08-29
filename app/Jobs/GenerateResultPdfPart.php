<?php

namespace App\Jobs;

use App\Models\ResultPdfPart;
use App\Services\ResultReportService;
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

class GenerateResultPdfPart implements ShouldQueue
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
        $this->tries = (int) config('results.job_tries', 3);
        $this->timeout = (int) config('results.job_timeout', 300);
        $this->onQueue(config('results.queue', 'pdf'));
    }

    /**
     * Execute the job.
     */
    public function handle(ResultReportService $reportService): void
    {
        $part = ResultPdfPart::with(['batch.school', 'batch.examination'])->find($this->partId);

        if (!$part || !$part->batch) {
            Log::warning("GenerateResultPdfPart: Part #{$this->partId} or its parent batch not found.");
            return;
        }

        $disk = Storage::disk(config('results.disk', 'local'));

        // If part is already completed and physical file exists, nothing to do
        if ($part->status === 'completed') {
            if (!empty($part->pdf_path) && $disk->exists($part->pdf_path)) {
                return;
            }

            // Completed in DB but file missing from disk: reset to pending
            $reset = ResultPdfPart::where('id', $part->id)
                ->where('status', 'completed')
                ->update([
                    'status' => 'pending',
                    'pdf_path' => null,
                    'file_size' => null,
                    'completed_students' => 0,
                    'completed_at' => null,
                    'processing_token' => null,
                    'error_message' => 'PDF file missing from storage; part was queued for regeneration.',
                ]);

            if ($reset > 0) {
                $part->refresh();
                $part->batch?->recalculateProgress();
            }
        }

        $this->processingToken = (string) Str::uuid();
        $token = $this->processingToken;

        // Atomic lock transition: Claim ownership of part
        $claimed = ResultPdfPart::where('id', $this->partId)
            ->whereIn('status', ['pending', 'failed'])
            ->update([
                'status' => 'processing',
                'processing_token' => $token,
                'started_at' => now(),
            ]);

        if ($claimed === 0) {
            $currentPart = ResultPdfPart::find($this->partId);

            if ($currentPart && $currentPart->status === 'processing') {
                $staleThresholdSeconds = (int) config('results.stale_after', 600);
                $staleCutoff = now()->subSeconds($staleThresholdSeconds);

                // Atomic stale recovery
                $reclaimed = ResultPdfPart::where('id', $this->partId)
                    ->where('status', 'processing')
                    ->where('started_at', '<=', $staleCutoff)
                    ->update([
                        'started_at' => now(),
                        'processing_token' => $token,
                    ]);

                if ($reclaimed === 0) {
                    Log::info("GenerateResultPdfPart: Part #{$this->partId} is being processed by another worker.");
                    return;
                }
            } elseif ($currentPart && $currentPart->status === 'completed') {
                return;
            }
        }

        $part->refresh();
        $part->batch->recalculateProgress();

        $generatedPath = null;

        try {
            $studentIds = $part->student_ids ?? [];

            if (empty($studentIds)) {
                $updated = ResultPdfPart::where('id', $this->partId)
                    ->where('status', 'processing')
                    ->where('processing_token', $token)
                    ->update([
                        'status' => 'completed',
                        'completed_students' => 0,
                        'completed_at' => now(),
                        'processing_token' => null,
                        'error_message' => null,
                    ]);

                if ($updated > 0) {
                    $part->batch->recalculateProgress();
                }
                return;
            }

            // Generate PDF via ResultReportService
            $res = $reportService->generatePartPdf($part);
            $generatedPath = $res['pdf_path'];
            $fileSize = $res['file_size'];
            $completedStudents = $res['completed_students'];

            // Validate that file exists on disk
            if (!$disk->exists($generatedPath)) {
                throw new RuntimeException("Generated PDF file could not be verified on storage disk at {$generatedPath}");
            }

            // Token-protected database completion update
            $updated = ResultPdfPart::where('id', $this->partId)
                ->where('status', 'processing')
                ->where('processing_token', $token)
                ->update([
                    'status' => 'completed',
                    'completed_students' => $completedStudents,
                    'pdf_path' => $generatedPath,
                    'file_size' => $fileSize,
                    'completed_at' => now(),
                    'error_message' => null,
                    'processing_token' => null,
                ]);

            if ($updated === 0) {
                Log::warning("GenerateResultPdfPart: Lost ownership for Part #{$this->partId}. Cleaning up file.");
                if ($generatedPath && $disk->exists($generatedPath)) {
                    $disk->delete($generatedPath);
                }
                return;
            }

            $part->batch->recalculateProgress();

            Log::info("GenerateResultPdfPart: Successfully completed Part #{$part->part_number} for ResultBatch #{$part->batch_id} ({$completedStudents} students, {$fileSize} bytes).");

        } catch (Throwable $e) {
            Log::error("GenerateResultPdfPart attempt {$this->attempts()} failed for Part #{$this->partId}", [
                'part_id' => $this->partId,
                'batch_id' => $part->batch_id ?? null,
                'attempt' => $this->attempts(),
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            if ($generatedPath && $disk->exists($generatedPath)) {
                $disk->delete($generatedPath);
            }

            if ($this->attempts() >= $this->tries) {
                ResultPdfPart::where('id', $this->partId)
                    ->where('processing_token', $token)
                    ->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'processing_token' => null,
                        'completed_at' => now(),
                    ]);

                $part->batch?->recalculateProgress();
            } else {
                // Release token so next retry attempt can claim it
                ResultPdfPart::where('id', $this->partId)
                    ->where('processing_token', $token)
                    ->update([
                        'status' => 'pending',
                        'processing_token' => null,
                    ]);
            }

            throw $e;
        } finally {
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
    }

    /**
     * Handle job failure after max retries.
     */
    public function failed(?Throwable $exception): void
    {
        $errorMsg = $exception ? $exception->getMessage() : 'Exceeded maximum retry attempts.';

        ResultPdfPart::where('id', $this->partId)->update([
            'status' => 'failed',
            'error_message' => $errorMsg,
            'processing_token' => null,
            'completed_at' => now(),
        ]);

        $part = ResultPdfPart::find($this->partId);
        $part?->batch?->recalculateProgress();

        Log::error("GenerateResultPdfPart: Permanently failed for Part #{$this->partId}: {$errorMsg}");
    }
}
