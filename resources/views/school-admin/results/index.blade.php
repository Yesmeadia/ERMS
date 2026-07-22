@extends('layouts.app')

@section('page_title', 'Student Exam Results')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-white">Student Exam Results</h2>
        <p class="text-sm text-slate-400 mt-1">
            View academic examination results for students registered by {{ auth()->user()->school->name ?? 'your school' }}.
        </p>
    </div>
</div>

{{-- Performance Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total Results Declared --}}
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/60 relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Results Declared</p>
                <h3 class="text-2xl font-extrabold text-white mt-1">{{ number_format($resultsDeclared) }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">out of {{ number_format($totalRegistered) }} registered students</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Passed Students --}}
    <div class="p-5 rounded-2xl bg-emerald-950/20 border border-emerald-800/40 relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Passed Students</p>
                <h3 class="text-2xl font-extrabold text-emerald-200 mt-1">{{ number_format($passedCount) }}</h3>
                <p class="text-xs text-emerald-400/70 mt-0.5">Passed examination</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Failed Students --}}
    <div class="p-5 rounded-2xl bg-rose-950/20 border border-rose-800/40 relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-rose-400 uppercase tracking-wider">Failed Students</p>
                <h3 class="text-2xl font-extrabold text-rose-200 mt-1">{{ number_format($failedCount) }}</h3>
                <p class="text-xs text-rose-400/70 mt-0.5 font-medium">Needs improvement</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- School Pass Rate % --}}
    <div class="p-5 rounded-2xl bg-indigo-950/30 border border-indigo-800/40 relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-indigo-300 uppercase tracking-wider">School Pass Rate</p>
                <h3 class="text-2xl font-extrabold text-indigo-100 mt-1">{{ $passPercentage }}%</h3>
                <p class="text-xs text-indigo-400/70 mt-0.5">Overall performance</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Search & Filter Section --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-6">
    <form method="GET" action="{{ route('school.results.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Exam Session</label>
            <select name="examination_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Exams</option>
                @foreach($examinations as $exam)
                    <option value="{{ $exam->id }}" @selected(request('examination_id') == $exam->id)>{{ $exam->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Class</label>
            <select name="class_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Classes</option>
                @foreach($classes as $cls)
                    <option value="{{ $cls->id }}" @selected(request('class_id') == $cls->id)>{{ $cls->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Result Status</label>
            <select name="result_status" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Statuses</option>
                <option value="Pass" @selected(request('result_status') == 'Pass')>Pass</option>
                <option value="Fail" @selected(request('result_status') == 'Fail')>Fail</option>
                <option value="Absent" @selected(request('result_status') == 'Absent')>Absent</option>
                <option value="Withheld" @selected(request('result_status') == 'Withheld')>Withheld</option>
                <option value="pending" @selected(request('result_status') == 'pending')>Result Pending</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Search Student</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Reg or Hall Ticket No..." class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
        </div>

        <div class="lg:col-span-4 flex justify-end gap-3 pt-2">
            <a href="{{ route('school.results.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-xl transition-colors">
                Clear Filters
            </a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors cursor-pointer flex items-center gap-2 shadow-lg shadow-indigo-600/10">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                Apply Filters
            </button>
        </div>
    </form>
</div>

{{-- Results Table --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden shadow-xl">
    <div class="px-6 py-4 border-b border-slate-800/60 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-200">Registered Students Results List</h3>
        <span class="text-xs text-slate-400">Total: {{ $students->total() }} Candidates</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-300">
            <thead class="bg-slate-950/60 text-xs uppercase text-slate-400 font-semibold border-b border-slate-800/60">
                <tr>
                    <th class="px-6 py-4">Student Details</th>
                    <th class="px-6 py-4">Class & Category</th>
                    <th class="px-6 py-4">Examination</th>
                    <th class="px-6 py-4">Marks Obtained</th>
                    <th class="px-6 py-4">Percentage</th>
                    <th class="px-6 py-4">Grade</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($students as $student)
                    @php $res = $student->result; @endphp
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($student->photograph)
                                    <img src="{{ asset('storage/' . $student->photograph) }}" class="w-10 h-10 rounded-xl object-cover border border-slate-700/60 shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ mb_substr($student->name, 0, 2) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-semibold text-slate-100">{{ $student->name }}</div>
                                    <div class="text-xs text-slate-500">Reg: {{ $student->registration_number ?? 'N/A' }}</div>
                                    <div class="text-[11px] text-indigo-400/80">HT: {{ $student->hall_ticket_number ?? 'Not Issued' }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-200">{{ $student->class->name ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500">{{ $student->category->name ?? 'General' }}</div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-xs font-medium text-slate-300">{{ $student->examination->name ?? 'N/A' }}</div>
                        </td>

                        <td class="px-6 py-4">
                            @if($res)
                                <span class="font-bold text-white">{{ $res->marks_obtained }}</span>
                                <span class="text-xs text-slate-500"> / {{ $res->max_marks }}</span>
                            @else
                                <span class="text-xs text-slate-500 italic">Not Entered</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            @if($res)
                                <span class="font-semibold text-slate-200">{{ number_format($res->percentage, 2) }}%</span>
                            @else
                                <span class="text-xs text-slate-500">—</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            @if($res && $res->grade)
                                <span class="px-2.5 py-1 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 font-bold text-xs">
                                    {{ $res->grade }}
                                </span>
                            @else
                                <span class="text-xs text-slate-500">—</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            @if($res)
                                @if($res->status === 'Pass')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Pass
                                    </span>
                                @elseif($res->status === 'Fail')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        Fail
                                    </span>
                                @elseif($res->status === 'Absent')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-slate-400 text-xs font-semibold">
                                        Absent
                                    </span>
                                @elseif($res->status === 'Withheld')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold">
                                        Withheld
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-slate-400 text-xs font-medium">
                                    Pending
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-right">
                            @if($res)
                                <a href="{{ route('school.results.marksheet', $student->id) }}" target="_blank" class="px-3 py-1.5 bg-indigo-600/20 hover:bg-indigo-600 border border-indigo-500/30 text-indigo-300 hover:text-white text-xs font-semibold rounded-xl transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.573 16.49 16.638 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Marksheet
                                </a>
                            @else
                                <span class="text-xs text-slate-500 italic">No Marksheet</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <p class="text-base font-semibold text-slate-400">No student exam results found</p>
                            <p class="text-xs text-slate-500 mt-1">Try adjusting your filters or search query.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($students->hasPages())
        <div class="px-6 py-4 border-t border-slate-800/60 bg-slate-950/40">
            {{ $students->links() }}
        </div>
    @endif
</div>
@endsection
