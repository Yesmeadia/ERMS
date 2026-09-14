<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Examination Instructions & System Verification | {{ $exam->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-between antialiased relative">
    <x-public-background />

    <div class="max-w-5xl w-full mx-auto space-y-6 relative z-10 p-4 sm:p-6 lg:p-8">
        <!-- Top Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <img src="{{ asset('icon.png') }}" alt="ERMS Logo" class="w-10 h-10 rounded-xl object-contain bg-slate-900 border border-slate-800 p-1">
                <div>
                    <h1 class="text-xl font-bold text-white">{{ $exam->name }}</h1>
                    <p class="text-xs text-slate-400">Code: <span class="font-mono text-indigo-300">{{ $exam->code }}</span> &bull; Category: <span class="text-slate-200">{{ $exam->category->name ?? 'General' }}</span></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="text-right text-xs">
                    <p class="font-semibold text-white">{{ $session->student->name }}</p>
                    <p class="text-slate-400 font-mono">Reg No: {{ $session->student->registration_number }}</p>
                </div>
                <div class="w-9 h-9 rounded-full bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-300 font-bold text-sm">
                    {{ substr($session->student->name, 0, 1) }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Exam Specs & Integrity Rules -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Exam Metrics Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 text-center">
                        <span class="text-[11px] uppercase tracking-wider text-slate-400 block mb-1">Total Questions</span>
                        <span class="text-2xl font-bold text-white font-mono">{{ $exam->examQuestions()->count() }}</span>
                    </div>
                    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 text-center">
                        <span class="text-[11px] uppercase tracking-wider text-slate-400 block mb-1">Total Marks</span>
                        <span class="text-2xl font-bold text-indigo-400 font-mono">{{ $exam->total_marks }}</span>
                    </div>
                    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 text-center">
                        <span class="text-[11px] uppercase tracking-wider text-slate-400 block mb-1">Passing Marks</span>
                        <span class="text-2xl font-bold text-emerald-400 font-mono">{{ $exam->pass_marks }}</span>
                    </div>
                    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 text-center">
                        <span class="text-[11px] uppercase tracking-wider text-slate-400 block mb-1">Exam Duration</span>
                        <span class="text-2xl font-bold text-amber-400 font-mono">{{ $exam->duration_minutes }} <span class="text-xs text-slate-400 font-normal">min</span></span>
                    </div>
                </div>

                <!-- Critical Rules & Flow -->
                <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        Important Instructions & Question Flow
                    </h2>
                    <ul class="space-y-3 text-xs text-slate-300 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold shrink-0 mt-0.5">1</span>
                            <span><strong>One Question at a Time:</strong> Questions will appear sequentially. You will see an individual question timer as well as the total exam timer.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold shrink-0 mt-0.5">2</span>
                            <span><strong>Explicit Answer Submission:</strong> Select or type your answer and click <span class="text-emerald-400 font-semibold">[ Submit Answer ]</span>. Once submitted, your response is safely recorded and locked. You must then click <span class="text-indigo-400 font-semibold">[ Next Question ]</span> to proceed.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold shrink-0 mt-0.5">3</span>
                            <span><strong>Strict Server-Side Timing:</strong> If the question timer expires, whatever input is currently selected is automatically captured and the session moves forward.</span>
                        </li>
                        @if($exam->enable_speed_bonus)
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold shrink-0 mt-0.5">★</span>
                            <span><strong>Speed Bonus Active:</strong> Answering questions correctly and swiftly awards additional bonus marks! Take your time to be accurate.</span>
                        </li>
                        @endif
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold shrink-0 mt-0.5">!</span>
                            <span><strong>Anti-Cheating Monitoring:</strong> Switching browser tabs, minimizing the browser, exiting fullscreen mode, or disabling your web camera constitutes a security violation. Reaching <span class="text-rose-400 font-bold">{{ $exam->max_fullscreen_violations }} violation(s)</span> will terminate your examination immediately.</span>
                        </li>
                    </ul>

                    @if($exam->instructions)
                    <div class="mt-4 pt-4 border-t border-slate-800">
                        <h3 class="text-xs uppercase tracking-wider font-semibold text-slate-400 mb-2">Examiner's Specific Instructions</h3>
                        <div class="prose prose-invert prose-xs max-w-none text-slate-300">
                            {!! $exam->instructions !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right Col: System Readiness & Proctoring Check -->
            <div class="space-y-6">
                <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 space-y-5">
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        System Readiness Checks
                    </h2>

                    @if($exam->enable_camera)
                    <!-- Camera Check -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-300 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse" id="cameraDot"></span>
                                Web Camera Proctor
                            </span>
                            <span id="cameraStatusBadge" class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-400">Verifying...</span>
                        </div>
                        <div class="relative w-full aspect-video bg-slate-950 rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center">
                            <video id="cameraPreview" autoplay playsinline muted class="w-full h-full object-cover hidden"></video>
                            <div id="cameraPlaceholder" class="text-center p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                <button type="button" id="allowCameraBtn" class="text-xs px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                                    Allow Camera Access
                                </button>
                                <p id="cameraErrorMessage" class="hidden text-xs text-rose-300 mt-2.5 px-3 py-2 rounded-xl bg-rose-950/60 border border-rose-800/60 text-center leading-relaxed"></p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($exam->enable_fullscreen)
                    <!-- Fullscreen Status -->
                    <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                        <div class="text-xs">
                            <p class="font-semibold text-slate-300">Fullscreen Mode</p>
                            <p class="text-[11px] text-slate-500">Required throughout the examination</p>
                        </div>
                        <span id="fullscreenBadge" class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-400">Waiting to Start</span>
                    </div>
                    @endif

                    <!-- Declaration Checkbox -->
                    <div class="pt-2 border-t border-slate-800">
                        <label class="flex items-start gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" id="rulesConsent"
                                   class="mt-1 w-4 h-4 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-950">
                            <span class="text-xs text-slate-400 leading-snug">
                                I confirm my identity as <strong class="text-white">{{ $session->student->name }}</strong> and agree to all examination rules and proctoring requirements.
                            </span>
                        </label>
                    </div>

                    <!-- Start Button Form -->
                    <form method="POST" action="{{ route('online-exam.start') }}" id="startExamForm">
                        @csrf
                        <input type="hidden" name="camera_status" id="formCameraStatus" value="{{ $exam->enable_camera ? 'UNKNOWN' : 'NOT_REQUIRED' }}">
                        <button type="button" id="startExamBtn" disabled
                                class="w-full py-3.5 px-4 rounded-2xl text-sm font-semibold bg-indigo-600 text-white shadow-xl shadow-indigo-600/20 disabled:opacity-40 disabled:cursor-not-allowed hover:enabled:bg-indigo-500 transition-all flex items-center justify-center gap-2">
                            <span>Enter Fullscreen & Start Exam</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Official ERMS Public Footer Component -->
    <x-public-footer page="instructions" />

    <script @nonce>
        let cameraReady = {{ $exam->enable_camera ? 'false' : 'true' }};
        const requiresCamera = {{ $exam->enable_camera ? 'true' : 'false' }};
        const requiresFullscreen = {{ $exam->enable_fullscreen ? 'true' : 'false' }};

        // Resilient camera stream resolver with progressive fallback
        async function getCameraStream(idealWidth = 640, idealHeight = 480) {
            // Check modern mediaDevices
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

                // Tier 2: Specific resolution without facingMode (essential for desktop/USB webcams)
                try {
                    return await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: idealWidth }, height: { ideal: idealHeight } },
                        audio: false
                    });
                } catch (e2) {
                    console.warn('Tier 2 constraints (resolution) failed, attempting Tier 3:', e2.name, e2.message);
                }

                // Tier 3: Bare minimum video
                return await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: false
                });
            }

            // Fallback for older browsers
            const legacyNav = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
            if (legacyNav) {
                return new Promise((resolve, reject) => {
                    legacyNav.call(navigator, { video: true, audio: false }, resolve, reject);
                });
            }

            const err = new Error('Camera access is not supported by your browser or requires a secure connection (HTTPS or localhost).');
            err.name = 'NotSupportedError';
            throw err;
        }

        async function initCamera(isUserGesture = false) {
            if (!requiresCamera) return;
            const badge = document.getElementById('cameraStatusBadge');
            const dot = document.getElementById('cameraDot');
            const video = document.getElementById('cameraPreview');
            const placeholder = document.getElementById('cameraPlaceholder');
            const errEl = document.getElementById('cameraErrorMessage');
            const allowBtn = document.getElementById('allowCameraBtn');

            if (errEl) errEl.classList.add('hidden');
            if (badge) {
                badge.textContent = 'Connecting...';
                badge.className = 'text-[10px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30';
            }

            try {
                const stream = await getCameraStream(640, 480);

                if (video) {
                    video.srcObject = stream;
                    video.muted = true;
                    video.playsInline = true;
                    video.setAttribute('playsinline', '');
                    video.setAttribute('muted', '');
                    video.setAttribute('autoplay', '');

                    try {
                        await video.play();
                    } catch (playErr) {
                        console.warn('Direct video.play() failed, waiting for metadata:', playErr);
                        video.onloadedmetadata = () => { video.play().catch(console.warn); };
                    }

                    video.classList.remove('hidden');
                }

                if (placeholder) placeholder.classList.add('hidden');

                cameraReady = true;
                if (badge) {
                    badge.innerHTML = `<span class="inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        <span>Camera Active</span>
                    </span>`;
                    badge.className = 'text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
                }
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-emerald-400';

                validateStartReadiness();
            } catch (err) {
                console.error('Camera access error:', err.name, err.message);
                cameraReady = false;

                let userMsg = 'Camera access could not be enabled.';
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    userMsg = 'Camera permission was blocked. Please click the lock or camera icon in your browser\'s address bar, select "Allow Camera", then click Try Again.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    userMsg = 'No camera device found. Please connect a webcam to continue.';
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    userMsg = 'Camera is currently in use by another program (e.g. Zoom, Teams, or another browser window). Please close other apps and click Try Again.';
                } else if (err.name === 'NotSupportedError') {
                    userMsg = err.message;
                }

                if (badge) {
                    const errMsg = (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') ? 'Camera Denied' : 'Camera Error';
                    badge.innerHTML = `<span class="inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span>${errMsg}</span>
                    </span>`;
                    badge.className = 'text-[10px] px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30';
                }
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-rose-400';

                if (errEl) {
                    errEl.textContent = userMsg;
                    errEl.classList.remove('hidden');
                }

                if (allowBtn) {
                    allowBtn.textContent = 'Try Again';
                    allowBtn.className = 'text-xs px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-medium shadow-md transition-all cursor-pointer';
                }

                validateStartReadiness();
            }
        }

        function validateStartReadiness() {
            const consentEl = document.getElementById('rulesConsent');
            const consent = consentEl ? consentEl.checked : false;
            const startBtn = document.getElementById('startExamBtn');

            const formCam = document.getElementById('formCameraStatus');
            if (formCam) {
                formCam.value = (!requiresCamera) ? 'NOT_REQUIRED' : (cameraReady ? 'ACTIVE' : 'UNKNOWN');
            }

            if (startBtn) {
                if (consent && (!requiresCamera || cameraReady)) {
                    startBtn.disabled = false;
                } else {
                    startBtn.disabled = true;
                }
            }
        }

        async function requestStart() {
            if (requiresFullscreen) {
                try {
                    const el = document.documentElement;
                    if (el.requestFullscreen) {
                        await el.requestFullscreen();
                    } else if (el.webkitRequestFullscreen) {
                        await el.webkitRequestFullscreen();
                    } else if (el.msRequestFullscreen) {
                        await el.msRequestFullscreen();
                    }
                } catch (e) {
                    console.warn('Fullscreen request failed or was dismissed:', e);
                }
            }

            document.getElementById('startExamForm').submit();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const allowCamBtn = document.getElementById('allowCameraBtn');
            if (allowCamBtn) {
                allowCamBtn.addEventListener('click', () => initCamera(true));
            }

            const consentBox = document.getElementById('rulesConsent');
            if (consentBox) {
                consentBox.addEventListener('change', validateStartReadiness);
            }

            const startBtn = document.getElementById('startExamBtn');
            if (startBtn) {
                startBtn.addEventListener('click', requestStart);
            }

            if (requiresCamera) {
                // If the browser already has granted permission, auto-initialize smoothly
                if (navigator.permissions && navigator.permissions.query) {
                    navigator.permissions.query({ name: 'camera' }).then(status => {
                        if (status.state === 'granted') {
                            initCamera(false);
                        } else {
                            const badge = document.getElementById('cameraStatusBadge');
                            if (badge) {
                                badge.textContent = 'Action Required';
                                badge.className = 'text-[10px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30';
                            }
                        }
                    }).catch(() => {
                        initCamera(false);
                    });
                } else {
                    initCamera(false);
                }
            }
        });
    </script>
</body>
</html>
