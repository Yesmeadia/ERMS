<?php

namespace App\Http\Controllers;

use App\Enums\ExamEventType;
use App\Enums\ExamSessionStatus;
use App\Models\OnlineExamSession;
use App\Models\OnlineQuestion;
use App\Services\AntiCheatingService;
use App\Services\ExamQuestionService;
use App\Services\ExamSessionService;
use App\Services\ExamTimerService;
use Illuminate\Http\Request;

class StudentOnlineExamController extends Controller
{
    public function __construct(
        protected ExamSessionService $sessionService,
        protected ExamQuestionService $questionService,
        protected AntiCheatingService $antiCheatingService,
        protected ExamTimerService $timerService
    ) {}

    /**
     * Show Student Login Form.
     */
    public function showLogin()
    {
        // Check if student already has active session in current browser
        $token = session('online_exam_session_token');
        if ($token) {
            $existing = OnlineExamSession::where('session_token', $token)->first();
            if ($existing && ! $existing->status->isFinal()) {
                return redirect()->route('online-exam.take');
            }
        }

        return response()
            ->view('student-exam.login')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
    }

    /**
     * Handle Student Login Authentication.
     */
    public function login(Request $request)
    {
        $request->validate([
            'registration_number' => ['required', 'string', 'max:32'],
            'dob' => ['required', 'string'],
            'device_fingerprint' => ['nullable', 'string'],
        ]);

        $result = $this->sessionService->validateStudentLogin(
            $request->input('registration_number'),
            $request->input('dob'),
            $request->input('device_fingerprint'),
            $request->ip(),
            $request->userAgent()
        );

        if (! $result['success']) {
            return back()->withInput(['registration_number' => $request->registration_number])
                ->withErrors(['registration_number' => $result['message']]);
        }

        $student = $result['student'];
        $exam = $result['exam'];

        // Start or resume session
        $session = $this->sessionService->startOrResumeSession(
            $exam,
            $student,
            $request->ip(),
            $request->userAgent(),
            $request->input('device_fingerprint')
        );

        // Regenerate session ID to prevent Session Fixation (OWASP A07)
        $request->session()->regenerate();

        // Store secure session token in Laravel session
        $request->session()->put('online_exam_session_token', $session->session_token);

        // Direct to take route where onboarding and examination run in a seamless single page
        return redirect()->route('online-exam.take');
    }

    /**
     * Show Instructions & System Check Screen (Camera & Fullscreen).
     */
    public function instructions(Request $request)
    {
        return redirect()->route('online-exam.take');
    }

    /**
     * Start the examination attempt.
     */
    public function startExam(Request $request)
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        if ($session->status === ExamSessionStatus::READY) {
            $questionOrder = $session->question_order ?: [];
            $firstQuestionId = ! empty($questionOrder) ? $questionOrder[0] : null;

            $updateData = ['status' => ExamSessionStatus::IN_PROGRESS];
            if ($request->filled('camera_status')) {
                $updateData['camera_status'] = strtoupper($request->input('camera_status'));
            }

            if ($firstQuestionId) {
                $session->update($updateData);
                $this->questionService->startQuestion($session, $firstQuestionId);
                $session->refresh();
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            $currentQuestionId = $session->current_question_id;
            $currentQuestion = $currentQuestionId ? OnlineQuestion::with(['options', 'images'])->find($currentQuestionId) : null;
            $examQuestion = $currentQuestionId ? $session->exam->examQuestions()->where('question_id', $currentQuestionId)->first() : null;
            $payload = ($currentQuestion && $examQuestion)
                ? $this->questionService->buildSafeQuestionPayload($currentQuestion, $examQuestion, $session)
                : null;

            return response()->json([
                'success' => true,
                'message' => 'Exam started successfully.',
                'started_at' => now()->toIso8601String(),
                'remaining_ms' => $this->timerService->getRemainingMilliseconds($session->current_question_deadline_at),
                'exam_remaining_ms' => $this->timerService->getRemainingMilliseconds($session->exam_deadline_at),
                'payload' => $payload,
            ]);
        }

        return redirect()->route('online-exam.take');
    }

