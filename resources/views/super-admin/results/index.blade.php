@extends('layouts.app')

@section('page_title', 'Student Exam Results Management')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-slate-400 mt-1">
                Manage student exam scores, enter results individually, export official result PDF tabulation reports, or
                import scores in bulk.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.result-timer.index') }}"
                class="px-4 py-2.5 bg-purple-600/20 hover:bg-purple-600/30 border border-purple-500/30 text-purple-300 text-sm font-semibold rounded-xl transition-all duration-200 flex items-center gap-2 cursor-pointer active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4 text-purple-400">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Release Timer
            </a>
            <a href="{{ route('admin.results.import-form') }}"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-md shadow-indigo-600/20 flex items-center gap-2 cursor-pointer active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                Bulk Import Results
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-6">
        <form method="GET" action="{{ route('admin.results.index') }}"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Exam
                    Session</label>
                <select name="examination_id"
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                    <option value="">All Exams</option>
                    @foreach($examinations as $exam)
                        <option value="{{ $exam->id }}" @selected(request('examination_id') == $exam->id)>{{ $exam->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Zone</label>
                <select name="zone"
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                    <option value="">All Zones</option>
                    @foreach($zones as $z)
                        <option value="{{ $z }}" @selected(request('zone') == $z)>{{ $z }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">School</label>
                <select name="school_id"
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                    <option value="">All Schools</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" @selected(request('school_id') == $school->id)>{{ $school->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Category</label>
                <select name="category_id"
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Class</label>
                <select name="class_id"
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                    <option value="">All Classes</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" @selected(request('class_id') == $cls->id)>{{ $cls->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Gender</label>
                <select name="gender"
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                    <option value="">All Genders</option>
                    <option value="Male" @selected(request('gender') == 'Male')>Male</option>
                    <option value="Female" @selected(request('gender') == 'Female')>Female</option>
                    <option value="Other" @selected(request('gender') == 'Other')>Other</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Result Status</label>
                <select name="result_status"
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                    <option value="">All Candidates</option>
                    <option value="entered" @selected(request('result_status') == 'entered')>Results Entered</option>
                    <option value="pending" @selected(request('result_status') == 'pending')>Results Pending</option>
                    <option value="Absent" @selected(request('result_status') == 'Absent')>Absent</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Search
                    Student</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Reg or HT number..."
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
            </div>

            <div class="lg:col-span-4 flex flex-wrap items-center justify-between gap-3 pt-2">
                <div>
                    <a href="{{ route('admin.results.pdf', request()->all()) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-950/40 hover:bg-rose-900/60 border border-rose-500/40 text-rose-300 hover:text-white text-xs font-semibold rounded-xl transition-colors shadow-lg shadow-rose-950/20 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Generate Filtered Result PDF
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.results.index') }}"
                        class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-xl transition-colors">Clear
                        Filters</a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors cursor-pointer flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Apply Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if(isset($recentBatches) && $recentBatches->isNotEmpty())
        {{-- Recent Generated PDF Batches --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-6 shadow-xl" x-data="{ open: true }">
            <div class="flex items-center justify-between cursor-pointer select-none" @click="open = !open">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-200">Recent Result Statement Exports</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Quick access to previously generated background PDF registers.</p>
                    </div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </button>
            </div>

            <div class="mt-4 space-y-2.5" x-show="open" x-collapse>
                @foreach($recentBatches as $batch)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-800/40 border border-slate-700/50">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-200">{{ $batch->title }}</span>
                                <span class="text-[11px] text-indigo-400 font-medium">· Batch #{{ $batch->id }}</span>
                                <span class="text-[11px] text-slate-400">({{ $batch->total_students }} Candidates in {{ $batch->total_parts }} Part(s))</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Session: {{ $batch->examination->name ?? 'All Sessions' }} · Created: {{ $batch->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.results.batches.show', $batch) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-lg border border-slate-700 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                View / Download ({{ $batch->completed_parts }}/{{ $batch->total_parts }})
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Results List Table --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden shadow-xl">
        <div class="px-6 py-4 border-b border-slate-800/60 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-200">Candidate Records</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ $students->total() }} candidate(s) found</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-800/60">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Candidate Info
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">School</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Class & Category
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">
                            Score</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">
                            Percentage</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">
                            Grade</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">
                            Remarks</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-800/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-lg overflow-hidden border border-slate-800 bg-slate-900 shrink-0">
                                        <img src="{{ $student->photo_url }}" alt="{{ $student->name }}"
                                            class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-200">{{ $student->name }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">HT: {{ $student->hall_ticket_number ?? 'N/A' }}
                                            | Reg: {{ $student->registration_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-300">{{ $student->school?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-slate-300">
                                <p class="font-medium text-xs">{{ $student->class?->name ?? 'N/A' }}</p>
                                <p class="text-[10px] text-slate-500 mt-0.5">{{ $student->category?->name ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center text-slate-200 font-medium font-mono">
                                @if($student->result)
                                    {{ $student->result->marks_obtained }} / {{ $student->result->max_marks }}
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-slate-300 font-mono">
                                @if($student->result)
                                    {{ $student->result->percentage }}%
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-slate-200 font-bold">
                                @if($student->result)
                                    <span
                                        class="px-2 py-1 rounded bg-slate-850 border border-slate-800 text-xs">{{ $student->result->grade }}</span>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($student->result && $student->result->remarks)
                                    <span class="max-w-[200px] truncate inline-block text-xs text-slate-300 font-medium" title="{{ $student->result->remarks }}">
                                        {{ $student->result->remarks }}
                                    </span>
                                @elseif($student->result)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border bg-indigo-500/10 text-indigo-400 border-indigo-500/20">
                                        Entered
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border bg-slate-800/40 text-slate-500 border-slate-700/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @php
                                    $attendancesList = $student->attendances ?? collect();
                                    $isPresent = $attendancesList->where('status', 'Present')->count() > 0;
                                    $isMarkedAbsent = $attendancesList->where('status', 'Absent')->count() > 0;
                                @endphp
                                <div class="flex items-center justify-end gap-2">
                                    @if($student->result)
                                        {{-- Result exists: allow edit/delete regardless --}}
                                        <a href="{{ route('admin.results.edit', $student->result->id) }}"
                                            class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-400 hover:text-indigo-300 transition-colors"
                                            title="Edit Result">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.83 20.062a4.5 4.5 0 0 1-1.89 1.13L2.685 21.8a.75.75 0 0 1-.944-.94l.813-2.831a4.5 4.5 0 0 1 1.13-1.89L16.863 4.487zm0 0L19.5 7.125" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.results.destroy', $student->result->id) }}"
                                            onsubmit="return confirm('Are you sure you want to delete the result of this student? This action cannot be undone.')"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 rounded-lg bg-slate-800 hover:bg-rose-950/40 text-rose-400 hover:text-rose-300 transition-colors cursor-pointer"
                                                title="Delete Result">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m14.74 9-.346 9m-4.788 0L9 9m9 9a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3V7h10v11ZM4 7h16m-3 0V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v3" />
                                                </svg>
                                            </button>
                                        </form>
                                    @elseif($isPresent)
                                        {{-- Student is Present — allow mark entry --}}
                                        <a href="{{ route('admin.results.create', $student->id) }}"
                                            id="enter-marks-{{ $student->id }}"
                                            class="px-3 py-1.5 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/10 transition-colors flex items-center gap-1 cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            Enter Marks
                                        </a>
                                    @else
                                        {{-- Student is Absent — block mark entry --}}
                                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-950/30 border border-orange-800/30 cursor-not-allowed"
                                            title="Student was not present during the examination. Marks cannot be entered.">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" class="w-3.5 h-3.5 text-orange-400">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            <span class="text-xs font-bold text-orange-400">Absent</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
                                    stroke="currentColor" class="w-12 h-12 mx-auto text-slate-700 mb-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 0A48.536 48.536 0 0112 3m0 0c2.917 0 5.747.294 8.5.862" />
                                </svg>
                                <p class="text-slate-500 font-medium">No verified/hall ticket issued candidate records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/60 bg-slate-900/10">
                {{ $students->links() }}
            </div>
        @endif
    </div>
@endsection