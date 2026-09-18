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

        @if($exam->show_result_immediately && $result)
        <!-- Score Breakdown Card (shown immediately after submission) -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-black/60 space-y-5">
            <!-- Pass / Fail Status Banner -->
            @if($result->is_passed)
            <div class="flex items-center justify-center gap-3 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm font-bold text-emerald-400">Congratulations — You Passed!</p>
                    <p class="text-[11px] text-emerald-300/70">You have successfully cleared the qualifying marks.</p>
                </div>
            </div>
            @else
            <div class="flex items-center justify-center gap-3 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div>
                    <p class="text-sm font-bold text-rose-400">Result: Not Qualified</p>
                    <p class="text-[11px] text-rose-300/70">You did not reach the required qualifying marks.</p>
                </div>
            </div>
            @endif

            <!-- Score Breakdown Title -->
            <div class="border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>
                    Score Breakdown
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Your final result is computed from exam marks and any earned speed bonus.</p>
            </div>

            <!-- Score Rows -->
            <div class="space-y-3">
                <!-- Exam Mark -->
                <div class="flex items-center justify-between bg-slate-950/60 border border-slate-800 rounded-2xl px-5 py-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-300">Exam Mark</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Based on {{ $result->total_correct }} correct, {{ $result->total_wrong }} wrong answers</p>
                    </div>
                    <span class="font-mono text-xl font-bold text-indigo-400">{{ number_format((float)$result->objective_marks, 2) }}</span>
                </div>

                @if($exam->enable_speed_bonus && (float)$result->speed_bonus_marks > 0)
                <!-- Time Bonus Mark -->
                <div class="flex items-center justify-between bg-amber-500/5 border border-amber-500/20 rounded-2xl px-5 py-4">
                    <div>
                        <p class="text-xs font-semibold text-amber-300 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                            </svg>
                            Time Bonus Mark
                        </p>
                        <p class="text-[11px] text-amber-400/60 mt-0.5">Earned by answering questions faster than the question timer</p>
                    </div>
                    <span class="font-mono text-xl font-bold text-amber-400">+{{ number_format((float)$result->speed_bonus_marks, 2) }}</span>
                </div>
                @elseif($exam->enable_speed_bonus)
                <!-- Time Bonus Mark (zero) -->
                <div class="flex items-center justify-between bg-slate-950/40 border border-slate-800/50 rounded-2xl px-5 py-4 opacity-60">
                    <div>
                        <p class="text-xs font-semibold text-slate-400">Time Bonus Mark</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">No time bonus earned this session</p>
                    </div>
                    <span class="font-mono text-xl font-bold text-slate-500">+0.00</span>
                </div>
                @endif

                @if((float)$result->negative_marks > 0)
                <!-- Negative Marks -->
                <div class="flex items-center justify-between bg-rose-500/5 border border-rose-500/20 rounded-2xl px-5 py-4">
                    <div>
                        <p class="text-xs font-semibold text-rose-300">Negative Marks Deducted</p>
                        <p class="text-[11px] text-rose-400/60 mt-0.5">Deducted for {{ $result->total_wrong }} incorrect answers</p>
                    </div>
                    <span class="font-mono text-xl font-bold text-rose-400">-{{ number_format((float)$result->negative_marks, 2) }}</span>
                </div>
                @endif

                <!-- Divider -->
                <div class="border-t border-slate-700/60 my-1"></div>

                <!-- Total Mark -->
                <div class="flex items-center justify-between bg-gradient-to-r from-indigo-600/10 to-emerald-600/10 border border-indigo-500/30 rounded-2xl px-5 py-5">
                    <div>
                        <p class="text-sm font-bold text-white">Total Mark</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Exam Mark + Time Bonus Mark{{ (float)$result->negative_marks > 0 ? ' - Negative Marks' : '' }} &bull; Out of {{ number_format((float)($exam->total_marks), 2) }} marks &bull; {{ number_format((float)$result->percentage, 1) }}% &bull; Grade: <span class="font-bold text-indigo-300">{{ $result->grade ?: '-' }}</span></p>
                    </div>
                    <div class="text-right">
                        <span class="font-mono text-3xl font-bold text-white">{{ number_format((float)$result->final_score, 2) }}</span>
                        @if($exam->show_rank && $result->rank)
                        <p class="text-[11px] text-indigo-300 font-mono mt-1">Rank #{{ $result->rank }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Results & Feedback Withheld Notice -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-black/60 text-center space-y-4">
            <div class="inline-flex p-3.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white">Results &amp; Feedback Withheld</h3>
                <p class="text-xs text-slate-400 max-w-md mx-auto mt-1 leading-relaxed">
                    Score calculation and detailed answer evaluation for this examination will be reviewed and published officially by the examination committee.
                </p>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-950 border border-slate-800 text-[11px] font-mono text-slate-400">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                <span>Evaluation Pending</span>
            </div>
        </div>
        @endif

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