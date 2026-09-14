<?php

namespace App\Services;

use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use App\Enums\SpeedBonusFormula;

class SpeedBonusService
{
    /**
     * Calculate speed bonus for a correctly answered question.
     *
     * @param OnlineExam $exam
     * @param OnlineExamQuestion $examQuestion
     * @param int $timeSpentMs Time spent in milliseconds
     * @param bool $isCorrect Whether the answer was evaluated as correct
     * @param float $currentTotalBonus Accumulated speed bonus so far in the session
     * @return float Awarded speed bonus, rounded to 2 decimal places
     */
    public function calculateBonus(
        OnlineExam $exam,
        OnlineExamQuestion $examQuestion,
        int $timeSpentMs,
        bool $isCorrect,
        float $currentTotalBonus = 0.0
    ): float {
        // 1. If speed bonus is disabled or answer is incorrect, no bonus
        if (!$exam->enable_speed_bonus || !$isCorrect) {
            return 0.00;
        }

        // 2. Question time limit in milliseconds
        $limitSeconds = $examQuestion->time_limit_seconds ?: $exam->default_question_time_limit;
        $limitMs = $limitSeconds * 1000;

        // If time spent exceeded or equaled the limit, no bonus
        if ($timeSpentMs <= 0 || $timeSpentMs >= $limitMs) {
            return 0.00;
        }

        $maxBonusPerQuestion = (float) $exam->max_bonus_per_question;
        $maxTotalBonus = (float) $exam->max_total_bonus;

        // If student has already reached the total bonus cap
        if ($currentTotalBonus >= $maxTotalBonus) {
            return 0.00;
        }

        $timeSavedRatio = ($limitMs - $timeSpentMs) / $limitMs; // e.g. 0.60 if 60% time saved
        $formula = $exam->speed_bonus_formula ?: 'linear';
        $rawBonus = 0.0;

        switch ($formula) {
            case SpeedBonusFormula::TIER->value:
            case 'tier':
                // Tier based slabs:
                if ($timeSpentMs <= ($limitMs * 0.25)) {
                    $rawBonus = $maxBonusPerQuestion; // 100% of bonus
                } elseif ($timeSpentMs <= ($limitMs * 0.50)) {
                    $rawBonus = $maxBonusPerQuestion * 0.60; // 60% of bonus
                } elseif ($timeSpentMs <= ($limitMs * 0.75)) {
                    $rawBonus = $maxBonusPerQuestion * 0.30; // 30% of bonus
                } else {
                    $rawBonus = 0.0;
                }
                break;

            case SpeedBonusFormula::PERCENTAGE->value:
            case 'percentage':
                // Proportional to question marks
                $rawBonus = (float) $examQuestion->marks * 0.20 * $timeSavedRatio;
                break;

            case SpeedBonusFormula::LINEAR->value:
            case 'linear':
            default:
                // Linear: faster submission linearly increases bonus up to max_bonus_per_question
                $rawBonus = $maxBonusPerQuestion * $timeSavedRatio;
                break;
        }

        // Cap 1: Question bonus cap
        $cappedQuestionBonus = min($rawBonus, $maxBonusPerQuestion);

        // Cap 2: Total bonus cap
        $remainingAllowedTotal = max(0.0, $maxTotalBonus - $currentTotalBonus);
        $finalBonus = min($cappedQuestionBonus, $remainingAllowedTotal);

        return round($finalBonus, 2);
    }
}
