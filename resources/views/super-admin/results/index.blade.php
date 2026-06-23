@extends('layouts.app')

@section('page_title', 'Student Exam Results Management')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <p class="text-sm text-slate-400">
            Manage student exam scores, enter results individually, or import them in bulk via CSV.
        </p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.results.import-form') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-md shadow-indigo-600/10 flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
            Bulk Import Results
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-6">
    <form method="GET" action="{{ route('admin.results.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">Exam Session</label>
            <select name="examination_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Exams</option>
                @foreach($examinations as $exam)
                    <option value="{{ $exam->id }}" @selected(request('examination_id') == $exam->id)>{{ $exam->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">School</label>
            <select name="school_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" @selected(request('school_id') == $school->id)>{{ $school->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">Class</label>
            <select name="class_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Classes</option>
                @foreach($classes as $cls)
                    <option value="{{ $cls->id }}" @selected(request('class_id') == $cls->id)>{{ $cls->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">Result Status</label>
            <select name="result_status" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Candidates</option>
                <option value="entered" @selected(request('result_status') == 'entered')>Results Entered</option>
                <option value="pending" @selected(request('result_status') == 'pending')>Results Pending</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">Search Student</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Reg or HT number..." class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
        </div>

        <div class="lg:col-span-5 flex justify-end gap-3 mt-2">
            <a href="{{ route('admin.results.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-xl transition-colors">Clear Filters</a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors cursor-pointer">Apply Filter</button>
        </div>
    </form>
</div>

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
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Candidate Info</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">School</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Class & Category</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Score</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Percentage</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Grade</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Status</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40">
                @forelse($students as $student)
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg overflow-hidden border border-slate-800 bg-slate-900 shrink-0">
                                    <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-200">{{ $student->name }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">HT: {{ $student->hall_ticket_number ?? 'N/A' }} | Reg: {{ $student->registration_number }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">{{ $student->school->name }}</td>
                        <td class="px-6 py-4 text-slate-300">
                            <p class="font-medium text-xs">{{ $student->class->name }}</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">{{ $student->category->name }}</p>
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
                                <span class="px-2 py-1 rounded bg-slate-850 border border-slate-800 text-xs">{{ $student->result->grade }}</span>
                            @else
                                <span class="text-slate-600">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($student->result)
                                @php
                                    $resStatus = $student->result->status;
                                    $statusClass = 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
                                    $dotClass = 'bg-indigo-400';
                                    if ($resStatus === 'Pass') {
                                        $statusClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                                        $dotClass = 'bg-emerald-400';
                                    } elseif ($resStatus === 'Fail') {
                                        $statusClass = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
                                        $dotClass = 'bg-rose-400';
                                    } elseif ($resStatus === 'Absent' || $resStatus === 'Withheld') {
                                        $statusClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                                        $dotClass = 'bg-amber-400';
                                    }
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $statusClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ $resStatus }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border bg-slate-800/40 text-slate-500 border-slate-700/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @php
                                $isPresent = $student->attendances->where('status', 'Present')->count() > 0;
                                $hallTicketIssued = in_array($student->status, ['Approved', 'Hall Ticket Issued']);
                                // Students whose exam day has not yet been scanned are treated as unresolved
                                // Show Absent guard ONLY when hall ticket has been issued (exam-eligible) and no Present scan recorded
                                $isAbsent = $hallTicketIssued && !$isPresent && $student->attendances->count() === 0;
                                // If attendances exist but none is Present, also treat as absent
                                if ($hallTicketIssued && !$isPresent && $student->attendances->count() > 0) {
                                    $isAbsent = true;
                                }
                            @endphp
                            <div class="flex items-center justify-end gap-2">
                                @if($student->result)
                                    {{-- Result exists: allow edit/delete regardless --}}
                                    <a href="{{ route('admin.results.edit', $student->result->id) }}"
                                        class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-400 hover:text-indigo-300 transition-colors"
                                        title="Edit Result">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.83 20.062a4.5 4.5 0 0 1-1.89 1.13L2.685 21.8a.75.75 0 0 1-.944-.94l.813-2.831a4.5 4.5 0 0 1 1.13-1.89L16.863 4.487zm0 0L19.5 7.125" />
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
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9 9m9 9a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3V7h10v11ZM4 7h16m-3 0V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v3" />
                                            </svg>
                                        </button>
                                    </form>
                                @elseif($isAbsent)
                                    {{-- Student is Absent — block mark entry --}}
                                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-950/30 border border-orange-800/30 cursor-not-allowed"
                                         title="Student was not present during the examination. Marks cannot be entered.">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-orange-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        <span class="text-xs font-bold text-orange-400">Absent</span>
                                    </div>
                                @elseif($isPresent)
                                    {{-- Student is Present — allow mark entry --}}
                                    <a href="{{ route('admin.results.create', $student->id) }}"
                                       id="enter-marks-{{ $student->id }}"
                                       class="px-3 py-1.5 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/10 transition-colors flex items-center gap-1 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Enter Marks
                                    </a>
                                @else
                                    {{-- Hall ticket issued but attendance not yet recorded (exam day not reached) --}}
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800/40 border border-slate-700/20 text-slate-500 text-xs font-medium cursor-default"
                                          title="Attendance not yet recorded for this student.">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        Awaiting Attendance
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mx-auto text-slate-700 mb-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 0A48.536 48.536 0 0112 3m0 0c2.917 0 5.747.294 8.5.862" />
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
