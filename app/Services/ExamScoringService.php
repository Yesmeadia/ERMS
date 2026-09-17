<?php

namespace App\Services;

use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineQuestion;
use App\Models\OnlineExamSession;
use App\Models\OnlineExamResult;
use App\Enums\QuestionType;
use Illuminate\Support\Facades\DB;

class ExamScoringService
{
    public function __construct(
        protected SpeedBonusService $speedBonusService
    ) {}

    /**
     * Evaluate an answer for an individual question.
     *
     * @param OnlineQuestion $question
     * @param OnlineExamQuestion $examQuestion
     * @param array|null $selectedOptionIds Array of integer IDs selected by the student
     * @param string|null $textAnswer Text submitted for fill-in-blank
     * @param OnlineExam $exam
     * @param int $timeSpentMs Time spent in milliseconds
     * @param float $currentTotalBonus Total speed bonus earned so far in this session
     * @return array [is_correct, score_awarded, negative_marks_deducted, speed_bonus_awarded, evaluation_status]
     */
    public function evaluateQuestionAnswer(
        OnlineQuestion $question,
        OnlineExamQuestion $examQuestion,
        ?array $selectedOptionIds,
        ?string $textAnswer,
        OnlineExam $exam,
        int $timeSpentMs = 0,
        float $currentTotalBonus = 0.0
    ): array {
        $marks = (float) $examQuestion->marks;
        $negativeMarks = (float) $examQuestion->negative_marks;

        $isCorrect = false;
        $scoreAwarded = 0.00;
        $negativeDeducted = 0.00;
        $speedBonus = 0.00;

        $selectedOptionIds = array_filter(array_map('intval', (array) $selectedOptionIds));

        switch ($question->question_type) {
            case QuestionType::MCQ:
            case QuestionType::TRUE_FALSE:
                $correctOption = $question->options()->where('is_correct', true)->first();
                $correctId = $correctOption ? (int) $correctOption->id : null;

                if (!empty($selectedOptionIds)) {
                    $selectedId = (int) reset($selectedOptionIds);
                    if ($correctId && $selectedId === $correctId) {
                        $isCorrect = true;
                        $scoreAwarded = $marks;
                    } else {
                        $isCorrect = false;
                        $negativeDeducted = $negativeMarks;
                    }
                }
                break;

            case QuestionType::MULTIPLE_SELECT:
                $correctOptionIds = $question->options()
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->map(fn($id) => (int)$id)
                    ->toArray();

                sort($correctOptionIds);
                $selected = $selectedOptionIds;
                sort($selected);

                $criteria = $question->multiple_select_criteria ?: 'ALL_CORRECT';

                if (!empty($selected)) {
                    if ($selected === $correctOptionIds) {
                        $isCorrect = true;
                        $scoreAwarded = $marks;
                    } else {
                        // Partial or penalty logic
                        if ($criteria === 'ALL_CORRECT') {
                            $isCorrect = false;
                            $negativeDeducted = $negativeMarks;
                        } elseif ($criteria === 'PARTIAL_WITH_PENALTY') {
                            $totalCorrectCount = count($correctOptionIds);
                            if ($totalCorrectCount > 0) {
                                $markPerOption = $marks / $totalCorrectCount;
                                $correctSelected = count(array_intersect($selected, $correctOptionIds));
                                $wrongSelected = count(array_diff($selected, $correctOptionIds));

                                $partialScore = ($correctSelected * $markPerOption) - ($wrongSelected * $negativeMarks);
                                $scoreAwarded = max(0.00, round($partialScore, 2));
                                $isCorrect = $scoreAwarded > 0;
                            }
                        } elseif ($criteria === 'PARTIAL_NO_PENALTY') {
                            $totalCorrectCount = count($correctOptionIds);
                            if ($totalCorrectCount > 0) {
                                $markPerOption = $marks / $totalCorrectCount;
                                $correctSelected = count(array_intersect($selected, $correctOptionIds));
                                $scoreAwarded = round($correctSelected * $markPerOption, 2);
                                $isCorrect = $scoreAwarded > 0;
                            }
                        }
                    }
                }
                break;

            case QuestionType::FILL_IN_BLANK:
                $correctOptions = $question->relationLoaded('options')
                    ? $question->options->where('is_correct', true)
                    : $question->options()->where('is_correct', true)->get();

                // Normalize student's input: collapse whitespace, trim, lowercase UTF-8
                $actual = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $textAnswer)), 'UTF-8');

                if ($actual !== '') {
                    $matched = false;
                    foreach ($correctOptions as $cOpt) {
                        $expectedRaw = (string) ($cOpt->option_text ?? '');

                        // Support alternative answers separated by comma, pipe, or semicolon
                        $alternatives = preg_split('/[,|;]/', $expectedRaw);
                        $alternatives[] = $expectedRaw;

                        foreach ($alternatives as $alt) {
                            $normAlt = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $alt)), 'UTF-8');
                            if ($normAlt !== '' && $actual === $normAlt) {
                                $matched = true;
                                break 2;
                            }
                        }
                    }

                    if ($matched) {
                        $isCorrect = true;
                        $scoreAwarded = $marks;
                    } else {
                        $isCorrect = false;
                        $negativeDeducted = $negativeMarks;
                    }
                }
                break;
        }

        // Calculate speed bonus if correct
        if ($isCorrect) {
            $speedBonus = $this->speedBonusService->calculateBonus(
                $exam,
                $examQuestion,
                $timeSpentMs,
                true,
                $currentTotalBonus
            );
        }

        return [
            'is_correct' => $isCorrect,
            'score_awarded' => round($scoreAwarded, 2),
            'negative_marks_deducted' => round($negativeDeducted, 2),
            'speed_bonus_awarded' => round($speedBonus, 2),
            'evaluation_status' => 'EVALUATED',
        ];
    }

    /**
     * Finalize and compute result for a completed student session.
     */
    public function finalizeResult(OnlineExamSession $session): OnlineExamResult
    {
        return DB::transaction(function () use ($session) {
            $exam = $session->exam;
            $totalQuestions = $exam->examQuestions()->count();

            $answers = $session->answers()->get();
            $attemptedAnswers = $answers->filter(fn($a) => !empty($a->selected_option_ids) || !empty($a->text_answer));

            $totalAttempted = $attemptedAnswers->count();
            $totalCorrect = $attemptedAnswers->where('is_correct', true)->count();
            $totalWrong = $attemptedAnswers->where('is_correct', false)->count();
            $totalUnanswered = max(0, $totalQuestions - $totalAttempted);

            $objectiveMarks = (float) $answers->sum('score_awarded');
            $speedBonusMarks = (float) $answers->sum('speed_bonus_awarded');
            $negativeMarks = (float) $answers->sum('negative_marks_deducted');

            // Final score formula: Objective + Speed Bonus - Negative Marks
            $finalScore = max(0.00, round(($objectiveMarks + $speedBonusMarks) - $negativeMarks, 2));

            $totalMarks = (float) $exam->total_marks > 0 ? (float) $exam->total_marks : 100.00;
            $percentage = round(($finalScore / $totalMarks) * 100, 2);

            $passMarks = (float) $exam->pass_marks;
            $status = ($finalScore >= $passMarks) ? 'PASS' : 'FAIL';

            $grade = $this->calculateGrade($percentage);

            $result = OnlineExamResult::updateOrCreate(
                [
                    'online_exam_id' => $exam->id,
                    'student_id' => $session->student_id,
                ],
                [
                    'online_exam_session_id' => $session->id,
                    'registration_number' => $session->registration_number,
                    'total_questions' => $totalQuestions,
                    'total_attempted' => $totalAttempted,
                    'total_correct' => $totalCorrect,
                    'total_wrong' => $totalWrong,
                    'total_unanswered' => $totalUnanswered,
                    'objective_marks' => $objectiveMarks,
                    'speed_bonus_marks' => $speedBonusMarks,
                    'negative_marks' => $negativeMarks,
                    'final_score' => $finalScore,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'status' => $status,
                    'published_at' => $exam->show_result_immediately ? now() : null,
                ]
            );

            // Dynamically recalculate ranks for this exam
            $this->recalculateRanks($exam);

            return $result->fresh();
        });
    }

    /**
     * Recalculate ranks across all results for an exam.
     */
    public function recalculateRanks(OnlineExam $exam): void
    {
        $results = OnlineExamResult::where('online_exam_id', $exam->id)
            ->orderByDesc('final_score')
            ->orderByDesc('speed_bonus_marks')
            ->orderByDesc('total_correct')
            ->orderBy('id')
            ->pluck('id');

        if ($results->isEmpty()) {
            return;
        }

        // Build a single CASE statement update — O(1) queries regardless of student count
        $cases = [];
        $ids   = [];
        foreach ($results as $rank => $id) {
            $cases[] = 'WHEN '.((int) $id).' THEN '.($rank + 1);
            $ids[]   = (int) $id;
        }

        $caseExpr = 'CASE id '.implode(' ', $cases).' END';
        DB::table('online_exam_results')
            ->whereIn('id', $ids)
            ->update(['rank' => DB::raw($caseExpr)]);
    }

    /**
     * Calculate letter grade based on percentage.
     */
    protected function calculateGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            default => 'F',
        };
    }
}
