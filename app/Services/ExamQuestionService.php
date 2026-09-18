<?php

namespace App\Services;

use App\Models\OnlineExamSession;
use App\Models\OnlineQuestion;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineExamAnswer;
use App\Enums\ExamSessionStatus;
use App\Enums\ExamEventType;
use App\Enums\QuestionType;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ExamQuestionService
{
    public function __construct(
        protected ExamTimerService $timerService,
        protected ExamScoringService $scoringService,
        protected AntiCheatingService $antiCheatingService
    ) {}

    /**
     * Start a specific question for the student session.
     */
    public function startQuestion(OnlineExamSession $session, int $questionId): array
    {
        $exam = $session->exam;
        $examQuestion = OnlineExamQuestion::where('online_exam_id', $exam->id)
            ->where('question_id', $questionId)
            ->firstOrFail();

        $question = OnlineQuestion::with(['options', 'images'])->findOrFail($questionId);

        $now = now();
        $deadline = $this->timerService->calculateQuestionDeadline($exam, $examQuestion, $now);

        $session->update([
            'current_question_id' => $question->id,
            'current_question_started_at' => $now,
            'current_question_deadline_at' => $deadline,
            'status' => ExamSessionStatus::QUESTION_ACTIVE,
            'last_activity_at' => $now,
        ]);

        $this->antiCheatingService->recordEvent($session, ExamEventType::QUESTION_STARTED, [
            'question_id' => $question->id,
            'question_index' => $session->current_question_index + 1,
        ]);

        return $this->buildSafeQuestionPayload($question, $examQuestion, $session);
    }

    /**
     * Build safe question payload with ZERO answer leakage.
     * The `is_correct` column is strictly omitted from all options.
     */
    public function buildSafeQuestionPayload(
        OnlineQuestion $question,
        OnlineExamQuestion $examQuestion,
        OnlineExamSession $session
    ): array {
        $exam = $session->exam;
        $questionOrder = $session->question_order ?: [];
        $totalQuestions = count($questionOrder);
        $currentIndex = $session->current_question_index;

        // Shuffle options if exam has randomize_options enabled
        $options = $question->options;
        if ($exam->randomize_options) {
            $options = $options->shuffle();
        }

        // Build sanitized options array (empty for fill in blank questions)
        $safeOptions = $question->question_type === QuestionType::FILL_IN_BLANK
            ? []
            : $options->map(function ($opt) {
                return [
                    'id' => (int) $opt->id,
                    'identifier' => $opt->option_identifier,
                    'text' => $opt->option_text,
                ];
            })->values()->toArray();

        // Build safe images
        $safeImages = $question->images->map(function ($img) {
            return [
                'id' => (int) $img->id,
                'url' => $img->url,
            ];
        })->values()->toArray();

        // Check if question has already been answered
        $existingAnswer = OnlineExamAnswer::where('online_exam_session_id', $session->id)
            ->where('question_id', $question->id)
            ->first();

        $qLimitSec = $examQuestion->time_limit_seconds ?: $exam->default_question_time_limit;
        $remainingMs = $session->current_question_deadline_at
            ? $this->timerService->getRemainingMilliseconds($session->current_question_deadline_at)
            : ($qLimitSec * 1000);

        $examRemainingMs = $session->exam_deadline_at
            ? $this->timerService->getRemainingMilliseconds($session->exam_deadline_at)
            : ($exam->duration_minutes * 60 * 1000);

        return [
            'question_id' => (int) $question->id,
            'question_index' => $currentIndex + 1,
            'total_questions' => $totalQuestions,
            'question_type' => $question->question_type->value,
            'question_type_label' => $question->question_type->shortLabel(),
            'question_text' => $question->question_text,
            'marks' => (float) $examQuestion->marks,
            'negative_marks' => (float) $examQuestion->negative_marks,
            'time_limit_seconds' => $examQuestion->time_limit_seconds ?: $exam->default_question_time_limit,
            'remaining_ms' => $remainingMs,
            'exam_remaining_ms' => $examRemainingMs,
            'options' => $safeOptions,
            'images' => $safeImages,
            'has_previous' => $exam->allow_previous_question && $currentIndex > 0,
            'has_next' => $currentIndex < ($totalQuestions - 1),
            'is_last_question' => $currentIndex >= ($totalQuestions - 1),
            'is_already_saved' => (bool) $existingAnswer,
            'saved_option_ids' => $existingAnswer ? $existingAnswer->selected_option_ids : [],
            'saved_text_answer' => $existingAnswer ? $existingAnswer->text_answer : null,
        ];
    }

    /**
     * Submit and persist answer for the current question.
     * Enforces:
     * - Answer is locked upon submission.
     * - Status transitions to ANSWERED.
     * - Does NOT automatically move to next question.
     */
    public function submitAnswer(
        OnlineExamSession $session,
        int $questionId,
        ?array $selectedOptionIds,
        ?string $textAnswer,
        ?string $ip = null,
        ?string $userAgent = null,
        ?int $clientTimeSpentMs = null,
        bool $isTimeout = false
    ): array {
        return DB::transaction(function () use ($session, $questionId, $selectedOptionIds, $textAnswer, $ip, $userAgent, $clientTimeSpentMs, $isTimeout) {
            // Row-level lock on the session prevents concurrent answer submissions
            $lockedSession = OnlineExamSession::where('id', $session->id)->lockForUpdate()->firstOrFail();

            if ($lockedSession->status->isFinal()) {
                throw new AccessDeniedHttpException('This examination session has already been completed.');
            }

            if ($lockedSession->hasExamExpired()) {
                throw new AccessDeniedHttpException('Examination overall time has expired.');
            }

            // Idempotency check: if answer already submitted and locked, return safely
            $existingAnswer = OnlineExamAnswer::where('online_exam_session_id', $lockedSession->id)
                ->where('question_id', $questionId)
                ->where('is_locked', true)
                ->first();

            if ($existingAnswer) {
                return [
                    'success' => true,
                    'message' => 'Answer already saved.',
                    'question_id' => $questionId,
                    'time_spent_ms' => $existingAnswer->time_spent_milliseconds,
                    'is_last_question' => $lockedSession->current_question_index >= (count($lockedSession->question_order ?: []) - 1),
                ];
            }
            $exam = $session->exam;
            $examQuestion = OnlineExamQuestion::where('online_exam_id', $exam->id)
                ->where('question_id', $questionId)
                ->firstOrFail();

            $question = OnlineQuestion::with('options')->findOrFail($questionId);

            $now = now();
            $startedAt = $session->current_question_started_at ?: $now;
            $serverTimeSpentMs = $this->timerService->calculateTimeSpentMilliseconds($startedAt, $now);

            $limitSeconds = $examQuestion->time_limit_seconds ?: $exam->default_question_time_limit;
            $limitMs = $limitSeconds * 1000;

            // Prioritize student's exact client answered time if within allowable timer limits; fallback to server timer
            if ($clientTimeSpentMs !== null && $clientTimeSpentMs >= 0) {
                $timeSpentMs = min($limitMs, $clientTimeSpentMs);
            } else {
                $timeSpentMs = min($limitMs, $serverTimeSpentMs);
            }

            // If timeout occurred, answer awarded marks must strictly be 0
            if ($isTimeout) {
                $eval = [
                    'is_correct' => false,
                    'score_awarded' => 0.00,
                    'negative_marks_deducted' => 0.00,
                    'speed_bonus_awarded' => 0.00,
                    'evaluation_status' => 'TIMED_OUT',
                ];
            } else {
                // Accumulated speed bonus so far
                $currentTotalBonus = (float) $session->answers()->sum('speed_bonus_awarded');

                // Evaluate answer server-side
                $eval = $this->scoringService->evaluateQuestionAnswer(
                    $question,
                    $examQuestion,
                    $selectedOptionIds,
                    $textAnswer,
                    $exam,
                    $timeSpentMs,
                    $currentTotalBonus
                );
            }

            // Persist locked answer with exact answered time
            $answer = OnlineExamAnswer::updateOrCreate(
                [
                    'online_exam_session_id' => $session->id,
                    'question_id' => $questionId,
                ],
                [
                    'student_id' => $session->student_id,
                    'selected_option_ids' => $isTimeout ? null : ($selectedOptionIds ? array_values(array_map('intval', $selectedOptionIds)) : null),
                    'text_answer' => $isTimeout ? null : $textAnswer,
                    'submitted_at' => $now,
                    'time_spent_milliseconds' => $timeSpentMs,
                    'is_locked' => true,
                    'is_correct' => $eval['is_correct'],
                    'score_awarded' => $eval['score_awarded'],
                    'negative_marks_deducted' => $eval['negative_marks_deducted'],
                    'speed_bonus_awarded' => $eval['speed_bonus_awarded'],
                    'evaluation_status' => $eval['evaluation_status'],
                ]
            );

            // Transition session to ANSWERED
            $session->update([
                'status' => ExamSessionStatus::ANSWERED,
                'last_activity_at' => $now,
            ]);

            $this->antiCheatingService->recordEvent($session, ExamEventType::ANSWER_SAVED, [
                'question_id' => $questionId,
                'time_spent_ms' => $timeSpentMs,
                'answered_time_seconds' => round($timeSpentMs / 1000, 1),
                'submitted_at' => $now->toDateTimeString(),
            ], $ip, $userAgent);

            $this->antiCheatingService->recordEvent($session, ExamEventType::ANSWER_SUBMITTED, [
                'question_id' => $questionId,
            ], $ip, $userAgent);

            return [
                'success' => true,
                'message' => $isTimeout ? 'Question Time Expired' : 'Answer Saved Successfully',
                'question_id' => $questionId,
                'time_spent_ms' => $timeSpentMs,
                'answered_time_seconds' => round($timeSpentMs / 1000, 1),
                'is_last_question' => $session->current_question_index >= (count($session->question_order ?: []) - 1),
            ];
        });
    }

    /**
     * Advance to the next question.
     * Must be explicitly requested by the student via the [Next Question] button.
     */
    public function getNextQuestion(OnlineExamSession $session): ?array
    {
        $questionOrder = $session->question_order ?: [];
        $currentIndex = $session->current_question_index;
        $currentQuestionId = $questionOrder[$currentIndex] ?? null;

        // Ensure current question has an answer record with 0 marks if student advanced without submitting
        if ($currentQuestionId) {
            $hasAnswer = OnlineExamAnswer::where('online_exam_session_id', $session->id)
                ->where('question_id', $currentQuestionId)
                ->exists();

            if (!$hasAnswer) {
                OnlineExamAnswer::create([
                    'online_exam_session_id' => $session->id,
                    'student_id' => $session->student_id,
                    'question_id' => $currentQuestionId,
                    'selected_option_ids' => null,
                    'text_answer' => null,
                    'submitted_at' => now(),
                    'time_spent_milliseconds' => 0,
                    'is_locked' => true,
                    'is_correct' => false,
                    'score_awarded' => 0.00,
                    'negative_marks_deducted' => 0.00,
                    'speed_bonus_awarded' => 0.00,
                    'evaluation_status' => 'TIMED_OUT',
                ]);
            }
        }

        $total = count($questionOrder);
        $nextIndex = $currentIndex + 1;

        if ($nextIndex >= $total) {
            return null; // Reached end of questions
        }

        $session->update([
            'current_question_index' => $nextIndex,
        ]);

        $nextQuestionId = $questionOrder[$nextIndex];
        return $this->startQuestion($session, $nextQuestionId);
    }

    /**
     * Retreat to the previous question.
     * Enforced strictly by server authorization: only if allow_previous_question is enabled.
     */
    public function getPreviousQuestion(OnlineExamSession $session): ?array
    {
        if (!$session->exam->allow_previous_question) {
            throw new AccessDeniedHttpException('Navigating to previous questions is not permitted for this examination.');
        }

        $questionOrder = $session->question_order ?: [];
        $prevIndex = $session->current_question_index - 1;

        if ($prevIndex < 0) {
            return null;
        }

        $session->update([
            'current_question_index' => $prevIndex,
        ]);

        $prevQuestionId = $questionOrder[$prevIndex];
        return $this->startQuestion($session, $prevQuestionId);
    }
}
