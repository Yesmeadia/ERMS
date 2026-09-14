<?php

namespace App\Services;

use App\Models\OnlineExamSession;
use App\Models\OnlineExamSessionEvent;
use App\Enums\ExamEventType;
use App\Enums\ExamSessionStatus;

class AntiCheatingService
{
    public function __construct(
        protected ExamScoringService $scoringService
    ) {
    }

    public function recordEvent(
        OnlineExamSession $session,
        ExamEventType $eventType,
        ?array $metadata = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): OnlineExamSessionEvent {
        $event = OnlineExamSessionEvent::create([
            'online_exam_session_id' => $session->id,
            'student_id' => $session->student_id,
            'event_type' => $eventType,
            'event_time' => now(),
            'metadata' => $metadata,
            'ip_address' => $ip ?: $session->ip_address,
            'user_agent' => $userAgent ?: $session->user_agent,
        ]);

        // Check if event is a rule violation
        if ($eventType->isViolation()) {
            $isDebounced = OnlineExamSessionEvent::where('online_exam_session_id', $session->id)
                ->where('id', '!=', $event->id)
                ->where('event_time', '>=', now()->subSeconds(2))
                ->whereIn('event_type', [
                    ExamEventType::FULLSCREEN_EXIT,
                    ExamEventType::WINDOW_BLUR,
                    ExamEventType::TAB_SWITCH,
                    ExamEventType::CAMERA_STOPPED,
                    ExamEventType::CAMERA_INTERRUPTED,
                ])
                ->exists();

            if (!$isDebounced) {
                $session->increment('violations_count');
                $session->refresh();

                // Tag event as an active violation strike
                $meta = $event->metadata ?? [];
                $meta['is_violation_strike'] = true;
                $meta['strike_number'] = $session->violations_count;
                $event->update(['metadata' => $meta]);

                $maxAllowed = (int) $session->exam->max_fullscreen_violations;
                if ($session->violations_count >= $maxAllowed && !$session->status->isFinal()) {
                    $this->terminateSessionDueToViolations($session, $maxAllowed);
                }
            } else {
                // Tag event as debounced incident (part of same rapid cluster)
                $meta = $event->metadata ?? [];
                $meta['is_debounced'] = true;
                $meta['is_violation_strike'] = false;
                $event->update(['metadata' => $meta]);
            }
        }

        return $event;
    }

    /**
     * Calculate authoritative violations count by checking the session event log.
     * Considers any group of violation events occurring within a 2-second window as 1 single incident.
     */
    public function countViolationsFromLog(OnlineExamSession $session): int
    {
        $violationTypes = [
            ExamEventType::FULLSCREEN_EXIT,
            ExamEventType::WINDOW_BLUR,
            ExamEventType::TAB_SWITCH,
            ExamEventType::CAMERA_STOPPED,
            ExamEventType::CAMERA_INTERRUPTED,
        ];

        $events = OnlineExamSessionEvent::where('online_exam_session_id', $session->id)
            ->whereIn('event_type', $violationTypes)
            ->orderBy('event_time')
            ->orderBy('id')
            ->get();

        $count = 0;
        $lastViolationTime = null;

        foreach ($events as $event) {
            $eventTime = $event->event_time;
            if ($lastViolationTime === null || $eventTime->diffInSeconds($lastViolationTime) >= 2) {
                $count++;
                $lastViolationTime = $eventTime;
            }
        }

        return $count;
    }

    /**
     * Reconcile session violations_count column with the audit event log.
     */
    public function syncViolationsCountWithLog(OnlineExamSession $session): int
    {
        $logCount = $this->countViolationsFromLog($session);
        if ($session->violations_count !== $logCount) {
            $session->update(['violations_count' => $logCount]);
            $session->refresh();
        }

        return $logCount;
    }

    /**
     * Terminate the session when student exceeds maximum allowed violations.
     */
    protected function terminateSessionDueToViolations(OnlineExamSession $session, int $maxViolations): void
    {
        $session->update([
            'status' => ExamSessionStatus::TERMINATED,
            'termination_reason' => "Examination terminated due to exceeding {$maxViolations} violation warnings.",
            'completed_at' => now(),
        ]);

        // Finalize student result based on answers submitted so far
        $this->scoringService->finalizeResult($session);

        // Record termination event
        OnlineExamSessionEvent::create([
            'online_exam_session_id' => $session->id,
            'student_id' => $session->student_id,
            'event_type' => ExamEventType::EXAM_TERMINATED,
            'event_time' => now(),
            'metadata' => [
                'reason' => 'max_violations_exceeded',
                'violations_count' => $session->violations_count,
            ],
            'ip_address' => $session->ip_address,
            'user_agent' => $session->user_agent,
        ]);
    }

    /**
     * Update camera status signal.
     */
    public function updateCameraStatus(OnlineExamSession $session, string $cameraStatus): void
    {
        $prev = strtoupper((string) $session->camera_status);
        $session->update(['camera_status' => $cameraStatus]);

        $normalized = strtoupper(trim($cameraStatus));
        if ($normalized === 'INACTIVE') {
            $normalized = 'STOPPED';
        }

        if ($prev !== $normalized) {
            $eventType = match ($normalized) {
                'ACTIVE' => ExamEventType::CAMERA_RECONNECTED,
                'STOPPED' => ExamEventType::CAMERA_STOPPED,
                'INTERRUPTED' => ExamEventType::CAMERA_INTERRUPTED,
                default => null,
            };

            if ($eventType) {
                $this->recordEvent($session, $eventType, ['previous' => $prev, 'current' => $cameraStatus]);
            }
        }
    }

    /**
     * Update fullscreen status signal.
     */
    public function updateFullscreenStatus(OnlineExamSession $session, bool $fullscreenStatus): void
    {
        $prev = $session->fullscreen_status;
        $session->update(['fullscreen_status' => $fullscreenStatus]);

        if ($prev && !$fullscreenStatus) {
            $this->recordEvent($session, ExamEventType::FULLSCREEN_EXIT, [
                'action' => 'fullscreen_exited',
            ]);
        } elseif (!$prev && $fullscreenStatus) {
            $this->recordEvent($session, ExamEventType::FULLSCREEN_ENTER, [
                'action' => 'fullscreen_entered',
            ]);
        }
    }

    /**
     * Terminate an active session (manually by proctor/admin or system).
     */
    public function terminateSession(OnlineExamSession $session, string $reason, ?\App\Models\User $admin = null): void
    {
        if ($admin) {
            app(ExamSessionService::class)->terminateSessionByAdmin($session, $admin, $reason);
        } else {
            $session->update([
                'status' => ExamSessionStatus::TERMINATED,
                'termination_reason' => $reason,
                'completed_at' => now(),
            ]);

            $this->scoringService->finalizeResult($session);

            $this->recordEvent($session, ExamEventType::EXAM_TERMINATED, [
                'reason' => $reason,
            ]);
        }
    }
}
