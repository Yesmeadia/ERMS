<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Submitted | {{ $exam->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-between antialiased relative">
    <x-public-background />

    <div class="max-w-2xl w-full mx-auto space-y-6 my-auto py-8 sm:py-12 px-4 sm:px-6 relative z-10">

        <!-- Brand Header -->
        <div class="text-center space-y-3">
            <div>
                <img src="{{ asset('logo-w.png') }}" alt="YES Genius"
                    class="h-12 sm:h-16 w-auto max-w-[240px] object-contain mx-auto filter drop-shadow-[0_2px_10px_rgba(0,0,0,0.3)]">
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">{{ $exam->name }}</h1>
            <p class="text-xs text-slate-400">
                Category: <span class="text-slate-300 font-medium">{{ $exam->category->name ?? 'General' }}</span>
                &bull;
                Exam Code: <span class="font-mono text-indigo-300">{{ $exam->code }}</span>
            </p>
        </div>

        <!-- Main Submission Card -->
        <div
            class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-black/60 space-y-6">

            <!-- Success Banner -->
            <div class="text-center space-y-3 pb-6 border-b border-slate-800/80">
                <div
                    class="inline-flex p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-white">Examination Submitted Successfully</h2>
                    <p class="text-xs text-slate-400 mt-1">
                        All your responses and activity logs have been recorded in the examination system.
                    </p>
                </div>

                <!-- Candidate Info Pill -->
                <div
                    class="inline-flex flex-wrap items-center justify-center gap-x-3 gap-y-1 bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-300">
                    <span>Candidate: <strong class="text-white">{{ $session->student->name }}</strong></span>
                    <span class="text-slate-600 hidden sm:inline">&bull;</span>
                    <span>Reg No: <strong
                            class="font-mono text-indigo-300">{{ $session->student->registration_number }}</strong></span>
                    @if($session->student->school)
                        <span class="text-slate-600 hidden sm:inline">&bull;</span>
                        <span>{{ $session->student->school->name }}</span>
                    @endif
                </div>
            </div>

            <!-- Focused 3 Metrics: Answered Questions, Used Time, Remaining Time -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <!-- 1. Answered Questions -->
                <div
                    class="bg-slate-950/70 border border-slate-800 rounded-2xl p-5 text-center flex flex-col justify-between">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-slate-400 block mb-2">Answered
                        Questions</span>
                    <div class="my-auto py-2">
                        <div class="items-baseline justify-center">
                            <span
                                class="text-3xl sm:text-4xl font-bold font-mono text-indigo-400">{{ $answeredQuestions }}</span>
                            <span class="text-sm font-mono text-slate-400"> / {{ $totalQuestions }}</span>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">
                        {{ $unansweredQuestions }} unanswered
                    </span>
                </div>

                <!-- 2. Used Time -->
                <div
                    class="bg-slate-950/70 border border-slate-800 rounded-2xl p-5 text-center flex flex-col justify-between">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-slate-400 block mb-2">Used
                        Time</span>
                    <div class="my-auto py-2">
                        <span
                            class="text-3xl sm:text-4xl font-bold font-mono text-emerald-400">{{ $usedTimeFormatted }}</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">
                        {{ $usedTimeText }}
                    </span>
                </div>

                <!-- 3. Remaining Time -->
                <div
                    class="bg-slate-950/70 border border-slate-800 rounded-2xl p-5 text-center flex flex-col justify-between">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-slate-400 block mb-2">Remaining
                        Time</span>
                    <div class="my-auto py-2">
                        <span
                            class="text-3xl sm:text-4xl font-bold font-mono text-amber-400">{{ $remainTimeFormatted }}</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">
                        {{ $remainTimeText }}
                    </span>
                </div>

            </div>
        </div>

        <!-- Action Button -->
        <div class="text-center pt-2">
            <a href="{{ route('home') }}"
                class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700/80 transition-colors gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Return to Home</span>
            </a>
        </div>

    </div>

    <!-- Official ERMS Public Footer Component -->
    <x-public-footer page="result" />
</body>
</html>