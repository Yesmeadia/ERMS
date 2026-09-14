<?php

namespace Tests\Feature\OnlineExam;

use App\Enums\ExamSessionStatus;
use App\Enums\ExamStatus;
use App\Models\OnlineExamSession;
use App\Models\Student;

class SuperAdminDashboardOnlineExamTest extends OnlineExamTestCase
{
    /**
     * Test Super Admin dashboard displays Online Exam status section and metrics.
     */
    public function test_super_admin_dashboard_displays_online_exam_status()
    {
        $this->actingAs($this->adminUser);

        // Create an online exam
        $exam = $this->createOnlineExam([
            'name' => 'Talent Search Online Round 1',
            'code' => 'TSR-001',
            'status' => ExamStatus::ACTIVE,
            'exam_date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '21:00:00',
        ]);

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Online Examination Status &amp; Control Center', false);
        $response->assertDontSee('Live Online Examinations Active Now');
        $response->assertSee('Total Created Exams');
        $response->assertSee('Participating Students');
        $response->assertSee('Live:');
        $response->assertSee('Drafted:');
        $response->assertSee('Terminated:');
        $response->assertSee('Completed:');
        $response->assertSee(route('admin.online-exams.index'));
    }

    /**
     * Test Live Proctoring Monitor correctly reports completed students count for SUBMITTED and EXPIRED sessions.
     */
    public function test_live_proctoring_monitor_counts_completed_students()
    {
        $this->actingAs($this->adminUser);

        $exam = $this->createOnlineExam([
            'name' => 'Mathematics Live Championship',
            'code' => 'MATH-LIVE',
            'status' => ExamStatus::ACTIVE,
            'exam_date' => now()->format('Y-m-d'),
        ]);

        // Student 1: Submitted manually
        $student1 = $this->createStudent();
        $this->enrollStudent($exam, $student1);
        OnlineExamSession::create([
            'online_exam_id' => $exam->id,
            'student_id' => $student1->id,
            'registration_number' => $student1->registration_number,
            'session_token' => 'token-student-1',
            'session_version' => 1,
            'status' => ExamSessionStatus::SUBMITTED,
            'completed_at' => now(),
        ]);

        // Student 2: Auto-submitted because time expired
        $student2 = $this->createStudent();
        $this->enrollStudent($exam, $student2);
        OnlineExamSession::create([
            'online_exam_id' => $exam->id,
            'student_id' => $student2->id,
            'registration_number' => $student2->registration_number,
            'session_token' => 'token-student-2',
            'session_version' => 1,
            'status' => ExamSessionStatus::EXPIRED,
            'completed_at' => now(),
        ]);

        // Student 3: In progress
        $student3 = $this->createStudent();
        $this->enrollStudent($exam, $student3);
        OnlineExamSession::create([
            'online_exam_id' => $exam->id,
            'student_id' => $student3->id,
            'registration_number' => $student3->registration_number,
            'session_token' => 'token-student-3',
            'session_version' => 1,
            'status' => ExamSessionStatus::IN_PROGRESS,
            'last_heartbeat_at' => now(),
        ]);

        // 1. Check HTML response on Live Monitor show page
        $pageResponse = $this->get(route('admin.online-exams.live', $exam));
        $pageResponse->assertStatus(200);

        // 2. Check JSON response on polling endpoint
        $pollResponse = $this->getJson(route('admin.online-exams.live.poll', $exam));
        $pollResponse->assertStatus(200);
        $pollResponse->assertJson([
            'success' => true,
            'stats' => [
                'total_enrolled' => 3,
                'in_progress' => 1,
                'completed' => 2, // Both Student 1 (SUBMITTED) and Student 2 (EXPIRED) counted as completed!
            ],
        ]);
    }
}
