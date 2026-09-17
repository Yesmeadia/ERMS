<?php

namespace App\Enums;

enum SpeedBonusFormula: string
{
    case LINEAR = 'linear';
    case TIER = 'tier';
    case PERCENTAGE = 'percentage';
    case REMAINING_SECONDS = 'remaining_seconds';

    public function label(): string
    {
        return match ($this) {
            self::LINEAR => 'Linear Degradation',
            self::TIER => 'Tier-Based Slabs',
            self::PERCENTAGE => 'Percentage of Base Marks',
            self::REMAINING_SECONDS => 'Remaining Seconds ÷ 100 (e.g. 30s left = +0.30)',
        };
    }
}
