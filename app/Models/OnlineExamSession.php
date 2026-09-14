<?php

namespace App\Models;

use App\Enums\ExamSessionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OnlineExamSession extends Model
{
    protected $table = 'online_exam_sessions';

    protected $fillable = [
        'online_exam_id',
        'student_id',
        'registration_number',
        'session_token',
        'session_version',
        'device_fingerprint_hash',
        'status',
        'question_order',
        'current_question_id',
        'current_question_index',
        'current_question_started_at',
        'current_question_deadline_at',
        'exam_started_at',
        'exam_deadline_at',
        'last_heartbeat_at',
        'last_activity_at',
        'completed_at',
        'locked_at',
        'camera_status',
        'fullscreen_status',
        'violations_count',
        'termination_reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'session_version' => 'integer',
        'status' => ExamSessionStatus::class,
        'question_order' => 'array',
        'current_question_index' => 'integer',
        'current_question_started_at' => 'datetime',
        'current_question_deadline_at' => 'datetime',
        'exam_started_at' => 'datetime',
        'exam_deadline_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'completed_at' => 'datetime',
        'locked_at' => 'datetime',
        'fullscreen_status' => 'boolean',
        'violations_count' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class, 'online_exam_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function currentQuestion(): BelongsTo
    {
        return $this->belongsTo(OnlineQuestion::class, 'current_question_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(OnlineExamSessionEvent::class, 'online_exam_session_id')->latest('event_time');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(OnlineExamAnswer::class, 'online_exam_session_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(OnlineExamResult::class, 'online_exam_session_id');
    }

    /**
     * Check if the session is currently active and sending heartbeats.
     * Considers heartbeats within the last 45 seconds as actively connected.
     */
    public function isHeartbeatActive(): bool
    {
        if (! $this->last_heartbeat_at) {
            return false;
        }

        return $this->last_heartbeat_at->diffInSeconds(now()) <= 45;
    }

    /**
     * Check if exam has reached overall expiration deadline.
     */
    public function hasExamExpired(): bool
    {
        if (! $this->exam_deadline_at) {
            return false;
        }

        return now()->isAfter($this->exam_deadline_at);
    }

    /**
     * Check if current question has expired.
     */
    public function hasCurrentQuestionExpired(): bool
    {
        if (! $this->current_question_deadline_at) {
            return false;
        }

        return now()->isAfter($this->current_question_deadline_at);
    }
}
