@extends('layouts.app')

@section('page_title', auth()->user()->hasRole('super-admin') ? 'Attendance Management' : 'Attendance Report')

@section('content')
<div class="mb-6">
    <p class="text-sm text-slate-400">
        @if(auth()->user()->hasRole('super-admin'))
            Monitor candidate attendance logs and manually adjust attendance status.
        @else
            Track candidate attendance status and log files for active examination sessions.
        @endif
    </p>
</div>

{{-- Search & Filters --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-6">
    <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">Exam Session</label>
            <select name="examination_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                @foreach($examinations as $exam)
                    <option value="{{ $exam->id }}" @selected($examinationId == $exam->id)>{{ $exam->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">Attendance Date</label>
            <input type="date" name="date" value="{{ $date }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
        </div>

        @if(auth()->user()->hasRole('super-admin'))
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">School</label>
            <select name="school_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" @selected($schoolId == $school->id)>{{ $school->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">Class</label>
            <select name="class_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Classes</option>
                @foreach($classes as $cls)
                    <option value="{{ $cls->id }}" @selected($classId == $cls->id)>{{ $cls->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">Search Student</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Name, Reg or HT number..." class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
        </div>

        <div class="lg:col-span-5 flex justify-end gap-3 mt-2">
            <a href="{{ url()->current() }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-xl transition-colors">Clear Filters</a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors cursor-pointer">Apply Filter</button>
        </div>
    </form>
</div>

{{-- Attendance Report Summary --}}
<div class="mb-6">
    {{-- Summary Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        {{-- Total --}}
        <div class="bg-slate-900/60 border border-indigo-500/20 rounded-2xl p-5 flex items-center gap-4 shadow-lg shadow-indigo-500/5">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm-1.2 6.477a7.5 7.5 0 0 0-5.1 0A10.5 10.5 0 0 1 9 12.75a10.5 10.5 0 0 1 5.1 1.602Z" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-100">{{ $summary['total'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Total Candidates</p>
            </div>
        </div>
        {{-- Present --}}
        <div class="bg-slate-900/60 border border-emerald-500/20 rounded-2xl p-5 flex items-center gap-4 shadow-lg shadow-emerald-500/5">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-emerald-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-emerald-400">{{ $summary['present'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Present</p>
            </div>
        </div>
        {{-- Absent --}}
        <div class="bg-slate-900/60 border border-rose-500/20 rounded-2xl p-5 flex items-center gap-4 shadow-lg shadow-rose-500/5">
            <div class="w-12 h-12 rounded-xl bg-rose-500/20 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-rose-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-rose-400">{{ $summary['absent'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Absent / Not Marked</p>
            </div>
        </div>
        {{-- Percentage --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center gap-4">
            <div class="relative w-14 h-14 shrink-0">
                <svg class="w-14 h-14 -rotate-90" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#1e293b" stroke-width="3.5"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#6366f1" stroke-width="3.5"
                        stroke-dasharray="{{ $summary['percent'] }}, 100"
                        stroke-linecap="round"/>
                </svg>
                <span class="absolute inset-0 flex items-center justify-center text-[11px] font-bold text-indigo-300">{{ $summary['percent'] }}%</span>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-100">{{ $summary['percent'] }}<span class="text-sm font-medium text-slate-500">%</span></p>
                <p class="text-xs text-slate-500 mt-0.5">Attendance Rate</p>
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    @if($summary['total'] > 0)
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl px-5 py-4 mb-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-slate-400">Overall Attendance Progress</p>
            <span class="text-xs text-slate-500">{{ $summary['present'] }} of {{ $summary['total'] }} present</span>
        </div>
        <div class="w-full h-2.5 bg-slate-800 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500"
                 style="width: {{ $summary['percent'] }}%; background: linear-gradient(90deg, #6366f1, #22d3ee)"></div>
        </div>
    </div>
    @endif

    {{-- Class-wise Breakdown --}}
    @if($classBreakdown->count() > 0)
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-800/60">
            <h3 class="text-sm font-semibold text-slate-200">Class-wise Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-800/40">
                        <th class="px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Class</th>
                        <th class="px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Total</th>
                        <th class="px-5 py-3 text-xs font-semibold text-emerald-400 uppercase tracking-wider text-center">Present</th>
                        <th class="px-5 py-3 text-xs font-semibold text-rose-400 uppercase tracking-wider text-center">Absent</th>
                        <th class="px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Attendance %</th>
                        <th class="px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @foreach($classBreakdown as $className => $data)
                    @php $pct = $data['total'] > 0 ? round(($data['present'] / $data['total']) * 100) : 0; @endphp
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-3 font-medium text-slate-200">{{ $className }}</td>
                        <td class="px-5 py-3 text-center text-slate-300">{{ $data['total'] }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                {{ $data['present'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                {{ $data['absent'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center font-bold {{ $pct >= 75 ? 'text-emerald-400' : ($pct >= 50 ? 'text-amber-400' : 'text-rose-400') }}">
                            {{ $pct }}%
                        </td>
                        <td class="px-5 py-3 w-40">
                            <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $pct >= 75 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- Results Table --}}
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
                    @if(auth()->user()->hasRole('super-admin'))
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">School</th>
                    @endif
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Class & Category</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Marked Time</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Status</th>
                    @if(auth()->user()->hasRole('super-admin'))
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40">
                @forelse($students as $student)
                @php
                    $status = $student->attendance_status ?? 'Absent';
                @endphp
                <tr class="hover:bg-slate-800/20 transition-colors" id="row-{{ $student->id }}">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg overflow-hidden border border-slate-800 bg-slate-900 shrink-0">
                                <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="font-semibold text-slate-200">{{ $student->name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">HT: {{ $student->hall_ticket_number }} | Reg: {{ $student->registration_number }}</p>
                            </div>
                        </div>
                    </td>
                    @if(auth()->user()->hasRole('super-admin'))
                        <td class="px-6 py-4 text-slate-300">{{ $student->school->name ?? 'N/A' }}</td>
                    @endif
                    <td class="px-6 py-4 text-slate-300">
                        <p class="font-medium text-xs">{{ $student->class->name ?? 'N/A' }}</p>
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ $student->category->name ?? 'N/A' }}</p>
                    </td>
                    <td class="px-6 py-4 text-slate-400 font-mono text-xs" id="time-{{ $student->id }}">
                        {{ $student->attendance_time ? date('h:i A', strtotime($student->attendance_time)) : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span id="badge-{{ $student->id }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $status === 'Present' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $status === 'Present' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                            <span class="status-text">{{ $status }}</span>
                        </span>
                    </td>
                    @if(auth()->user()->hasRole('super-admin'))
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex rounded-xl bg-slate-950 p-1 border border-slate-800/80 gap-1" id="actions-{{ $student->id }}">
                                <button onclick="markAttendance({{ $student->id }}, 'Present')" 
                                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all duration-200 flex items-center gap-1.5 {{ $status === 'Present' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                                    Present
                                </button>
                                <button onclick="markAttendance({{ $student->id }}, 'Absent')" 
                                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all duration-200 flex items-center gap-1.5 {{ $status === 'Absent' ? 'bg-rose-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                                    Absent
                                </button>
                            </div>
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mx-auto text-slate-700 mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        <p class="text-slate-500 font-medium">No candidates with Hall Ticket Issued status found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
    <div class="px-6 py-4 border-t border-slate-800/60">{{ $students->links() }}</div>
    @endif
</div>

@if(auth()->user()->hasRole('super-admin'))
<script @nonce>
    function markAttendance(studentId, status) {
        const url = '{{ route("admin.attendance.mark") }}';
        const payload = {
            student_id: studentId,
            exam_id: '{{ $examinationId }}',
            date: '{{ $date }}',
            status: status
        };

        const buttonsContainer = document.querySelector(`#actions-${studentId}`);
        const originalHTML = buttonsContainer.innerHTML;
        
        // Show lightweight loading indicator
        buttonsContainer.style.opacity = '0.5';
        buttonsContainer.style.pointerEvents = 'none';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to update attendance status');
            }
            return response.json();
        })
        .then(data => {
            buttonsContainer.style.opacity = '1';
            buttonsContainer.style.pointerEvents = 'auto';

            // 1. Update status badge
            const badge = document.querySelector(`#badge-${studentId}`);
            const dot = badge.querySelector('span');
            const statusText = badge.querySelector('.status-text');

            if (status === 'Present') {
                badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                dot.className = 'w-1.5 h-1.5 rounded-full bg-emerald-400';
            } else {
                badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border bg-rose-500/10 text-rose-400 border-rose-500/20';
                dot.className = 'w-1.5 h-1.5 rounded-full bg-rose-400';
            }
            statusText.textContent = status;

            // 2. Update time stamp
            const timeEl = document.querySelector(`#time-${studentId}`);
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            minutes = minutes < 10 ? '0' + minutes : minutes;
            timeEl.textContent = `${hours}:${minutes} ${ampm}`;

            // 3. Update actions buttons active states
            const presentButton = buttonsContainer.children[0];
            const absentButton = buttonsContainer.children[1];

            if (status === 'Present') {
                presentButton.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all duration-200 flex items-center gap-1.5 bg-emerald-600 text-white shadow-md';
                absentButton.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all duration-200 flex items-center gap-1.5 text-slate-400 hover:text-slate-200';
            } else {
                presentButton.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all duration-200 flex items-center gap-1.5 text-slate-400 hover:text-slate-200';
                absentButton.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all duration-200 flex items-center gap-1.5 bg-rose-600 text-white shadow-md';
            }
        })
        .catch(error => {
            buttonsContainer.style.opacity = '1';
            buttonsContainer.style.pointerEvents = 'auto';
            alert(error.message || 'An error occurred while marking attendance.');
        });
    }
</script>
@endif
@endsection
