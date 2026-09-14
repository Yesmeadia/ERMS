<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 select-none">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->name }} | Online Examination</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        /* Prevent selection during test */
        * { -webkit-user-select: none; user-select: none; }
        input[type="text"], textarea { -webkit-user-select: auto; user-select: auto; }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col justify-between overflow-x-hidden antialiased">

    <!-- Top Navigation / Proctoring Bar -->
    <header class="bg-slate-900/90 border-b border-slate-800 px-4 sm:px-6 py-3 shrink-0 backdrop-blur z-20 sticky top-0">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <!-- Left: Exam & Student Info -->
            <div class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('icon.png') }}" alt="Logo" class="w-8 h-8 rounded-lg bg-slate-950 border border-slate-800 p-1 shrink-0">
                <div class="truncate">
                    <h1 class="text-sm font-bold text-white truncate">{{ $exam->name }}</h1>
                    <p class="text-[11px] text-slate-400 font-mono truncate">
                        {{ $session->student->name }} &bull; Reg: <span class="text-indigo-300">{{ $session->student->registration_number }}</span>
                    </p>
                </div>
            </div>

            <!-- Middle: Timers -->
            <div class="flex items-center gap-3 sm:gap-6">
                <!-- Question Timer -->
                <div class="flex items-center gap-2 bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-1.5 shadow-inner">
                    <div class="relative flex items-center justify-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400" id="qTimerPulse"></span>
                    </div>
                    <div class="text-left">
                        <span class="text-[9px] uppercase tracking-wider text-slate-400 block font-semibold leading-none">Question Time</span>
                        <span id="questionTimerText" class="font-mono text-sm font-bold text-emerald-400 leading-none">--:--</span>
                    </div>
                </div>

                <!-- Total Exam Timer -->
                <div class="flex items-center gap-2 bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-1.5 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-left">
                        <span class="text-[9px] uppercase tracking-wider text-slate-400 block font-semibold leading-none">Total Exam</span>
                        <span id="examTimerText" class="font-mono text-sm font-bold text-amber-300 leading-none">--:--:--</span>
                    </div>
                </div>
            </div>

            <!-- Right: Violations & Webcam Thumbnail -->
            <div class="flex items-center gap-3 shrink-0">
                <!-- Violation Badge -->
                <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-950 border border-slate-800 text-xs">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span class="text-slate-400 text-[11px]">Violations:</span>
                    <span id="violationCounterBadge" class="font-mono font-bold text-rose-400">
                        {{ $session->violations_count }} / {{ $exam->max_fullscreen_violations }}
                    </span>
                </div>

                @if($exam->enable_camera)
                <!-- Webcam PiP Box -->
                <div class="relative w-16 h-12 sm:w-20 sm:h-14 rounded-xl overflow-hidden bg-slate-950 border-2 border-indigo-500/40 shadow-lg">
                    <video id="proctorWebcam" autoplay playsinline muted class="w-full h-full object-cover"></video>
                    <div class="absolute top-1 left-1 flex items-center gap-1 bg-black/60 px-1 py-0.5 rounded text-[8px] font-mono text-rose-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> REC
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Question Progress Bar -->
        <div class="w-full bg-slate-950 h-1.5 rounded-full mt-2.5 overflow-hidden border border-slate-800">
            <div id="examProgressBar" class="bg-gradient-to-r from-indigo-500 to-emerald-400 h-full transition-all duration-300"
                 style="width: {{ (($payload['question_index']) / max(1, $payload['total_questions'])) * 100 }}%;"></div>
        </div>
    </header>

    <!-- Main Content Area: Centered Question Card -->
    <main class="flex-1 max-w-4xl w-full mx-auto p-4 sm:p-6 flex flex-col justify-center">
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-black/60 relative overflow-hidden backdrop-blur-sm">
            
            <!-- Question Header Meta -->
            <div class="flex items-center justify-between gap-4 pb-4 border-b border-slate-800">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1 rounded-xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-300 font-mono font-bold text-xs">
                        Question <span id="qIndexText">{{ $payload['question_index'] }}</span> of <span id="qTotalText">{{ $payload['total_questions'] }}</span>
                    </span>
                    <span class="px-2.5 py-1 rounded-xl bg-slate-800 text-slate-300 text-[11px] font-medium" id="qTypeBadge">
                        {{ $payload['question_type_label'] }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-mono text-xs font-semibold" id="qMarksBadge">
                        +{{ $payload['marks'] }} marks
                    </span>
                    @if($payload['negative_marks'] > 0)
                    <span class="px-2.5 py-1 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 font-mono text-xs" id="qNegMarksBadge">
                        -{{ $payload['negative_marks'] }}
                    </span>
                    @endif
                </div>
            </div>

            <!-- Question Body -->
            <div class="py-6 space-y-6">
                <!-- Question Text -->
                <div id="questionTextContainer" class="text-base sm:text-lg text-white font-normal leading-relaxed">
                    {!! $payload['question_text'] !!}
                </div>

                <!-- Attached Images -->
                <div id="questionImagesContainer" class="space-y-3 {{ empty($payload['images']) ? 'hidden' : '' }}">
                    @if(!empty($payload['images']))
                        @foreach($payload['images'] as $img)
                            <div class="rounded-2xl overflow-hidden border border-slate-800 max-h-72 flex items-center justify-center bg-slate-950/60 p-2">
                                <img src="{{ $img['url'] }}" alt="Question Illustration" class="max-h-64 object-contain rounded-xl">
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Interactive Options / Input Area -->
                <div id="optionsContainer" class="space-y-3 pt-2">
                    @if(in_array($payload['question_type'], ['MCQ', 'TRUE_FALSE']))
                        @foreach($payload['options'] as $opt)
                            <label class="option-label group flex items-center gap-3.5 p-4 rounded-2xl bg-slate-950/60 border border-slate-800 hover:border-indigo-500/50 hover:bg-slate-950 transition-all cursor-pointer select-none">
                                <input type="radio" name="option_choice" value="{{ $opt['id'] }}"
                                       {{ in_array($opt['id'], $payload['saved_option_ids'] ?? []) ? 'checked' : '' }}
                                       {{ $payload['is_already_saved'] ? 'disabled' : '' }}
                                       class="w-4 h-4 text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500">
                                <span class="w-7 h-7 rounded-xl bg-slate-800 group-hover:bg-indigo-600/30 text-slate-300 group-hover:text-indigo-200 flex items-center justify-center font-mono font-bold text-xs shrink-0 transition-colors">
                                    {{ $opt['identifier'] }}
                                </span>
                                <span class="text-sm text-slate-200 group-hover:text-white leading-normal">
                                    {{ $opt['text'] }}
                                </span>
                            </label>
                        @endforeach
                    @elseif($payload['question_type'] === 'MULTIPLE_SELECT')
                        @foreach($payload['options'] as $opt)
                            <label class="option-label group flex items-center gap-3.5 p-4 rounded-2xl bg-slate-950/60 border border-slate-800 hover:border-indigo-500/50 hover:bg-slate-950 transition-all cursor-pointer select-none">
                                <input type="checkbox" name="option_choices[]" value="{{ $opt['id'] }}"
                                       {{ in_array($opt['id'], $payload['saved_option_ids'] ?? []) ? 'checked' : '' }}
                                       {{ $payload['is_already_saved'] ? 'disabled' : '' }}
                                       class="w-4 h-4 text-indigo-600 rounded bg-slate-900 border-slate-700 focus:ring-indigo-500">
                                <span class="w-7 h-7 rounded-xl bg-slate-800 group-hover:bg-indigo-600/30 text-slate-300 group-hover:text-indigo-200 flex items-center justify-center font-mono font-bold text-xs shrink-0 transition-colors">
                                    {{ $opt['identifier'] }}
                                </span>
                                <span class="text-sm text-slate-200 group-hover:text-white leading-normal">
                                    {{ $opt['text'] }}
                                </span>
                            </label>
                        @endforeach
                    @elseif($payload['question_type'] === 'FILL_IN_BLANK')
                        <div class="space-y-3">
                            <label for="fillBlankInput" class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Your Answer</label>
                            <input type="text" id="fillBlankInput" name="fill_blank_answer"
                                   value="{{ $payload['saved_text_answer'] ?? '' }}"
                                   {{ $payload['is_already_saved'] ? 'disabled' : '' }}
                                   placeholder="Type your answer here (not case-sensitive)..."
                                   autocomplete="off"
                                   class="w-full bg-slate-950 border border-slate-700 rounded-2xl px-5 py-4 text-base text-white font-mono placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors {{ $payload['is_already_saved'] ? 'opacity-75 cursor-not-allowed' : '' }}">
                            <p class="text-[11px] text-slate-400">Answer is case-insensitive (capitalization does not matter).</p>
                        </div>
                    @endif
                </div>

                <!-- Answer Saved Notification Banner -->
                <div id="answerSavedBanner" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 flex items-center justify-between {{ $payload['is_already_saved'] ? '' : 'hidden' }}">
                    <div class="flex items-center gap-2.5 text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Answer Saved Successfully</span>
                    </div>
                    <span class="text-[11px] text-emerald-400/80 font-mono">Response Locked</span>
                </div>
            </div>

            <!-- Footer Action Controls: SEPARATE SUBMIT & NEXT BUTTONS -->
            <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <!-- Left: Previous Question (if allowed) -->
                <div>
                    @if($exam->allow_previous_question)
                    <button type="button" id="prevBtn"
                            class="px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-800/60 hover:bg-slate-800 text-slate-300 text-xs font-semibold transition-colors disabled:opacity-30 disabled:cursor-not-allowed flex items-center gap-2"
                            {{ $payload['has_previous'] ? '' : 'disabled' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        <span>Previous Question</span>
                    </button>
                    @endif
                </div>

                <!-- Right: Submit Answer & Next Question / Finish -->
                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <!-- Submit Answer Button -->
                    <button type="button" id="submitAnswerBtn"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs shadow-lg shadow-emerald-600/20 transition-all flex items-center gap-2 {{ $payload['is_already_saved'] ? 'hidden' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Submit Answer</span>
                    </button>

                    <!-- Next Question Button -->
                    <button type="button" id="nextQuestionBtn"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2 {{ $payload['is_last_question'] ? 'hidden' : '' }}">
                        <span>Next Question</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>

                    <!-- Finish Exam Button (shown on last question or when ready) -->
                    <button type="button" id="finishExamBtn"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-semibold text-xs shadow-lg shadow-rose-600/20 transition-all flex items-center gap-2 {{ $payload['is_last_question'] ? '' : 'hidden' }}">
                        <span>Finish Examination</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Bottom Status Bar -->
    <footer class="bg-slate-900/60 border-t border-slate-800 px-4 py-2 shrink-0 text-center text-[11px] text-slate-500">
        YES INDIA ERMS &bull; Exam: <span class="font-mono text-slate-400">{{ $exam->code }}</span> &bull; All interactions and timing are digitally verified.
    </footer>

    <!-- Security Violation / Fullscreen Alert Modal -->
    <div id="violationModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-rose-500/50 rounded-3xl max-w-md w-full p-6 text-center space-y-4 shadow-2xl shadow-rose-950/50">
            <div class="w-14 h-14 rounded-2xl bg-rose-500/20 border border-rose-500/30 flex items-center justify-center text-rose-400 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white">Security Violation Warning</h3>
                <p id="violationModalMessage" class="text-xs text-rose-300 mt-1 leading-relaxed">
                    You have exited Fullscreen or switched away from the examination window.
                </p>
                <p class="text-[11px] text-slate-400 mt-2">
                    Current Violations: <strong id="modalViolationCount" class="text-rose-400 font-mono">0</strong> / {{ $exam->max_fullscreen_violations }}. Exceeding this limit will terminate your exam immediately!
                </p>
            </div>
            <button type="button" id="resumeViolationBtn"
                    class="w-full py-3 px-4 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-lg transition-colors">
                Return to Fullscreen & Resume Exam
            </button>
        </div>
    </div>

    <!-- Confirm Finish Modal -->
    <div id="finishModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 text-center space-y-4 shadow-2xl">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white">Submit Examination?</h3>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    Are you sure you want to finish and submit your exam? You will not be able to change any answers after submission.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="cancelFinishBtn"
                        class="w-1/2 py-2.5 px-4 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                    Continue Exam
                </button>
                <form method="POST" action="{{ route('online-exam.finish') }}" class="w-1/2">
                    @csrf
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-lg transition-colors">
                        Yes, Submit Now
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Client Script: Timers, AJAX Flow, Anti-Cheating & WebRTC -->
    <script @nonce>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const SUBMIT_ANSWER_URL = "{{ route('online-exam.submit-answer') }}";
        const NEXT_QUESTION_URL = "{{ route('online-exam.next-question') }}";
        const PREV_QUESTION_URL = "{{ route('online-exam.previous-question') }}";
        const HEARTBEAT_URL = "{{ route('online-exam.heartbeat') }}";
        const EVENT_URL = "{{ route('online-exam.event') }}";
        const TERMINATED_URL = "{{ route('online-exam.terminated') }}";
        const RESULT_URL = "{{ route('online-exam.result') }}";
        const WEBRTC_SIGNALS_URL = "{{ route('online-exam.webrtc.signals') }}";
        const WEBRTC_SIGNAL_URL = "{{ route('online-exam.webrtc.signal') }}";

        const RTC_CONFIG = {
            iceServers: @json(\App\Http\Controllers\OnlineExamWebRTCController::getIceServersConfig())
        };

        const requiresCamera = {{ $exam->enable_camera ? 'true' : 'false' }};
        const requiresFullscreen = {{ $exam->enable_fullscreen ? 'true' : 'false' }};
        const maxViolations = {{ (int) $exam->max_fullscreen_violations }};

        let currentPayload = @json($payload);
        let violationsCount = {{ (int) $session->violations_count }};
        let isTerminated = false;
        let isSubmitting = false;
        let isTransitioning = false;

        // Timers in Milliseconds
        let questionRemainingMs = {{ (int) $payload['remaining_ms'] }};
        let examRemainingMs = {{ (int) $payload['exam_remaining_ms'] }};
        let timerInterval = null;
        let heartbeatInterval = null;
        let mediaStream = null;

        // WebRTC Signaling: Maintain single active proctor peer connection to save student device resources
        let activePeer = null;
        let currentAdminId = null;
        let bufferedCandidates = [];
        let webrtcSignalingInterval = null;
        let isPollingSignals = false;

        // Anti-Cheating violation debounce timestamp
        let lastViolationTime = 0;
        const VIOLATION_DEBOUNCE_MS = 2000;

        // ==========================================
        // XSS Prevention & HTML Sanitization Helpers
        // ==========================================
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function sanitizeQuestionHtml(html) {
            if (!html) return '';
            return String(html)
                .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
                .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '')
                .replace(/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi, '')
                .replace(/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/gi, '')
                .replace(/\s*on\w+\s*=\s*(['"]).*?\1/gi, '')
                .replace(/\s*on\w+\s*=\s*[^>\s]+/gi, '')
                .replace(/href\s*=\s*(['"])\s*javascript:[^'"]*\1/gi, 'href="#"');
        }

        function isValidImageUrl(url) {
            if (!url) return false;
            return /^(https?:\/\/|\/)/i.test(url.trim());
        }

        // Resilient camera stream resolver with progressive fallback
        async function getCameraStream(idealWidth = 320, idealHeight = 240) {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                // Tier 1: Ideal user-facing camera
                try {
                    return await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: idealWidth }, height: { ideal: idealHeight }, facingMode: 'user' },
                        audio: false
                    });
                } catch (e1) {
                    console.warn('Tier 1 constraints (facingMode: user) failed, attempting Tier 2:', e1.name, e1.message);
                }

                // Tier 2: Specific resolution without facingMode
                try {
                    return await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: idealWidth }, height: { ideal: idealHeight } },
                        audio: false
                    });
                } catch (e2) {
                    console.warn('Tier 2 constraints failed, attempting Tier 3:', e2.name, e2.message);
                }

                // Tier 3: Bare minimum video
                return await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            }

            const legacyNav = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
            if (legacyNav) {
                return new Promise((resolve, reject) => {
                    legacyNav.call(navigator, { video: true, audio: false }, resolve, reject);
                });
            }

            throw new Error('Camera access not supported');
        }

        // Start WebRTC Proctoring Camera
        async function startCamera() {
            if (!requiresCamera) return true;
            try {
                mediaStream = await getCameraStream(320, 240);
                const video = document.getElementById('proctorWebcam');
                if (video) {
                    video.srcObject = mediaStream;
                    video.muted = true;
                    video.playsInline = true;
                    video.setAttribute('playsinline', '');
                    video.setAttribute('muted', '');
                    video.setAttribute('autoplay', '');
                    try {
                        await video.play();
                    } catch (pErr) {
                        video.onloadedmetadata = () => { video.play().catch(console.warn); };
                    }
                }

                // Immediately notify server that camera is active
                sendHeartbeat();
                initWebRtcSignaling();

                // Detect track ended & attempt single automatic recovery
                mediaStream.getVideoTracks().forEach(track => {
                    track.onended = async () => {
                        console.warn('[Camera] Track ended, attempting recovery...');
                        try {
                            mediaStream = await getCameraStream(320, 240);
                            const v = document.getElementById('proctorWebcam');
                            if (v) v.srcObject = mediaStream;
                            sendHeartbeat();
                        } catch (recErr) {
                            recordSecurityViolation('CAMERA_STOPPED', { note: 'Camera track ended and recovery failed: ' + (recErr.message || recErr.name) });
                            sendHeartbeat();
                        }
                    };
                });
                return true;
            } catch (err) {
                console.warn('Camera stream error:', err);
                recordSecurityViolation('CAMERA_STOPPED', { note: 'Camera permission denied or unavailable: ' + (err.message || err.name) });
                sendHeartbeat();
                return false;
            }
        }

        // ==========================================
        // WebRTC Live Proctoring Video Broadcaster
        // ==========================================
        function initWebRtcSignaling() {
            if (!requiresCamera) return;
            if (webrtcSignalingInterval) clearInterval(webrtcSignalingInterval);
            pollAdminWebRtcSignals();
            webrtcSignalingInterval = setInterval(pollAdminWebRtcSignals, 2000);
        }

        async function pollAdminWebRtcSignals() {
            if (isTerminated || !requiresCamera || isPollingSignals) return;
            isPollingSignals = true;
            try {
                const res = await fetch(WEBRTC_SIGNALS_URL, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && Array.isArray(data.signals) && data.signals.length > 0) {
                        for (const sig of data.signals) {
                            await handleAdminSignal(sig);
                        }
                    }
                }
            } catch (err) {
                // Non-fatal background signaling polling error
            } finally {
                isPollingSignals = false;
            }
        }

        async function handleAdminSignal(sig) {
            const adminId = sig.admin_id ?? 0;
            if (sig.type === 'offer') {
                await processAdminOffer(adminId, sig.payload);
            } else if (sig.type === 'candidate') {
                await processAdminCandidate(adminId, sig.payload);
            } else if (sig.type === 'close') {
                if (currentAdminId === adminId) {
                    closeAdminPeer();
                }
            }
        }

        async function processAdminOffer(adminId, offerPayload) {
            if (!mediaStream) {
                console.warn('[WebRTC Student] Offer received but mediaStream is not active.');
                return;
            }

            // Close previous peer connection to maintain only 1 active inspection stream
            closeAdminPeer();

            currentAdminId = adminId;
            bufferedCandidates = [];

            const pc = new RTCPeerConnection(RTC_CONFIG);
            activePeer = pc;

            // Send ICE candidates to admin
            pc.onicecandidate = (event) => {
                if (event.candidate && currentAdminId === adminId) {
                    sendWebRtcSignal('candidate', adminId, event.candidate);
                }
            };

            // Stream camera tracks to the peer connection
            mediaStream.getTracks().forEach(track => {
                pc.addTrack(track, mediaStream);
            });

            pc.onconnectionstatechange = () => {
                if (['disconnected', 'failed', 'closed'].includes(pc.connectionState)) {
                    if (currentAdminId === adminId) {
                        closeAdminPeer();
                    }
                }
            };

            try {
                await pc.setRemoteDescription(new RTCSessionDescription(offerPayload));

                // Process buffered ICE candidates
                for (const cand of bufferedCandidates) {
                    try {
                        await pc.addIceCandidate(new RTCIceCandidate(cand));
                    } catch (e) {
                        console.warn('[WebRTC Student] Buffered candidate error:', e);
                    }
                }
                bufferedCandidates = [];

                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);

                sendWebRtcSignal('answer', adminId, pc.localDescription);
            } catch (err) {
                console.error('[WebRTC Student] Failed processing admin offer:', err);
                closeAdminPeer();
            }
        }

        async function processAdminCandidate(adminId, candidatePayload) {
            if (currentAdminId !== adminId || !activePeer) return;
            const pc = activePeer;
            if (pc.remoteDescription && pc.remoteDescription.type) {
                try {
                    await pc.addIceCandidate(new RTCIceCandidate(candidatePayload));
                } catch (err) {
                    console.warn('[WebRTC Student] Candidate add failed:', err);
                }
            } else {
                bufferedCandidates.push(candidatePayload);
            }
        }

        function closeAdminPeer() {
            if (activePeer) {
                try {
                    activePeer.close();
                } catch (e) {}
                activePeer = null;
            }
            currentAdminId = null;
            bufferedCandidates = [];
        }

        async function sendWebRtcSignal(type, adminId, payload) {
            try {
                await fetch(WEBRTC_SIGNAL_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        type: type,
                        admin_id: adminId || null,
                        payload: payload
                    })
                });
            } catch (err) {
                console.warn('[WebRTC Student] Failed sending signal:', err);
            }
        }

        // Initialize Timers
        function startTimers() {
            if (timerInterval) clearInterval(timerInterval);

            timerInterval = setInterval(() => {
                // Decrement timers by 1 second
                questionRemainingMs = Math.max(0, questionRemainingMs - 1000);
                examRemainingMs = Math.max(0, examRemainingMs - 1000);

                updateTimerDisplay();

                // Question Timer Expiration: Auto-submit current answer and advance
                if (questionRemainingMs <= 0 && !currentPayload.is_already_saved && !isSubmitting && !isTransitioning) {
                    autoSubmitOnTimeout();
                }

                // Overall Exam Expiration: Verify with server authoritative timer
                if (examRemainingMs <= 0 && !isTransitioning) {
                    isTransitioning = true;
                    clearInterval(timerInterval);
                    sendHeartbeat().finally(() => {
                        window.location.href = RESULT_URL;
                    });
                }
            }, 1000);

            updateTimerDisplay();
        }

        function updateTimerDisplay() {
            // Format Question Timer
            const qSec = Math.floor(questionRemainingMs / 1000);
            const qMins = Math.floor(qSec / 60);
            const qRemSec = qSec % 60;
            const qStr = `${String(qMins).padStart(2, '0')}:${String(qRemSec).padStart(2, '0')}`;
            const qEl = document.getElementById('questionTimerText');
            if (qEl) {
                qEl.textContent = qStr;
                if (qSec <= 10) {
                    qEl.className = 'font-mono text-sm font-bold text-rose-400 animate-pulse leading-none';
                    document.getElementById('qTimerPulse').className = 'w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping';
                } else {
                    qEl.className = 'font-mono text-sm font-bold text-emerald-400 leading-none';
                    document.getElementById('qTimerPulse').className = 'w-2.5 h-2.5 rounded-full bg-emerald-400';
                }
            }

            // Format Overall Exam Timer
            const eSec = Math.floor(examRemainingMs / 1000);
            const eHours = Math.floor(eSec / 3600);
            const eMins = Math.floor((eSec % 3600) / 60);
            const eRemSec = eSec % 60;
            const eStr = `${String(eHours).padStart(2, '0')}:${String(eMins).padStart(2, '0')}:${String(eRemSec).padStart(2, '0')}`;
            const eEl = document.getElementById('examTimerText');
            if (eEl) {
                eEl.textContent = eStr;
            }
        }

        // Get currently selected answer value(s) from DOM
        function getSelectedAnswer() {
            const qType = currentPayload.question_type;
            if (qType === 'MCQ' || qType === 'TRUE_FALSE') {
                const radio = document.querySelector('input[name="option_choice"]:checked');
                return { selectedOptionIds: radio ? [parseInt(radio.value, 10)] : [], textAnswer: null };
            } else if (qType === 'MULTIPLE_SELECT') {
                const checkedBoxes = document.querySelectorAll('input[name="option_choices[]"]:checked');
                const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value, 10));
                return { selectedOptionIds: ids, textAnswer: null };
            } else if (qType === 'FILL_IN_BLANK') {
                const input = document.getElementById('fillBlankInput');
                return { selectedOptionIds: null, textAnswer: input ? input.value.trim() : '' };
            }
            return { selectedOptionIds: null, textAnswer: null };
        }

        // Submit Answer (Separate Action)
        async function handleSubmitAnswer() {
            if (isSubmitting || isTransitioning || currentPayload.is_already_saved) return;
            const answer = getSelectedAnswer();

            // Validate that something was entered/selected
            const hasSelection = (answer.selectedOptionIds && answer.selectedOptionIds.length > 0) || (answer.textAnswer !== null && answer.textAnswer !== '');
            if (!hasSelection) {
                alert('Please select or enter an answer before clicking Submit Answer.');
                return;
            }

            isSubmitting = true;
            const submitBtn = document.getElementById('submitAnswerBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Saving...</span>';

            try {
                const res = await fetch(SUBMIT_ANSWER_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        question_id: currentPayload.question_id,
                        selected_option_ids: answer.selectedOptionIds,
                        text_answer: answer.textAnswer
                    })
                });

                const data = await res.json();
                if (data.success) {
                    currentPayload.is_already_saved = true;

                    // Lock Inputs
                    document.querySelectorAll('#optionsContainer input').forEach(input => input.disabled = true);
                    document.querySelectorAll('.option-label').forEach(label => label.classList.add('opacity-75', 'cursor-not-allowed'));

                    // Show Banner & Swap Buttons
                    document.getElementById('answerSavedBanner').classList.remove('hidden');
                    submitBtn.classList.add('hidden');

                    if (currentPayload.is_last_question) {
                        document.getElementById('finishExamBtn').classList.remove('hidden');
                        document.getElementById('nextQuestionBtn').classList.add('hidden');
                    } else {
                        document.getElementById('nextQuestionBtn').classList.remove('hidden');
                    }
                } else {
                    alert(data.message || 'Error saving answer.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>Submit Answer</span>';
                }
            } catch (err) {
                console.error('Submit error:', err);
                alert('Network error while saving answer. Please retry.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Submit Answer</span>';
            } finally {
                isSubmitting = false;
            }
        }

        // Auto-submit when question timer expires with strict transition lock
        async function autoSubmitOnTimeout() {
            if (isSubmitting || isTransitioning || currentPayload.is_already_saved) return;
            isSubmitting = true;
            isTransitioning = true;
            const answer = getSelectedAnswer();

            try {
                await fetch(SUBMIT_ANSWER_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        question_id: currentPayload.question_id,
                        selected_option_ids: answer.selectedOptionIds,
                        text_answer: answer.textAnswer
                    })
                });
            } catch (e) {
                console.warn('Auto-submit timeout error:', e);
            } finally {
                isSubmitting = false;
            }

            // Advance automatically upon question timer expiration
            if (!currentPayload.is_last_question) {
                await handleNextQuestion();
            } else {
                window.location.href = RESULT_URL;
            }
        }

        // Advance to Next Question
        async function handleNextQuestion() {
            if (isTransitioning && !isSubmitting) return;
            isTransitioning = true;
            const nextBtn = document.getElementById('nextQuestionBtn');
            if (nextBtn) {
                nextBtn.disabled = true;
                nextBtn.innerHTML = '<span>Loading...</span>';
            }

            try {
                const res = await fetch(NEXT_QUESTION_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    }
                });

                const data = await res.json();
                if (data.success) {
                    if (data.has_next && data.question) {
                        renderQuestionPayload(data.question);
                    } else {
                        // No more questions -> Finish
                        document.getElementById('finishExamBtn').classList.remove('hidden');
                        if (nextBtn) nextBtn.classList.add('hidden');
                        confirmFinishExam();
                    }
                } else {
                    alert(data.message || 'Failed to load next question.');
                }
            } catch (err) {
                console.error('Next question error:', err);
                alert('Connection error. Please try again.');
            } finally {
                isTransitioning = false;
                if (nextBtn) {
                    nextBtn.disabled = false;
                    nextBtn.innerHTML = `<span>Next Question</span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>`;
                }
            }
        }

        // Retreat to Previous Question
        async function handlePreviousQuestion() {
            if (isTransitioning) return;
            const prevBtn = document.getElementById('prevBtn');
            if (!prevBtn) return;
            isTransitioning = true;
            prevBtn.disabled = true;

            try {
                const res = await fetch(PREV_QUESTION_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    }
                });

                const data = await res.json();
                if (data.success && data.question) {
                    renderQuestionPayload(data.question);
                }
            } catch (err) {
                console.error('Previous question error:', err);
            } finally {
                isTransitioning = false;
                if (prevBtn) prevBtn.disabled = false;
            }
        }

        // Dynamically Render Question Payload into DOM with XSS Sanitization
        function renderQuestionPayload(p) {
            currentPayload = p;
            questionRemainingMs = p.remaining_ms;
            examRemainingMs = p.exam_remaining_ms;

            // Update Progress & Badges
            document.getElementById('qIndexText').textContent = p.question_index;
            document.getElementById('qTotalText').textContent = p.total_questions;
            document.getElementById('qTypeBadge').textContent = p.question_type_label;
            document.getElementById('qMarksBadge').textContent = `+${p.marks} marks`;

            const negBadge = document.getElementById('qNegMarksBadge');
            if (negBadge) {
                if (p.negative_marks > 0) {
                    negBadge.textContent = `-${p.negative_marks}`;
                    negBadge.classList.remove('hidden');
                } else {
                    negBadge.classList.add('hidden');
                }
            }

            const pct = (p.question_index / Math.max(1, p.total_questions)) * 100;
            document.getElementById('examProgressBar').style.width = `${pct}%`;

            // Question Text: sanitized HTML
            document.getElementById('questionTextContainer').innerHTML = sanitizeQuestionHtml(p.question_text);

            // Images: validated URL and escaped attributes
            const imgContainer = document.getElementById('questionImagesContainer');
            const validImages = (p.images || []).filter(img => isValidImageUrl(img.url));
            if (validImages.length > 0) {
                imgContainer.innerHTML = validImages.map(img => `
                    <div class="rounded-2xl overflow-hidden border border-slate-800 max-h-72 flex items-center justify-center bg-slate-950/60 p-2">
                        <img src="${escapeHtml(img.url)}" alt="Question Illustration" class="max-h-64 object-contain rounded-xl">
                    </div>
                `).join('');
                imgContainer.classList.remove('hidden');
            } else {
                imgContainer.innerHTML = '';
                imgContainer.classList.add('hidden');
            }

            // Render Options with XSS escaping
            const optContainer = document.getElementById('optionsContainer');
            optContainer.innerHTML = '';

            const isSaved = p.is_already_saved;
            const savedIds = p.saved_option_ids || [];

            if (p.question_type === 'MCQ' || p.question_type === 'TRUE_FALSE') {
                optContainer.innerHTML = (p.options || []).map(opt => `
                    <label class="option-label group flex items-center gap-3.5 p-4 rounded-2xl bg-slate-950/60 border border-slate-800 hover:border-indigo-500/50 hover:bg-slate-950 transition-all cursor-pointer select-none ${isSaved ? 'opacity-75 cursor-not-allowed' : ''}">
                        <input type="radio" name="option_choice" value="${opt.id}"
                               ${savedIds.includes(opt.id) ? 'checked' : ''}
                               ${isSaved ? 'disabled' : ''}
                               class="w-4 h-4 text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500">
                        <span class="w-7 h-7 rounded-xl bg-slate-800 group-hover:bg-indigo-600/30 text-slate-300 group-hover:text-indigo-200 flex items-center justify-center font-mono font-bold text-xs shrink-0 transition-colors">
                            ${escapeHtml(opt.identifier)}
                        </span>
                        <span class="text-sm text-slate-200 group-hover:text-white leading-normal">
                            ${escapeHtml(opt.text)}
                        </span>
                    </label>
                `).join('');
            } else if (p.question_type === 'MULTIPLE_SELECT') {
                optContainer.innerHTML = (p.options || []).map(opt => `
                    <label class="option-label group flex items-center gap-3.5 p-4 rounded-2xl bg-slate-950/60 border border-slate-800 hover:border-indigo-500/50 hover:bg-slate-950 transition-all cursor-pointer select-none ${isSaved ? 'opacity-75 cursor-not-allowed' : ''}">
                        <input type="checkbox" name="option_choices[]" value="${opt.id}"
                               ${savedIds.includes(opt.id) ? 'checked' : ''}
                               ${isSaved ? 'disabled' : ''}
                               class="w-4 h-4 text-indigo-600 rounded bg-slate-900 border-slate-700 focus:ring-indigo-500">
                        <span class="w-7 h-7 rounded-xl bg-slate-800 group-hover:bg-indigo-600/30 text-slate-300 group-hover:text-indigo-200 flex items-center justify-center font-mono font-bold text-xs shrink-0 transition-colors">
                            ${escapeHtml(opt.identifier)}
                        </span>
                        <span class="text-sm text-slate-200 group-hover:text-white leading-normal">
                            ${escapeHtml(opt.text)}
                        </span>
                    </label>
                `).join('');
            } else if (p.question_type === 'FILL_IN_BLANK') {
                optContainer.innerHTML = `
                    <div class="space-y-3">
                        <label for="fillBlankInput" class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Your Answer</label>
                        <input type="text" id="fillBlankInput" name="fill_blank_answer"
                               value="${escapeHtml(p.saved_text_answer || '')}"
                               ${isSaved ? 'disabled' : ''}
                               placeholder="Type your answer here (not case-sensitive)..."
                               autocomplete="off"
                               class="w-full bg-slate-950 border border-slate-700 rounded-2xl px-5 py-4 text-base text-white font-mono placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors ${isSaved ? 'opacity-75 cursor-not-allowed' : ''}">
                        <p class="text-[11px] text-slate-400">Answer is case-insensitive (capitalization does not matter).</p>
                    </div>
                `;
                setupFillBlankEnterSubmit();
            }

            // Banner & Action Buttons Visibility
            const banner = document.getElementById('answerSavedBanner');
            const submitBtn = document.getElementById('submitAnswerBtn');
            const nextBtn = document.getElementById('nextQuestionBtn');
            const finishBtn = document.getElementById('finishExamBtn');
            const prevBtn = document.getElementById('prevBtn');

            if (isSaved) {
                banner.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            } else {
                banner.classList.add('hidden');
                submitBtn.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg><span>Submit Answer</span>`;
            }

            if (p.is_last_question) {
                nextBtn.classList.add('hidden');
                finishBtn.classList.remove('hidden');
            } else {
                nextBtn.classList.remove('hidden');
                finishBtn.classList.add('hidden');
            }

            if (prevBtn) {
                prevBtn.disabled = !p.has_previous;
            }

            setupFillBlankEnterSubmit();
            startTimers();
        }

        function setupFillBlankEnterSubmit() {
            const fillInput = document.getElementById('fillBlankInput');
            if (fillInput && !currentPayload.is_already_saved) {
                fillInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        handleSubmitAnswer();
                    }
                });
            }
        }

        function confirmFinishExam() {
            document.getElementById('finishModal').classList.remove('hidden');
        }

        // Anti-Cheating & Violations Tracking with Client-Side Debouncing
        async function recordSecurityViolation(eventType, metadata = {}) {
            if (isTerminated) return;

            // Debounce to prevent multiple hits from a single user Alt-Tab transition
            const now = Date.now();
            if (now - lastViolationTime < VIOLATION_DEBOUNCE_MS) {
                return;
            }
            lastViolationTime = now;

            try {
                const res = await fetch(EVENT_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        event_type: eventType,
                        metadata: metadata
                    })
                });

                const data = await res.json();
                if (data.violations_count !== undefined) {
                    violationsCount = data.violations_count;
                    const b = document.getElementById('violationCounterBadge');
                    if (b) b.textContent = `${violationsCount} / ${maxViolations}`;
                    const mb = document.getElementById('modalViolationCount');
                    if (mb) mb.textContent = violationsCount;
                }

                if (data.is_terminated) {
                    isTerminated = true;
                    window.location.href = TERMINATED_URL;
                }
            } catch (err) {
                console.warn('Violation reporting error:', err);
            }
        }

        // Fullscreen & Window Focus Watchdogs
        function setupAntiCheatingWatchdogs() {
            // Fullscreen change listener
            if (requiresFullscreen) {
                document.addEventListener('fullscreenchange', handleFullscreenChange);
                document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
                document.addEventListener('msfullscreenchange', handleFullscreenChange);
            }

            // Tab switch / visibility listener
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    recordSecurityViolation('TAB_SWITCH', { note: 'Student navigated away from tab' });
                    showViolationModal('You switched browser tabs! This has been recorded as a violation.');
                }
            });

            // Window blur listener: only record if not already captured by tab switch/fullscreen exit
            window.addEventListener('blur', () => {
                if (!document.hidden && requiresFullscreen && !(document.fullscreenElement || document.webkitFullscreenElement)) {
                    recordSecurityViolation('WINDOW_BLUR', { note: 'Exam window lost focus' });
                }
            });

            // Prevent right-click context menu
            document.addEventListener('contextmenu', e => e.preventDefault());

            // Prevent F12 / Inspect shortcut keys
            document.addEventListener('keydown', e => {
                if (
                    e.key === 'F12' ||
                    (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) ||
                    (e.ctrlKey && e.key === 'u')
                ) {
                    e.preventDefault();
                    recordSecurityViolation('KEYBOARD_SHORTCUT', { key: e.key });
                    return false;
                }
            });
        }

        function handleFullscreenChange() {
            const isFull = !!(document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement);
            if (!isFull && requiresFullscreen && !isTerminated) {
                recordSecurityViolation('FULLSCREEN_EXIT', { note: 'Student exited fullscreen mode' });
                showViolationModal('You exited Fullscreen mode! Please return to fullscreen immediately.');
            }
        }

        function showViolationModal(msg) {
            const modal = document.getElementById('violationModal');
            const msgEl = document.getElementById('violationModalMessage');
            if (msgEl) msgEl.textContent = msg;
            if (modal) modal.classList.remove('hidden');
        }

        async function resumeExamFromViolation() {
            if (requiresFullscreen) {
                try {
                    const el = document.documentElement;
                    if (el.requestFullscreen) await el.requestFullscreen();
                    else if (el.webkitRequestFullscreen) await el.webkitRequestFullscreen();
                } catch (e) {
                    console.warn(e);
                }
            }
            document.getElementById('violationModal').classList.add('hidden');
        }

        // Heartbeat Sender: Synchronizes timers and server state
        async function sendHeartbeat() {
            if (isTerminated) return;
            try {
                const isCamActive = mediaStream && mediaStream.active && mediaStream.getVideoTracks().some(t => t.readyState === 'live');
                const camState = isCamActive ? 'ACTIVE' : (requiresCamera ? 'STOPPED' : 'NOT_REQUIRED');
                const res = await fetch(HEARTBEAT_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        camera_status: camState,
                        fullscreen_status: !!(document.fullscreenElement || document.webkitFullscreenElement)
                    })
                });

                const data = await res.json();
                if (data.is_terminated) {
                    isTerminated = true;
                    window.location.href = TERMINATED_URL;
                    return;
                } else if (data.is_submitted || data.is_expired) {
                    window.location.href = RESULT_URL;
                    return;
                }

                // Synchronize with server authoritative timers if drift exceeds 3 seconds
                if (data.exam_remaining_ms !== undefined && Math.abs(data.exam_remaining_ms - examRemainingMs) > 3000) {
                    examRemainingMs = data.exam_remaining_ms;
                }
                if (data.question_remaining_ms !== undefined && Math.abs(data.question_remaining_ms - questionRemainingMs) > 3000) {
                    questionRemainingMs = data.question_remaining_ms;
                }
            } catch (e) {
                console.warn('Heartbeat connection failure:', e);
            }
        }

        // Heartbeat Loop (Immediate + every 10s)
        function startHeartbeat() {
            if (heartbeatInterval) clearInterval(heartbeatInterval);
            sendHeartbeat();
            heartbeatInterval = setInterval(sendHeartbeat, 10000);
        }

        // Boot
        document.addEventListener('DOMContentLoaded', async () => {
            const prevBtn = document.getElementById('prevBtn');
            if (prevBtn) prevBtn.addEventListener('click', handlePreviousQuestion);

            const submitBtn = document.getElementById('submitAnswerBtn');
            if (submitBtn) submitBtn.addEventListener('click', handleSubmitAnswer);

            const nextBtn = document.getElementById('nextQuestionBtn');
            if (nextBtn) nextBtn.addEventListener('click', handleNextQuestion);

            const finishBtn = document.getElementById('finishExamBtn');
            if (finishBtn) finishBtn.addEventListener('click', confirmFinishExam);

            const resumeBtn = document.getElementById('resumeViolationBtn');
            if (resumeBtn) resumeBtn.addEventListener('click', resumeExamFromViolation);

            const cancelFinishBtn = document.getElementById('cancelFinishBtn');
            if (cancelFinishBtn) cancelFinishBtn.addEventListener('click', () => {
                document.getElementById('finishModal').classList.add('hidden');
            });

            if (requiresCamera) {
                await startCamera();
            }

            setupAntiCheatingWatchdogs();
            setupFillBlankEnterSubmit();
            startTimers();
            startHeartbeat();

            window.addEventListener('beforeunload', () => {
                if (webrtcSignalingInterval) clearInterval(webrtcSignalingInterval);
                closeAdminPeer();
            });
        });
    </script>
</body>
</html>
