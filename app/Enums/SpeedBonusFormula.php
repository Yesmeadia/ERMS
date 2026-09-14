<?php

namespace App\Enums;

enum SpeedBonusFormula: string
{
    case LINEAR = 'linear';
    case TIER = 'tier';
    case PERCENTAGE = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::LINEAR => 'Linear Degradation',
            self::TIER => 'Tier-Based Slabs',
            self::PERCENTAGE => 'Percentage of Base Marks',
        };
    }
}
