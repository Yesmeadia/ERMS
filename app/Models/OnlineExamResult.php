<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineExamResult extends Model
{
    protected $table = 'online_exam_results';

    protected $fillable = [
        'online_exam_session_id',
        'online_exam_id',
        'student_id',
        'registration_number',
        'total_questions',
        'total_attempted',
        'total_correct',
        'total_wrong',
        'total_unanswered',
        'objective_marks',
        'speed_bonus_marks',
        'negative_marks',
        'final_score',
        'percentage',
        'grade',
        'rank',
        'status',
        'published_at',
    ];

    protected $casts = [
        'total_questions' => 'integer',
        'total_attempted' => 'integer',
        'total_correct' => 'integer',
        'total_wrong' => 'integer',
        'total_unanswered' => 'integer',
        'objective_marks' => 'decimal:2',
        'speed_bonus_marks' => 'decimal:2',
        'negative_marks' => 'decimal:2',
        'final_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'rank' => 'integer',
        'published_at' => 'datetime',
    ];

    public function getIsPassedAttribute(): bool
    {
        return $this->status === 'PASS';
    }

    public function getTotalMarksAttribute(): float
    {
        return (float) ($this->exam->total_marks ?? 100.00);
    }

    public function getCorrectAnswersCountAttribute(): int
    {
        return (int) $this->total_correct;
    }

    public function getWrongAnswersCountAttribute(): int
    {
        return (int) $this->total_wrong;
    }

    public function getAttemptedQuestionsCountAttribute(): int
    {
        return (int) $this->total_attempted;
    }

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
}
