<?php

namespace App\Services;

use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use Carbon\Carbon;

class ExamTimerService
{
    /**
     * Calculate effective overall exam deadline for a student.
     * The earlier of (started_at + duration_minutes) and global exam_end_datetime wins.
     */
    public function calculateStudentExamDeadline(OnlineExam $exam, Carbon $startedAt): Carbon
    {
        $durationDeadline = $startedAt->copy()->addMinutes($exam->duration_minutes);
        $globalExamEnd = $exam->end_date_time;

        return $durationDeadline->lessThan($globalExamEnd) ? $durationDeadline : $globalExamEnd;
    }

    /**
     * Calculate question-specific deadline.
     */
    public function calculateQuestionDeadline(OnlineExam $exam, OnlineExamQuestion $examQuestion, Carbon $startedAt): Carbon
    {
        $seconds = $examQuestion->time_limit_seconds ?: $exam->default_question_time_limit;
        return $startedAt->copy()->addSeconds($seconds);
    }

    /**
     * Check if a deadline has expired.
     */
    public function isExpired(?Carbon $deadline): bool
    {
        if (!$deadline) {
            return false;
        }

        return now()->isAfter($deadline);
    }

    /**
     * Calculate remaining time in milliseconds.
     */
    public function getRemainingMilliseconds(?Carbon $deadline): int
    {
        if (!$deadline) {
            return 0;
        }

        $now = now();
        if ($now->isAfter($deadline)) {
            return 0;
        }

        return (int) round($now->diffInRealMilliseconds($deadline));
    }

    /**
     * Calculate time spent in milliseconds between two timestamps.
     */
    public function calculateTimeSpentMilliseconds(Carbon $startedAt, Carbon $submittedAt): int
    {
        return max(0, (int) round($startedAt->diffInRealMilliseconds($submittedAt)));
    }
}
