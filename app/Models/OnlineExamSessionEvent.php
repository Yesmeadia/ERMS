<?php

namespace App\Models;

use App\Enums\ExamEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineExamSessionEvent extends Model
{
    public $timestamps = false;

    protected $table = 'online_exam_session_events';

    protected $fillable = [
        'online_exam_session_id',
        'student_id',
        'event_type',
        'event_time',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'event_type' => ExamEventType::class,
        'event_time' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'is_violation',
    ];

    /**
     * Determine if this event constitutes a security violation.
     */
    public function getIsViolationAttribute(): bool
    {
        return $this->event_type instanceof ExamEventType && $this->event_type->isViolation();
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(OnlineExamSession::class, 'online_exam_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
