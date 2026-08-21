<?php

namespace App\Console\Commands;

use App\Models\HallTicketBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupHallTicketBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'halltickets:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired Hall Ticket PDF batch files and prune old records from storage.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Hall Ticket expired batches cleanup...');

        $disk = Storage::disk(config('hallticket.disk', 'local'));
        $now = now();
        $deletedFilesCount = 0;
        $expiredBatchesCount = 0;

        // Clean only batches that are no longer actively processing
        // Prevents deleting files while a queue job is actively rendering
        HallTicketBatch::with('parts')
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

        // Prune old batch records older than the configured retention period
        $retentionDays = (int) config('hallticket.retention_days', 7);
        $pruneCutoff = $now->copy()->subDays($retentionDays);

        $prunedBatchesCount = HallTicketBatch::where('status', 'expired')
            ->where('updated_at', '<=', $pruneCutoff)
            ->delete();

        $this->info("Cleanup completed: {$deletedFilesCount} PDF files removed, {$expiredBatchesCount} batches marked expired, {$prunedBatchesCount} old batch records pruned.");
        Log::info("CleanupHallTicketBatches: {$deletedFilesCount} files removed, {$expiredBatchesCount} marked expired, {$prunedBatchesCount} pruned.");

        return Command::SUCCESS;
    }
}
