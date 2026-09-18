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

        $maxBonusPerQuestion = (float) ($exam->max_bonus_per_question ?? 0);
        $maxTotalBonus = (float) ($exam->max_total_bonus ?? 0);
        $formula = $exam->speed_bonus_formula ?: 'remaining_seconds';

        // If total bonus cap is set and student has already reached it (bypassed for remaining_seconds)
        if ($maxTotalBonus > 0 && $formula !== 'remaining_seconds' && $formula !== SpeedBonusFormula::REMAINING_SECONDS->value && $currentTotalBonus >= $maxTotalBonus) {
            return 0.00;
        }

        $timeSavedRatio = ($limitMs - $timeSpentMs) / $limitMs; // e.g. 0.60 if 60% time saved
        $rawBonus = 0.0;

        switch ($formula) {
            case SpeedBonusFormula::TIER->value:
            case 'tier':
                // Tier based slabs:
                $base = $maxBonusPerQuestion > 0 ? $maxBonusPerQuestion : 1.0;
                if ($timeSpentMs <= ($limitMs * 0.25)) {
                    $rawBonus = $base; // 100% of bonus
                } elseif ($timeSpentMs <= ($limitMs * 0.50)) {
                    $rawBonus = $base * 0.60; // 60% of bonus
                } elseif ($timeSpentMs <= ($limitMs * 0.75)) {
                    $rawBonus = $base * 0.30; // 30% of bonus
                } else {
                    $rawBonus = 0.0;
                }
                break;

            case SpeedBonusFormula::PERCENTAGE->value:
            case 'percentage':
                // Proportional to question marks
                $rawBonus = (float) $examQuestion->marks * 0.20 * $timeSavedRatio;
                break;

            case SpeedBonusFormula::REMAINING_SECONDS->value:
            case 'remaining_seconds':
                // Remaining seconds / 100: e.g. 30s remaining → +0.30
                $remainingSeconds = max(0, floor(($limitMs - $timeSpentMs) / 1000));
                $rawBonus = $remainingSeconds / 100.0;
                break;

            case SpeedBonusFormula::LINEAR->value:
            case 'linear':
            default:
                // Linear: faster submission linearly increases bonus up to max_bonus_per_question
                $base = $maxBonusPerQuestion > 0 ? $maxBonusPerQuestion : 1.0;
                $rawBonus = $base * $timeSavedRatio;
                break;
        }

        // Cap 1: Question bonus cap (applied for linear/tier or if explicitly specified > 0 for remaining_seconds)
        if ($maxBonusPerQuestion > 0 && $formula !== 'remaining_seconds' && $formula !== SpeedBonusFormula::REMAINING_SECONDS->value) {
            $cappedQuestionBonus = min($rawBonus, $maxBonusPerQuestion);
        } else {
            $cappedQuestionBonus = $rawBonus;
        }

        // Cap 2: Total bonus cap (only applies if max_total_bonus > 0 and not remaining_seconds)
        if ($maxTotalBonus > 0 && $formula !== 'remaining_seconds' && $formula !== SpeedBonusFormula::REMAINING_SECONDS->value) {
            $remainingAllowedTotal = max(0.0, $maxTotalBonus - $currentTotalBonus);
            $finalBonus = min($cappedQuestionBonus, $remainingAllowedTotal);
        } else {
            $finalBonus = $cappedQuestionBonus;
        }

        return round(max(0.0, $finalBonus), 2);
    }
}
