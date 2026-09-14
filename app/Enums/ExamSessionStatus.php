<?php

namespace App\Enums;

enum ExamSessionStatus: string
{
    case NOT_STARTED = 'NOT_STARTED';
    case READY = 'READY';
    case IN_PROGRESS = 'IN_PROGRESS';
    case QUESTION_ACTIVE = 'QUESTION_ACTIVE';
    case ANSWERED = 'ANSWERED';
    case QUESTION_TIMEOUT = 'QUESTION_TIMEOUT';
    case SUBMITTED = 'SUBMITTED';
    case EXPIRED = 'EXPIRED';
    case TERMINATED = 'TERMINATED';

    public function label(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'Not Started',
            self::READY => 'Ready',
            self::IN_PROGRESS => 'In Progress',
            self::QUESTION_ACTIVE => 'Answering Question',
            self::ANSWERED => 'Answered',
            self::QUESTION_TIMEOUT => 'Question Timed Out',
            self::SUBMITTED => 'Submitted',
            self::EXPIRED => 'Timed Out',
            self::TERMINATED => 'Terminated',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
            self::READY => 'bg-sky-500/10 text-sky-400 border-sky-500/30',
            self::IN_PROGRESS => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            self::QUESTION_ACTIVE => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
            self::ANSWERED => 'bg-teal-500/10 text-teal-400 border-teal-500/30',
            self::QUESTION_TIMEOUT => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            self::SUBMITTED => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
            self::EXPIRED => 'bg-orange-500/10 text-orange-400 border-orange-500/30',
            self::TERMINATED => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::SUBMITTED, self::EXPIRED, self::TERMINATED], true);
    }
}
