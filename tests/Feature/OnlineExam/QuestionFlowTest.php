<?php

namespace Tests\Feature\OnlineExam;

use App\Enums\ExamSessionStatus;
use App\Enums\QuestionType;
use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineExamSession;
use App\Models\OnlineQuestion;
use App\Models\OnlineQuestionOption;
use App\Models\Student;
use App\Services\ExamQuestionService;

class QuestionFlowTest extends OnlineExamTestCase
{
    protected ExamQuestionService $questionService;

    protected OnlineExam $exam;

    protected Student $student;

    protected OnlineExamSession $session;

    protected OnlineQuestion $q1;

    protected OnlineQuestion $q2;

    protected OnlineQuestionOption $opt1Correct;

    protected OnlineQuestionOption $opt1Wrong;

    protected function setUp(): void
    {
        parent::setUp();

        $this->questionService = app(ExamQuestionService::class);

        $this->exam = $this->createOnlineExam([
            'allow_previous_question' => false,
            'total_marks' => 20,
            'pass_marks' => 8,
            'enable_camera' => false,
            'enable_fullscreen' => false,
        ]);

        $this->student = $this->createStudent();
        $this->enrollStudent($this->exam, $this->student);

        // Question 1
        $this->q1 = OnlineQuestion::create([
            'category_id' => $this->testCategory->id,
            'question_type' => QuestionType::MCQ,
            'question_text' => 'What is 2 + 2?',
            'default_marks' => 10,
            'difficulty' => 'EASY',
            'is_active' => true,
        ]);
        $this->opt1Correct = OnlineQuestionOption::create([
            'question_id' => $this->q1->id,
            'option_identifier' => 'A',
            'option_text' => '4',
            'is_correct' => true,
            'sort_order' => 1,
        ]);
        $this->opt1Wrong = OnlineQuestionOption::create([
            'question_id' => $this->q1->id,
            'option_identifier' => 'B',
            'option_text' => '5',
            'is_correct' => false,
            'sort_order' => 2,
        ]);
        OnlineExamQuestion::create([
            'online_exam_id' => $this->exam->id,
            'question_id' => $this->q1->id,
            'sort_order' => 1,
            'marks' => 10,
        ]);

        // Question 2
        $this->q2 = OnlineQuestion::create([
            'category_id' => $this->testCategory->id,
            'question_type' => QuestionType::MCQ,
            'question_text' => 'What is the capital of India?',
            'default_marks' => 10,
            'difficulty' => 'EASY',
            'is_active' => true,
        ]);
        OnlineQuestionOption::create([
            'question_id' => $this->q2->id,
            'option_identifier' => 'A',
            'option_text' => 'New Delhi',
            'is_correct' => true,
            'sort_order' => 1,
        ]);
        OnlineExamQuestion::create([
            'online_exam_id' => $this->exam->id,
            'question_id' => $this->q2->id,
            'sort_order' => 2,
            'marks' => 10,
        ]);

        // Session
        $this->session = OnlineExamSession::create([
            'online_exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'registration_number' => $this->student->registration_number,
            'session_token' => 'flow_token_'.rand(1000, 9999),
            'status' => ExamSessionStatus::IN_PROGRESS,
            'question_order' => [$this->q1->id, $this->q2->id],
            'current_question_index' => 0,
            'current_question_id' => $this->q1->id,
            'started_at' => now(),
            'current_question_started_at' => now(),
            'current_question_deadline_at' => now()->addSeconds(60),
            'exam_deadline_at' => now()->addMinutes(60),
            'last_activity_at' => now(),
        ]);
    }

    public function test_safe_payload_strictly_omits_is_correct_column(): void
    {
        $examQuestion = OnlineExamQuestion::where('online_exam_id', $this->exam->id)
            ->where('question_id', $this->q1->id)->first();

        $payload = $this->questionService->buildSafeQuestionPayload($this->q1, $examQuestion, $this->session);

        $this->assertArrayHasKey('options', $payload);
        foreach ($payload['options'] as $option) {
            $this->assertArrayNotHasKey('is_correct', $option, 'CRITICAL SECURITY: is_correct must NEVER be in client payload!');
            $this->assertArrayHasKey('id', $option);
            $this->assertArrayHasKey('identifier', $option);
            $this->assertArrayHasKey('text', $option);
        }
    }

    public function test_submit_answer_persists_and_locks_without_advancing_question(): void
    {
        $response = $this->withSession(['online_exam_session_token' => $this->session->session_token])
            ->postJson('/online-exam/submit-answer', [
                'question_id' => $this->q1->id,
                'selected_option_ids' => [$this->opt1Correct->id],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Answer Saved Successfully',
        ]);

        // Verify answer is saved and locked in database
        $this->assertDatabaseHas('online_exam_answers', [
            'online_exam_session_id' => $this->session->id,
            'question_id' => $this->q1->id,
            'is_locked' => true,
            'is_correct' => true,
            'score_awarded' => 10.00,
        ]);

        // Verify session index did NOT advance
        $this->assertEquals(0, $this->session->fresh()->current_question_index);
        $this->assertEquals($this->q1->id, $this->session->fresh()->current_question_id);
    }

    public function test_next_question_advances_explicitly_to_second_question(): void
    {
        $response = $this->withSession(['online_exam_session_token' => $this->session->session_token])
            ->postJson('/online-exam/next-question');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'has_next' => true,
        ]);

        $freshSession = $this->session->fresh();
        $this->assertEquals(1, $freshSession->current_question_index);
        $this->assertEquals($this->q2->id, $freshSession->current_question_id);
    }

    public function test_retreat_to_previous_question_is_forbidden_when_disabled(): void
    {
        // Advance to Q2
        $this->session->update(['current_question_index' => 1, 'current_question_id' => $this->q2->id]);

        $response = $this->withSession(['online_exam_session_token' => $this->session->session_token])
            ->postJson('/online-exam/previous-question');

        $response->assertStatus(403);
    }

    public function test_timeout_submission_sets_score_to_zero(): void
    {
        $response = $this->withSession(['online_exam_session_token' => $this->session->session_token])
            ->postJson('/online-exam/submit-answer', [
                'question_id' => $this->q1->id,
                'is_timeout' => true,
                'time_spent_ms' => 60000,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Question Time Expired',
        ]);

        $this->assertDatabaseHas('online_exam_answers', [
            'online_exam_session_id' => $this->session->id,
            'question_id' => $this->q1->id,
            'is_locked' => true,
            'is_correct' => false,
            'score_awarded' => 0.00,
            'negative_marks_deducted' => 0.00,
            'speed_bonus_awarded' => 0.00,
            'evaluation_status' => 'TIMED_OUT',
        ]);
    }

    public function test_advancing_unanswered_question_persists_timed_out_with_zero_marks(): void
    {
        $response = $this->withSession(['online_exam_session_token' => $this->session->session_token])
            ->postJson('/online-exam/next-question');

        $response->assertStatus(200);

        // Verify Q1 was automatically recorded with 0 marks
        $this->assertDatabaseHas('online_exam_answers', [
            'online_exam_session_id' => $this->session->id,
            'question_id' => $this->q1->id,
            'score_awarded' => 0.00,
            'evaluation_status' => 'TIMED_OUT',
        ]);
    }
}
