<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OnlineExamRecording extends Model
{
    protected $table = 'online_exam_recordings';

    protected $fillable = [
        'online_exam_session_id',
        'online_exam_id',
        'student_id',
        'chunk_index',
        'storage_disk',
        'storage_path',
        'session_hash_dir',
        'sha256_hash',
        'file_size_bytes',
        'mime_type',
        'duration_seconds',
        'is_final',
        'recorded_at',
    ];

    protected $casts = [
        'chunk_index' => 'integer',
        'file_size_bytes' => 'integer',
        'duration_seconds' => 'decimal:2',
        'is_final' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(OnlineExamSession::class, 'online_exam_session_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class, 'online_exam_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Check if the recorded file physically exists on the assigned disk.
     */
    public function fileExists(): bool
    {
        return Storage::disk($this->storage_disk)->exists($this->storage_path);
    }

    /**
     * Verify cryptographic SHA-256 integrity of the stored recording file.
     */
    public function verifyHash(): bool
    {
        if (! $this->fileExists()) {
            return false;
        }

        $fullPath = Storage::disk($this->storage_disk)->path($this->storage_path);
        if (! file_exists($fullPath)) {
            return false;
        }

        return hash_file('sha256', $fullPath) === $this->sha256_hash;
    }
}
