<?php

namespace App\Console\Commands;

use App\Models\ResultBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupResultBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'results:cleanup {--all : Delete all result batches and their generated PDF files immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired or all Result PDF batch files and records.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $diskName = config('results.disk', 'local');
        $disk = Storage::disk($diskName);
        $deleteAll = $this->option('all');

        if ($deleteAll) {
            $this->info('Deleting ALL result export batches and stored PDFs...');
            $batches = ResultBatch::with('parts')->get();
            $deletedFilesCount = 0;

            foreach ($batches as $batch) {
                foreach ($batch->parts as $part) {
                    if ($part->pdf_path && $disk->exists($part->pdf_path)) {
                        $disk->delete($part->pdf_path);
                        $deletedFilesCount++;
                    }
                }
                // Delete directory if exists
                $dir = "results_reports/{$batch->batch_uuid}";
                if ($disk->exists($dir)) {
                    $disk->deleteDirectory($dir);
                }
                $batch->parts()->delete();
                $batch->delete();
            }

            $this->info("Successfully deleted {$batches->count()} batch(es) and {$deletedFilesCount} PDF file(s).");
            return Command::SUCCESS;
        }

        $this->info('Starting Result expired batches cleanup...');
        $now = now();
        $deletedFilesCount = 0;
        $expiredBatchesCount = 0;

        ResultBatch::with('parts')
            ->where(function ($query) use ($now) {
                $query->where(function ($q) use ($now) {
                    $q->whereIn('status', ['completed', 'completed_with_errors', 'failed'])
                      ->where('expires_at', '<=', $now);
                })->orWhere('status', 'expired');
            })
            ->chunkById(50, function ($batches) use ($disk, &$deletedFilesCount, &$expiredBatchesCount) {
                foreach ($batches as $batch) {
                    foreach ($batch->parts as $part) {
                        if ($part->pdf_path && $disk->exists($part->pdf_path)) {
                            $disk->delete($part->pdf_path);
                            $deletedFilesCount++;
                        }
                    }

                    if ($batch->status !== 'expired') {
                        $batch->update(['status' => 'expired']);
                        $expiredBatchesCount++;
                    }
                }
            });

        $retentionDays = (int) config('results.retention_days', 7);
        $pruneCutoff = $now->copy()->subDays($retentionDays);

        $prunedBatchesCount = ResultBatch::where('status', 'expired')
            ->where('updated_at', '<=', $pruneCutoff)
            ->delete();

        $this->info("Cleanup completed: {$deletedFilesCount} PDF files removed, {$expiredBatchesCount} batches marked expired, {$prunedBatchesCount} old batch records pruned.");
        Log::info("CleanupResultBatches: {$deletedFilesCount} files removed, {$expiredBatchesCount} marked expired, {$prunedBatchesCount} pruned.");

        return Command::SUCCESS;
    }
}
