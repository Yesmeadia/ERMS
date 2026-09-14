@extends('layouts.app')

@section('page_title', 'Online Examination Dashboard')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-white via-slate-200 to-indigo-300 bg-clip-text text-transparent">
                Online Examination Dashboard
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Real-time management of online examinations, question banks, and student sessions.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.online-questions.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium bg-slate-800/80 hover:bg-slate-800 text-slate-200 border border-slate-700/60 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                Question Bank
            </a>

            <a href="{{ route('admin.online-exams.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Create Online Exam
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- Total Exams -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Exams</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white mt-3">{{ $metrics['total_exams'] }}</p>
        </div>

        <!-- Active Today -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Today</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-emerald-400 mt-3">{{ $metrics['active_today'] }}</p>
        </div>

        <!-- Question Bank -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Question Bank</span>
                <span class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white mt-3">{{ $metrics['total_questions'] }}</p>
        </div>

        <!-- Total Enrolled -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Enrolled</span>
                <span class="w-8 h-8 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white mt-3">{{ $metrics['total_enrolled'] }}</p>
        </div>

        <!-- Live Sessions -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Live Active</span>
                <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                </span>
            </div>
            <p class="text-2xl font-bold text-amber-400 mt-3">{{ $metrics['live_sessions'] }}</p>
        </div>

        <!-- Completed Results -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Results</span>
                <span class="w-8 h-8 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0H9.496m5.004 0a3 3 0 002.996-3V7.5a3 3 0 00-3-3h-5a3 3 0 00-3 3v4.875a3 3 0 002.996 3z" />
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white mt-3">{{ $metrics['total_results'] }}</p>
        </div>
    </div>

    <!-- Active Live Exams Banner -->
    @if($liveExams->count() > 0)
        <div class="bg-slate-900/80 border border-emerald-500/30 rounded-2xl p-5 shadow-lg shadow-emerald-500/5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <h2 class="text-base font-semibold text-white">Currently Active Online Examinations</h2>
                </div>
                <span class="text-xs text-emerald-400 font-medium">Auto-refreshes via polling</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($liveExams as $lExam)
                    <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-300 font-medium">{{ $lExam->category->name }}</span>
                                <span class="text-xs text-slate-400">{{ $lExam->start_time }} - {{ $lExam->end_time }}</span>
                            </div>
                            <h3 class="text-sm font-semibold text-white mt-2">{{ $lExam->name }}</h3>
                            <p class="text-xs text-slate-400 mt-1">Sessions Started: <span class="text-slate-200 font-medium">{{ $lExam->sessions_count }}</span></p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-end">
                            <a href="{{ route('admin.online-exams.live', $lExam) }}" 
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300">
                                Open Live Monitor
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Examinations Table -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-800/80 flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-white">Recent Online Examinations</h2>
                <p class="text-xs text-slate-400 mt-0.5">Overview of configured examinations and readiness</p>
            </div>
            <a href="{{ route('admin.online-exams.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                View All Exams &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/60 text-xs uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Exam Name & Code</th>
                        <th class="px-5 py-3.5 font-semibold">Category</th>
                        <th class="px-5 py-3.5 font-semibold">Schedule</th>
                        <th class="px-5 py-3.5 font-semibold">Questions</th>
                        <th class="px-5 py-3.5 font-semibold">Enrolled / Max</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($recentExams as $exam)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.online-exams.show', $exam) }}" class="font-medium text-white hover:text-indigo-300">
                                    {{ $exam->name }}
                                </a>
                                <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $exam->code }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 text-slate-200 border border-slate-700/60">
                                    {{ $exam->category->name }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-300">
                                <div class="font-medium text-slate-200">{{ $exam->exam_date->format('d M Y') }}</div>
                                <div class="text-slate-500">{{ $exam->start_time }} - {{ $exam->end_time }} ({{ $exam->duration_minutes }}m)</div>
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <span class="font-semibold text-white">{{ $exam->questions_count }}</span>
                                <span class="text-slate-500">questions</span>
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <span class="font-bold {{ $exam->enrolled_count >= 199 ? 'text-amber-400' : 'text-emerald-400' }}">
                                    {{ $exam->enrolled_count }}
                                </span>
                                <span class="text-slate-500">/ 199 max</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $exam->status->badgeClass() }}">
                                    {{ $exam->status->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.online-exams.show', $exam) }}" 
                                       class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700">
                                        Manage
                                    </a>
                                    <a href="{{ route('admin.online-exams.live', $exam) }}" 
                                       class="px-2.5 py-1 text-xs font-medium rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30">
                                        Monitor
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-500">
                                No online examinations created yet. Click "Create Online Exam" to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
