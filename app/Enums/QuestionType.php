<?php

namespace App\Enums;

enum QuestionType: string
{
    case MCQ = 'MCQ';
    case TRUE_FALSE = 'TRUE_FALSE';
    case MULTIPLE_SELECT = 'MULTIPLE_SELECT';
    case FILL_IN_BLANK = 'FILL_IN_BLANK';

    public function label(): string
    {
        return match ($this) {
            self::MCQ => 'Multiple Choice (Single Answer)',
            self::TRUE_FALSE => 'True / False',
            self::MULTIPLE_SELECT => 'Multiple Select (Multiple Correct)',
            self::FILL_IN_BLANK => 'Fill in the Blank',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::MCQ => 'MCQ',
            self::TRUE_FALSE => 'True/False',
            self::MULTIPLE_SELECT => 'Multiple Select',
            self::FILL_IN_BLANK => 'Fill in Blank',
        };
    }
}
