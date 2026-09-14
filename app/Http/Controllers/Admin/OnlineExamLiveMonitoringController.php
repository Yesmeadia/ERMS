<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExamSessionStatus;
use App\Http\Controllers\Controller;
use App\Models\OnlineExam;
use App\Models\OnlineExamSession;
use App\Services\AntiCheatingService;
use App\Services\ExamSessionService;
use Illuminate\Http\Request;

class OnlineExamLiveMonitoringController extends Controller
{
    public function __construct(
        protected AntiCheatingService $antiCheatingService,
        protected ExamSessionService $sessionService
    ) {}

    /**
     * Show the live monitoring dashboard for an exam.
     */
    public function show(OnlineExam $online_exam)
    {
        $exam = $online_exam->load(['category']);
        $totalEnrolled = $exam->examStudents()->count();
        $totalQuestions = $exam->examQuestions()->count();
        $monitoringData = $this->buildMonitoringData($exam);

        return view('admin.online-exams.live', [
            'exam' => $exam,
            'totalEnrolled' => $totalEnrolled,
            'totalQuestions' => $totalQuestions,
            'stats' => $monitoringData['stats'],
            'initialSessions' => $monitoringData['sessions'],
        ]);
    }

    /**
     * Poll live status of all student sessions (AJAX endpoint for shared-hosting).
     */
    public function poll(OnlineExam $online_exam)
    {
        $monitoringData = $this->buildMonitoringData($online_exam);

        return response()->json([
            'success' => true,
            'exam_id' => $online_exam->id,
            'stats' => $monitoringData['stats'],
            'sessions' => $monitoringData['sessions'],
            'uninitiated_students_count' => $monitoringData['uninitiated_students_count'],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Build real-time statistics and session data for monitoring.
     */
    protected function buildMonitoringData(OnlineExam $exam): array
    {
        $sessions = OnlineExamSession::with(['student.school', 'currentQuestion'])
            ->where('online_exam_id', $exam->id)
            ->latest('last_activity_at')
            ->get();

        $now = now();
        $thresholdSeconds = 30; // Offline threshold

        $activeSessions = [];
        $stats = [
            'total_enrolled' => $exam->examStudents()->count(),
            'not_started' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'timed_out' => 0,
            'terminated' => 0,
            'violations_total' => 0,
        ];

        foreach ($sessions as $s) {
            // Auto-finalize any session that has exceeded exam deadline
            if (! $s->status->isFinal() && $s->hasExamExpired()) {
                $this->sessionService->finishExam($s, true);
                $s->refresh();
            }

            // Ensure violations count is strictly verified against audit event log
            $this->antiCheatingService->syncViolationsCountWithLog($s);

            $isOnline = $s->last_heartbeat_at && $now->diffInSeconds($s->last_heartbeat_at) <= $thresholdSeconds;
            $isCompleted = in_array($s->status, [ExamSessionStatus::SUBMITTED, ExamSessionStatus::EXPIRED], true);
            $isTimedOut = in_array($s->status, [ExamSessionStatus::EXPIRED, ExamSessionStatus::QUESTION_TIMEOUT], true);

            if ($s->status === ExamSessionStatus::TERMINATED) {
                $stats['terminated']++;
            } elseif ($isCompleted) {
                $stats['completed']++;
                if ($s->status === ExamSessionStatus::EXPIRED) {
                    $stats['timed_out']++;
                }
            } elseif ($s->status === ExamSessionStatus::QUESTION_TIMEOUT) {
                $stats['timed_out']++;
                $stats['in_progress']++;
            } elseif ($s->status === ExamSessionStatus::NOT_STARTED) {
                $stats['not_started']++;
            } else {
                $stats['in_progress']++;
            }

            $stats['violations_total'] += $s->violations_count;

            $activeSessions[] = [
                'session_id' => $s->id,
                'student_id' => $s->student_id,
                'student_name' => $s->student->name,
                'registration_number' => $s->student->registration_number,
                'school_name' => $s->student->school->name ?? 'N/A',
                'status' => $s->status->value,
                'status_label' => $s->status->label(),
                'status_color' => $s->status->badgeColor(),
                'current_question_index' => $s->current_question_index + 1,
                'violations_count' => $s->violations_count,
                'is_online' => $isOnline,
                'is_timed_out' => $isTimedOut,
                'is_completed' => $isCompleted,
                'last_heartbeat_ago' => $s->last_heartbeat_at ? $s->last_heartbeat_at->diffForHumans(null, true) : 'Never',
                'camera_status' => $s->camera_status ?? 'unknown',
                'fullscreen_status' => (bool) $s->fullscreen_status,
                'started_at' => ($s->exam_started_at ?? $s->started_at) ? ($s->exam_started_at ?? $s->started_at)->format('H:i:s') : null,
                'finished_at' => $s->completed_at ? $s->completed_at->format('H:i:s') : null,
                'termination_reason' => $s->termination_reason,
            ];
        }

        // Add students who haven't logged in yet
        $enrolledStudentsWithoutSession = $exam->examStudents()
            ->with(['student.school'])
            ->whereNotIn('student_id', $sessions->pluck('student_id'))
            ->get();

        $stats['not_started'] += $enrolledStudentsWithoutSession->count();

        return [
            'stats' => $stats,
            'sessions' => $activeSessions,
            'uninitiated_students_count' => $enrolledStudentsWithoutSession->count(),
        ];
    }

    /**
     * Terminate an active student session manually from the proctor dashboard.
     */
    public function terminateSession(Request $request, OnlineExam $online_exam, OnlineExamSession $session)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if ($session->online_exam_id !== $online_exam->id) {
            return response()->json(['success' => false, 'message' => 'Session does not belong to this exam.'], 404);
        }

        if ($session->status->isFinal()) {
            return response()->json(['success' => false, 'message' => 'Session is already finished.'], 422);
        }

        $this->sessionService->terminateSessionByAdmin(
            $session,
            auth()->user(),
            $validated['reason']
        );

        return response()->json([
            'success' => true,
            'message' => "Session for student {$session->student->name} terminated successfully.",
        ]);
    }

    /**
     * Fetch event timeline for a specific session.
     */
    public function sessionEvents(OnlineExam $online_exam, OnlineExamSession $session)
    {
        if ($session->online_exam_id !== $online_exam->id) {
            abort(404);
        }

        // Verify and reconcile violations count by checking the event log
        $this->antiCheatingService->syncViolationsCountWithLog($session);
        $session->refresh();

        $events = $session->events()->latest('event_time')->get();

        return response()->json([
            'success' => true,
            'violations_count' => $session->violations_count,
            'max_violations' => (int) $online_exam->max_fullscreen_violations,
            'status' => $session->status->value,
            'status_label' => $session->status->label(),
            'events' => $events,
        ]);
    }
}
