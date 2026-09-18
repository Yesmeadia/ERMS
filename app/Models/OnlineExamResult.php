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

    public function getRawScoreAttribute(): float
    {
        return (float) $this->objective_marks;
    }

    public function getSpeedBonusPointsAttribute(): float
    {
        return (float) $this->speed_bonus_marks;
    }

    public function getUnansweredCountAttribute(): int
    {
        return (int) $this->total_unanswered;
    }

    public function getTimeTakenSecondsAttribute(): int
    {
        if ($this->session && $this->session->exam_started_at && $this->session->completed_at) {
            return (int) $this->session->exam_started_at->diffInSeconds($this->session->completed_at);
        }

        if ($this->session) {
            $sumMs = (int) $this->session->answers()->sum('time_spent_milliseconds');
            if ($sumMs > 0) {
                return (int) round($sumMs / 1000);
            }
        }

        return 0;
    }

    public function getTimeTakenFormattedAttribute(): string
    {
        $sec = $this->time_taken_seconds;
        if ($sec <= 0) {
            return '-';
        }
        $h = intdiv($sec, 3600);
        $m = intdiv($sec % 3600, 60);
        $s = $sec % 60;
        if ($h > 0) {
            return sprintf('%dh %02dm %02ds', $h, $m, $s);
        }
        return sprintf('%02dm %02ds', $m, $s);
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
