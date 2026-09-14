<?php

namespace App\Enums;

enum ExamStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case ACTIVE = 'ACTIVE';
    case COMPLETED = 'COMPLETED';
    case RESULT_PUBLISHED = 'RESULT_PUBLISHED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::ACTIVE => 'Active',
            self::COMPLETED => 'Completed',
            self::RESULT_PUBLISHED => 'Results Published',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-slate-700/50 text-slate-300 border-slate-600',
            self::PUBLISHED => 'bg-sky-500/10 text-sky-400 border-sky-500/30',
            self::ACTIVE => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            self::COMPLETED => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
            self::RESULT_PUBLISHED => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            self::CANCELLED => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
        };
    }
}
