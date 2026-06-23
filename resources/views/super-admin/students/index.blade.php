@extends('layouts.app')

@section('page_title', 'Manage Students')

@section('content')

{{-- ─── Stats Row ─── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-indigo-500/10 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-white">{{ number_format($totalCount) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Total Students</p>
        </div>
    </div>

    {{-- Draft --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-slate-500/10 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-white">{{ number_format($draftCount) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Drafted</p>
        </div>
    </div>

    {{-- Submitted --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-blue-500/10 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-white">{{ number_format($submittedCount) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Submitted</p>
        </div>
    </div>

    {{-- Approved/Issued --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-white">{{ number_format($approvedCount) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Approved / Issued</p>
        </div>
    </div>
</div>

{{-- ─── Filters ─── --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-6">
    <form method="GET" action="{{ route('admin.students.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 items-end">

        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5">Exam Session</label>
            <select name="examination_id" id="filter-exam" class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Exams</option>
                @foreach($examinations as $exam)
                    <option value="{{ $exam->id }}" @selected(request('examination_id') == $exam->id)>{{ $exam->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5">School</label>
            <select name="school_id" id="filter-school" class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" @selected(request('school_id') == $school->id)>{{ $school->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5">Category</label>
            <select name="category_id" id="filter-category" class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5">Gender</label>
            <select name="gender" id="filter-gender" class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Genders</option>
                @foreach($genders as $g)
                    <option value="{{ $g }}" @selected(request('gender') == $g)>{{ $g }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5">Status</label>
            <select name="status" id="filter-status" class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Statuses</option>
                @foreach($statuses as $st)
                    <option value="{{ $st }}" @selected(request('status') == $st)>{{ $st }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5">Search</label>
            <input type="text" name="search" id="filter-search" value="{{ request('search') }}"
                placeholder="Name, Reg No, HT No…"
                class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm placeholder-slate-600 focus:outline-none focus:border-indigo-500/50">
        </div>

        <div class="xl:col-span-6 flex justify-end gap-3 mt-1">
            <a href="{{ route('admin.students.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-xl transition-colors">
                Clear Filters
            </a>
            <button type="submit" id="apply-filters-btn" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors cursor-pointer">
                Apply Filters
            </button>
        </div>
    </form>
</div>

{{-- ─── Table ─── --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden shadow-xl">
    <div class="px-6 py-4 border-b border-slate-800/60 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold text-slate-200">All Student Registrations</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ $students->total() }} student(s) found</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-slate-800/60">
                    <th class="px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                    <th class="px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Reg No</th>
                    <th class="px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">School</th>
                    <th class="px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Category</th>
                    <th class="px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Gender</th>
                    <th class="px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Examination</th>
                    <th class="px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Status</th>
                    <th class="px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40">
                @forelse($students as $student)
                    @php
                        // Determine effective display status
                        $isPresent = $student->attendances->where('status', 'Present')->count() > 0;
                        $displayStatus = $student->status;
                        if ($isPresent && in_array($student->status, ['Approved', 'Hall Ticket Issued'])) {
                            $displayStatus = 'Present';
                        } elseif (!$isPresent && in_array($student->status, ['Approved', 'Hall Ticket Issued']) && $student->attendances->count() === 0 && $student->status === 'Hall Ticket Issued') {
                            // Keep as Hall Ticket Issued unless exam day passed — show as-is
                        }

                        // Badge styling map
                        $badgeMap = [
                            'Draft'              => ['bg-slate-800/60 text-slate-400 border-slate-700/40',       'bg-slate-500'],
                            'Submitted'          => ['bg-blue-500/10 text-blue-400 border-blue-500/20',          'bg-blue-400'],
                            'Under Review'       => ['bg-amber-500/10 text-amber-400 border-amber-500/20',       'bg-amber-400'],
                            'Approved'           => ['bg-emerald-500/10 text-emerald-400 border-emerald-500/20', 'bg-emerald-400'],
                            'Rejected'           => ['bg-rose-500/10 text-rose-400 border-rose-500/20',          'bg-rose-400'],
                            'Hall Ticket Issued' => ['bg-indigo-500/10 text-indigo-400 border-indigo-500/20',    'bg-indigo-400'],
                            'Present'            => ['bg-teal-500/10 text-teal-400 border-teal-500/20',          'bg-teal-400'],
                            'Absent'             => ['bg-orange-500/10 text-orange-400 border-orange-500/20',    'bg-orange-400'],
                        ];
                        [$badgeClass, $dotClass] = $badgeMap[$displayStatus] ?? ['bg-slate-800/60 text-slate-400 border-slate-700/40', 'bg-slate-500'];
                    @endphp
                    <tr class="hover:bg-slate-800/20 transition-colors">

                        {{-- Student Info --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg overflow-hidden border border-slate-800 bg-slate-900 shrink-0">
                                    <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-200 text-sm leading-tight">{{ $student->name }}</p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $student->class->name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Reg No --}}
                        <td class="px-5 py-3.5">
                            @if($student->registration_number)
                                <span class="font-mono text-xs font-bold text-indigo-300 bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-1 rounded-lg">
                                    {{ $student->registration_number }}
                                </span>
                            @else
                                <span class="text-slate-600 text-xs italic">Not issued</span>
                            @endif
                        </td>

                        {{-- School --}}
                        <td class="px-5 py-3.5 text-slate-300 text-xs">{{ $student->school->name ?? '—' }}</td>

                        {{-- Category --}}
                        <td class="px-5 py-3.5 text-slate-400 text-xs">{{ $student->category->name ?? '—' }}</td>

                        {{-- Gender --}}
                        <td class="px-5 py-3.5 text-center">
                            @php
                                $gIcon = $student->gender === 'Female' ? '♀' : ($student->gender === 'Male' ? '♂' : '⚥');
                                $gClass = $student->gender === 'Female' ? 'text-pink-400' : ($student->gender === 'Male' ? 'text-sky-400' : 'text-slate-400');
                            @endphp
                            <span class="text-xs font-semibold {{ $gClass }}">{{ $gIcon }} {{ $student->gender }}</span>
                        </td>

                        {{-- Examination --}}
                        <td class="px-5 py-3.5 text-slate-400 text-xs max-w-[130px] truncate" title="{{ $student->examination->name ?? '—' }}">
                            {{ $student->examination->name ?? '—' }}
                        </td>

                        {{-- Status Badge --}}
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                {{ $displayStatus }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- View Profile Button --}}
                                <a href="{{ route('admin.students.show', $student->id) }}"
                                   id="view-student-{{ $student->id }}"
                                   class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-100 transition-colors"
                                   title="View Profile">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>

                                {{-- Issue Registration Number Button --}}
                                @if(!$student->registration_number && in_array($student->status, ['Submitted', 'Under Review', 'Approved', 'Rejected', 'Hall Ticket Issued']))
                                    <form method="POST" action="{{ route('admin.students.issue-registration', $student->id) }}"
                                          onsubmit="return confirm('Issue a registration number for {{ addslashes($student->name) }}?')"
                                          class="inline">
                                        @csrf
                                        <button type="submit" id="issue-reg-{{ $student->id }}"
                                            class="px-3 py-1.5 text-xs font-bold rounded-lg bg-violet-600 hover:bg-violet-500 text-white shadow-md shadow-violet-600/10 transition-colors flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                                            title="Issue Registration Number">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m0-3l-3-3m0 0l-3 3m3-3v11.25m6-2.25h.75a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25h-7.5a2.25 2.25 0 01-2.25-2.25v-.75" />
                                            </svg>
                                            Issue Reg No
                                        </button>
                                    </form>
                                @elseif($student->registration_number)
                                    <span class="text-[10px] text-slate-600 italic">Reg Issued</span>
                                @else
                                    <span class="text-[10px] text-slate-700 italic">Draft</span>
                                @endif
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mx-auto text-slate-700 mb-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            <p class="text-slate-500 font-medium">No students found matching the applied filters</p>
                            <a href="{{ route('admin.students.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm mt-2 inline-block">Clear all filters</a>
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
