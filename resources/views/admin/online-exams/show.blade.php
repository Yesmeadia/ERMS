@extends('layouts.app')

@section('page_title', $exam->name)

@section('content')
<div class="space-y-6 pb-16">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 pb-1">
        <div class="min-w-0 flex-1 space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.online-exams.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium inline-flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Examinations
                </a>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">{{ $exam->name }}</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $exam->status->badgeClass() }}">
                    {{ $exam->status->label() }}
                </span>
            </div>
            <p class="text-xs text-slate-400 font-mono">Code: <span class="text-slate-300">{{ $exam->code }}</span> &bull; Category: <span class="text-slate-300">{{ $exam->category->name }}</span></p>
        </div>

        <!-- Action Buttons Toolbar -->
        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap shrink-0 pt-1">
            <a href="{{ route('admin.online-exams.preview', $exam) }}" target="_blank"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800/90 hover:bg-slate-700/90 text-slate-200 border border-slate-700 shadow-sm transition-all hover:border-slate-600">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Preview Exam
            </a>

            <a href="{{ route('admin.online-exams.live', $exam) }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-sm transition-all hover:border-emerald-500/50">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                Live Monitor
            </a>

            <a href="{{ route('admin.online-exams.edit', $exam) }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800/90 hover:bg-slate-700/90 text-slate-200 border border-slate-700 shadow-sm transition-all hover:border-slate-600">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Settings
            </a>

            @if($exam->status === \App\Enums\ExamStatus::DRAFT)
                @if($canPublish)
                    <form method="POST" action="{{ route('admin.online-exams.publish', $exam) }}" class="inline-flex"
                          onsubmit="return confirm('Are you sure you want to publish this examination? Questions and student enrollments will be locked.')">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-all">
                            <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Publish Examination
                        </button>
                    </form>
                @endif
            @elseif($exam->status === \App\Enums\ExamStatus::PUBLISHED)
                <form method="POST" action="{{ route('admin.online-exams.unpublish', $exam) }}" class="inline-flex"
                      onsubmit="return confirm('Return this examination to Draft status?')">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 shadow-sm transition-all hover:border-amber-500/50">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        Unpublish to Draft
                    </button>
                </form>
            @endif

            @can('delete', $exam)
                <form method="POST" action="{{ route('admin.online-exams.destroy', $exam) }}" class="inline-flex"
                      onsubmit="return confirm('PERMANENT ACTION: Are you sure you want to delete this examination \'{{ addslashes($exam->name) }}\'?\n\nAll student attempts, marks/results, answer evaluations, proctoring events, and video recordings will be permanently deleted. This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 shadow-sm transition-all hover:border-rose-500/50">
                        <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Delete Exam
                    </button>
                </form>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Critical Publishing Status Card -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Publishing Status Check</span>
                
                @if($enrolledCount >= 200)
                    <div class="flex items-center gap-2 text-rose-400 font-medium text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        This examination cannot be published because the maximum allowed number of eligible students is 199.
                    </div>
                @elseif($canPublish)
                    <div class="flex items-center gap-2 text-emerald-400 font-medium text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        Eligible Students: {{ $enrolledCount }} | Status: Eligible for Publishing
                    </div>
                @else
                    <div class="flex items-center gap-2 text-amber-400 font-medium text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        {{ $publishWarning }}
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">Enrolled: <strong class="text-white">{{ $enrolledCount }}</strong> / 199 max</span>
                <span class="text-slate-600">|</span>
                <span class="text-xs text-slate-400">Questions: <strong class="text-white">{{ $questionsCount }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Hub -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Assigned Questions Card -->
        <a href="{{ route('admin.online-exams.questions.index', $exam) }}" 
           class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-indigo-500/50 hover:bg-slate-900 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-indigo-400 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
            <h3 class="text-sm font-semibold text-white">Exam Questions</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $questionsCount }} questions assigned ({{ $exam->total_marks }} marks)</p>
        </a>

        <!-- Enrolled Students Card -->
        <a href="{{ route('admin.online-exams.students.index', $exam) }}" 
           class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-violet-500/50 hover:bg-slate-900 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-violet-400 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
            <h3 class="text-sm font-semibold text-white">Eligible Students</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $enrolledCount }} / 199 enrolled students</p>
        </a>

        <!-- Live Monitoring Card -->
        <a href="{{ route('admin.online-exams.live', $exam) }}" 
           class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-emerald-500/50 hover:bg-slate-900 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-emerald-400 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
            <h3 class="text-sm font-semibold text-white">Live Monitoring</h3>
            <p class="text-xs text-slate-400 mt-1">Real-time candidate sessions & signals</p>
        </a>

        <!-- Results & Reports Card -->
        <a href="{{ route('admin.online-exams.results', $exam) }}" 
           class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-teal-500/50 hover:bg-slate-900 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-teal-400 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
            <h3 class="text-sm font-semibold text-white">Results & Reports</h3>
            <p class="text-xs text-slate-400 mt-1">Export Excel, CSV & PDF reports</p>
        </a>
    </div>

    <!-- Parameter Details Card -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm">
        <h2 class="text-base font-semibold text-white mb-4">Configured Parameters</h2>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6 text-sm">
            <div>
                <span class="text-xs text-slate-500 block uppercase">Schedule</span>
                <span class="text-slate-200 font-medium">{{ $exam->exam_date->format('d M Y') }}</span>
                <span class="text-slate-400 text-xs block">{{ $exam->start_time }} – {{ $exam->end_time }}</span>
            </div>

            <div>
                <span class="text-xs text-slate-500 block uppercase">Student Duration</span>
                <span class="text-slate-200 font-medium">{{ $exam->duration_minutes }} minutes</span>
                <span class="text-slate-400 text-xs block">{{ $exam->default_question_time_limit }}s per question</span>
            </div>

            <div>
                <span class="text-xs text-slate-500 block uppercase">Marks & Passing</span>
                <span class="text-slate-200 font-medium">{{ $exam->total_marks }} Total</span>
                <span class="text-slate-400 text-xs block">{{ $exam->pass_marks }} Pass Marks</span>
            </div>

            <div>
                <span class="text-xs text-slate-500 block uppercase">Camera Check</span>
                <span class="font-medium {{ $exam->enable_camera ? 'text-emerald-400' : 'text-slate-400' }}">
                    {{ $exam->enable_camera ? 'Enabled (Required)' : 'Disabled' }}
                </span>
            </div>

            <div>
                <span class="text-xs text-slate-500 block uppercase">Fullscreen Mode</span>
                <span class="font-medium {{ $exam->enable_fullscreen ? 'text-emerald-400' : 'text-slate-400' }}">
                    {{ $exam->enable_fullscreen ? 'Enabled' : 'Disabled' }}
                </span>
                <span class="text-slate-400 text-xs block">Max {{ $exam->max_fullscreen_violations }} violations</span>
            </div>

            <div>
                <span class="text-xs text-slate-500 block uppercase">Previous Question</span>
                <span class="font-medium {{ $exam->allow_previous_question ? 'text-indigo-400' : 'text-slate-400' }}">
                    {{ $exam->allow_previous_question ? 'Allowed' : 'Disabled' }}
                </span>
            </div>

            <div>
                <span class="text-xs text-slate-500 block uppercase">Randomization</span>
                <span class="text-slate-200 font-medium">
                    {{ $exam->randomize_questions ? 'Questions: Yes' : 'Questions: No' }},
                    {{ $exam->randomize_options ? 'Options: Yes' : 'Options: No' }}
                </span>
            </div>

            <div>
                <span class="text-xs text-slate-500 block uppercase">Speed Bonus</span>
                <span class="font-medium {{ $exam->enable_speed_bonus ? 'text-violet-400' : 'text-slate-400' }}">
                    {{ $exam->enable_speed_bonus ? 'Enabled (' . ucfirst($exam->speed_bonus_formula) . ')' : 'Disabled' }}
                </span>
            </div>
        </div>
    </div>

    @can('delete', $exam)
    <!-- Danger Zone Card -->
    <div class="bg-rose-950/20 border border-rose-900/40 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-sm font-semibold text-rose-400 flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Danger Zone: Delete Examination
                </h3>
                <p class="text-xs text-slate-400">
                    Permanently delete this examination. All student attempt sessions, marks/results, answer evaluations, proctoring events, and video recordings will be permanently destroyed.
                </p>
            </div>
            <form method="POST" action="{{ route('admin.online-exams.destroy', $exam) }}" class="shrink-0"
                  onsubmit="return confirm('PERMANENT ACTION: Are you absolutely sure you want to delete this examination \'{{ addslashes($exam->name) }}\'?\n\nAll student attempts, marks/results, events, and recordings will be permanently removed. This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Delete Examination
                </button>
            </form>
        </div>
    </div>
    @endcan
</div>
@endsection
