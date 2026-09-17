<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineExamAnswer extends Model
{
    protected $table = 'online_exam_answers';

    protected $fillable = [
        'online_exam_session_id',
        'student_id',
        'question_id',
        'selected_option_ids',
        'text_answer',
        'submitted_at',
        'time_spent_milliseconds',
        'is_locked',
        'is_correct',
        'score_awarded',
        'negative_marks_deducted',
        'speed_bonus_awarded',
        'evaluation_status',
    ];

    protected $casts = [
        'selected_option_ids' => 'array',
        'submitted_at' => 'datetime',
        'time_spent_milliseconds' => 'integer',
        'is_locked' => 'boolean',
        'is_correct' => 'boolean',
        'score_awarded' => 'decimal:2',
        'negative_marks_deducted' => 'decimal:2',
        'speed_bonus_awarded' => 'decimal:2',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(OnlineExamSession::class, 'online_exam_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(OnlineQuestion::class, 'question_id');
    }

    public function getTimeSpentSecondsAttribute(): float
    {
        return round(($this->time_spent_milliseconds ?: 0) / 1000, 1);
    }

    public function getAnsweredTimeFormattedAttribute(): string
    {
        $sec = (int) round(($this->time_spent_milliseconds ?: 0) / 1000);
        $m = intdiv($sec, 60);
        $s = $sec % 60;
        return sprintf('%02d:%02ds', $m, $s);
    }
}
