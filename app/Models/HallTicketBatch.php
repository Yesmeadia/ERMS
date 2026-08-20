<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class HallTicketBatch extends Model
{
    use HasFactory;

    protected $table = 'hall_ticket_batches';

    protected $fillable = [
        'batch_uuid',
        'school_id',
        'examination_id',
        'requested_by',
        'total_students',
        'total_parts',
        'completed_parts',
        'failed_parts',
        'completed_students',
        'failed_students',
        'status',
        'filter_criteria',
        'error_message',
        'started_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'filter_criteria' => 'array',
        'total_students' => 'integer',
        'total_parts' => 'integer',
        'completed_parts' => 'integer',
        'failed_parts' => 'integer',
        'completed_students' => 'integer',
        'failed_students' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($batch) {
            if (empty($batch->batch_uuid)) {
                $batch->batch_uuid = (string) Str::uuid();
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class, 'examination_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(HallTicketPdfPart::class, 'batch_id')->orderBy('part_number');
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_students <= 0) {
            return 0;
        }

        $processed = $this->completed_students + $this->failed_students;
        return (int) min(100, round(($processed / $this->total_students) * 100));
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'completed_with_errors'], true);
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Idempotently recalculate and synchronize batch progress counters from part records.
     * Prevents counter drift, double counting, and race conditions between concurrent workers.
     */
    public function recalculateProgress(): void
    {
        $parts = $this->parts()->get(['id', 'status', 'total_students', 'completed_students']);

        $completedParts = $parts->where('status', 'completed')->count();
        $failedParts = $parts->where('status', 'failed')->count();
        $completedStudents = (int) $parts->where('status', 'completed')->sum('completed_students');
        $failedStudents = (int) $parts->where('status', 'failed')->sum('total_students');

        $this->completed_parts = $completedParts;
        $this->failed_parts = $failedParts;
        $this->completed_students = $completedStudents;
        $this->failed_students = $failedStudents;

        $totalParts = (int) $this->total_parts;

        if ($totalParts > 0 && ($completedParts + $failedParts >= $totalParts)) {
            if ($failedParts === 0) {
                $this->status = 'completed';
            } elseif ($completedParts > 0) {
                $this->status = 'completed_with_errors';
            } else {
                $this->status = 'failed';
            }

            if (!$this->completed_at) {
                $this->completed_at = now();
            }
        } elseif ($parts->whereIn('status', ['processing', 'completed'])->count() > 0) {
            $this->status = 'processing';
            if (!$this->started_at) {
                $this->started_at = now();
            }
        } elseif ($parts->where('status', 'pending')->count() === $totalParts) {
            $this->status = 'pending';
        }

        $this->save();
    }
}
