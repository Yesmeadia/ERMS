@extends('layouts.app')
@section('page_title', 'Review Registration')
@section('content')
<div class="w-full py-6" id="review-page-wrapper">
    <style>
        /* Force page-level full width of review page wrapper and its layout ancestors */
        #review-page-wrapper,
        #review-page-wrapper > .grid {
            width: 100% !important;
            max-width: none !important;
        }
        /* Override layout's content wrapper constraint if any exists */
        main div.pb-20,
        main .flex-1.pb-20 {
            max-width: none !important;
            width: 100% !important;
        }
    </style>
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.verification.index') }}"
                class="p-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-xl font-bold text-white">Review — {{ $student->name }}</h2>
            @php
                $statusColors = [
                    'Submitted' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                    'Under Review' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                    'Approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                    'Rejected' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                    'Hall Ticket Issued' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                ];
            @endphp
            <span
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColors[$student->status] ?? '' }}">
                {{ $student->status }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 w-full">
            {{-- Student Details --}}
            <div class="lg:col-span-8 space-y-6 w-full">
                {{-- Personal Information --}}
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                    <h3 class="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Personal Information
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Full Name</p>
                            <p class="text-sm text-slate-200 font-medium">{{ $student->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Gender</p>
                            <p class="text-sm text-slate-200">{{ $student->gender }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Date of Birth</p>
                            <p class="text-sm text-slate-200">{{ $student->dob->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Mobile Number</p>
                            <p class="text-sm text-slate-200">{{ $student->mobile_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Father's Name</p>
                            <p class="text-sm text-slate-200">{{ $student->father_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Mother's Name</p>
                            <p class="text-sm text-slate-200">{{ $student->mother_name }}</p>
                        </div>
                    </div>
                </div>

                {{-- Academic Information --}}
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                    <h3 class="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6.75L12 4.5 3.75 9 12 13.5 20.25 9l-4.5-2.25z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 12.75v3c0 1.657 2.35 3 5.25 3s5.25-1.343 5.25-3v-3" />
                        </svg>
                        Academic Information
                    </h3>
                    <div class="grid grid-cols-2 gap-4">

                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Registration Number</p>
                            <p class="text-sm text-indigo-400 font-medium">
                                {{ $student->registration_number ?? 'Not assigned' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Class</p>
                            <p class="text-sm text-slate-200">{{ $student->class->name ?? '—' }}
                                ({{ $student->class->code ?? '' }})</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Category</p>
                            <p class="text-sm text-slate-200">{{ $student->category->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">School</p>
                            <p class="text-sm text-slate-200">{{ $student->school->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Examination</p>
                            <p class="text-sm text-slate-200">{{ $student->examination->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>

            {{-- Payment / Transaction Details --}}
            @if($student->payment_status === 'Paid' && $student->payments->isNotEmpty())
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
                    <h3 class="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2 text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Payment / Transaction Details
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($student->payments as $payment)
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Transaction ID</p>
                                <p class="text-sm text-indigo-400 font-mono font-semibold">{{ $payment->transaction_id }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Amount Paid</p>
                                <p class="text-sm text-slate-200 font-semibold">₹{{ number_format($payment->amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Payment Method</p>
                                <p class="text-sm text-slate-200 font-semibold uppercase">{{ $payment->payment_method }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Paid On</p>
                                <p class="text-sm text-slate-200 font-semibold">{{ $payment->paid_at ? $payment->paid_at->format('d M Y, h:i A') : '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($student->remarks)
                <div class="bg-rose-950/30 border border-rose-800/40 rounded-2xl p-6">
                    <h3 class="text-sm font-semibold text-rose-300 mb-2">Rejection Remarks</h3>
                    <p class="text-sm text-rose-200">{{ $student->remarks }}</p>
                </div>
            @endif
        </div>

            {{-- Sidebar Actions --}}
            <div class="lg:col-span-4 space-y-6 w-full">
                {{-- Photo --}}
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 text-center">
                    <img src="{{ $student->photo_url }}" alt="{{ $student->name }}"
                        class="w-28 h-28 rounded-2xl mx-auto object-cover border-2 border-slate-700/60 mb-3">
                    <p class="text-sm font-medium text-slate-200">{{ $student->name }}</p>
                    <p class="text-xs text-slate-500">{{ $student->registration_number ?? 'Draft' }}</p>
                </div>

                {{-- Verification Actions --}}
                @if(in_array($student->status, ['Submitted', 'Under Review']))
                    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-3">
                        <h3 class="text-sm font-semibold text-slate-300 mb-3">Verification Actions</h3>

                        @if($student->status === 'Submitted')
                            <form method="POST" action="{{ route('admin.verification.verify', $student) }}">
                                @csrf
                                <input type="hidden" name="action" value="review">
                                <button type="submit"
                                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-amber-400 bg-amber-600/10 hover:bg-amber-600/20 border border-amber-500/20 transition-all cursor-pointer">
                                    Mark Under Review
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.verification.verify', $student) }}">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button type="submit"
                                class="w-full py-2.5 rounded-xl text-sm font-semibold text-emerald-400 bg-emerald-600/10 hover:bg-emerald-600/20 border border-emerald-500/20 transition-all cursor-pointer">
                                ✓ Approve Registration
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.verification.verify', $student) }}"
                            x-data="{ showRemarks: false }">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <button type="button" @click="showRemarks = !showRemarks"
                                class="w-full py-2.5 rounded-xl text-sm font-semibold text-rose-400 bg-rose-600/10 hover:bg-rose-600/20 border border-rose-500/20 transition-all cursor-pointer">
                                ✕ Reject Registration
                            </button>
                            <div x-show="showRemarks" x-transition class="mt-3 space-y-3" style="display: none;">
                                <textarea name="remarks" rows="3" required placeholder="Enter rejection remarks…"
                                    class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-rose-500"></textarea>
                                <button type="submit"
                                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-white bg-rose-600 hover:bg-rose-500 transition-all cursor-pointer">
                                    Confirm Rejection
                                </button>
                            </div>
                        </form>
                    </div>
                @elseif($student->status === 'Approved')
                    <div class="bg-emerald-950/30 border border-emerald-800/40 rounded-2xl p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-10 h-10 mx-auto text-emerald-400 mb-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium text-emerald-300">This registration has been approved</p>
                        <p class="text-xs text-emerald-400/60 mt-1">Ready for hall ticket generation</p>
                    </div>
                @elseif($student->status === 'Rejected')
                    <div class="bg-rose-950/30 border border-rose-800/40 rounded-2xl p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-10 h-10 mx-auto text-rose-400 mb-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium text-rose-300">This registration was rejected</p>
                    </div>
                @elseif($student->status === 'Hall Ticket Issued')
                    <div class="bg-purple-950/30 border border-purple-800/40 rounded-2xl p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="#c084fc" class="w-10 h-10 mx-auto mb-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 8.25A2.25 2.25 0 016.25 6h11.5A2.25 2.25 0 0120 8.25V10a2 2 0 010 4v1.75A2.25 2.25 0 0117.75 18H6.25A2.25 2.25 0 014 15.75V14a2 2 0 010-4V8.25z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12" />
                        </svg>
                        <p class="text-sm font-medium text-purple-300">Hall Ticket Issued</p>
                        <p class="text-xs text-purple-400/60 mt-1">{{ $student->hall_ticket_number }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection