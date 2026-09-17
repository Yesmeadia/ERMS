@extends('layouts.app')

@section('page_title', 'Results & Item Analysis: ' . $exam->name)

@section('content')
<div class="space-y-6 pb-16">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.online-exams.show', $exam) }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                    &larr; Back to Examination Details
                </a>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Results & Item Analysis</h1>
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border {{ $exam->status->badgeClass() }}">
                    {{ $exam->status->label() }}
                </span>
            </div>
            <p class="text-xs text-slate-400 font-mono mt-1">
                {{ $exam->name }} ({{ $exam->code }}) &bull; Category: {{ $exam->category->name }}
            </p>
        </div>

        <!-- Export Buttons -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <form method="POST" action="{{ route('admin.online-exams.results.recalculate', $exam) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Recalculate Ranks
                </button>
            </form>

            <a href="{{ route('admin.online-exams.results.csv', $exam) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.5V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export CSV
            </a>

            <a href="{{ route('admin.online-exams.results.pdf', $exam) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Download PDF
            </a>
        </div>
    </div>

    <!-- Overview Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
            <span class="text-[11px] uppercase tracking-wider text-slate-400 block mb-1">Total Submissions</span>
            <span class="text-2xl font-bold text-white font-mono">{{ $totalCandidates }}</span>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
            <span class="text-[11px] uppercase tracking-wider text-emerald-400 block mb-1">Pass Rate</span>
            <span class="text-2xl font-bold text-emerald-400 font-mono">
                {{ $totalCandidates > 0 ? round(($passedCount / $totalCandidates) * 100, 1) : 0 }}%
                <span class="text-xs text-slate-400 font-normal">({{ $passedCount }} passed)</span>
            </span>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
            <span class="text-[11px] uppercase tracking-wider text-indigo-400 block mb-1">Average Score</span>
            <span class="text-2xl font-bold text-indigo-400 font-mono">{{ number_format($avgScore, 2) }}</span>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
            <span class="text-[11px] uppercase tracking-wider text-amber-400 block mb-1">Highest Score</span>
            <span class="text-2xl font-bold text-amber-400 font-mono">{{ number_format($highestScore, 2) }} <span class="text-xs text-slate-400 font-normal">/ {{ $exam->total_marks }}</span></span>
        </div>
    </div>

    <!-- Candidate Results Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl space-y-4 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="text-base font-bold text-white">Candidate Merit Standings</h2>
            <!-- Filter Bar -->
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or reg no..."
                       class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                <select name="status" onchange="this.form.submit()"
                        class="bg-slate-950 border border-slate-800 rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                    <option value="">All Candidates</option>
                    <option value="pass" {{ request('status') === 'pass' ? 'selected' : '' }}>Passed</option>
                    <option value="fail" {{ request('status') === 'fail' ? 'selected' : '' }}>Failed</option>
                </select>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('admin.online-exams.results', $exam) }}" class="text-xs text-slate-400 hover:text-white px-2">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto -mx-6">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-y border-slate-800 bg-slate-950/60 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-6 text-center">Rank</th>
                        <th class="py-3 px-4">Candidate</th>
                        <th class="py-3 px-4">School</th>
                        <th class="py-3 px-4 text-center">Attempted</th>
                        <th class="py-3 px-4 text-center">Accuracy</th>
                        <th class="py-3 px-4 text-right">Exam Mark</th>
                        @if($exam->enable_speed_bonus)
                        <th class="py-3 px-4 text-right">Time Bonus</th>
                        @endif
                        <th class="py-3 px-4 text-right">Total Mark</th>
                        <th class="py-3 px-4 text-center">%</th>
                        <th class="py-3 px-4 text-center">Grade</th>
                        <th class="py-3 px-6 text-center">Result</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($results as $res)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-6 text-center font-mono font-bold text-indigo-400">
                            {{ $res->rank ? '#' . $res->rank : '-' }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-white">{{ $res->student->name }}</div>
                            <div class="font-mono text-[11px] text-slate-400">{{ $res->student->registration_number }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-400">{{ $res->student->school->name ?? 'N/A' }}</td>
                        <td class="py-3 px-4 text-center font-mono">
                            <div>{{ $res->attempted_questions_count }} / {{ $res->total_questions }}</div>
                            <span class="text-[10px] text-slate-500 font-normal block" title="Total Answered Time">{{ $res->time_taken_formatted }}</span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono">
                            <span class="text-emerald-400">{{ $res->correct_answers_count }}C</span>
                            <span class="text-slate-500">/</span>
                            <span class="text-rose-400">{{ $res->wrong_answers_count }}W</span>
                        </td>
                        {{-- Exam (Objective) Marks --}}
                        <td class="py-3 px-4 text-right font-mono text-slate-300">
                            {{ number_format((float)$res->objective_marks, 2) }}
                            @if((float)$res->negative_marks > 0)
                                <span class="text-[10px] text-rose-400 font-normal block">-{{ number_format((float)$res->negative_marks, 2) }} neg</span>
                            @endif
                        </td>
                        @if($exam->enable_speed_bonus)
                        {{-- Speed Bonus --}}
                        <td class="py-3 px-4 text-right font-mono">
                            @if((float)$res->speed_bonus_marks > 0)
                                <span class="text-amber-400 font-semibold">+{{ number_format((float)$res->speed_bonus_marks, 2) }}</span>
                            @else
                                <span class="text-slate-600">—</span>
                            @endif
                        </td>
                        @endif
                        {{-- Total Score --}}
                        <td class="py-3 px-4 text-right font-mono font-bold text-white">
                            {{ number_format((float)$res->final_score, 2) }}
                        </td>
                        <td class="py-3 px-4 text-center font-mono">{{ number_format((float)$res->percentage, 1) }}%</td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-indigo-300">{{ $res->grade ?: '-' }}</td>
                        <td class="py-3 px-6 text-center">
                            @if($res->is_passed)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                    PASS
                                </span>
                            @else
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                    FAIL
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $exam->enable_speed_bonus ? 10 : 9 }}" class="py-8 text-center text-slate-500">
                            No finalized examination results found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($results->hasPages())
        <div class="pt-4 border-t border-slate-800">
            {{ $results->links() }}
        </div>
        @endif
    </div>

    <!-- Question Item Analysis Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl p-6 space-y-4">
        <h2 class="text-base font-bold text-white">Question Item Analysis & Performance</h2>
        <div class="overflow-x-auto -mx-6">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-y border-slate-800 bg-slate-950/60 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-6 text-center">#</th>
                        <th class="py-3 px-4">Question Text</th>
                        <th class="py-3 px-4 text-center">Type</th>
                        <th class="py-3 px-4 text-center">Marks</th>
                        <th class="py-3 px-4 text-center">Attempts</th>
                        <th class="py-3 px-4 text-center">Correct</th>
                        <th class="py-3 px-4 text-center">Wrong</th>
                        <th class="py-3 px-6 text-center">Accuracy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($itemAnalysis as $item)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-6 text-center font-mono text-slate-400">{{ $item['sort_order'] }}</td>
                        <td class="py-3 px-4 max-w-md truncate text-white">
                            {{ strip_tags($item['question_text']) }}
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-[11px] text-slate-400">{{ $item['type'] }}</td>
                        <td class="py-3 px-4 text-center font-mono">{{ $item['marks'] }}</td>
                        <td class="py-3 px-4 text-center font-mono">{{ $item['attempts'] }}</td>
                        <td class="py-3 px-4 text-center font-mono text-emerald-400 font-bold">{{ $item['correct'] }}</td>
                        <td class="py-3 px-4 text-center font-mono text-rose-400 font-bold">{{ $item['wrong'] }}</td>
                        <td class="py-3 px-6 text-center font-mono">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $item['accuracy'] >= 60 ? 'bg-emerald-500/10 text-emerald-400' : ($item['accuracy'] >= 40 ? 'bg-amber-500/10 text-amber-400' : 'bg-rose-500/10 text-rose-400') }}">
                                {{ $item['accuracy'] }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">No questions assigned to analyze.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
