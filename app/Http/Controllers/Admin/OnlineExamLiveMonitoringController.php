<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExamSessionStatus;
use App\Http\Controllers\Controller;
use App\Models\OnlineExam;
use App\Models\OnlineExamRecording;
use App\Models\OnlineExamResult;
use App\Models\OnlineExamSession;
use App\Services\AntiCheatingService;
use App\Services\ExamSessionService;
use App\Services\ProctoringRecordingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OnlineExamLiveMonitoringController extends Controller
{
    public function __construct(
        protected AntiCheatingService $antiCheatingService,
        protected ExamSessionService $sessionService,
        protected ProctoringRecordingService $recordingService
    ) {}

    /**
     * Show the live monitoring dashboard for an exam.
     */
    public function show(OnlineExam $online_exam)
    {
        $this->authorize('manageSubResource', $online_exam);

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
        $this->authorize('manageSubResource', $online_exam);

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
        // Try to join recordings count; gracefully degrade if table doesn't exist yet.
        try {
            $sessions = OnlineExamSession::with(['student.school', 'currentQuestion'])
                ->withCount('recordings')
                ->where('online_exam_id', $exam->id)
                ->latest('last_activity_at')
                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('LiveMonitor: Could not load recordings count (table may not exist): ' . $e->getMessage());
            $sessions = OnlineExamSession::with(['student.school', 'currentQuestion'])
                ->where('online_exam_id', $exam->id)
                ->latest('last_activity_at')
                ->get()
                ->each(fn ($s) => $s->recordings_count = 0);
        }

        $now = now();
        $thresholdSeconds = 30; // Offline threshold

        $activeSessions = [];
        $stats = [
            'total_enrolled' => $exam->examStudents()->count(),
            'attended' => 0,
            'not_started' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'timed_out' => 0,
            'terminated' => 0,
            'violations_total' => 0,
        ];

        foreach ($sessions as $s) {
            try {
                // Auto-finalize any session that has exceeded exam deadline
                if (! $s->status->isFinal() && $s->hasExamExpired()) {
                    try {
                        $this->sessionService->finishExam($s, true);
                        $s->refresh();
                    } catch (\Throwable $fe) {
                        \Illuminate\Support\Facades\Log::warning("Auto-finalize failed for session {$s->id}: " . $fe->getMessage());
                    }
                }

                // Ensure violations count is strictly verified against audit event log
                try {
                    $this->antiCheatingService->syncViolationsCountWithLog($s);
                } catch (\Throwable) {}

                $isOnline = $s->last_heartbeat_at && $now->diffInSeconds($s->last_heartbeat_at) <= $thresholdSeconds;
                $isCompleted = in_array($s->status, [ExamSessionStatus::SUBMITTED, ExamSessionStatus::EXPIRED], true)
                    || (!empty($s->completed_at) && $s->status !== ExamSessionStatus::TERMINATED);
                $isTimedOut = in_array($s->status, [ExamSessionStatus::EXPIRED, ExamSessionStatus::QUESTION_TIMEOUT], true);

                if ($s->status === ExamSessionStatus::TERMINATED) {
                    $stats['terminated']++;
                } elseif ($s->status === ExamSessionStatus::SUBMITTED
                    || (!empty($s->completed_at) && $s->status !== ExamSessionStatus::TERMINATED)) {
                    // SUBMITTED = finished voluntarily or auto-scored
                    $stats['completed']++;
                } elseif ($s->status === ExamSessionStatus::EXPIRED) {
                    // EXPIRED = timed out; counts as both completed and timed_out
                    $stats['completed']++;
                    $stats['timed_out']++;
                } elseif ($s->status === ExamSessionStatus::QUESTION_TIMEOUT) {
                    $stats['timed_out']++;
                    $stats['in_progress']++;
                } elseif ($s->status === ExamSessionStatus::NOT_STARTED) {
                    $stats['not_started']++;
                } else {
                    $stats['in_progress']++;
                }

                $stats['violations_total'] += (int) $s->violations_count;

                $activeSessions[] = [
                    'session_id' => $s->id,
                    'student_id' => $s->student_id,
                    'student_name' => $s->student?->name ?? 'Candidate #' . $s->student_id,
                    'registration_number' => $s->student?->registration_number ?? '-',
                    'school_name' => $s->student?->school?->name ?? 'N/A',
                    'status' => $s->status->value,
                    'status_label' => $s->status->label(),
                    'status_color' => $s->status->badgeColor(),
                    'current_question_index' => $s->current_question_index + 1,
                    'violations_count' => (int) $s->violations_count,
                    'is_online' => (bool) $isOnline,
                    'is_timed_out' => (bool) $isTimedOut,
                    'is_completed' => (bool) $isCompleted,
                    'last_heartbeat_ago' => $s->last_heartbeat_at ? $s->last_heartbeat_at->diffForHumans(null, true) : 'Never',
                    'camera_status' => $s->camera_status ?? 'unknown',
                    'fullscreen_status' => (bool) $s->fullscreen_status,
                    'started_at' => $s->exam_started_at ? $s->exam_started_at->format('H:i:s') : null,
                    'finished_at' => $s->completed_at ? $s->completed_at->format('H:i:s') : null,
                    'termination_reason' => $s->termination_reason,
                    'has_snapshot' => $this->recordingService->getLatestSnapshotPath($s) !== null,
                    'snapshot_url' => route('admin.online-exams.live.snapshot', ['online_exam' => $exam->id, 'session' => $s->id]),
                    'recordings_count' => (int) ($s->recordings_count ?? 0),
                ];
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("LiveMonitor: Failed to process session {$s->id}: " . $e->getMessage());
                // Skip this session rather than crashing the entire JSON response
            }
        }

        // Add enrolled students who haven't logged in yet (or have no session record).
        // Also look up whether they have a completed OnlineExamResult — if so, show them as Completed
        // rather than Not Started (handles data migrations / session resets).
        $enrolledStudentsWithoutSession = $exam->examStudents()
            ->with(['student.school'])
            ->whereNotIn('student_id', $sessions->pluck('student_id'))
            ->get();

        // Pre-load results for these students so we can show real completion data
        $studentIdsWithoutSession = $enrolledStudentsWithoutSession->pluck('student_id');
        $completedResults = OnlineExamResult::where('online_exam_id', $exam->id)
            ->whereIn('student_id', $studentIdsWithoutSession)
            ->get()
            ->keyBy('student_id');

        $resultBasedCompletedCount = $completedResults->count();

        // Increase stats to reflect result-backed completions
        $stats['completed'] += $resultBasedCompletedCount;
        $stats['attended'] = $sessions->filter(
            fn ($s) => $s->status !== ExamSessionStatus::NOT_STARTED
        )->count() + $resultBasedCompletedCount;

        // Only count truly not-started (no result, no session)
        $trueNotStarted = $enrolledStudentsWithoutSession->filter(
            fn ($es) => !$completedResults->has($es->student_id)
        );
        $stats['not_started'] += $trueNotStarted->count();

        foreach ($enrolledStudentsWithoutSession as $es) {
            $result = $completedResults->get($es->student_id);

            if ($result) {
                // Student has a completed result but no live session — show as Completed
                $activeSessions[] = [
                    'session_id'             => null,
                    'student_id'             => $es->student_id,
                    'student_name'           => $es->student?->name ?? 'Candidate #' . $es->student_id,
                    'registration_number'    => $es->student?->registration_number ?? '-',
                    'school_name'            => $es->student?->school?->name ?? 'N/A',
                    'status'                 => 'SUBMITTED',
                    'status_label'           => 'Submitted',
                    'status_color'           => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
                    'current_question_index' => $result->total_attempted ?? 0,
                    'violations_count'       => 0,
                    'is_online'              => false,
                    'is_timed_out'           => false,
                    'is_completed'           => true,
                    'last_heartbeat_ago'     => 'Submitted',
                    'camera_status'          => 'NOT_REQUIRED',
                    'fullscreen_status'      => false,
                    'started_at'             => null,
                    'finished_at'            => $result->created_at?->format('H:i:s'),
                    'termination_reason'     => null,
                    'has_snapshot'           => false,
                    'snapshot_url'           => null,
                    'recordings_count'       => 0,
                ];
            } else {
                // Truly not started — no session and no result
                $activeSessions[] = [
                    'session_id'             => null,
                    'student_id'             => $es->student_id,
                    'student_name'           => $es->student?->name ?? 'Candidate #' . $es->student_id,
                    'registration_number'    => $es->student?->registration_number ?? '-',
                    'school_name'            => $es->student?->school?->name ?? 'N/A',
                    'status'                 => 'NOT_STARTED',
                    'status_label'           => 'Not Started',
                    'status_color'           => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
                    'current_question_index' => 0,
                    'violations_count'       => 0,
                    'is_online'              => false,
                    'is_timed_out'           => false,
                    'is_completed'           => false,
                    'last_heartbeat_ago'     => 'Not Logged In',
                    'camera_status'          => 'NOT_REQUIRED',
                    'fullscreen_status'      => false,
                    'started_at'             => null,
                    'finished_at'            => null,
                    'termination_reason'     => null,
                    'has_snapshot'           => false,
                    'snapshot_url'           => null,
                    'recordings_count'       => 0,
                ];
            }
        }

        return [
            'stats'                       => $stats,
            'sessions'                    => $activeSessions,
            'uninitiated_students_count'  => $trueNotStarted->count(),
        ];
    }

    /**
     * Terminate an active student session manually from the proctor dashboard.
     */
    public function terminateSession(Request $request, OnlineExam $online_exam, OnlineExamSession $session)
    {
        $this->authorize('manageSubResource', $online_exam);

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
        try {
            $this->authorize('manageSubResource', $online_exam);

            if ($session->online_exam_id !== $online_exam->id) {
                return response()->json(['success' => false, 'message' => 'Session does not belong to this exam.'], 404);
            }

            // Verify and reconcile violations count by checking the event log
            try {
                $this->antiCheatingService->syncViolationsCountWithLog($session);
                $session->refresh();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Violations count sync issue for session {$session->id}: " . $e->getMessage());
            }

            $events = $session->events()->get();

            return response()->json([
                'success' => true,
                'violations_count' => (int) ($session->violations_count ?? 0),
                'max_violations' => (int) ($online_exam->max_fullscreen_violations ?? 3),
                'status' => $session->status?->value ?? 'UNKNOWN',
                'status_label' => $session->status ? $session->status->label() : 'Unknown',
                'events' => $events,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to view events for this exam.'], 403);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to load session events: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load events: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Stream the latest live camera snapshot image for a session.
     */
    public function streamSnapshot(OnlineExam $online_exam, OnlineExamSession $session)
    {
        $this->authorize('manageSubResource', $online_exam);

        if ($session->online_exam_id !== $online_exam->id) {
            abort(404);
        }

        $imageContent = $this->recordingService->getLatestSnapshotContent($session);

        if (! $imageContent) {
            // Return a lightweight 1x1 transparent JPEG
            $transparentJpeg = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');

            return response($transparentJpeg, 200, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        return response($imageContent, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Fetch list of all recorded proctoring chunks for a session.
     */
    public function recordingsList(OnlineExam $online_exam, OnlineExamSession $session)
    {
        $this->authorize('manageSubResource', $online_exam);

        if ($session->online_exam_id !== $online_exam->id) {
            abort(404);
        }

        $recordings = $this->recordingService->getSessionRecordings($session);

        $items = $recordings->map(function ($rec) use ($online_exam, $session) {
            return [
                'id' => $rec->id,
                'chunk_index' => $rec->chunk_index,
                'file_size_bytes' => $rec->file_size_bytes,
                'file_size_human' => round($rec->file_size_bytes / 1024, 1).' KB',
                'duration_seconds' => $rec->duration_seconds,
                'sha256_hash' => $rec->sha256_hash,
                'recorded_at' => $rec->recorded_at ? $rec->recorded_at->toIso8601String() : null,
                'is_final' => $rec->is_final,
                'stream_url' => route('admin.online-exams.live.recording.stream', [
                    'online_exam' => $online_exam->id,
                    'session' => $session->id,
                    'recording' => $rec->id,
                ]),
            ];
        });

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'student_name' => $session->student->name,
            'registration_number' => $session->registration_number,
            'total_chunks' => $recordings->count(),
            'total_size_bytes' => $recordings->sum('file_size_bytes'),
            'recordings' => $items,
        ]);
    }

    /**
     * Stream a recorded video chunk to the admin inspector.
     */
    public function streamRecording(OnlineExam $online_exam, OnlineExamSession $session, OnlineExamRecording $recording)
    {
        $this->authorize('manageSubResource', $online_exam);

        if ($session->online_exam_id !== $online_exam->id || $recording->online_exam_session_id !== $session->id) {
            abort(404);
        }

        $disk = Storage::disk($recording->storage_disk);
        if (! $disk->exists($recording->storage_path)) {
            abort(404, 'Recording file not found.');
        }

        $filename = "session_{$session->id}_chunk_{$recording->chunk_index}.webm";
        $mimeType = $recording->mime_type ?: 'video/webm';

        try {
            $fullPath = $disk->path($recording->storage_path);
            if (file_exists($fullPath)) {
                return response()->file($fullPath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => "inline; filename=\"{$filename}\"",
                    'Cache-Control' => 'private, max-age=3600',
                ]);
            }
        } catch (\Throwable) {}

        // Fallback for cloud/remote disks without local path
        $stream = $disk->readStream($recording->storage_path);

        return response()->stream(function () use ($stream) {
            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control' => 'private, max-age=3600',
            'Accept-Ranges' => 'bytes',
        ]);
    }
}
