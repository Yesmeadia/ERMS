<?php

namespace Tests\Feature\OnlineExam;

use App\Enums\ExamStatus;
use App\Enums\QuestionType;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineQuestion;
use App\Services\OnlineExamService;

class ExamCapacityTest extends OnlineExamTestCase
{
    protected OnlineExamService $examService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->examService = app(OnlineExamService::class);
    }

    public function test_publish_fails_when_enrolled_students_exceed_199(): void
    {
        $exam = $this->createOnlineExam([
            'status' => ExamStatus::DRAFT,
            'max_eligible_students' => 199,
        ]);

        $question = OnlineQuestion::create([
            'category_id' => $this->testCategory->id,
            'question_type' => QuestionType::MCQ,
            'question_text' => 'Sample capacity question?',
            'default_marks' => 5,
            'difficulty' => 'EASY',
            'is_active' => true,
        ]);

        OnlineExamQuestion::create([
            'online_exam_id' => $exam->id,
            'question_id' => $question->id,
            'sort_order' => 1,
            'marks' => 5,
        ]);

        // Enroll 200 students
        for ($i = 1; $i <= 200; $i++) {
            $st = $this->createStudent([
                'name' => "Batch Student {$i}",
                'registration_number' => 'CAP'.str_pad($i, 6, '0', STR_PAD_LEFT),
            ]);

            $this->enrollStudent($exam, $st);
        }

        $result = $this->examService->publishExam($exam, $this->adminUser);

        $this->assertFalse($result['success']);
        $this->assertEquals(
            'This examination cannot be published because the maximum allowed number of eligible students is 199.',
            $result['message']
        );
        $this->assertEquals(ExamStatus::DRAFT, $exam->fresh()->status);
    }

    public function test_publish_succeeds_when_enrolled_students_are_199_or_fewer(): void
    {
        $exam = $this->createOnlineExam([
            'status' => ExamStatus::DRAFT,
            'max_eligible_students' => 199,
        ]);

        $question = OnlineQuestion::create([
            'category_id' => $this->testCategory->id,
            'question_type' => QuestionType::MCQ,
            'question_text' => 'Sample capacity question?',
            'default_marks' => 5,
            'difficulty' => 'EASY',
            'is_active' => true,
        ]);

        OnlineExamQuestion::create([
            'online_exam_id' => $exam->id,
            'question_id' => $question->id,
            'sort_order' => 1,
            'marks' => 5,
        ]);

        // Enroll 5 students (<= 199)
        for ($i = 1; $i <= 5; $i++) {
            $st = $this->createStudent([
                'name' => "Valid Student {$i}",
                'registration_number' => 'VAL'.str_pad($i, 6, '0', STR_PAD_LEFT),
            ]);

            $this->enrollStudent($exam, $st);
        }

        $result = $this->examService->publishExam($exam, $this->adminUser);

        $this->assertTrue($result['success']);
        $this->assertEquals(ExamStatus::PUBLISHED, $exam->fresh()->status);
    }
}
