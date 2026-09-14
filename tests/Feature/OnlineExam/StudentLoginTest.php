<?php

namespace Tests\Feature\OnlineExam;

use App\Enums\ExamSessionStatus;
use App\Models\OnlineExam;
use App\Models\OnlineExamSession;
use App\Models\Student;

class StudentLoginTest extends OnlineExamTestCase
{
    protected OnlineExam $exam;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exam = $this->createOnlineExam();
        $this->student = $this->createStudent(['dob' => '2008-05-15']);

        $this->enrollStudent($this->exam, $this->student);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/online-exam/login');
        $response->assertStatus(200);
        $response->assertSee('Online Examination Portal');
        $response->assertSee('Registration Number');
    }

    public function test_valid_registration_number_and_dob_logs_in_successfully(): void
    {
        $response = $this->post('/online-exam/login', [
            'registration_number' => $this->student->registration_number,
            'dob' => '2008-05-15',
            'device_fingerprint' => 'test_device_1',
        ]);

        $response->assertRedirect(route('online-exam.instructions'));
        $this->assertNotNull(session('online_exam_session_token'));

        $this->assertDatabaseHas('online_exam_sessions', [
            'online_exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => ExamSessionStatus::READY->value,
        ]);
    }

    public function test_login_fails_with_incorrect_date_of_birth(): void
    {
        $response = $this->from('/online-exam/login')->post('/online-exam/login', [
            'registration_number' => $this->student->registration_number,
            'dob' => '2000-01-01',
        ]);

        $response->assertRedirect('/online-exam/login');
        $response->assertSessionHasErrors('registration_number');
    }

    public function test_login_fails_for_non_existent_registration_number(): void
    {
        $response = $this->from('/online-exam/login')->post('/online-exam/login', [
            'registration_number' => 'NONEXISTENT999',
            'dob' => '2008-05-15',
        ]);

        $response->assertRedirect('/online-exam/login');
        $response->assertSessionHasErrors('registration_number');
    }

    public function test_rejects_concurrent_login_from_another_device(): void
    {
        // First device logs in
        OnlineExamSession::create([
            'online_exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'registration_number' => $this->student->registration_number,
            'session_token' => 'active_token_123',
            'device_fingerprint_hash' => hash('sha256', 'dev_device_A'),
            'ip_address' => '192.168.1.10',
            'status' => ExamSessionStatus::IN_PROGRESS,
            'last_heartbeat_at' => now(), // Active within 30s
            'started_at' => now(),
            'last_activity_at' => now(),
            'exam_deadline_at' => now()->addMinutes(60),
        ]);

        // Second device attempts login
        $response = $this->from('/online-exam/login')->post('/online-exam/login', [
            'registration_number' => $this->student->registration_number,
            'dob' => '2008-05-15',
            'device_fingerprint' => 'dev_device_B',
        ]);

        $response->assertRedirect('/online-exam/login');
        $response->assertSessionHasErrors('registration_number');
    }
}
