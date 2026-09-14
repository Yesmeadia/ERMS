<?php

namespace Tests\Feature\OnlineExam;

use App\Enums\ExamEventType;
use App\Enums\ExamSessionStatus;
use App\Models\OnlineExam;
use App\Models\OnlineExamSession;
use App\Models\OnlineExamSessionEvent;
use App\Models\Student;
use App\Services\AntiCheatingService;

class AntiCheatingTest extends OnlineExamTestCase
{
    protected AntiCheatingService $antiCheatingService;

    protected OnlineExam $exam;

    protected Student $student;

    protected OnlineExamSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->antiCheatingService = app(AntiCheatingService::class);

        $this->exam = $this->createOnlineExam([
            'max_fullscreen_violations' => 2,
            'enable_camera' => true,
            'enable_fullscreen' => true,
        ]);

        $this->student = $this->createStudent();
        $this->enrollStudent($this->exam, $this->student);

        $this->session = OnlineExamSession::create([
            'online_exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'registration_number' => $this->student->registration_number,
            'session_token' => 'integ_token_'.rand(1000, 9999),
            'status' => ExamSessionStatus::IN_PROGRESS,
            'violations_count' => 0,
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    public function test_fullscreen_exit_records_security_violation_and_increments_count(): void
    {
        $response = $this->withSession(['online_exam_session_token' => $this->session->session_token])
            ->postJson('/online-exam/event', [
                'event_type' => ExamEventType::FULLSCREEN_EXIT->value,
                'metadata' => ['window_width' => 1024],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'violations_count' => 1,
            'is_terminated' => false,
        ]);

        $this->assertEquals(1, $this->session->fresh()->violations_count);

        $event = OnlineExamSessionEvent::where('online_exam_session_id', $this->session->id)
            ->where('event_type', ExamEventType::FULLSCREEN_EXIT)
            ->first();

        $this->assertNotNull($event);
        $this->assertTrue((bool) $event->is_violation);
    }

    public function test_exceeding_max_violations_automatically_terminates_session(): void
    {
        // First violation
        $this->antiCheatingService->recordEvent($this->session, ExamEventType::FULLSCREEN_EXIT);
        $this->assertEquals(1, $this->session->fresh()->violations_count);
        $this->assertEquals(ExamSessionStatus::IN_PROGRESS, $this->session->fresh()->status);

        // Advance past 2-second debounce window to simulate a second distinct incident
        $this->travel(3)->seconds();

        // Second violation (max_fullscreen_violations = 2)
        $this->antiCheatingService->recordEvent($this->session, ExamEventType::TAB_SWITCH);
        $fresh = $this->session->fresh();

        $this->assertEquals(2, $fresh->violations_count);
        $this->assertEquals(ExamSessionStatus::TERMINATED, $fresh->status);
        $this->assertNotNull($fresh->completed_at);
        $this->assertStringContainsString('exceeding 2 violation warnings', $fresh->termination_reason);
    }

    public function test_heartbeat_updates_session_camera_and_fullscreen_status(): void
    {
        $response = $this->withSession(['online_exam_session_token' => $this->session->session_token])
            ->postJson('/online-exam/heartbeat', [
                'camera_status' => 'active',
                'fullscreen_status' => true,
            ]);

        $response->assertStatus(200);
        $fresh = $this->session->fresh();

        $this->assertEquals('active', $fresh->camera_status);
        $this->assertTrue((bool) $fresh->fullscreen_status);
        $this->assertNotNull($fresh->last_heartbeat_at);
    }

    public function test_rapid_violations_within_debounce_window_do_not_double_count(): void
    {
        // First violation
        $this->antiCheatingService->recordEvent($this->session, ExamEventType::FULLSCREEN_EXIT);
        $this->assertEquals(1, $this->session->fresh()->violations_count);

        // Rapid second violation (within 2 seconds, e.g. from an Alt+Tab that triggers both)
        $this->antiCheatingService->recordEvent($this->session, ExamEventType::WINDOW_BLUR);

        // Violations count should remain 1 (debounced), but both events should exist in audit trail
        $this->assertEquals(1, $this->session->fresh()->violations_count);
        $this->assertEquals(2, OnlineExamSessionEvent::where('online_exam_session_id', $this->session->id)->count());
    }

    public function test_proctor_can_terminate_session_manually_via_live_monitoring(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson("/admin/online-exams/{$this->exam->id}/live/terminate/{$this->session->id}", [
                'reason' => 'Suspected proxy test taker detected on camera',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $fresh = $this->session->fresh();
        $this->assertEquals(ExamSessionStatus::TERMINATED, $fresh->status);
        $this->assertStringContainsString('Suspected proxy test taker detected on camera', $fresh->termination_reason);
    }
}