    /**
     * Main Examination Screen.
     */
    public function take(Request $request)
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');
        $exam = $session->exam;

        // Check if overall exam has expired
        if ($session->hasExamExpired()) {
            $this->sessionService->finishExam($session, true);

            return redirect()->route('online-exam.result');
        }

        // Determine current question to render
        $currentQuestionId = $session->current_question_id;
        if (! $currentQuestionId) {
            $questionOrder = $session->question_order ?: [];
            $firstQuestionId = ! empty($questionOrder) ? $questionOrder[0] : null;

            if ($firstQuestionId) {
                if ($session->status !== ExamSessionStatus::READY) {
                    $this->questionService->startQuestion($session, $firstQuestionId);
                    $session->refresh();
                }
                $currentQuestionId = $firstQuestionId;
            }
        }

        $currentQuestion = OnlineQuestion::with(['options', 'images'])->find($currentQuestionId);
        $examQuestion = $exam->examQuestions()->where('question_id', $currentQuestionId)->first();

        if (! $currentQuestion || ! $examQuestion) {
            return redirect()->route('online-exam.result');
        }

        $payload = $this->questionService->buildSafeQuestionPayload($currentQuestion, $examQuestion, $session);
        $isReady = ($session->status === ExamSessionStatus::READY);

        return response()
            ->view('student-exam.take', compact('session', 'exam', 'payload', 'isReady'))
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
    }

    /**
     * Submit answer for the current question (AJAX).
     * Enforces: Answer is saved & locked. Does NOT move to next question!
     */
    public function submitAnswer(Request $request)
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'selected_option_ids' => ['nullable', 'array'],
            'selected_option_ids.*' => ['integer'],
            'text_answer' => ['nullable', 'string', 'max:5000'],
            'time_spent_ms' => ['nullable', 'integer', 'min:0'],
            'is_timeout' => ['nullable', 'boolean'],
        ]);

        $result = $this->questionService->submitAnswer(
            $session,
            (int) $validated['question_id'],
            $validated['selected_option_ids'] ?? null,
            $validated['text_answer'] ?? null,
            $request->ip(),
            $request->userAgent(),
            isset($validated['time_spent_ms']) ? (int) $validated['time_spent_ms'] : null,
            $request->boolean('is_timeout')
        );

        return response()->json($result);
    }

    /**
     * Next question action (AJAX / POST).
     * Only called when student explicitly clicks [Next Question].
     */
    public function nextQuestion(Request $request)
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        $nextPayload = $this->questionService->getNextQuestion($session);

        if (! $nextPayload) {
            return response()->json([
                'success' => true,
                'has_next' => false,
                'message' => 'You have completed all questions. You may now submit your examination.',
            ]);
        }

        return response()->json([
            'success' => true,
            'has_next' => true,
            'question' => $nextPayload,
        ]);
    }

    /**
     * Previous question action (AJAX / POST).
     * Only permitted if allow_previous_question is enabled.
     */
    public function previousQuestion(Request $request)
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        $prevPayload = $this->questionService->getPreviousQuestion($session);

        if (! $prevPayload) {
            return response()->json([
                'success' => false,
                'message' => 'Already at first question.',
            ]);
        }

        return response()->json([
            'success' => true,
            'has_previous' => true,
            'question' => $prevPayload,
        ]);
    }

    /**
     * Heartbeat endpoint (every 10-15s from client).
     */
    public function heartbeat(Request $request)
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        $cameraStatus = $request->input('camera_status');
        $fullscreenStatus = $request->has('fullscreen_status') ? $request->boolean('fullscreen_status') : null;

        $status = $this->sessionService->heartbeat(
            $session->session_token,
            $cameraStatus,
            $fullscreenStatus
        );

        return response()->json($status);
    }

    /**
     * Record Anti-Cheating Event from client (fullscreen exit, tab switch, blur).
     */
    public function recordEvent(Request $request)
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        $validated = $request->validate([
            'event_type' => ['required', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $eventType = ExamEventType::tryFrom($validated['event_type']);
        if ($eventType) {
            $this->antiCheatingService->recordEvent(
                $session,
                $eventType,
                $validated['metadata'] ?? null,
                $request->ip(),
                $request->userAgent()
            );
        }

        $session->refresh();

        return response()->json([
            'success' => true,
            'violations_count' => $session->violations_count,
            'is_terminated' => $session->status === ExamSessionStatus::TERMINATED,
            'termination_reason' => $session->termination_reason,
        ]);
    }

    /**
     * Finish examination (student submission).
     */
    public function finish(Request $request)
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        $result = $this->sessionService->finishExam($session, false);

        return redirect()->route('online-exam.result');
    }

    /**
     * Show Result Screen.
     */
    public function result(Request $request)
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        if (! $session->status->isFinal()) {
            $this->sessionService->finishExam($session, true);
            $session->refresh();
        }

        $exam = $session->exam;
        $result = $session->result;

        // Total questions & answered count
        $totalQuestions = $result ? $result->total_questions : ($session->question_order ? count($session->question_order) : $exam->examQuestions()->count());
        $answeredQuestions = $result ? $result->total_attempted : $session->answers()->count();
        $unansweredQuestions = max(0, $totalQuestions - $answeredQuestions);

        // Calculate Used Time & Remaining Time
        $totalDurationSeconds = (int) (($exam->duration_minutes ?: 0) * 60);

        if ($session->exam_started_at && $session->completed_at) {
            $usedSeconds = (int) $session->exam_started_at->diffInSeconds($session->completed_at);
        } elseif ($session->exam_started_at) {
            $usedSeconds = (int) $session->exam_started_at->diffInSeconds(now());
        } else {
            $usedSeconds = 0;
        }

        if ($totalDurationSeconds > 0 && $usedSeconds > $totalDurationSeconds) {
            $usedSeconds = $totalDurationSeconds;
        }

        $remainSeconds = $totalDurationSeconds > 0 ? max(0, $totalDurationSeconds - $usedSeconds) : 0;

        $formatTime = function (int $sec): string {
            $h = intdiv($sec, 3600);
            $m = intdiv($sec % 3600, 60);
            $s = $sec % 60;
            if ($h > 0) {
                return sprintf('%02d:%02d:%02d', $h, $m, $s);
            }

            return sprintf('%02d:%02d', $m, $s);
        };

        $formatTimeText = function (int $sec): string {
            $h = intdiv($sec, 3600);
            $m = intdiv($sec % 3600, 60);
            $s = $sec % 60;
            if ($h > 0) {
                return "{$h}h {$m}m {$s}s";
            }
            if ($m > 0) {
                return "{$m}m {$s}s";
            }

            return "{$s}s";
        };

        $usedTimeFormatted = $formatTime($usedSeconds);
        $usedTimeText = $formatTimeText($usedSeconds);

        $remainTimeFormatted = $totalDurationSeconds > 0 ? $formatTime($remainSeconds) : '--:--';
        $remainTimeText = $totalDurationSeconds > 0 ? $formatTimeText($remainSeconds) : 'Untimed';

        return response()
            ->view('student-exam.result', compact(
                'session',
                'exam',
                'result',
                'totalQuestions',
                'answeredQuestions',
                'unansweredQuestions',
                'usedSeconds',
                'remainSeconds',
                'totalDurationSeconds',
                'usedTimeFormatted',
                'usedTimeText',
                'remainTimeFormatted',
                'remainTimeText'
            ))
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
    }

    /**
     * Show Terminated Screen.
     */
    public function terminated(Request $request)
    {
        $token = session('online_exam_session_token');
        $session = $token ? OnlineExamSession::where('session_token', $token)->first() : null;

        return response()
            ->view('student-exam.terminated', compact('session'))
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
    }
}
