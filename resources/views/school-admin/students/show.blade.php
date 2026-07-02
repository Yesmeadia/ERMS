@extends('layouts.app')

@section('page_title', 'Student Profile')

@section('content')
    {{-- Back Link --}}
    <div class="mb-6">
        <a href="{{ route('school.students.index') }}"
            class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-100 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            Back to Students
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Side: Profile Photo & Quick Info Card --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 flex flex-col items-center text-center">
                <img src="{{ $student->photo_url }}" alt="{{ $student->name }}"
                    class="w-32 h-32 rounded-2xl object-cover border-2 border-indigo-500/20 shadow-lg shadow-indigo-600/5 mb-4">

                <h2 class="text-lg font-bold text-white leading-tight">{{ $student->name }}</h2>

                {{-- Status Badge --}}
                @php
                    $badges = [
                        'Draft' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                        'Submitted' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                        'Under Review' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                        'Approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                        'Rejected' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                        'Hall Ticket Issued' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                    ];
                    $badgeStyle = $badges[$student->status] ?? 'bg-slate-500/10 text-slate-400 border-slate-500/20';
                @endphp
                <div class="mt-4 flex items-center justify-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $badgeStyle }}">
                        {{ $student->status }}
                    </span>
                    {{-- Payment badge --}}
                    @if($student->payment_status === 'Paid')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Paid
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>Unpaid
                        </span>
                    @endif
                </div>

                @if($student->registration_number)
                    <div class="mt-4 w-full bg-slate-800/30 border border-slate-800/80 rounded-xl p-3 text-left">
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Registration Number</p>
                        <p class="text-sm font-mono font-bold text-indigo-400 mt-0.5">{{ $student->registration_number }}</p>
                    </div>
                @endif
            </div>

            {{-- ── Payment Status Card ──────────────────────────── --}}
            @if(in_array($student->status, ['Draft', 'Rejected']))
            <div class="rounded-2xl border p-5
                {{ $student->payment_status === 'Paid'
                    ? 'bg-emerald-950/30 border-emerald-800/40'
                    : 'bg-amber-950/30 border-amber-800/40' }}">
                <h4 class="text-xs font-bold uppercase tracking-wider mb-3
                    {{ $student->payment_status === 'Paid' ? 'text-emerald-400' : 'text-amber-400' }}">
                    Registration Fee
                </h4>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xl font-bold
                            {{ $student->payment_status === 'Paid' ? 'text-emerald-300' : 'text-amber-300' }}">
                            ₹{{ number_format($student->class->registration_fee, 2) }}
                        </p>
                        <p class="text-xs mt-0.5
                            {{ $student->payment_status === 'Paid' ? 'text-emerald-500' : 'text-amber-500' }}">
                            {{ $student->class->name }} · {{ $student->class->code }}
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center
                        {{ $student->payment_status === 'Paid'
                            ? 'bg-emerald-500/20 border border-emerald-500/30'
                            : 'bg-amber-500/20 border border-amber-500/30' }}">
                        @if($student->payment_status === 'Paid')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-400"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-400"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                        @endif
                    </div>
                </div>
                @if($student->payment_status === 'Unpaid')
                    {{-- Pay Fee & Submit CTA --}}
                    <form method="POST" action="{{ route('school.payments.checkout') }}">
                        @csrf
                        <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2
                                   bg-indigo-600 hover:bg-indigo-500 text-white
                                   text-sm font-bold py-3 rounded-xl
                                   transition-all shadow-lg shadow-indigo-600/20 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                            Pay Fee & Submit
                        </button>
                    </form>
                @else
                    <p class="text-xs text-emerald-500 text-center font-medium">
                        ✓ Fee paid — candidate will be auto-submitted to the board.
                    </p>
                @endif
            </div>
            @endif

            {{-- ── Other Action Buttons ─────────────────────────── --}}
            @if(in_array($student->status, ['Draft', 'Rejected']) || $student->status === 'Hall Ticket Issued')
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Other Actions</h4>

                    @if(in_array($student->status, ['Draft', 'Rejected']))
                        {{-- Edit --}}
                        <a href="{{ route('school.students.edit', $student) }}"
                            class="w-full flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold py-2.5 rounded-xl transition-all cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                            Edit Information
                        </a>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('school.students.destroy', $student) }}"
                            onsubmit="return confirm('Delete this candidate registration draft?')" class="w-full">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-sm font-semibold py-2.5 rounded-xl transition-all cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                Delete Draft
                            </button>
                        </form>
                    @endif

                    @if($student->status === 'Hall Ticket Issued')
                        <a href="{{ route('school.hall-tickets.download-single', $student) }}"
                            class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold py-2.5 rounded-xl transition-all shadow-lg shadow-emerald-600/15 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Download Hall Ticket
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Right Side: Profile Details Cards --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ── Unpaid Fee Warning Banner ─────────────────────────── --}}
            @if(in_array($student->status, ['Draft', 'Rejected']) && $student->payment_status === 'Unpaid')
            <div class="flex items-start gap-4 p-4 bg-amber-950/30 border border-amber-800/40 rounded-2xl">
                <div class="w-9 h-9 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center shrink-0 mt-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-amber-400"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-amber-300">Registration fee payment required</p>
                    <p class="text-xs text-amber-500/90 mt-1 leading-relaxed">
                        This candidate cannot be submitted to the board until the registration fee of
                        <strong class="text-amber-300">₹{{ number_format($student->class->registration_fee, 2) }}</strong>
                        has been paid. Use the <strong class="text-amber-300">Pay Fee &amp; Submit</strong> button on the left to proceed.
                    </p>
                </div>
            </div>
            @endif

            {{-- Rejection remarks banner --}}
            @if($student->status === 'Rejected' && $student->remarks)
                <div class="p-5 bg-rose-950/30 border border-rose-800/40 rounded-2xl">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-rose-400 mb-2">Rejection Reason</h4>
                    <p class="text-sm text-rose-200/90 leading-relaxed">{{ $student->remarks }}</p>
                </div>
            @endif

            {{-- Personal Details --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                <h3
                    class="text-sm font-bold text-slate-200 border-b border-slate-800 pb-3 mb-4 flex items-center gap-2 text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
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
                </div>
            </div>

            {{-- Academic Details --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                <h3
                    class="text-sm font-bold text-slate-200 border-b border-slate-800 pb-3 mb-4 flex items-center gap-2 text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6.75L12 4.5 3.75 9 12 13.5 20.25 9l-4.5-2.25z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 12.75v3c0 1.657 2.35 3 5.25 3s5.25-1.343 5.25-3v-3" />
                    </svg>
                    Academic Details
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <span class="block text-slate-500 text-xs">Class</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->class->name }}
                            ({{ $student->class->code }})</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">Category</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->category->name }}
                            ({{ $student->category->code }})</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">School Name</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->school->name }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">School Code</span>
                        <span class="text-slate-200 font-mono font-semibold mt-1 block">{{ $student->school->code }}</span>
                    </div>
                </div>
            </div>

            {{-- Examination Details --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                <h3
                    class="text-sm font-bold text-slate-200 border-b border-slate-800 pb-3 mb-4 flex items-center gap-2 text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    Examination Details
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <span class="block text-slate-500 text-xs">Examination Session</span>
                        <span class="text-slate-200 font-semibold mt-1 block">{{ $student->examination->name }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs">Academic Year</span>
                        <span
                            class="text-slate-200 font-semibold mt-1 block">{{ $student->examination->academic_year }}</span>
                    </div>
                    @if($student->hall_ticket_number)
                        <div>
                            <span class="block text-slate-500 text-xs">Hall Ticket Number</span>
                            <span
                                class="text-indigo-400 font-mono font-bold mt-1 block">{{ $student->hall_ticket_number }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 text-xs">Hall Ticket Issued At</span>
                            <span
                                class="text-slate-200 font-semibold mt-1 block">{{ $student->hall_ticket_issued_at->format('d M Y, h:i A') }}</span>
                        </div>
                    @endif
                    <div>
                        <span class="block text-slate-500 text-xs">Last Updated</span>
                        <span class="text-slate-400 text-xs mt-1 block">{{ $student->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment / Transaction Details --}}
            @if($student->payment_status === 'Paid' && $student->payments->isNotEmpty())
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                <h3
                    class="text-sm font-bold text-slate-200 border-b border-slate-800 pb-3 mb-4 flex items-center gap-2 text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Payment / Transaction Details
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

        </div>

    </div>
@endsection