<?php

namespace Tests\Feature\OnlineExam;

use App\Enums\ExamSessionStatus;
use App\Enums\QuestionType;
use App\Models\OnlineExam;
use App\Models\OnlineExamAnswer;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineExamResult;
use App\Models\OnlineExamSession;
use App\Models\OnlineQuestion;
use App\Models\OnlineQuestionOption;
use App\Models\Student;
use App\Services\ExamScoringService;

class ScoringEngineTest extends OnlineExamTestCase
{
    protected ExamScoringService $scoringService;

    protected OnlineExam $exam;

    protected Student $student;

    protected OnlineExamSession $session;

    protected OnlineQuestion $q1;

    protected OnlineQuestion $q2;

    protected OnlineQuestionOption $opt1Correct;

    protected OnlineQuestionOption $opt2Wrong;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scoringService = app(ExamScoringService::class);

        $this->exam = $this->createOnlineExam([
            'total_marks' => 20,
            'pass_marks' => 8,
        ]);

        $this->student = $this->createStudent();
        $this->enrollStudent($this->exam, $this->student);

        // Question 1: +10 marks, -2 negative marks
        $this->q1 = OnlineQuestion::create([
            'category_id' => $this->testCategory->id,
            'question_type' => QuestionType::MCQ,
            'question_text' => 'Q1 text',
            'default_marks' => 10,
            'negative_marks' => 2,
            'difficulty' => 'EASY',
            'is_active' => true,
        ]);
        $this->opt1Correct = OnlineQuestionOption::create([
            'question_id' => $this->q1->id,
            'option_identifier' => 'A',
            'option_text' => 'Correct Ans',
            'is_correct' => true,
            'sort_order' => 1,
        ]);
        OnlineExamQuestion::create([
            'online_exam_id' => $this->exam->id,
            'question_id' => $this->q1->id,
            'sort_order' => 1,
            'marks' => 10,
            'negative_marks' => 2,
        ]);

        // Question 2: +10 marks, -2 negative marks
        $this->q2 = OnlineQuestion::create([
            'category_id' => $this->testCategory->id,
            'question_type' => QuestionType::MCQ,
            'question_text' => 'Q2 text',
            'default_marks' => 10,
            'negative_marks' => 2,
            'difficulty' => 'EASY',
            'is_active' => true,
        ]);
        $this->opt2Wrong = OnlineQuestionOption::create([
            'question_id' => $this->q2->id,
            'option_identifier' => 'B',
            'option_text' => 'Wrong Ans',
            'is_correct' => false,
            'sort_order' => 2,
        ]);
        OnlineExamQuestion::create([
            'online_exam_id' => $this->exam->id,
            'question_id' => $this->q2->id,
            'sort_order' => 2,
            'marks' => 10,
            'negative_marks' => 2,
        ]);

