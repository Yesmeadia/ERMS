<?php

namespace App\Enums;

enum ExamEventType: string
{
    case LOGIN = 'LOGIN';
    case EXAM_STARTED = 'EXAM_STARTED';
    case FULLSCREEN_ENTER = 'FULLSCREEN_ENTER';
    case FULLSCREEN_EXIT = 'FULLSCREEN_EXIT';
    case WINDOW_BLUR = 'WINDOW_BLUR';
    case WINDOW_FOCUS = 'WINDOW_FOCUS';
    case TAB_SWITCH = 'TAB_SWITCH';
    case CAMERA_STARTED = 'CAMERA_STARTED';
    case CAMERA_STOPPED = 'CAMERA_STOPPED';
    case CAMERA_INTERRUPTED = 'CAMERA_INTERRUPTED';
    case CAMERA_RECONNECTED = 'CAMERA_RECONNECTED';
    case QUESTION_STARTED = 'QUESTION_STARTED';
    case ANSWER_SAVED = 'ANSWER_SAVED';
    case ANSWER_SUBMITTED = 'ANSWER_SUBMITTED';
    case QUESTION_TIMEOUT = 'QUESTION_TIMEOUT';
    case NEXT_QUESTION = 'NEXT_QUESTION';
    case PREVIOUS_QUESTION = 'PREVIOUS_QUESTION';
    case EXAM_SUBMITTED = 'EXAM_SUBMITTED';
    case EXAM_AUTO_SUBMITTED = 'EXAM_AUTO_SUBMITTED';
    case EXAM_TERMINATED = 'EXAM_TERMINATED';

    public function isViolation(): bool
    {
        return in_array($this, [
            self::FULLSCREEN_EXIT,
            self::WINDOW_BLUR,
            self::TAB_SWITCH,
            self::CAMERA_STOPPED,
            self::CAMERA_INTERRUPTED,
        ], true);
    }
}
