@extends('layouts.app')

@section('page_title', 'Student Profile')

@section('content')
    {{-- Back Link --}}
    <div class="mb-6">
        <a href="{{ route('admin.students.index') }}"
            class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-100 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            Back to Manage Students
        </a>
    </div>

    @php
        // Compute present/absent from attendance
        $isPresent = $student->attendances->where('status', 'Present')->count() > 0;
        $badgeMap = [
            'Draft'              => ['bg-slate-500/10 text-slate-400 border-slate-500/20',         'bg-slate-500'],
            'Submitted'          => ['bg-blue-500/10 text-blue-400 border-blue-500/20',             'bg-blue-400'],
            'Under Review'       => ['bg-amber-500/10 text-amber-400 border-amber-500/20',          'bg-amber-400'],
            'Approved'           => ['bg-emerald-500/10 text-emerald-400 border-emerald-500/20',    'bg-emerald-400'],
            'Rejected'           => ['bg-rose-500/10 text-rose-400 border-rose-500/20',             'bg-rose-400'],
            'Hall Ticket Issued' => ['bg-indigo-500/10 text-indigo-400 border-indigo-500/20',       'bg-indigo-400'],
        ];
        $displayStatus = $student->status;
        if ($isPresent && in_array($student->status, ['Approved', 'Hall Ticket Issued'])) {
            $displayStatus = 'Present';
            $badgeStyle = 'bg-teal-500/10 text-teal-400 border-teal-500/20';
        } elseif (!$isPresent && in_array($student->status, ['Approved', 'Hall Ticket Issued']) && $student->attendances->count() > 0) {
            $displayStatus = 'Absent';
            $badgeStyle = 'bg-orange-500/10 text-orange-400 border-orange-500/20';
        } else {
            [$badgeStyle] = $badgeMap[$student->status] ?? ['bg-slate-500/10 text-slate-400 border-slate-500/20'];
        }
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Left: Photo + Quick Info ── --}}
        <div class="lg:col-span-1 space-y-5">

            {{-- Photo Card --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 flex flex-col items-center text-center">
                <div class="relative mb-4">
                    <img src="{{ $student->photo_url }}" alt="{{ $student->name }}"
                        class="w-32 h-32 rounded-2xl object-cover border-2 border-indigo-500/20 shadow-lg shadow-indigo-600/5">
                    {{-- Gender badge overlay --}}
                    @php
                        $gBg = $student->gender === 'Female' ? 'bg-pink-500' : ($student->gender === 'Male' ? 'bg-sky-500' : 'bg-slate-500');
                    @endphp
                    <span class="absolute -bottom-2 -right-2 w-7 h-7 rounded-full {{ $gBg }} flex items-center justify-center text-white text-xs font-bold shadow-md">
                        {{ $student->gender === 'Female' ? '♀' : ($student->gender === 'Male' ? '♂' : '⚥') }}
                    </span>
                </div>

                <h2 class="text-lg font-bold text-white leading-tight">{{ $student->name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ $student->school->name ?? '—' }}</p>

                {{-- Status Badge --}}
                <div class="mt-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $badgeStyle }}">
                        <span class="w-1.5 h-1.5 rounded-full
                            @if($displayStatus === 'Present') bg-teal-400
                            @elseif($displayStatus === 'Absent') bg-orange-400
                            @elseif($displayStatus === 'Draft') bg-slate-400
                            @elseif($displayStatus === 'Submitted') bg-blue-400
                            @elseif($displayStatus === 'Under Review') bg-amber-400
                            @elseif($displayStatus === 'Approved') bg-emerald-400
                            @elseif($displayStatus === 'Rejected') bg-rose-400
                            @elseif($displayStatus === 'Hall Ticket Issued') bg-indigo-400
                            @else bg-slate-500 @endif
                        "></span>
                        {{ $displayStatus }}
                    </span>
                </div>

                {{-- Reg No --}}
                @if($student->registration_number)
                    <div class="mt-4 w-full bg-slate-800/30 border border-slate-800/80 rounded-xl p-3 text-left">
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Registration Number</p>
                        <p class="text-sm font-mono font-bold text-indigo-400 mt-0.5">{{ $student->registration_number }}</p>
                    </div>
                @endif

                {{-- Hall Ticket No --}}
                @if($student->hall_ticket_number)
                    <div class="mt-3 w-full bg-slate-800/30 border border-slate-800/80 rounded-xl p-3 text-left">
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Hall Ticket Number</p>
                        <p class="text-sm font-mono font-bold text-purple-400 mt-0.5">{{ $student->hall_ticket_number }}</p>
                    </div>
                @endif
            </div>

            {{-- Super Admin Actions --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Admin Actions</h4>

                {{-- Issue Reg No --}}
                @if(!$student->registration_number && in_array($student->status, ['Submitted', 'Under Review', 'Approved', 'Rejected', 'Hall Ticket Issued']))
                    <form method="POST" action="{{ route('admin.students.issue-registration', $student->id) }}"
                          onsubmit="return confirm('Issue a registration number for {{ addslashes($student->name) }}?')">
                        @csrf
                        <button type="submit" id="issue-reg-profile-{{ $student->id }}"
                            class="w-full flex items-center justify-center gap-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold py-2.5 rounded-xl transition-all shadow-lg shadow-violet-600/15 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m0-3l-3-3m0 0l-3 3m3-3v11.25m6-2.25h.75a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25h-7.5a2.25 2.25 0 01-2.25-2.25v-.75" />
                            </svg>
                            Issue Registration Number
                        </button>
                    </form>
                @elseif($student->registration_number)
                    <div class="w-full flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold py-2.5 px-4 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Registration Number Issued
                    </div>
                @endif

                {{-- Print Hall Ticket (if issued) --}}
                @if($student->status === 'Hall Ticket Issued')
                    <a href="{{ route('admin.hall-tickets.print-single', $student) }}" target="_blank"
                        class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/15 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                        </svg>
                        Print Hall Ticket
                    </a>
                @endif

                {{-- Enter Marks (if present & no result) --}}
                @if($isPresent && !$student->result)
                    <a href="{{ route('admin.results.create', $student) }}"
                        class="w-full flex items-center justify-center gap-2 bg-teal-600 hover:bg-teal-500 text-white text-sm font-semibold py-2.5 rounded-xl transition-all shadow-lg shadow-teal-600/15 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Enter Exam Marks
                    </a>
                @elseif($student->result)
                    <a href="{{ route('admin.results.edit', $student->result->id) }}"
                        class="w-full flex items-center justify-center gap-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-semibold py-2.5 rounded-xl transition-all cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.83 20.062a4.5 4.5 0 0 1-1.89 1.13L2.685 21.8a.75.75 0 0 1-.944-.94l.813-2.831a4.5 4.5 0 0 1 1.13-1.89L16.863 4.487zm0 0L19.5 7.125" />
                        </svg>
                        Edit Exam Result
                    </a>
                @endif

                {{-- Assign Examination Centre (Single Candidate) --}}
                @if(in_array($student->status, ['Submitted', 'Under Review', 'Approved']))
                    <div class="border-t border-slate-800/80 pt-3.5 mt-3.5">
                        <form method="POST" action="{{ route('admin.exam-centres.assign-single', $student->id) }}">
                            @csrf
                            <label for="profile_centre_id" class="block text-[10px] text-slate-500 uppercase tracking-widest font-semibold mb-1.5">Assign Examination Centre</label>
                            <div class="flex gap-2">
                                <select name="centre_id" id="profile_centre_id" required
                                        class="flex-1 bg-slate-800 border border-slate-700/60 rounded-xl px-3 py-2 text-slate-100 text-xs focus:outline-none focus:border-indigo-500">
                                    <option value="" disabled {{ $student->centre_id ? '' : 'selected' }}>Select Centre...</option>
                                    @foreach($designatedCentres as $centre)
                                        <option value="{{ $centre->id }}" @selected($student->centre_id === $centre->id)>
                                            {{ $centre->name }} ({{ $centre->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-4 py-2 rounded-xl text-xs transition-all cursor-pointer">
                                    Assign
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Right: Detail Cards ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Rejection Banner --}}
            @if($student->status === 'Rejected' && $student->remarks)
                <div class="p-5 bg-rose-950/30 border border-rose-800/40 rounded-2xl">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-rose-400 mb-2">Rejection Reason</h4>
                    <p class="text-sm text-rose-200/90 leading-relaxed">{{ $student->remarks }}</p>
                </div>
            @endif

            {{-- Personal Details --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-indigo-400 border-b border-slate-800 pb-3 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    Personal Details
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <span class="block text-slate-500 text-xs">Father's Name</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->father_name }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">Mother's Name</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->mother_name }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">Gender</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->gender }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">Date of Birth</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->dob->format('d F Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">Mobile Number</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->mobile_number }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">Last Updated</span>
                        <span class="text-slate-400 text-xs mt-1 block">{{ $student->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            {{-- Academic Details --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-indigo-400 border-b border-slate-800 pb-3 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M12 13.489v-3.342" />
                    </svg>
                    Academic & School Details
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <span class="block text-slate-500 text-xs">Class</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->class->name ?? '—' }} ({{ $student->class->code ?? '' }})</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">Category</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->category->name ?? '—' }} ({{ $student->category->code ?? '' }})</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">School Name</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->school->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">School Code</span>
                        <span class="text-slate-200 font-mono font-semibold mt-1 block">{{ $student->school->code ?? '—' }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="block text-slate-500 text-xs">Centre of Examination</span>
                        @if($student->centre)
                            <span class="text-indigo-400 font-bold mt-1 block">{{ $student->centre->name }} ({{ $student->centre->code }})</span>
                        @else
                            <span class="text-amber-500 font-semibold mt-1 block italic flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending Centre Assignment
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Examination Details --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-indigo-400 border-b border-slate-800 pb-3 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    Examination Details
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <span class="block text-slate-500 text-xs">Examination Session</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->examination->name ?? '—' }}</span>
                    </div>
                    @if($student->examination)
                    <div>
                        <span class="block text-slate-500 text-xs">Academic Year</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->examination->academic_year ?? '—' }}</span>
                    </div>
                    @endif
                    @if($student->hall_ticket_number)
                        <div>
                            <span class="block text-slate-500 text-xs">Hall Ticket Number</span>
                            <span class="text-indigo-400 font-mono font-bold mt-1 block">{{ $student->hall_ticket_number }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 text-xs">Hall Ticket Issued At</span>
                            <span class="text-slate-200 font-semibold mt-1 block">{{ $student->hall_ticket_issued_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Payment / Transaction Details --}}
            @if($student->payment_status === 'Paid' && $student->payments->isNotEmpty())
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                    <h3 class="text-sm font-bold border-b border-slate-800 pb-3 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-emerald-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-emerald-400">Payment / Transaction Details</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                        @foreach($student->payments as $payment)
                            <div>
                                <span class="block text-slate-500 text-xs">Transaction ID</span>
                                <span class="text-indigo-400 font-mono font-semibold mt-1 block">{{ $payment->transaction_id }}</span>
                            </div>
                            <div>
                                <span class="block text-slate-500 text-xs">Amount Paid</span>
                                <span class="text-slate-200 font-semibold mt-1 block">₹{{ number_format($payment->amount, 2) }}</span>
                            </div>
                            <div>
                                <span class="block text-slate-500 text-xs">Payment Method</span>
                                <span class="text-slate-200 font-semibold mt-1 block uppercase">{{ $payment->payment_method }}</span>
                            </div>
                            <div>
                                <span class="block text-slate-500 text-xs">Paid On</span>
                                <span class="text-slate-200 font-semibold mt-1 block">{{ $payment->paid_at ? $payment->paid_at->format('d M Y, h:i A') : '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Attendance Record --}}
            @if($student->attendances->count() > 0)
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-indigo-400 border-b border-slate-800 pb-3 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Attendance Record
                    </h3>
                    <div class="space-y-2">
                        @foreach($student->attendances as $att)
                            <div class="flex items-center justify-between px-4 py-2.5 rounded-xl bg-slate-800/40 border border-slate-700/30">
                                <span class="text-xs text-slate-400">{{ $att->attendance_date->format('d M Y') }} at {{ $att->attendance_time }}</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold
                                    @if($att->status === 'Present') bg-teal-500/10 text-teal-400 border border-teal-500/20
                                    @else bg-orange-500/10 text-orange-400 border border-orange-500/20 @endif">
                                    <span class="w-1.5 h-1.5 rounded-full @if($att->status === 'Present') bg-teal-400 @else bg-orange-400 @endif"></span>
                                    {{ $att->status }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Exam Result --}}
            @if($student->result)
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-indigo-400 border-b border-slate-800 pb-3 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />
                        </svg>
                        Exam Result
                    </h3>
                    @php
                        $res = $student->result;
                        $resColors = [
                            'Pass'     => 'text-emerald-400',
                            'Fail'     => 'text-rose-400',
                            'Absent'   => 'text-orange-400',
                            'Withheld' => 'text-amber-400',
                        ];
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                        <div class="bg-slate-800/40 rounded-xl p-4">
                            <p class="text-xs text-slate-500 mb-1">Marks</p>
                            <p class="text-xl font-bold text-white font-mono">{{ $res->marks_obtained }}<span class="text-slate-500 text-sm">/{{ $res->max_marks }}</span></p>
                        </div>
                        <div class="bg-slate-800/40 rounded-xl p-4">
                            <p class="text-xs text-slate-500 mb-1">Percentage</p>
                            <p class="text-xl font-bold text-indigo-400 font-mono">{{ $res->percentage }}%</p>
                        </div>
                        <div class="bg-slate-800/40 rounded-xl p-4">
                            <p class="text-xs text-slate-500 mb-1">Grade</p>
                            <p class="text-xl font-bold text-white">{{ $res->grade }}</p>
                        </div>
                        <div class="bg-slate-800/40 rounded-xl p-4">
                            <p class="text-xs text-slate-500 mb-1">Result</p>
                            <p class="text-xl font-bold {{ $resColors[$res->status] ?? 'text-white' }}">{{ $res->status }}</p>
                        </div>
                    </div>
                    @if($res->remarks)
                        <p class="mt-4 text-xs text-slate-500 bg-slate-800/30 rounded-xl px-4 py-2.5">
                            <span class="font-semibold text-slate-400">Remarks:</span> {{ $res->remarks }}
                        </p>
                    @endif
                </div>
            @endif

        </div>
    </div>
@endsection