        $this->session = OnlineExamSession::create([
            'online_exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'registration_number' => $this->student->registration_number,
            'session_token' => 'score_tok_'.rand(1000, 9999),
            'status' => ExamSessionStatus::IN_PROGRESS,
            'question_order' => [$this->q1->id, $this->q2->id],
            'current_question_index' => 1,
            'started_at' => now()->subMinutes(10),
            'last_activity_at' => now(),
        ]);
    }

    public function test_correct_answer_evaluation_awards_full_marks(): void
    {
        $examQ1 = OnlineExamQuestion::where('online_exam_id', $this->exam->id)->where('question_id', $this->q1->id)->first();
        $eval = $this->scoringService->evaluateQuestionAnswer(
            $this->q1,
            $examQ1,
            [$this->opt1Correct->id],
            null,
            $this->exam,
            5000
        );

        $this->assertTrue($eval['is_correct']);
        $this->assertEquals(10.00, $eval['score_awarded']);
        $this->assertEquals(0.00, $eval['negative_marks_deducted']);
    }

    public function test_wrong_answer_deducts_negative_marks(): void
    {
        $examQ2 = OnlineExamQuestion::where('online_exam_id', $this->exam->id)->where('question_id', $this->q2->id)->first();
        $eval = $this->scoringService->evaluateQuestionAnswer(
            $this->q2,
            $examQ2,
            [$this->opt2Wrong->id],
            null,
            $this->exam,
            5000
        );

        $this->assertFalse($eval['is_correct']);
        $this->assertEquals(0.00, $eval['score_awarded']);
        $this->assertEquals(2.00, $eval['negative_marks_deducted']);
    }

    public function test_finalizing_session_calculates_and_persists_result(): void
    {
        // Answer Q1 Correct (+10)
        OnlineExamAnswer::create([
            'online_exam_session_id' => $this->session->id,
            'student_id' => $this->student->id,
            'question_id' => $this->q1->id,
            'selected_option_ids' => [$this->opt1Correct->id],
            'is_locked' => true,
            'is_correct' => true,
            'score_awarded' => 10.00,
            'negative_marks_deducted' => 0.00,
            'speed_bonus_awarded' => 0.00,
            'time_spent_milliseconds' => 15000,
            'submitted_at' => now(),
            'evaluation_status' => 'AUTO_EVALUATED',
        ]);

        // Answer Q2 Wrong (-2)
        OnlineExamAnswer::create([
            'online_exam_session_id' => $this->session->id,
            'student_id' => $this->student->id,
            'question_id' => $this->q2->id,
            'selected_option_ids' => [$this->opt2Wrong->id],
            'is_locked' => true,
            'is_correct' => false,
            'score_awarded' => 0.00,
            'negative_marks_deducted' => 2.00,
            'speed_bonus_awarded' => 0.00,
            'time_spent_milliseconds' => 20000,
            'submitted_at' => now(),
            'evaluation_status' => 'AUTO_EVALUATED',
        ]);

        $result = $this->scoringService->finalizeResult($this->session);

        $this->assertInstanceOf(OnlineExamResult::class, $result);
        $this->assertEquals(8.00, $result->final_score); // 10 - 2 = 8
        $this->assertEquals(20.00, $result->total_marks);
        $this->assertEquals(40.0, $result->percentage); // 8 / 20 = 40%
        $this->assertTrue((bool) $result->is_passed); // pass_marks = 8
        $this->assertEquals(1, $result->correct_answers_count);
        $this->assertEquals(1, $result->wrong_answers_count);
        $this->assertEquals(2, $result->attempted_questions_count);
    }

    public function test_fill_in_blank_evaluation_is_case_insensitive_and_whitespace_tolerant(): void
    {
        $qBlank = OnlineQuestion::create([
            'category_id' => $this->testCategory->id,
            'question_type' => QuestionType::FILL_IN_BLANK,
            'question_text' => 'Capital of France?',
            'default_marks' => 5,
            'negative_marks' => 1,
            'difficulty' => 'EASY',
            'is_active' => true,
        ]);
        OnlineQuestionOption::create([
            'question_id' => $qBlank->id,
            'option_identifier' => 'A',
            'option_text' => 'Paris',
            'is_correct' => true,
            'sort_order' => 1,
        ]);
        $examQ = OnlineExamQuestion::create([
            'online_exam_id' => $this->exam->id,
            'question_id' => $qBlank->id,
            'sort_order' => 3,
            'marks' => 5,
            'negative_marks' => 1,
        ]);

        // Lowercase match
        $evalLower = $this->scoringService->evaluateQuestionAnswer($qBlank, $examQ, null, 'paris', $this->exam);
        $this->assertTrue($evalLower['is_correct']);
        $this->assertEquals(5.00, $evalLower['score_awarded']);
        $this->assertEquals(0.00, $evalLower['negative_marks_deducted']);

        // Uppercase match
        $evalUpper = $this->scoringService->evaluateQuestionAnswer($qBlank, $examQ, null, 'PARIS', $this->exam);
        $this->assertTrue($evalUpper['is_correct']);
        $this->assertEquals(5.00, $evalUpper['score_awarded']);

        // Mixed case + whitespace match
        $evalMixed = $this->scoringService->evaluateQuestionAnswer($qBlank, $examQ, null, '   pArIs   ', $this->exam);
        $this->assertTrue($evalMixed['is_correct']);
        $this->assertEquals(5.00, $evalMixed['score_awarded']);

        // Wrong answer
        $evalWrong = $this->scoringService->evaluateQuestionAnswer($qBlank, $examQ, null, 'London', $this->exam);
        $this->assertFalse($evalWrong['is_correct']);
        $this->assertEquals(0.00, $evalWrong['score_awarded']);
        $this->assertEquals(1.00, $evalWrong['negative_marks_deducted']);
    }

    public function test_fill_in_blank_supports_multiple_acceptable_alternatives(): void
    {
        $qMulti = OnlineQuestion::create([
            'category_id' => $this->testCategory->id,
            'question_type' => QuestionType::FILL_IN_BLANK,
            'question_text' => 'Country name?',
            'default_marks' => 5,
            'negative_marks' => 0,
            'difficulty' => 'EASY',
            'is_active' => true,
        ]);
        OnlineQuestionOption::create([
            'question_id' => $qMulti->id,
            'option_identifier' => 'A',
            'option_text' => 'USA | United States | America',
            'is_correct' => true,
            'sort_order' => 1,
        ]);
        $examQ = OnlineExamQuestion::create([
            'online_exam_id' => $this->exam->id,
            'question_id' => $qMulti->id,
            'sort_order' => 4,
            'marks' => 5,
            'negative_marks' => 0,
        ]);

        $eval1 = $this->scoringService->evaluateQuestionAnswer($qMulti, $examQ, null, 'usa', $this->exam);
        $this->assertTrue($eval1['is_correct']);

        $eval2 = $this->scoringService->evaluateQuestionAnswer($qMulti, $examQ, null, 'united states', $this->exam);
        $this->assertTrue($eval2['is_correct']);

        $eval3 = $this->scoringService->evaluateQuestionAnswer($qMulti, $examQ, null, 'AMERICA', $this->exam);
        $this->assertTrue($eval3['is_correct']);
    }
}
