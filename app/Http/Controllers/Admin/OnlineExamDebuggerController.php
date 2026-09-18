<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExamEventType;
use App\Enums\ExamSessionStatus;
use App\Http\Controllers\Controller;
use App\Models\OnlineExam;
use App\Models\OnlineExamSession;
use App\Models\OnlineExamSessionEvent;
use App\Models\OnlineExamWebrtcSignal;
use App\Services\AntiCheatingService;
use App\Services\WebRTC\IceServerService;
use App\Services\WebRTC\MeteredTurnProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OnlineExamDebuggerController extends Controller
{
    public function __construct(
        protected IceServerService $iceServerService,
        protected MeteredTurnProvider $meteredProvider,
        protected AntiCheatingService $antiCheatingService
    ) {}

    /**
     * Show the WebRTC & Live Proctoring Diagnostic Debugger view.
     */
    public function show(OnlineExam $online_exam)
    {
        $this->authorize('manageSubResource', $online_exam);

        $exam = $online_exam->load(['category']);

        // Gather server & PHP environmental baseline
        $envInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => request()->server('SERVER_SOFTWARE', 'Unknown'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'db_connection' => config('database.default'),
            'server_time' => now()->toIso8601String(),
            'server_timezone' => config('app.timezone'),
        ];

        // Masked WebRTC config
        $domain = config('webrtc.metered.domain');
        $secretKey = config('webrtc.metered.secret_key');
        $apiKey = config('webrtc.metered.project_api_key');
        $webrtcConfig = [
            'webrtc_enabled' => (bool) config('webrtc.enabled', true),
            'stun_enabled' => (bool) config('webrtc.stun.enabled', true),
            'stun_urls' => config('webrtc.stun.urls', []),
            'turn_enabled' => (bool) config('webrtc.turn.enabled', false),
            'turn_provider' => config('webrtc.turn.provider', 'metered'),
            'metered_enabled' => (bool) config('webrtc.metered.enabled', false),
            'metered_domain' => $domain ? $this->maskString($domain, 4) : 'NOT SET',
            'metered_domain_raw' => $domain,
            'has_secret_key' => !empty($secretKey),
            'secret_key_masked' => $secretKey ? $this->maskString($secretKey, 6) : 'NOT SET',
            'has_project_api_key' => !empty($apiKey),
            'project_api_key_masked' => $apiKey ? $this->maskString($apiKey, 6) : 'NOT SET',
            'credential_label' => config('webrtc.metered.credential_label'),
            'credential_expiry' => (int) config('webrtc.metered.credential_expiry', 172800),
            'cache_key' => config('webrtc.cache.key', 'webrtc:metered:active-credential'),
        ];

        // Check cached credentials
        $cacheKey = config('webrtc.cache.key', 'webrtc:metered:active-credential');
        $cached = Cache::get($cacheKey);
        $cachedInfo = [
            'is_cached' => is_array($cached) && !empty($cached['ice_servers']),
            'server_count' => is_array($cached) && !empty($cached['ice_servers']) ? count($cached['ice_servers']) : 0,
            'created_at' => (is_array($cached) && isset($cached['created_at'])) ? date('Y-m-d H:i:s', $cached['created_at']) : null,
            'expires_at' => (is_array($cached) && isset($cached['expires_at'])) ? date('Y-m-d H:i:s', $cached['expires_at']) : null,
            'seconds_remaining' => (is_array($cached) && isset($cached['expires_at'])) ? max(0, $cached['expires_at'] - now()->timestamp) : 0,
        ];

        // Active generated ICE servers
        $iceServers = $this->iceServerService->getIceServers();
        $hasTurnRelay = false;
        foreach ($iceServers as $server) {
            $urls = (array) ($server['urls'] ?? []);
            foreach ($urls as $u) {
                if (str_starts_with($u, 'turn:') || str_starts_with($u, 'turns:')) {
                    $hasTurnRelay = true;
                    break 2;
                }
            }
        }

        // Exam summary stats from DB
        $totalEnrolled = $exam->examStudents()->count();
        $sessionsCount = OnlineExamSession::where('online_exam_id', $exam->id)->count();
        $eventsCount = OnlineExamSessionEvent::whereHas('session', fn ($q) => $q->where('online_exam_id', $exam->id))->count();
        $signalsCount = OnlineExamWebrtcSignal::whereHas('session', fn ($q) => $q->where('online_exam_id', $exam->id))->count();

        return view('admin.online-exams.debugger', [
            'exam' => $exam,
            'envInfo' => $envInfo,
            'webrtcConfig' => $webrtcConfig,
            'cachedInfo' => $cachedInfo,
            'iceServers' => $iceServers,
            'hasTurnRelay' => $hasTurnRelay,
            'totalEnrolled' => $totalEnrolled,
            'sessionsCount' => $sessionsCount,
            'eventsCount' => $eventsCount,
            'signalsCount' => $signalsCount,
        ]);
    }

    /**
     * Return comprehensive real-time diagnostics JSON.
     */
    public function diagnosticsData(OnlineExam $online_exam)
    {
        $this->authorize('manageSubResource', $online_exam);

        $exam = $online_exam;

        // 1. Check TURN Provider status
        $isAvailable = $this->meteredProvider->isAvailable();
        $iceServers = $this->iceServerService->getIceServers();
        $hasTurnRelay = false;
        $turnServersList = [];
        $stunServersList = [];

        foreach ($iceServers as $server) {
            $urls = (array) ($server['urls'] ?? []);
            foreach ($urls as $u) {
                if (str_starts_with($u, 'turn:') || str_starts_with($u, 'turns:')) {
                    $hasTurnRelay = true;
                    $turnServersList[] = [
                        'url' => $u,
                        'username' => $server['username'] ?? 'none',
                        'has_credential' => !empty($server['credential']),
                    ];
                } else {
                    $stunServersList[] = $u;
                }
            }
        }

        // 2. Fetch Sessions & check reconciliation
        $sessions = OnlineExamSession::with(['student.school', 'currentQuestion'])
            ->where('online_exam_id', $exam->id)
            ->latest('last_activity_at')
            ->get();

        $now = now();
        $thresholdSeconds = 30;

        $sessionDetails = [];
        $violationsMismatchCount = 0;
        $stats = [
            'enrolled' => $exam->examStudents()->count(),
            'attended' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'timed_out' => 0,
            'terminated' => 0,
            'violations_total' => 0,
        ];

        foreach ($sessions as $s) {
            $logCount = $this->antiCheatingService->countViolationsFromLog($s);
            $hasMismatch = ($s->violations_count !== $logCount);
            if ($hasMismatch) $violationsMismatchCount++;

            $isCompleted = in_array($s->status, [ExamSessionStatus::SUBMITTED, ExamSessionStatus::EXPIRED], true)
                || (!empty($s->completed_at) && $s->status !== ExamSessionStatus::TERMINATED);
            $isTerminated = $s->status === ExamSessionStatus::TERMINATED;
            $isOnline = ! $isCompleted && ! $isTerminated && $s->last_heartbeat_at && $now->diffInSeconds($s->last_heartbeat_at) <= $thresholdSeconds;
            $isTimedOut = in_array($s->status, [ExamSessionStatus::EXPIRED, ExamSessionStatus::QUESTION_TIMEOUT], true);

            if ($isTerminated) {
                $stats['terminated']++;
            } elseif ($s->status === ExamSessionStatus::SUBMITTED || (!empty($s->completed_at) && $s->status !== ExamSessionStatus::TERMINATED)) {
                $stats['completed']++;
            } elseif ($s->status === ExamSessionStatus::EXPIRED) {
                $stats['completed']++;
                $stats['timed_out']++;
            } elseif ($s->status === ExamSessionStatus::QUESTION_TIMEOUT) {
                $stats['timed_out']++;
                $stats['in_progress']++;
            } elseif ($s->status !== ExamSessionStatus::NOT_STARTED) {
                $stats['in_progress']++;
            }

            if ($s->status !== ExamSessionStatus::NOT_STARTED) {
                $stats['attended']++;
            }

            $stats['violations_total'] += (int) $s->violations_count;

            $sessionDetails[] = [
                'session_id' => $s->id,
                'student_id' => $s->student_id,
                'student_name' => $s->student?->name ?? 'Candidate #' . $s->student_id,
                'registration_number' => $s->student?->registration_number ?? '-',
                'status' => $s->status?->value ?? 'UNKNOWN',
                'status_label' => $s->status ? $s->status->label() : 'Unknown',
                'violations_count' => (int) $s->violations_count,
                'log_violations_count' => $logCount,
                'has_violations_mismatch' => $hasMismatch,
                'is_online' => (bool) $isOnline,
                'is_completed' => (bool) $isCompleted,
                'is_terminated' => (bool) $isTerminated,
                'is_timed_out' => (bool) $isTimedOut,
                'last_heartbeat_at' => $s->last_heartbeat_at?->toIso8601String(),
                'last_heartbeat_ago' => $s->last_heartbeat_at ? $s->last_heartbeat_at->diffForHumans(null, true) : 'Never',
                'camera_status' => $s->camera_status ?? 'NOT_RECORDED',
                'fullscreen_status' => (bool) $s->fullscreen_status,
                'termination_reason' => $s->termination_reason,
                'ip_address' => $s->ip_address,
            ];
        }

        // 3. Latest 50 Audit Events
        $recentEvents = OnlineExamSessionEvent::whereHas('session', fn ($q) => $q->where('online_exam_id', $exam->id))
            ->with(['session.student'])
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'session_id' => $e->online_exam_session_id,
                    'student_name' => $e->session?->student?->name ?? 'Candidate #' . $e->student_id,
                    'registration_number' => $e->session?->student?->registration_number ?? '-',
                    'event_type' => $e->event_type instanceof \BackedEnum ? $e->event_type->value : (string) $e->event_type,
                    'is_violation' => (bool) $e->is_violation,
                    'event_time' => $e->event_time?->format('Y-m-d H:i:s'),
                    'event_time_ago' => $e->event_time ? $e->event_time->diffForHumans(null, true) : '',
                    'metadata' => $e->metadata,
                    'ip_address' => $e->ip_address,
                ];
            });

        // 4. Latest 50 WebRTC Signals
        $recentSignals = OnlineExamWebrtcSignal::whereHas('session', fn ($q) => $q->where('online_exam_id', $exam->id))
            ->with(['session.student'])
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'session_id' => $s->online_exam_session_id,
                    'student_name' => $s->session?->student?->name ?? 'Candidate',
                    'sender_type' => $s->sender_type,
                    'recipient_type' => $s->recipient_type,
                    'type' => $s->type,
                    'is_consumed' => (bool) $s->is_consumed,
                    'created_at' => $s->created_at?->format('Y-m-d H:i:s'),
                    'payload_summary' => is_array($s->payload) ? array_keys($s->payload) : (is_string($s->payload) ? substr($s->payload, 0, 50) : null),
                ];
            });

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'turn' => [
                'is_available' => $isAvailable,
                'has_turn_relay' => $hasTurnRelay,
                'stun_servers' => $stunServersList,
                'turn_servers' => $turnServersList,
                'total_ice_servers' => count($iceServers),
            ],
            'stats' => $stats,
            'violations_mismatch_count' => $violationsMismatchCount,
            'sessions' => $sessionDetails,
            'recent_events' => $recentEvents,
            'recent_signals' => $recentSignals,
        ]);
    }

    /**
     * Test real connection to Metered TURN API from PHP server with full HTTP probe.
     */
    public function testMeteredApi(OnlineExam $online_exam)
    {
        $this->authorize('manageSubResource', $online_exam);

        $domain = $this->meteredProvider->getCleanDomain();
        $apiKey = config('webrtc.metered.project_api_key');
        $secretKey = config('webrtc.metered.secret_key');

        $results = [
            'timestamp' => now()->toIso8601String(),
            'clean_domain' => $domain,
            'get_test' => null,
            'post_test' => null,
            'overall_status' => 'FAIL',
            'summary' => '',
            'recommended_fix' => '',
        ];

        if (empty($domain)) {
            $results['summary'] = 'METERED_DOMAIN is empty in .env. Please set METERED_DOMAIN=your-app.metered.live';
            $results['recommended_fix'] = 'Add METERED_DOMAIN=your-app.metered.live to .env on your server.';
            return response()->json($results);
        }

        // Test Method 1: GET /api/v1/turn/credentials?apiKey={projectApiKey}
        if (!empty($apiKey)) {
            $start = microtime(true);
            try {
                $getUrl = "https://{$domain}/api/v1/turn/credentials";
                $res = Http::timeout(8)->acceptJson()->get($getUrl, ['apiKey' => $apiKey]);
                $duration = round((microtime(true) - $start) * 1000, 1);

                $rawBody = $res->json() ?? $res->body();
                $isOk = $res->successful() && is_array($rawBody) && count($rawBody) > 0;

                $results['get_test'] = [
                    'url' => $getUrl . '?apiKey=' . substr($apiKey, 0, 4) . '...',
                    'http_status' => $res->status(),
                    'duration_ms' => $duration,
                    'is_success' => $isOk,
                    'headers' => $res->headers(),
                    'response_body' => $rawBody,
                    'ice_servers_count' => is_array($rawBody) ? count($rawBody) : 0,
                ];

                if ($isOk) {
                    $results['overall_status'] = 'PASS';
                    $results['summary'] = "GET /credentials succeeded in {$duration}ms! Returned " . count($rawBody) . " ICE servers.";
                } else {
                    $results['summary'] = "GET /credentials returned HTTP {$res->status()}. Response: " . json_encode($rawBody);
                    if ($res->status() === 401) {
                        $results['recommended_fix'] = '401 Invalid API Key: Check your Metered Dashboard under "API Keys" and copy the API Key into METERED_PROJECT_API_KEY in .env.';
                    }
                }
            } catch (\Throwable $e) {
                $results['get_test'] = [
                    'error' => $e->getMessage(),
                    'duration_ms' => round((microtime(true) - $start) * 1000, 1),
                ];
                $results['summary'] = 'GET /credentials threw exception: ' . $e->getMessage();
            }
        } else {
            $results['get_test'] = [
                'skipped' => true,
                'reason' => 'METERED_PROJECT_API_KEY is empty in .env.',
            ];
        }

        // Test Method 2: POST /api/v1/turn/credential?secretKey={secretKey}
        if (!empty($secretKey)) {
            $start = microtime(true);
            try {
                $postUrl = "https://{$domain}/api/v1/turn/credential?secretKey=" . urlencode($secretKey);
                $res = Http::timeout(8)->acceptJson()->post($postUrl, [
                    'label' => 'erms-diagnostic-probe',
                    'expiryInSeconds' => 3600,
                ]);
                $duration = round((microtime(true) - $start) * 1000, 1);
                $rawBody = $res->json() ?? $res->body();
                $isOk = $res->successful() && !empty($rawBody['username']);

                $results['post_test'] = [
                    'url' => "https://{$domain}/api/v1/turn/credential?secretKey=" . substr($secretKey, 0, 4) . '...',
                    'http_status' => $res->status(),
                    'duration_ms' => $duration,
                    'is_success' => $isOk,
                    'response_body' => $rawBody,
                    'username_returned' => $rawBody['username'] ?? null,
                ];

                if ($isOk) {
                    if ($results['overall_status'] !== 'PASS') {
                        $results['overall_status'] = 'PASS';
                        $results['summary'] = "POST /credential succeeded in {$duration}ms! Successfully created credentials for user: " . ($rawBody['username'] ?? '');
                    }
                } else {
                    if ($results['overall_status'] !== 'PASS') {
                        $results['summary'] .= " | POST /credential returned HTTP {$res->status()}.";
                        if ($res->status() === 401) {
                            $results['recommended_fix'] .= ' 401 Invalid Secret Key: In your Metered Dashboard, check "Secret Key" (under Developer / Settings) and copy to METERED_SECRET_KEY in .env.';
                        }
                    }
                }
            } catch (\Throwable $e) {
                $results['post_test'] = [
                    'error' => $e->getMessage(),
                    'duration_ms' => round((microtime(true) - $start) * 1000, 1),
                ];
            }
        } else {
            $results['post_test'] = [
                'skipped' => true,
                'reason' => 'METERED_SECRET_KEY is empty in .env.',
            ];
        }

        // If either test passed, also refresh cache now
        if ($results['overall_status'] === 'PASS') {
            try {
                $fresh = $this->meteredProvider->refreshCredentials(true);
                $results['refreshed_credentials_count'] = count($fresh);
            } catch (\Throwable) {}
        }

        return response()->json($results);
    }

    /**
     * Send a test WebRTC signal to verify signal insertion and retrieval.
     */
    public function testSignal(Request $request, OnlineExam $online_exam)
    {
        $this->authorize('manageSubResource', $online_exam);

        $validated = $request->validate([
            'session_id' => ['required', 'integer'],
            'type' => ['required', 'string', 'in:offer,answer,candidate,close,test_ping'],
        ]);

        $session = OnlineExamSession::where('online_exam_id', $online_exam->id)
            ->where('id', $validated['session_id'])
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found for this exam.'], 404);
        }

        $signal = OnlineExamWebrtcSignal::create([
            'online_exam_session_id' => $session->id,
            'admin_id' => Auth::id(),
            'sender_type' => 'admin',
            'recipient_type' => 'student',
            'type' => $validated['type'] === 'test_ping' ? 'offer' : $validated['type'],
            'payload' => [
                'is_diagnostic_ping' => true,
                'sent_at' => now()->toIso8601String(),
                'sender' => Auth::user()?->name ?? 'Admin',
            ],
            'is_consumed' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Diagnostic signal ID {$signal->id} successfully written to database.",
            'signal' => $signal,
        ]);
    }

    /**
     * Simulate recording an anti-cheating audit event to test DB writes and strike calculations.
     */
    public function testEvent(Request $request, OnlineExam $online_exam)
    {
        $this->authorize('manageSubResource', $online_exam);

        $validated = $request->validate([
            'session_id' => ['required', 'integer'],
            'event_type' => ['required', 'string'],
        ]);

        $session = OnlineExamSession::where('online_exam_id', $online_exam->id)
            ->where('id', $validated['session_id'])
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found for this exam.'], 404);
        }

        $eventType = ExamEventType::tryFrom($validated['event_type']);
        if (!$eventType) {
            return response()->json(['success' => false, 'message' => "Unknown event type '{$validated['event_type']}'."], 422);
        }

        $initialViolations = $session->violations_count;

        $event = $this->antiCheatingService->recordEvent(
            $session,
            $eventType,
            ['simulated_from_debugger' => true, 'triggered_by' => Auth::user()?->name],
            $request->ip(),
            $request->userAgent()
        );

        $session->refresh();

        return response()->json([
            'success' => true,
            'message' => "Event {$eventType->value} recorded successfully.",
            'event_id' => $event->id,
            'is_violation' => $event->is_violation,
            'initial_violations' => $initialViolations,
            'new_violations_count' => $session->violations_count,
            'is_terminated' => $session->status === ExamSessionStatus::TERMINATED,
        ]);
    }

    /**
     * Synchronize violations count for all sessions in this exam.
     */
    public function syncViolations(OnlineExam $online_exam)
    {
        $this->authorize('manageSubResource', $online_exam);

        $sessions = OnlineExamSession::where('online_exam_id', $online_exam->id)->get();
        $updated = 0;
        $details = [];

        foreach ($sessions as $session) {
            $before = $session->violations_count;
            $after = $this->antiCheatingService->syncViolationsCountWithLog($session);
            if ($before !== $after) {
                $updated++;
                $details[] = [
                    'session_id' => $session->id,
                    'student' => $session->student?->name ?? 'Candidate #' . $session->student_id,
                    'before' => $before,
                    'after' => $after,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Synchronized {$sessions->count()} sessions. {$updated} sessions had mismatched counts updated.",
            'updated_count' => $updated,
            'details' => $details,
        ]);
    }

    /**
     * Helper to safely mask credentials for display.
     */
    protected function maskString(?string $str, int $visibleChars = 4): string
    {
        if (empty($str)) return '';
        $len = strlen($str);
        if ($len <= $visibleChars * 2) return str_repeat('*', $len);
        return substr($str, 0, $visibleChars) . str_repeat('*', max(4, $len - ($visibleChars * 2))) . substr($str, -$visibleChars);
    }
}
