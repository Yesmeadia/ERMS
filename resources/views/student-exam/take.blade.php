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

        /* Question layout with side-by-side image */
        .q-layout-row {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        @media (min-width: 768px) {
            .q-layout-row.has-image {
                display: grid;
                grid-template-columns: 320px minmax(0, 1fr);
                align-items: start;
                gap: 1.75rem;
            }
        }
        .q-image-col {
            width: 100%;
        }
        @media (min-width: 768px) {
            .q-image-col {
                width: 320px;
                position: sticky;
                top: 5.5rem;
            }
        }
        .q-image-card {
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid rgba(51, 65, 85, 0.8);
            background: rgba(2, 6, 23, 0.8);
            padding: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
        }
        .q-image-card img {
            max-height: 260px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            border-radius: 0.75rem;
            display: block;
            margin: 0 auto;
        }
        .q-content-col {
            min-width: 0;
            width: 100%;
        }
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
                        <span class="text-[9px] uppercase tracking-wider text-slate-400 block font-semibold leading-none">Question Timer</span>
                        <span id="questionTimerText" class="font-mono text-sm font-bold text-emerald-400 leading-none">--:--</span>
                        <span id="questionTimerLimit" class="text-[9px] text-slate-500 font-mono block leading-none mt-0.5">/ {{ $payload['time_limit_seconds'] }}s</span>
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


            </div>

            <!-- Question Body -->
            <div id="questionContentArea" class="py-6">
                <!-- Main Layout: Image Left + Content Right -->
                <div id="questionBodyLayout" class="q-layout-row {{ !empty($payload['images']) ? 'has-image' : '' }}">

                    <!-- Left: Attached Images (shown only when images exist) -->
                    <div id="questionImagesContainer" class="q-image-col space-y-3 {{ empty($payload['images']) ? 'hidden' : '' }}">
                        @if(!empty($payload['images']))
                            @foreach($payload['images'] as $img)
                                <div class="q-image-card">
                                    <img src="{{ $img['url'] }}" alt="Question Illustration">
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <!-- Right (or Full-Width): Question Text + Options -->
                    <div class="q-content-col space-y-6">
                        <!-- Question Text -->
                        <div id="questionTextContainer" class="text-base sm:text-lg text-white font-normal leading-relaxed">
                            {!! preg_replace('/\s*on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', strip_tags($payload['question_text'], '<p><br><b><strong><i><em><u><s><sub><sup><ul><ol><li><table><thead><tbody><tr><th><td><span><div><code><pre><blockquote><h1><h2><h3><h4><h5><h6>')) !!}
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
                </div>
            </div>

            <!-- Question Time Up Container (Hidden by default, shown when question timer expires) -->
            <div id="timeUpContainer" class="hidden py-8 px-6 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-center space-y-3 my-6">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center mx-auto shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-amber-300">Time's Up for This Question!</h3>
                <p class="text-sm text-slate-300 max-w-md mx-auto">The allotted time has expired. This question has been hidden and cannot be answered.</p>
                <p class="text-xs text-slate-400 font-medium">Click <span class="text-indigo-400 font-semibold">[ Next Question ]</span> to proceed to the next question.</p>
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

                    <!-- Next Question Button (Hidden until answer is submitted, unless already saved) -->
                    <button type="button" id="nextQuestionBtn"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2 {{ (!$payload['is_already_saved'] || $payload['is_last_question']) ? 'hidden' : '' }}">
                        <span>Next Question</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>

                    <!-- Finish Exam Button (shown on last question ONLY after answer is submitted) -->
                    <button type="button" id="finishExamBtn"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-semibold text-xs shadow-lg shadow-rose-600/20 transition-all flex items-center gap-2 {{ ($payload['is_already_saved'] && $payload['is_last_question']) ? '' : 'hidden' }}">
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

    @if(!empty($isReady))
    <!-- Fullscreen Onboarding & Instructions Modal Overlay -->
    <div id="instructionsOverlay" class="fixed inset-0 bg-slate-950/95 backdrop-blur-xl z-50 overflow-y-auto p-4 sm:p-6 lg:p-8 flex flex-col justify-between">
        <div class="max-w-5xl w-full mx-auto space-y-6 my-auto">
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
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 text-center">
                            <span class="text-[11px] uppercase tracking-wider text-slate-400 block mb-1">Total Questions</span>
                            <span class="text-2xl font-bold text-white font-mono">{{ $exam->examQuestions()->count() }}</span>
                        </div>
                        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 text-center">
                            <span class="text-[11px] uppercase tracking-wider text-slate-400 block mb-1">Total Marks</span>
                            <span class="text-2xl font-bold text-indigo-400 font-mono">{{ $exam->total_marks }}</span>
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
                                <span><strong>One Question at a Time:</strong> Questions will appear sequentially with an individual question timer as well as the total exam timer.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold shrink-0 mt-0.5">2</span>
                                <span><strong>Explicit Answer Submission:</strong> Select or type your answer and click <span class="text-emerald-400 font-semibold">[ Submit Answer ]</span>. Once submitted, your response is safely recorded and locked. You must then click <span class="text-indigo-400 font-semibold">[ Next Question ]</span> to proceed.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold shrink-0 mt-0.5">3</span>
                                <span><strong>Strict Server-Side Timing:</strong> If the question timer expires, the question is hidden and awarded 0 marks. The <span class="text-indigo-400 font-semibold">[ Next Question ]</span> button will then appear for you to proceed.</span>
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
                                {!! preg_replace('/\s*on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', strip_tags($exam->instructions, '<p><br><b><strong><i><em><u><s><ul><ol><li><span><div>')) !!}
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
                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse" id="instructionCameraDot"></span>
                                    Web Camera Proctor
                                </span>
                                <span id="instructionCameraStatusBadge" class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-400">Verifying...</span>
                            </div>
                            <div class="relative w-full aspect-video bg-slate-950 rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center">
                                <video id="instructionCameraPreview" autoplay playsinline muted class="w-full h-full object-cover hidden"></video>
                                <div id="instructionCameraPlaceholder" class="text-center p-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                    <button type="button" id="instructionAllowCameraBtn" class="text-xs px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                                        Allow Camera Access
                                    </button>
                                    <p id="instructionCameraErrorMessage" class="hidden text-xs text-rose-300 mt-2.5 px-3 py-2 rounded-xl bg-rose-950/60 border border-rose-800/60 text-center leading-relaxed"></p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($exam->enable_fullscreen)
                        <!-- Fullscreen Status -->
                        <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                            <div class="text-xs">
                                <p class="font-semibold text-slate-300">Fullscreen Mode</p>
                                <p class="text-[11px] text-slate-500">Locks into fullscreen and begins immediately</p>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-indigo-400 font-medium">Auto-Lock</span>
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
                        <button type="button" id="startExamBtn" disabled
                                class="w-full py-3.5 px-4 rounded-2xl text-sm font-semibold bg-indigo-600 text-white shadow-xl shadow-indigo-600/20 disabled:opacity-40 disabled:cursor-not-allowed hover:enabled:bg-indigo-500 transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <span>Enter Fullscreen & Start Exam</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($exam->enable_fullscreen)
    <!-- Fullscreen Initial Activation Modal -->
    <div id="fullscreenStartPrompt" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 flex items-center justify-center p-4 {{ !empty($isReady) ? 'hidden' : '' }}">
        <div class="bg-slate-900 border border-indigo-500/50 rounded-3xl max-w-md w-full p-6 text-center space-y-4 shadow-2xl">
            <div class="w-14 h-14 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white">Enter Fullscreen Mode</h3>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    This examination requires fullscreen mode. Click below to expand your window and begin answering questions.
                </p>
            </div>
            <button type="button" id="enterFullscreenBtn"
                    class="w-full py-3.5 px-4 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                <span>Enter Fullscreen & Begin</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>
    </div>
    @endif

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
        const WEBRTC_ICE_SERVERS_URL = "{{ route('online-exam.webrtc.ice-servers') }}";
        const PROCTORING_RECORD_CHUNK_URL = "{{ route('online-exam.proctoring.record-chunk') }}";
        const PROCTORING_SNAPSHOT_URL = "{{ route('online-exam.proctoring.snapshot') }}";
        const EXAM_SESSION_TOKEN = "{{ $session->session_token }}";

        const RTC_CONFIG = {
            iceServers: @json(\App\Http\Controllers\OnlineExamWebRTCController::getIceServersConfig())
        };

        const START_EXAM_URL = "{{ route('online-exam.start') }}";
        const isReadySession = {{ !empty($isReady) ? 'true' : 'false' }};
        let cameraReady = {{ $exam->enable_camera ? 'false' : 'true' }};

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

        // Anti-Cheating violation debounce timestamp & startup grace period
        let lastViolationTime = 0;
        const VIOLATION_DEBOUNCE_MS = 2000;
        let examStartTime = Date.now();
        const EXAM_STARTUP_GRACE_PERIOD_MS = 15000;
        let hasEnteredFullscreenOnce = false;

        // Instructions Onboarding Camera Initializer
        async function initInstructionsCamera() {
            if (!requiresCamera) return;
            const badge = document.getElementById('instructionCameraStatusBadge');
            const dot = document.getElementById('instructionCameraDot');
            const video = document.getElementById('instructionCameraPreview');
            const placeholder = document.getElementById('instructionCameraPlaceholder');
            const errEl = document.getElementById('instructionCameraErrorMessage');
            const allowBtn = document.getElementById('instructionAllowCameraBtn');

            if (errEl) errEl.classList.add('hidden');
            if (badge) {
                badge.textContent = 'Connecting...';
                badge.className = 'text-[10px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30';
            }

            try {
                mediaStream = await getCameraStream(320, 240);

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
                    video.classList.remove('hidden');
                }

                if (placeholder) placeholder.classList.add('hidden');
                if (badge) {
                    badge.innerHTML = `<span class="inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        <span>Camera Active</span>
                    </span>`;
                    badge.className = 'text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
                }
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-emerald-400';
                cameraReady = true;

                const consentBox = document.getElementById('rulesConsent');
                const startBtn = document.getElementById('startExamBtn');
                if (startBtn && consentBox) {
                    startBtn.disabled = !consentBox.checked;
                }
            } catch (err) {
                cameraReady = false;
                let userMsg = 'Camera error. Please allow camera permissions to begin.';
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    userMsg = 'Camera permission was blocked. Please click the lock icon in your address bar, allow camera access, and click Try Again.';
                }
                if (errEl) {
                    errEl.textContent = userMsg;
                    errEl.classList.remove('hidden');
                }
                if (badge) {
                    badge.textContent = 'Camera Denied';
                    badge.className = 'text-[10px] px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30';
                }
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-rose-400';
                if (allowBtn) {
                    allowBtn.textContent = 'Try Again';
                    allowBtn.className = 'text-xs px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-medium shadow-md transition-all cursor-pointer';
                }
            }
        }

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

        // Resilient camera & microphone stream resolver with progressive fallback
        async function getCameraStream(idealWidth = 320, idealHeight = 240) {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                // Tier 1: Ideal user-facing camera with audio
                try {
                    return await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: idealWidth }, height: { ideal: idealHeight }, facingMode: 'user' },
                        audio: true
                    });
                } catch (e1) {
                    console.warn('Tier 1 constraints (facingMode: user + audio) failed, attempting Tier 2:', e1.name, e1.message);
                }

                // Tier 2: Specific resolution without facingMode with audio
                try {
                    return await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: idealWidth }, height: { ideal: idealHeight } },
                        audio: true
                    });
                } catch (e2) {
                    console.warn('Tier 2 constraints (audio) failed, attempting Tier 3:', e2.name, e2.message);
                }

                // Tier 3: Bare minimum video with audio
                try {
                    return await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                } catch (e3) {
                    console.warn('Tier 3 with audio failed, falling back to video only:', e3.name, e3.message);
                }

                // Tier 4: Fallback to video only if microphone is unavailable or blocked
                return await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            }

            const legacyNav = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
            if (legacyNav) {
                return new Promise((resolve, reject) => {
                    legacyNav.call(navigator, { video: true, audio: true }, resolve, (err) => {
                        legacyNav.call(navigator, { video: true, audio: false }, resolve, reject);
                    });
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
                initProctoringVideoRecording();

                // Detect track ended & attempt single automatic recovery
                mediaStream.getVideoTracks().forEach(track => {
                    track.onended = async () => {
                        console.warn('[Camera] Track ended, attempting recovery...');
                        try {
                            mediaStream = await getCameraStream(320, 240);
                            const v = document.getElementById('proctorWebcam');
                            if (v) v.srcObject = mediaStream;
                            sendHeartbeat();
                            initProctoringVideoRecording();
                        } catch (recErr) {
                            recordSecurityViolation('CAMERA_STOPPED', { note: 'Camera track ended and recovery failed: ' + (recErr.message || recErr.name) });
                            sendHeartbeat();
                        }
                    };
                });
                return true;
            } catch (err) {
                console.warn('Camera stream error:', err);
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
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'X-Exam-Session-Token': EXAM_SESSION_TOKEN
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

            // NOTE: vanilla (non-trickle) ICE — candidates are embedded in the answer SDP.
            // On shared-hosting HTTP polling the 1.5-2s round-trip makes trickle ICE
            // unreliable; bundling all candidates in the answer SDP fixes this.
            pc.onicecandidate = () => {}; // intentionally no-op; gathering handled below

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
                // The incoming offer already contains all admin candidates (vanilla ICE),
                // so setRemoteDescription immediately gives us usable remote info.
                await pc.setRemoteDescription(new RTCSessionDescription(offerPayload));

                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);

                // Wait for ICE gathering to complete before sending the answer
                await new Promise((resolve) => {
                    if (pc.iceGatheringState === 'complete') return resolve();
                    const checkDone = () => {
                        if (pc.iceGatheringState === 'complete') {
                            pc.removeEventListener('icegatheringstatechange', checkDone);
                            resolve();
                        }
                    };
                    pc.addEventListener('icegatheringstatechange', checkDone);
                    // Safety timeout: proceed after 4s even if gathering stalls
                    setTimeout(resolve, 4000);
                });

                if (currentAdminId !== adminId) return; // admin inspector closed during gathering

                // Send the complete answer SDP (all candidates embedded)
                sendWebRtcSignal('answer', adminId, pc.localDescription);
            } catch (err) {
                console.error('[WebRTC Student] Failed processing admin offer:', err);
                closeAdminPeer();
            }
        }

        async function processAdminCandidate(adminId, candidatePayload) {
            // Kept for backwards-compat; vanilla ICE makes this a no-op in normal flow.
            if (currentAdminId !== adminId || !activePeer) return;
            const pc = activePeer;
            if (pc.remoteDescription && pc.remoteDescription.type) {
                try { await pc.addIceCandidate(new RTCIceCandidate(candidatePayload)); } catch (e) {}
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
                        'X-Exam-Session-Token': EXAM_SESSION_TOKEN,
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

        // ==========================================
        // Continuous Proctoring Recording & Snapshots
        // ==========================================
        let proctorMediaRecorder = null;
        let proctorChunkIndex = 0;
        let proctorSnapshotInterval = null;

        function initProctoringVideoRecording() {
            if (!requiresCamera || !mediaStream || !window.MediaRecorder) return;

            const hasAudio = mediaStream.getAudioTracks && mediaStream.getAudioTracks().length > 0;

            // Determine best supported MIME type based on track availability
            let mimeType = '';
            if (hasAudio) {
                if (MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')) {
                    mimeType = 'video/webm;codecs=vp8,opus';
                } else if (MediaRecorder.isTypeSupported('video/webm')) {
                    mimeType = 'video/webm';
                } else if (MediaRecorder.isTypeSupported('video/mp4')) {
                    mimeType = 'video/mp4';
                }
            } else {
                if (MediaRecorder.isTypeSupported('video/webm;codecs=vp8')) {
                    mimeType = 'video/webm;codecs=vp8';
                } else if (MediaRecorder.isTypeSupported('video/webm')) {
                    mimeType = 'video/webm';
                } else if (MediaRecorder.isTypeSupported('video/mp4')) {
                    mimeType = 'video/mp4';
                }
            }

            // Start periodic lightweight snapshot sender for the live admin grid
            startSnapshotBroadcaster();

            // Start segmented video recorder (every 10 seconds)
            startSegmentedVideoRecorder(mimeType, hasAudio);
        }

        function startSnapshotBroadcaster() {
            if (proctorSnapshotInterval) clearInterval(proctorSnapshotInterval);

            async function captureAndSendSnapshot() {
                if (!mediaStream || isTerminated) return;
                const video = document.getElementById('proctorWebcam');
                if (!video) return;

                const width = video.videoWidth || 320;
                const height = video.videoHeight || 240;

                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = 320;
                    canvas.height = 240;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const base64Jpeg = canvas.toDataURL('image/jpeg', 0.65);
                    if (!base64Jpeg || base64Jpeg.length < 100) return;

                    await fetch(PROCTORING_SNAPSHOT_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'X-Exam-Session-Token': EXAM_SESSION_TOKEN,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ snapshot: base64Jpeg }),
                        keepalive: true
                    });
                } catch (err) {
                    // Non-blocking snapshot upload
                }
            }

            // Initial capture after 1.5 seconds, then every 5 seconds
            setTimeout(captureAndSendSnapshot, 1500);
            proctorSnapshotInterval = setInterval(captureAndSendSnapshot, 5000);
        }

        function startSegmentedVideoRecorder(mimeType, hasAudio) {
            try {
                const options = {};
                if (mimeType) {
                    options.mimeType = mimeType;
                }
                options.videoBitsPerSecond = 250000; // 250 kbps: lightweight, clear for faces
                if (hasAudio) {
                    options.audioBitsPerSecond = 64000; // 64 kbps: voice recording
                }

                try {
                    proctorMediaRecorder = new MediaRecorder(mediaStream, options);
                } catch (e1) {
                    console.warn('[Proctoring Recorder] Options error, falling back:', e1);
                    if (mimeType) {
                        try {
                            proctorMediaRecorder = new MediaRecorder(mediaStream, { mimeType });
                        } catch (e2) {
                            proctorMediaRecorder = new MediaRecorder(mediaStream);
                        }
                    } else {
                        proctorMediaRecorder = new MediaRecorder(mediaStream);
                    }
                }

                proctorMediaRecorder.ondataavailable = async (event) => {
                    if (event.data && event.data.size > 0) {
                        await uploadRecordedChunk(event.data, proctorChunkIndex++);
                    }
                };

                // Time slice: triggers ondataavailable every 10 seconds
                proctorMediaRecorder.start(10000);
            } catch (err) {
                console.warn('[Proctoring Recorder] MediaRecorder initialization failed:', err);
            }
        }

        async function uploadRecordedChunk(blob, index, isFinal = false) {
            if (!blob || blob.size === 0) return;

            try {
                const formData = new FormData();
                formData.append('video_chunk', blob, `chunk_${index}.webm`);
                formData.append('chunk_index', index);
                formData.append('duration', 10.0);
                formData.append('is_final', isFinal ? 1 : 0);

                await fetch(PROCTORING_RECORD_CHUNK_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'X-Exam-Session-Token': EXAM_SESSION_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: formData,
                    keepalive: true
                });
            } catch (err) {
                console.warn('[Proctoring Recorder] Chunk upload failed:', err);
            }
        }

        function stopProctoringRecording(isFinal = true) {
            if (proctorSnapshotInterval) {
                clearInterval(proctorSnapshotInterval);
                proctorSnapshotInterval = null;
            }

            if (proctorMediaRecorder && proctorMediaRecorder.state !== 'inactive') {
                try {
                    proctorMediaRecorder.requestData();
                    proctorMediaRecorder.stop();
                } catch (e) {}
            }
        }

        async function finalizeAndUploadLastChunk() {
            if (proctorSnapshotInterval) {
                clearInterval(proctorSnapshotInterval);
                proctorSnapshotInterval = null;
            }

            if (!proctorMediaRecorder || proctorMediaRecorder.state === 'inactive') {
                return;
            }

            return new Promise((resolve) => {
                let resolved = false;
                const safeResolve = () => {
                    if (!resolved) {
                        resolved = true;
                        resolve();
                    }
                };

                // 2-second safety timeout so form submission is never blocked
                const timeoutId = setTimeout(safeResolve, 2000);

                proctorMediaRecorder.ondataavailable = async (event) => {
                    if (event.data && event.data.size > 0) {
                        try {
                            await uploadRecordedChunk(event.data, proctorChunkIndex++, true);
                        } catch (e) {
                            console.warn('Final chunk upload error:', e);
                        }
                    }
                    clearTimeout(timeoutId);
                    safeResolve();
                };

                try {
                    proctorMediaRecorder.stop();
                } catch (e) {
                    clearTimeout(timeoutId);
                    safeResolve();
                }
            });
        }

        function stopCameraTracks() {
            if (mediaStream) {
                try {
                    mediaStream.getTracks().forEach(track => {
                        try { track.stop(); } catch (e) {}
                    });
                } catch (e) {}
                mediaStream = null;
            }
            const video = document.getElementById('proctorWebcam');
            if (video) {
                video.srcObject = null;
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

                // Question Timer Expiration: Hide question, set marks to 0, show Next Question button
                if (questionRemainingMs <= 0 && !currentPayload.is_already_saved && !isSubmitting && !isTransitioning) {
                    handleQuestionTimeout();
                }

                // Overall Exam Expiration: Verify with server authoritative timer
                if (examRemainingMs <= 0 && !isTransitioning) {
                    isTransitioning = true;
                    clearInterval(timerInterval);
                    finalizeAndUploadLastChunk().finally(() => {
                        stopCameraTracks();
                        sendHeartbeat().finally(() => {
                            window.location.href = RESULT_URL;
                        });
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

            const qLimitMs = (currentPayload.time_limit_seconds || 60) * 1000;
            const timeSpentMs = Math.max(0, qLimitMs - questionRemainingMs);

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
                        text_answer: answer.textAnswer,
                        time_spent_ms: timeSpentMs
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
                        document.getElementById('finishExamBtn').classList.add('hidden');
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

        // When question timer expires: hide question, set marks to 0, show Next Question button
        async function handleQuestionTimeout() {
            if (isSubmitting || currentPayload.is_already_saved) return;
            isSubmitting = true;

            currentPayload.is_already_saved = true;
            currentPayload.is_timed_out = true;

            // 1. Hide the question content area
            const questionContentArea = document.getElementById('questionContentArea');
            if (questionContentArea) questionContentArea.classList.add('hidden');

            // 2. Show the Time's Up notification container
            const timeUpContainer = document.getElementById('timeUpContainer');
            if (timeUpContainer) timeUpContainer.classList.remove('hidden');

            // 3. Set the question mark as 0 in UI
            const qMarksBadge = document.getElementById('qMarksBadge');
            if (qMarksBadge) {
                qMarksBadge.textContent = '0 marks';
                qMarksBadge.className = 'px-2.5 py-1 rounded-xl bg-slate-800 border border-slate-700 text-slate-400 font-mono text-xs font-semibold';
            }
            const qNegMarksBadge = document.getElementById('qNegMarksBadge');
            if (qNegMarksBadge) {
                qNegMarksBadge.classList.add('hidden');
            }

            // 4. Hide Submit Answer button
            const submitBtn = document.getElementById('submitAnswerBtn');
            if (submitBtn) submitBtn.classList.add('hidden');

            // 5. Show Next Question button (or Finish Exam button if last question)
            const nextBtn = document.getElementById('nextQuestionBtn');
            const finishBtn = document.getElementById('finishExamBtn');
            if (currentPayload.is_last_question) {
                if (finishBtn) finishBtn.classList.remove('hidden');
                if (nextBtn) nextBtn.classList.add('hidden');
            } else {
                if (nextBtn) nextBtn.classList.remove('hidden');
                if (finishBtn) finishBtn.classList.add('hidden');
            }

            // 6. Asynchronously record the timeout with 0 marks on the server
            const qLimitMs = (currentPayload.time_limit_seconds || 60) * 1000;
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
                        selected_option_ids: null,
                        text_answer: null,
                        time_spent_ms: qLimitMs,
                        is_timeout: true
                    })
                });
            } catch (e) {
                console.warn('Timeout submit error:', e);
            } finally {
                isSubmitting = false;
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

            // Update per-question time limit label in the timer header
            const limitEl = document.getElementById('questionTimerLimit');
            if (limitEl && p.time_limit_seconds) {
                limitEl.textContent = `/ ${p.time_limit_seconds}s`;
            }

            // Restore question content area visibility and hide time-up container
            const questionContentArea = document.getElementById('questionContentArea');
            if (questionContentArea) questionContentArea.classList.remove('hidden');
            const timeUpContainer = document.getElementById('timeUpContainer');
            if (timeUpContainer) timeUpContainer.classList.add('hidden');

            // Update Progress & Badges
            document.getElementById('qIndexText').textContent = p.question_index;
            document.getElementById('qTotalText').textContent = p.total_questions;
            document.getElementById('qTypeBadge').textContent = p.question_type_label;


            const pct = (p.question_index / Math.max(1, p.total_questions)) * 100;
            document.getElementById('examProgressBar').style.width = `${pct}%`;

            // Question Text: sanitized HTML
            document.getElementById('questionTextContainer').innerHTML = sanitizeQuestionHtml(p.question_text);

            // Images: validated URL and escaped attributes — shown on the LEFT side
            const imgContainer = document.getElementById('questionImagesContainer');
            const bodyLayout = document.getElementById('questionBodyLayout');
            const validImages = (p.images || []).filter(img => isValidImageUrl(img.url));
            if (validImages.length > 0) {
                imgContainer.innerHTML = validImages.map(img => `
                    <div class="q-image-card">
                        <img src="${escapeHtml(img.url)}" alt="Question Illustration">
                    </div>
                `).join('');
                imgContainer.classList.remove('hidden');
                // Enable side-by-side layout
                if (bodyLayout) {
                    bodyLayout.classList.add('has-image');
                }
            } else {
                imgContainer.innerHTML = '';
                imgContainer.classList.add('hidden');
                // Revert to stacked layout
                if (bodyLayout) {
                    bodyLayout.classList.remove('has-image');
                }
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
                if (p.is_last_question) {
                    nextBtn.classList.add('hidden');
                    finishBtn.classList.remove('hidden');
                } else {
                    nextBtn.classList.remove('hidden');
                    finishBtn.classList.add('hidden');
                }
            } else {
                banner.classList.add('hidden');
                submitBtn.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg><span>Submit Answer</span>`;
                // Hide Next and Finish buttons until question is submitted!
                nextBtn.classList.add('hidden');
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
                    if (Date.now() - examStartTime > EXAM_STARTUP_GRACE_PERIOD_MS) {
                        recordSecurityViolation('TAB_SWITCH', { note: 'Student navigated away from tab' });
                        showViolationModal('You switched browser tabs! This has been recorded as a violation.');
                    }
                }
            });

            // Window blur listener: only record if student is in fullscreen and past grace period
            window.addEventListener('blur', () => {
                const now = Date.now();
                if (now - examStartTime < EXAM_STARTUP_GRACE_PERIOD_MS) {
                    return; // Grace period during startup and browser permission dialogs
                }
                const isFull = !!(document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement);
                if (!document.hidden && requiresFullscreen && isFull) {
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
            if (isFull) {
                hasEnteredFullscreenOnce = true;
                const prompt = document.getElementById('fullscreenStartPrompt');
                if (prompt) prompt.classList.add('hidden');
            } else if (requiresFullscreen && !isTerminated && hasEnteredFullscreenOnce && (Date.now() - examStartTime > EXAM_STARTUP_GRACE_PERIOD_MS)) {
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

            const enterFsBtn = document.getElementById('enterFullscreenBtn');
            if (enterFsBtn) {
                enterFsBtn.addEventListener('click', async () => {
                    try {
                        const el = document.documentElement;
                        if (el.requestFullscreen) await el.requestFullscreen();
                        else if (el.webkitRequestFullscreen) await el.webkitRequestFullscreen();
                    } catch (e) {
                        console.warn('Fullscreen request:', e);
                    }
                    hasEnteredFullscreenOnce = true;
                    document.getElementById('fullscreenStartPrompt')?.classList.add('hidden');
                });
            }

            if (document.fullscreenElement || document.webkitFullscreenElement) {
                hasEnteredFullscreenOnce = true;
                document.getElementById('fullscreenStartPrompt')?.classList.add('hidden');
            }

            const finishForm = document.querySelector('#finishModal form');
            let isFinishSubmitting = false;
            if (finishForm) {
                finishForm.addEventListener('submit', async (e) => {
                    if (isFinishSubmitting) return;
                    e.preventDefault();
                    isFinishSubmitting = true;

                    const submitBtn = finishForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `
                            <svg class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Submitting...
                        `;
                    }

                    // 1. Finalize and flush final video chunk
                    try {
                        await finalizeAndUploadLastChunk();
                    } catch (err) {
                        console.warn('Final chunk flush error:', err);
                    }

                    // 2. Send WebRTC close signal to proctor
                    try {
                        await sendWebRtcSignal('close', currentAdminId, { reason: 'exam_submitted' });
                    } catch (err) {}

                    // 3. Stop camera and microphone hardware tracks
                    stopCameraTracks();

                    // 4. Submit the form
                    finishForm.submit();
                });
            }

            if (isReadySession) {
                // Ensure fallback prompt is hidden while onboarding overlay is active
                document.getElementById('fullscreenStartPrompt')?.classList.add('hidden');

                const rulesConsent = document.getElementById('rulesConsent');
                const startExamBtn = document.getElementById('startExamBtn');

                if (rulesConsent && startExamBtn) {
                    rulesConsent.addEventListener('change', () => {
                        const canStart = rulesConsent.checked && (!requiresCamera || cameraReady);
                        startExamBtn.disabled = !canStart;
                        if (canStart) {
                            startExamBtn.classList.add('ring-2', 'ring-indigo-400', 'animate-pulse');
                        } else {
                            startExamBtn.classList.remove('ring-2', 'ring-indigo-400', 'animate-pulse');
                        }
                    });

                    startExamBtn.addEventListener('click', async () => {
                        startExamBtn.disabled = true;
                        startExamBtn.classList.remove('animate-pulse');
                        startExamBtn.innerHTML = `
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Starting Exam & Locking Fullscreen...</span>
                        `;

                        // 1. Enter Fullscreen IMMEDIATELY within direct user gesture activation!
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
                                console.warn('Fullscreen request:', e);
                            }
                        }
                        hasEnteredFullscreenOnce = true;

                        // 2. Start exam on server via background AJAX (NO page reload, so fullscreen NEVER drops!)
                        try {
                            const res = await fetch(START_EXAM_URL, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': CSRF_TOKEN,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    camera_status: (requiresCamera && cameraReady) ? 'ACTIVE' : 'UNKNOWN'
                                })
                            });

                            if (res.ok) {
                                const data = await res.json();
                                if (data.success) {
                                    if (data.remaining_ms && data.remaining_ms > 0) {
                                        questionRemainingMs = data.remaining_ms;
                                    }
                                    if (data.exam_remaining_ms && data.exam_remaining_ms > 0) {
                                        examRemainingMs = data.exam_remaining_ms;
                                    }
                                    if (data.payload) {
                                        currentPayload = data.payload;
                                        renderQuestion(currentPayload);
                                    }
                                }
                            }
                        } catch (e) {
                            console.warn('Start exam error:', e);
                        }

                        // 3. Remove onboarding overlay smoothly
                        const overlay = document.getElementById('instructionsOverlay');
                        if (overlay) {
                            overlay.classList.add('transition-opacity', 'duration-300', 'opacity-0');
                            setTimeout(() => overlay.remove(), 300);
                        }

                        document.getElementById('fullscreenStartPrompt')?.classList.add('hidden');

                        // 4. Attach proctor camera to thumbnail preview
                        if (requiresCamera) {
                            const proctorVid = document.getElementById('proctorWebcam');
                            if (proctorVid && mediaStream) {
                                proctorVid.srcObject = mediaStream;
                                proctorVid.muted = true;
                                proctorVid.playsInline = true;
                                try { await proctorVid.play(); } catch(e) {}
                                initWebRtcSignaling();
                                initProctoringVideoRecording();
                            } else {
                                await startCamera();
                            }
                        }

                        // 5. Start timers, heartbeats, and anti-cheating watchdogs
                        examStartTime = Date.now();
                        setupAntiCheatingWatchdogs();
                        setupFillBlankEnterSubmit();
                        updateTimerDisplay();
                        startTimers();
                        startHeartbeat();
                    });
                }

                // If camera required, bind camera preview in the instructions overlay
                if (requiresCamera) {
                    const allowCamBtn = document.getElementById('instructionAllowCameraBtn');
                    if (allowCamBtn) {
                        allowCamBtn.addEventListener('click', () => initInstructionsCamera());
                    }

                    if (navigator.permissions && navigator.permissions.query) {
                        navigator.permissions.query({ name: 'camera' }).then(status => {
                            if (status.state === 'granted') {
                                initInstructionsCamera();
                            }
                        }).catch(() => {
                            initInstructionsCamera();
                        });
                    } else {
                        initInstructionsCamera();
                    }
                }
            } else {
                // Resume existing session in progress
                if (requiresCamera) {
                    await startCamera();
                }

                // Mark fullscreen as already entered so resuming doesn't fire false FULLSCREEN_EXIT violations.
                // The student was already in fullscreen when they started; page reload re-enters via the prompt.
                if (requiresFullscreen) {
                    hasEnteredFullscreenOnce = true;
                    // If not currently fullscreen (e.g. after a hard refresh), show the prompt
                    if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                        const resumePrompt = document.getElementById('fullscreenStartPrompt');
                        if (resumePrompt) {
                            resumePrompt.classList.remove('hidden');
                        }
                    }
                }

                setupAntiCheatingWatchdogs();
                setupFillBlankEnterSubmit();
                startTimers();
                startHeartbeat();
            }

            window.addEventListener('beforeunload', () => {
                if (webrtcSignalingInterval) clearInterval(webrtcSignalingInterval);
                closeAdminPeer();
                stopProctoringRecording(true);
                stopCameraTracks();
            });
        });
    </script>
</body>
</html>
