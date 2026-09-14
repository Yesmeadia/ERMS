<?php

namespace Tests\Feature\OnlineExam;

use App\Enums\ExamSessionStatus;
use App\Models\OnlineExam;
use App\Models\OnlineExamSession;
use App\Models\OnlineExamWebrtcSignal;
use App\Models\Student;

class WebRTCProctoringTest extends OnlineExamTestCase
{
    protected OnlineExam $exam;

    protected Student $student;

    protected OnlineExamSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exam = $this->createOnlineExam([
            'enable_camera' => true,
            'enable_fullscreen' => true,
        ]);

        $this->student = $this->createStudent();
        $this->enrollStudent($this->exam, $this->student);

        $this->session = OnlineExamSession::create([
            'online_exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'registration_number' => $this->student->registration_number,
            'session_token' => 'webrtc_token_'.rand(1000, 9999),
            'status' => ExamSessionStatus::IN_PROGRESS,
            'violations_count' => 0,
            'camera_status' => 'active',
            'fullscreen_status' => true,
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    public function test_admin_can_send_webrtc_offer_to_student_session(): void
    {
        $offerPayload = [
            'type' => 'offer',
            'sdp' => 'v=0\r\no=- 4611731400430051336 2 IN IP4 127.0.0.1\r\ns=-\r\n',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.online-exams.live.webrtc.signal', [$this->exam, $this->session]), [
                'type' => 'offer',
                'payload' => $offerPayload,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('online_exam_webrtc_signals', [
            'online_exam_session_id' => $this->session->id,
            'admin_id' => $this->adminUser->id,
            'sender_type' => 'admin',
            'recipient_type' => 'student',
            'type' => 'offer',
            'is_consumed' => false,
        ]);
    }

    public function test_student_can_poll_incoming_signals_from_admin(): void
    {
        // Admin creates an offer
        OnlineExamWebrtcSignal::create([
            'online_exam_session_id' => $this->session->id,
            'admin_id' => $this->adminUser->id,
            'sender_type' => 'admin',
            'recipient_type' => 'student',
            'type' => 'offer',
            'payload' => ['sdp' => 'offer_sdp'],
            'is_consumed' => false,
        ]);

        $response = $this->withSession(['online_exam_session_token' => $this->session->session_token])
            ->getJson(route('online-exam.webrtc.signals'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'signals' => [
                [
                    'admin_id' => $this->adminUser->id,
                    'type' => 'offer',
                    'payload' => ['sdp' => 'offer_sdp'],
                ],
            ],
        ]);

        // Verify signal is marked as consumed
        $this->assertDatabaseHas('online_exam_webrtc_signals', [
            'online_exam_session_id' => $this->session->id,
            'type' => 'offer',
            'is_consumed' => true,
        ]);

        // Subsequent poll returns empty
        $response2 = $this->withSession(['online_exam_session_token' => $this->session->session_token])
            ->getJson(route('online-exam.webrtc.signals'));

        $response2->assertStatus(200);
        $response2->assertJsonCount(0, 'signals');
    }

    public function test_student_can_send_webrtc_answer_and_candidates(): void
    {
        $answerPayload = [
            'type' => 'answer',
            'sdp' => 'v=0\r\nanswer_sdp',
        ];

        $response = $this->withSession(['online_exam_session_token' => $this->session->session_token])
            ->postJson(route('online-exam.webrtc.signal'), [
                'type' => 'answer',
                'admin_id' => $this->adminUser->id,
                'payload' => $answerPayload,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('online_exam_webrtc_signals', [
            'online_exam_session_id' => $this->session->id,
            'admin_id' => $this->adminUser->id,
            'sender_type' => 'student',
            'recipient_type' => 'admin',
            'type' => 'answer',
            'is_consumed' => false,
        ]);
    }

    public function test_admin_can_poll_student_webrtc_answers_and_candidates(): void
    {
        // Student creates answer
        OnlineExamWebrtcSignal::create([
            'online_exam_session_id' => $this->session->id,
            'admin_id' => $this->adminUser->id,
            'sender_type' => 'student',
            'recipient_type' => 'admin',
            'type' => 'answer',
            'payload' => ['sdp' => 'student_answer_sdp'],
            'is_consumed' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.online-exams.live.webrtc.signals', [$this->exam, $this->session]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'signals' => [
                [
                    'type' => 'answer',
                    'payload' => ['sdp' => 'student_answer_sdp'],
                ],
            ],
        ]);

        // Signal consumed
        $this->assertDatabaseHas('online_exam_webrtc_signals', [
            'online_exam_session_id' => $this->session->id,
            'type' => 'answer',
            'is_consumed' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_signaling(): void
    {
        $response = $this->post(route('admin.online-exams.live.webrtc.signal', [$this->exam, $this->session]), [
            'type' => 'offer',
        ]);

        $response->assertRedirect(route('login'));
    }
}
