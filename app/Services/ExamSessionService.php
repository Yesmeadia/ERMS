<?php

namespace App\Services;

use App\Models\OnlineExam;
use App\Models\OnlineExamStudent;
use App\Models\OnlineExamSession;
use App\Models\OnlineExamResult;
use App\Models\OnlineExamFailedLogin;
use App\Models\Student;
use App\Models\User;
use App\Enums\ExamStatus;
use App\Enums\ExamSessionStatus;
use App\Enums\ExamEventType;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ExamSessionService
{
    public function __construct(
        protected ExamTimerService $timerService,
        protected ExamScoringService $scoringService,
        protected AntiCheatingService $antiCheatingService,
        protected ExamQuestionService $questionService
    ) {}

    /**
     * Validate student login credentials and eligibility against 8 requirements.
     * Returns [success => bool, message => ?string, student => ?Student, exam => ?OnlineExam, existingSession => ?OnlineExamSession]
     */
    public function validateStudentLogin(
        string $registrationNumber,
        string $dobInput,
        ?string $deviceFingerprint = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): array {
        $regNumber = trim($registrationNumber);
        $ip = $ip ?? request()->ip();
        $userAgent = $userAgent ?? request()->userAgent();

        // 1. Registration number exists
        $student = Student::with(['category', 'school'])
            ->where('registration_number', $regNumber)
            ->first();

        if (!$student) {
            $this->logFailedLogin($regNumber, 'Student record not found', $ip, $userAgent, $dobInput);
            return [
                'success' => false,
                'message' => 'No student record found with this Registration Number.',
            ];
        }

        // 2. Date of birth matches
        $dobParsed = null;
        try {
            $dobParsed = Carbon::parse($dobInput)->format('Y-m-d');
        } catch (\Exception $e) {
            // Try alternate format
        }

        if (!$dobParsed || !$student->dob || $student->dob->format('Y-m-d') !== $dobParsed) {
            $this->logFailedLogin($regNumber, 'Date of birth mismatch', $ip, $userAgent, $dobInput);
            return [
                'success' => false,
                'message' => 'The Date of Birth provided does not match our records.',
            ];
        }

        // 3. Student is enrolled in an online exam
        $enrollment = OnlineExamStudent::with('exam')
            ->where('student_id', $student->id)
            ->where('is_eligible', true)
            ->whereHas('exam', function ($q) {
                $q->whereIn('status', [ExamStatus::PUBLISHED, ExamStatus::ACTIVE]);
            })
            ->latest('id')
            ->first();

        if (!$enrollment || !$enrollment->exam) {
            $this->logFailedLogin($regNumber, 'Not enrolled in any active exam', $ip, $userAgent, null);
            return [
                'success' => false,
                'message' => 'You are not currently enrolled in any active online examination.',
            ];
        }

        $exam = $enrollment->exam;

        // 4. Student belongs to the correct category
        if ($exam->category_id !== $student->category_id) {
            $this->logFailedLogin($regNumber, 'Student category mismatch', $ip, $userAgent, null);
            return [
                'success' => false,
                'message' => 'Your student category does not match this examination category.',
            ];
        }

        // 5. Exam is available today
        $today = now()->format('Y-m-d');
        if ($exam->exam_date->format('Y-m-d') !== $today && !app()->environment('testing', 'local')) {
            $this->logFailedLogin($regNumber, 'Exam not scheduled for today', $ip, $userAgent, null);
            return [
                'success' => false,
                'message' => "This examination is scheduled for {$exam->exam_date->format('d M Y')}.",
            ];
        }

        // 6. Check existing results: Enforce strictly 1 attempt per student
        $existingResult = OnlineExamResult::where('online_exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingResult) {
            $this->logFailedLogin($regNumber, 'Already submitted — 1-attempt enforced', $ip, $userAgent, null);
            return [
                'success' => false,
                'message' => 'You have already completed this examination. Students are only permitted to enter the exam once.',
            ];
        }

        // 7. Check existing session and enforce: One Registration Number = One Active Entry
        $existingSession = OnlineExamSession::where('online_exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSession) {
            // Check if overall exam deadline has expired for this session
            if (! $existingSession->status->isFinal() && $existingSession->hasExamExpired()) {
                $this->finishExam($existingSession, true);
                $existingSession->refresh();
            }

            // Already completed, expired, or terminated
            if ($existingSession->status === ExamSessionStatus::SUBMITTED) {
                $this->logFailedLogin($regNumber, 'Session already SUBMITTED — 1-attempt enforced', $ip, $userAgent, null);
                return [
                    'success' => false,
                    'message' => 'You have already completed and submitted this examination. Only 1 entry is permitted.',
                ];
            }

            if ($existingSession->status === ExamSessionStatus::TERMINATED) {
                $this->logFailedLogin($regNumber, 'Session TERMINATED — 1-attempt enforced', $ip, $userAgent, null);
                return [
                    'success' => false,
                    'message' => 'Your examination session was terminated due to rule violations. Only 1 entry is permitted.',
                ];
            }

            if ($existingSession->status === ExamSessionStatus::EXPIRED) {
                $this->logFailedLogin($regNumber, 'Session EXPIRED — 1-attempt enforced', $ip, $userAgent, null);
                return [
                    'success' => false,
                    'message' => 'Your examination session has expired. Only 1 entry is permitted.',
                ];
            }

            // Check if active on another device/browser
            $hasRecentHeartbeat = $existingSession->last_heartbeat_at &&
                $existingSession->last_heartbeat_at->diffInSeconds(now()) < 45;

            $isDifferentDevice = $deviceFingerprint &&
                $existingSession->device_fingerprint_hash &&
                $existingSession->device_fingerprint_hash !== hash('sha256', $deviceFingerprint);

            if ($hasRecentHeartbeat && $isDifferentDevice) {
                $this->logFailedLogin($regNumber, 'Concurrent device detected — two-browser block', $ip, $userAgent, null);
                return [
                    'success' => false,
                    'message' => 'This examination is already active on another device.',
                ];
            }
        }

        return [
            'success' => true,
            'student' => $student,
            'exam' => $exam,
            'existingSession' => $existingSession ?? null,
        ];
    }

    /**
     * Persist a failed student login attempt for forensic/audit purposes.
     * Deliberately non-throwing — a logging failure must never block the login response.
     */
    private function logFailedLogin(
        string $registrationNumber,
        string $reason,
        ?string $ip,
        ?string $userAgent,
        ?string $dobAttempted
    ): void {
        try {
            OnlineExamFailedLogin::create([
                'registration_number' => $registrationNumber,
                'failure_reason'      => $reason,
                'ip_address'          => $ip,
                'user_agent'          => $userAgent ? substr($userAgent, 0, 512) : null,
                'dob_attempted'       => $dobAttempted ? substr($dobAttempted, 0, 20) : null,
                'attempted_at'        => now(),
            ]);
        } catch (\Throwable) {
            // Silently swallow — logging must never disrupt the auth flow
        }
    }

    /**
     * Start or resume an examination session.
     */
    public function startOrResumeSession(
        OnlineExam $exam,
        Student $student,
        string $ip,
        ?string $userAgent = null,
        ?string $deviceFingerprint = null
    ): OnlineExamSession {
        return DB::transaction(function () use ($exam, $student, $ip, $userAgent, $deviceFingerprint) {
            $fingerprintHash = $deviceFingerprint ? hash('sha256', $deviceFingerprint) : null;

            $session = OnlineExamSession::where('online_exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            $now = now();

            if ($session) {
                // Resume existing session
                $session->update([
                    'session_version' => $session->session_version + 1,
                    'device_fingerprint_hash' => $fingerprintHash ?: $session->device_fingerprint_hash,
                    'last_activity_at' => $now,
                    'last_heartbeat_at' => $now,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent ?: $session->user_agent,
                ]);

                $this->antiCheatingService->recordEvent($session, ExamEventType::LOGIN, [
                    'action' => 'session_resumed',
                    'session_version' => $session->session_version,
                ], $ip, $userAgent);

                return $session;
            }

            // Create new session
            $questionIds = $exam->examQuestions()->pluck('question_id')->toArray();
            if ($exam->randomize_questions) {
                shuffle($questionIds);
            }

            $examDeadline = $this->timerService->calculateStudentExamDeadline($exam, $now);
            $token = Str::random(64);

            $firstQuestionId = !empty($questionIds) ? $questionIds[0] : null;

            $session = OnlineExamSession::create([
                'online_exam_id' => $exam->id,
                'student_id' => $student->id,
                'registration_number' => $student->registration_number,
                'session_token' => $token,
                'session_version' => 1,
                'device_fingerprint_hash' => $fingerprintHash,
                'status' => ExamSessionStatus::READY,
                'question_order' => $questionIds,
                'current_question_id' => $firstQuestionId,
                'current_question_index' => 0,
                'exam_started_at' => $now,
                'exam_deadline_at' => $examDeadline,
                'last_heartbeat_at' => $now,
                'last_activity_at' => $now,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);

            $this->antiCheatingService->recordEvent($session, ExamEventType::LOGIN, [
                'action' => 'new_session_created',
            ], $ip, $userAgent);

            $this->antiCheatingService->recordEvent($session, ExamEventType::EXAM_STARTED, [
                'total_questions' => count($questionIds),
                'exam_deadline' => $examDeadline->toIso8601String(),
            ], $ip, $userAgent);

            return $session;
        });
    }

    /**
     * Process student heartbeat, update activity, check deadlines.
     */
    public function heartbeat(
        string $sessionToken,
        ?string $cameraStatus = null,
        ?bool $fullscreenStatus = null
    ): array {
        $session = OnlineExamSession::with('exam')
            ->where('session_token', $sessionToken)
            ->firstOrFail();

        $now = now();
        $session->update([
            'last_heartbeat_at' => $now,
            'last_activity_at' => $now,
        ]);

        if ($cameraStatus) {
            $this->antiCheatingService->updateCameraStatus($session, $cameraStatus);
        }

        if ($fullscreenStatus !== null) {
            $this->antiCheatingService->updateFullscreenStatus($session, $fullscreenStatus);
        }

        // Check if overall exam timer has expired
        if ($session->hasExamExpired() && !$session->status->isFinal()) {
            $this->finishExam($session, true);
            $session->refresh();
        }

        $examRemainingMs = $this->timerService->getRemainingMilliseconds($session->exam_deadline_at);
        $questionRemainingMs = $this->timerService->getRemainingMilliseconds($session->current_question_deadline_at);

        return [
            'status' => $session->status->value,
            'server_time' => $now->toIso8601String(),
            'exam_remaining_ms' => $examRemainingMs,
            'question_remaining_ms' => $questionRemainingMs,
            'violations_count' => $session->violations_count,
            'is_terminated' => $session->status === ExamSessionStatus::TERMINATED,
            'is_submitted' => $session->status === ExamSessionStatus::SUBMITTED || $session->status === ExamSessionStatus::EXPIRED,
        ];
    }

    /**
     * Finalize and submit the examination (student action or auto-submit).
     */
    public function finishExam(OnlineExamSession $session, bool $isAutoSubmit = false): OnlineExamResult
    {
        return DB::transaction(function () use ($session, $isAutoSubmit) {
            $status = $isAutoSubmit ? ExamSessionStatus::EXPIRED : ExamSessionStatus::SUBMITTED;
            $eventType = $isAutoSubmit ? ExamEventType::EXAM_AUTO_SUBMITTED : ExamEventType::EXAM_SUBMITTED;

            $session->update([
                'status' => $status,
                'completed_at' => now(),
            ]);

            // Finalize result and rankings
            $result = $this->scoringService->finalizeResult($session);

            $this->antiCheatingService->recordEvent($session, $eventType, [
                'final_score' => $result->final_score,
                'total_attempted' => $result->total_attempted,
                'is_auto_submit' => $isAutoSubmit,
            ]);

            return $result;
        });
    }

    /**
     * Terminate an active student session by an administrator with audit log.
     */
    public function terminateSessionByAdmin(
        OnlineExamSession $session,
        User $admin,
        string $reason
    ): void {
        DB::transaction(function () use ($session, $admin, $reason) {
            $session->update([
                'status' => ExamSessionStatus::TERMINATED,
                'termination_reason' => "Terminated by Administrator ({$admin->name}): {$reason}",
                'completed_at' => now(),
            ]);

            $this->scoringService->finalizeResult($session);

            $this->antiCheatingService->recordEvent($session, ExamEventType::EXAM_TERMINATED, [
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'reason' => $reason,
            ]);
        });
    }
}
