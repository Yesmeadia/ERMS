<?php

namespace App\Services;

use App\Models\OnlineExamRecording;
use App\Models\OnlineExamSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProctoringRecordingService
{
    protected string $disk = 'local';

    /**
     * Compute a shared-hosting optimized, two-tier hashed directory path.
     *
     * Example: "a1/b2/a1b2c3d4e5f6..."
     * Distributes files across 65,536 subdirectories to prevent directory/inode
     * lookup bottlenecks on shared hosting ext4 file systems.
     */
    public function getSessionHashDir(OnlineExamSession $session): string
    {
        $appKey = config('app.key') ?: 'erms_proctoring_salt';
        $rawHash = hash_hmac('sha256', "exam_{$session->online_exam_id}_sess_{$session->id}_{$session->registration_number}", $appKey);

        $tier1 = substr($rawHash, 0, 2);
        $tier2 = substr($rawHash, 2, 2);

        return "{$tier1}/{$tier2}/{$rawHash}";
    }

    /**
     * Maximum cumulative recording bytes allowed per student session (512 MB).
     * Adjust via config('exam.max_recording_bytes_per_session') if needed.
     */
    public const MAX_SESSION_RECORDING_BYTES = 512 * 1024 * 1024;

    /**
     * Store an incoming video chunk safely with cryptographic integrity hashing.
     */
    public function storeChunk(
        OnlineExamSession $session,
        UploadedFile $file,
        int $chunkIndex = 0,
        float $duration = 0.0,
        bool $isFinal = false
    ): OnlineExamRecording {
        // Enforce per-session cumulative storage quota before accepting the chunk
        $maxBytes = config('exam.max_recording_bytes_per_session', self::MAX_SESSION_RECORDING_BYTES);
        $currentTotal = (int) ($session->total_recording_bytes ?? 0);
        $incomingSize = $file->getSize() ?: 0;

        if (($currentTotal + $incomingSize) > $maxBytes) {
            throw new \RuntimeException(
                sprintf(
                    'Session recording quota exceeded. Maximum allowed: %s MB per session.',
                    round($maxBytes / (1024 * 1024))
                )
            );
        }

        $hashDir = $this->getSessionHashDir($session);
        $realPath = $file->getRealPath();
        $sha256 = hash_file('sha256', $realPath);
        $fileSize = $file->getSize() ?: filesize($realPath);
        $mime = $file->getMimeType() ?: 'video/webm';

        $ext = 'webm';
        if (str_contains($mime, 'mp4')) {
            $ext = 'mp4';
        }

        $cleanHashPrefix = substr($sha256, 0, 12);
        $fileName = sprintf('chunk_%05d_%s.%s', $chunkIndex, $cleanHashPrefix, $ext);
        $relativeDir = "proctoring_recordings/{$session->online_exam_id}/{$hashDir}";
        $fullRelativePath = "{$relativeDir}/{$fileName}";

        // Save on private local disk
        Storage::disk($this->disk)->putFileAs($relativeDir, $file, $fileName);

        // Record in database
        $recording = OnlineExamRecording::create([
            'online_exam_session_id' => $session->id,
            'online_exam_id' => $session->online_exam_id,
            'student_id' => $session->student_id,
            'chunk_index' => $chunkIndex,
            'storage_disk' => $this->disk,
            'storage_path' => $fullRelativePath,
            'session_hash_dir' => $hashDir,
            'sha256_hash' => $sha256,
            'file_size_bytes' => $fileSize,
            'mime_type' => $mime,
            'duration_seconds' => $duration,
            'is_final' => $isFinal,
            'recorded_at' => now(),
        ]);

        // Update cumulative byte counter on the session
        $session->increment('total_recording_bytes', $fileSize);

        // Keep camera status recorded as ACTIVE
        if ($session->camera_status !== 'ACTIVE') {
            $session->update(['camera_status' => 'ACTIVE']);
        }

        return $recording;
    }

    /**
     * Store a periodic lightweight video frame snapshot for the live monitor.
     */
    public function storeSnapshot(OnlineExamSession $session, string $base64Data): string
    {
        // Strip data URI scheme if present (e.g. data:image/jpeg;base64,...)
        if (str_contains($base64Data, ',')) {
            $parts = explode(',', $base64Data, 2);
            $base64Data = $parts[1];
        }

        $imageData = base64_decode($base64Data);
        if ($imageData === false || strlen($imageData) === 0) {
            throw new \InvalidArgumentException('Invalid base64 snapshot data provided.');
        }

        // Magic-byte validation: only accept JPEG (FFD8FF) or PNG (89504E47) binaries
        $magicBytes = substr($imageData, 0, 4);
        $isJpeg = str_starts_with($magicBytes, "\xFF\xD8\xFF");
        $isPng  = $magicBytes === "\x89PNG";
        if (! $isJpeg && ! $isPng) {
            throw new \InvalidArgumentException('Snapshot data is not a valid JPEG or PNG image.');
        }

        $hashDir = $this->getSessionHashDir($session);
        $relativeDir = "proctoring_snapshots/{$session->online_exam_id}/{$hashDir}";
        $fileName = 'latest.jpg';
        $fullRelativePath = "{$relativeDir}/{$fileName}";

        Storage::disk($this->disk)->put($fullRelativePath, $imageData);

        if ($session->camera_status !== 'ACTIVE') {
            $session->update(['camera_status' => 'ACTIVE']);
        }

        return $fullRelativePath;
    }

    /**
     * Get the relative disk path for the latest snapshot of a session.
     */
    public function getLatestSnapshotPath(OnlineExamSession $session): ?string
    {
        $hashDir = $this->getSessionHashDir($session);
        $fullRelativePath = "proctoring_snapshots/{$session->online_exam_id}/{$hashDir}/latest.jpg";

        if (Storage::disk($this->disk)->exists($fullRelativePath)) {
            return $fullRelativePath;
        }

        return null;
    }

    /**
     * Retrieve the binary content of the latest snapshot.
     */
    public function getLatestSnapshotContent(OnlineExamSession $session): ?string
    {
        $path = $this->getLatestSnapshotPath($session);
        if ($path) {
            return Storage::disk($this->disk)->get($path);
        }

        return null;
    }

    /**
     * Retrieve all recorded chunks for an exam session in chronological order.
     */
    public function getSessionRecordings(OnlineExamSession $session)
    {
        return OnlineExamRecording::where('online_exam_session_id', $session->id)
            ->orderBy('chunk_index')
            ->get();
    }
}
