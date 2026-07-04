@extends('layouts.app')

@section('page_title', 'Payments & Balance Sheet')

@section('content')

    {{-- ─── Page Header ─── --}}
    <div class="relative overflow-hidden rounded-3xl bg-slate-900/40 border border-slate-800/80 p-6 sm:p-8 shadow-2xl mb-8">
        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none">
        </div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-2xl font-black text-white tracking-tight mt-2 flex items-center gap-2.5">
                    Financial Operations
                </h2>
                <p class="text-xs text-slate-400 mt-1 max-w-xl">
                    Manage candidate registration payments, analyze class-wise financial balances, and track payment
                    transactions.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <div class="bg-slate-950/40 border border-slate-850 p-3 rounded-2xl flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="w-5 h-5 text-emerald-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div
            class="mb-6 p-4 bg-emerald-950/40 border border-emerald-800/40 text-emerald-200 rounded-2xl text-xs font-semibold shadow-lg shadow-emerald-950/20 flex items-center gap-3">
            <div class="w-6 h-6 rounded-lg bg-emerald-500/20 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                    class="w-3.5 h-3.5 text-emerald-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            {{ session('success') }}
        </div>
    @endif

    {{-- ─── KPI Cards ─── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">

        {{-- Card 1: Registered Candidates --}}
        <div
            class="relative overflow-hidden rounded-3xl bg-slate-900/40 backdrop-blur-md border border-slate-800/60 p-6 shadow-xl group hover:border-indigo-500/30 hover:shadow-indigo-950/30 transition-all duration-300">
            <div
                class="absolute -right-6 -bottom-6 w-20 h-20 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/15 transition-all">
            </div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Registered Candidates</p>
                    <h3 class="text-3xl font-black text-white mt-3 tracking-tight font-mono">{{ $totalRegistered }}</h3>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/15 flex items-center justify-center text-indigo-400 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
            </div>
            <div class="mt-6 flex items-center gap-2">
                <span
                    class="inline-flex items-center text-[10px] font-bold text-emerald-400 bg-emerald-500/8 border border-emerald-500/15 px-2.5 py-0.5 rounded-md">
                    {{ $totalPaidCount }} Paid
                </span>
                <span
                    class="inline-flex items-center text-[10px] font-bold text-rose-400 bg-rose-500/8 border border-rose-500/15 px-2.5 py-0.5 rounded-md">
                    {{ $totalOutstandingCount }} Unpaid
                </span>
            </div>
        </div>

        {{-- Card 2: Total Fees Collected --}}
        <div
            class="relative overflow-hidden rounded-3xl bg-slate-900/40 backdrop-blur-md border border-slate-800/60 p-6 shadow-xl group hover:border-emerald-500/30 hover:shadow-emerald-950/30 transition-all duration-300">
            <div
                class="absolute -right-6 -bottom-6 w-20 h-20 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/15 transition-all">
            </div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Fees Collected</p>
                    <h3 class="text-3xl font-black text-emerald-400 mt-3 tracking-tight font-mono">
                        ₹{{ number_format($totalPaidAmount, 2) }}</h3>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/15 flex items-center justify-center text-emerald-400 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-6 flex items-center gap-2">
                @php
                    $total = $totalPaidAmount + $totalOutstandingAmount;
                    $pct = $total > 0 ? round(($totalPaidAmount / $total) * 100) : 0;
                @endphp
                <div class="flex-1 h-1.5 bg-slate-950 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full"
                        style="width: {{ $pct }}%"></div>
                </div>
                <span class="text-[10px] font-bold text-slate-400 font-mono">{{ $pct }}% Paid</span>
            </div>
        </div>

        {{-- Card 3: Outstanding Balance --}}
        <div
            class="relative overflow-hidden rounded-3xl bg-slate-900/40 backdrop-blur-md border border-slate-800/60 p-6 shadow-xl group hover:border-rose-500/30 hover:shadow-rose-950/30 transition-all duration-300">
            <div
                class="absolute -right-6 -bottom-6 w-20 h-20 bg-rose-500/10 rounded-full blur-2xl group-hover:bg-rose-500/15 transition-all">
            </div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Outstanding Balance</p>
                    <h3 class="text-3xl font-black text-rose-450 mt-3 tracking-tight font-mono">
                        ₹{{ number_format($totalOutstandingAmount, 2) }}</h3>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/15 flex items-center justify-center text-rose-400 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
                    </svg>
                </div>
            </div>
            <p class="text-[10px] text-slate-500 mt-7">Outstanding fees for {{ $totalOutstandingCount }} draft registrations
            </p>
        </div>

    </div>

    {{-- ─── Main Content Layout ─── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Class-wise Balance Sheet --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900/40 backdrop-blur-md border border-slate-800/60 rounded-3xl shadow-xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800/60 bg-slate-950/20">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-200 flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="w-4 h-4 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                        </svg>
                        Class Balance Matrix
                    </h3>
                    <span
                        class="text-[10px] text-slate-500 font-bold bg-slate-800/60 px-2 py-0.5 rounded border border-slate-700/50">
                        {{ count($balanceSheet) }} Classes
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="border-b border-slate-800 bg-slate-950/10">
                                <th class="py-3 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Class /
                                    Level</th>
                                <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">
                                    Fee Per Student</th>
                                <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">
                                    Candidates</th>
                                <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">
                                    Paid Count</th>
                                <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">
                                    Outstanding</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-850/60">
                            @forelse($balanceSheet as $item)
                                <tr class="hover:bg-slate-800/20 transition-all duration-150">
                                    <td class="py-3.5 px-6 font-bold text-slate-200">
                                        {{ $item['class_name'] }}
                                    </td>
                                    <td class="py-3.5 text-center text-slate-400 font-mono font-medium">
                                        ₹{{ number_format($item['fee'], 0) }}
                                    </td>
                                    <td class="py-3.5 text-center text-slate-300 font-bold font-mono">
                                        {{ $item['total_count'] }}
                                    </td>
                                    <td class="py-3.5 text-center">
                                        <span
                                            class="inline-flex items-center text-[10px] font-black text-emerald-400 bg-emerald-500/8 border border-emerald-500/15 px-2.5 py-0.5 rounded-lg">
                                            {{ $item['paid_count'] }} Paid
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-6 text-right font-black text-rose-450 font-mono">
                                        ₹{{ number_format($item['unpaid_amount'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-500">
                                        No registrations found to compile balance statistics.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($balanceSheet) > 0)
                            <tfoot>
                                <tr class="border-t border-slate-800 font-black bg-slate-950/45 text-slate-200">
                                    <td class="py-4 px-6 uppercase tracking-wider text-[9px] font-bold text-slate-400">Total
                                        Balance Summary</td>
                                    <td class="py-4 text-center text-slate-600">&mdash;</td>
                                    <td class="py-4 text-center font-mono text-slate-300">{{ $totalRegistered }}</td>
                                    <td class="py-4 text-center font-mono text-emerald-400">{{ $totalPaidCount }}</td>
                                    <td class="py-4 px-6 text-right font-mono text-rose-450">
                                        ₹{{ number_format($totalOutstandingAmount, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Transactions Layout --}}
        <div class="lg:col-span-1" x-data="{ filter: 'all' }">
            <div
                class="bg-slate-900/40 backdrop-blur-md border border-slate-800/60 rounded-3xl shadow-xl p-6 h-full flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-200 flex items-center gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                stroke="currentColor" class="w-4 h-4 text-indigo-400">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Recent Activity
                        </h3>
                    </div>

                    {{-- Interactive Filter tabs --}}
                    <div
                        class="flex items-center gap-1 bg-slate-950/80 p-1 rounded-xl border border-slate-850 mb-4 text-[10px]">
                        <button type="button" @click="filter = 'all'"
                            :class="filter === 'all' ? 'bg-indigo-500/15 text-indigo-400 border-indigo-500/20' : 'text-slate-500 hover:text-slate-300 border-transparent'"
                            class="flex-1 text-center py-1.5 px-2 rounded-lg font-bold border transition-all cursor-pointer">
                            All
                        </button>
                        <button type="button" @click="filter = 'Paid'"
                            :class="filter === 'Paid' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20' : 'text-slate-500 hover:text-slate-300 border-transparent'"
                            class="flex-1 text-center py-1.5 px-2 rounded-lg font-bold border transition-all cursor-pointer">
                            Paid
                        </button>
                        <button type="button" @click="filter = 'Pending'"
                            :class="filter === 'Pending' ? 'bg-amber-500/15 text-amber-400 border-amber-500/20' : 'text-slate-500 hover:text-slate-300 border-transparent'"
                            class="flex-1 text-center py-1.5 px-2 rounded-lg font-bold border transition-all cursor-pointer">
                            Pending
                        </button>
                    </div>

                    <div class="space-y-3.5 max-h-[500px] overflow-y-auto pr-1">
                        @forelse($payments as $payment)
                            <div x-show="filter === 'all' || filter === '{{ $payment->status }}'" x-data="{ expanded: false }"
                                x-transition
                                class="bg-slate-950/40 border border-slate-850 hover:border-slate-800 rounded-2xl p-4 transition-all duration-200">

                                {{-- Header detail --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-mono font-bold text-indigo-300 truncate"
                                            title="{{ $payment->transaction_id ?? $payment->razorpay_order_id }}">
                                            {{ $payment->transaction_id ? substr($payment->transaction_id, 0, 14) . '...' : 'Pending' }}
                                        </p>
                                        <p class="text-[9px] text-slate-500 mt-1 font-medium">
                                            {{ $payment->created_at->format('d M, h:i A') }}
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-xs font-black text-slate-200 font-mono">
                                            ₹{{ number_format($payment->amount, 0) }}</p>
                                        @if($payment->status === 'Paid')
                                            <span
                                                class="inline-flex items-center text-[8px] font-extrabold uppercase tracking-widest text-emerald-400 bg-emerald-500/10 border border-emerald-500/15 px-2 py-0.5 rounded mt-1.5">
                                                Paid
                                            </span>
                                        @elseif($payment->status === 'Pending')
                                            <span
                                                class="inline-flex items-center text-[8px] font-extrabold uppercase tracking-widest text-amber-400 bg-amber-500/10 border border-amber-500/15 px-2 py-0.5 rounded mt-1.5">
                                                Pending
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center text-[8px] font-extrabold uppercase tracking-widest text-rose-400 bg-rose-500/10 border border-rose-500/15 px-2 py-0.5 rounded mt-1.5">
                                                Failed
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Footer action controls --}}
                                <div class="flex items-center justify-between border-t border-slate-800/80 pt-3 mt-3">
                                    <span class="text-[10px] font-semibold text-slate-400">
                                        {{ $payment->students_count }} Candidates
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="expanded = !expanded"
                                            class="text-slate-400 hover:text-slate-200 font-bold text-[9px] uppercase tracking-wider flex items-center gap-1 cursor-pointer">
                                            <span x-text="expanded ? 'Close' : 'Candidates'">Candidates</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2.5" stroke="currentColor"
                                                class="w-2.5 h-2.5 transition-transform" :class="expanded ? 'rotate-180' : ''">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </button>
                                        @if($payment->status === 'Paid')
                                            <span class="text-slate-800 font-bold text-[9px]">|</span>
                                            <a href="{{ route('school.payments.receipt', $payment->id) }}"
                                                class="text-indigo-400 hover:text-indigo-300 font-bold text-[9px] uppercase tracking-wider flex items-center gap-0.5">
                                                Receipt
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                {{-- Collapsible Candidates List --}}
                                <div x-show="expanded" x-transition
                                    class="pt-3 mt-3 border-t border-slate-850 text-[10px] text-slate-400 space-y-2 bg-slate-900/30 p-2.5 rounded-xl max-h-36 overflow-y-auto"
                                    style="display: none;">
                                    @foreach($payment->students as $student)
                                        <div
                                            class="flex justify-between items-center bg-slate-950/20 p-1.5 rounded border border-slate-850/60">
                                            <span
                                                class="text-slate-350 truncate font-semibold max-w-[110px]">{{ $student->name }}</span>
                                            <span class="text-slate-500 font-mono text-[9px]">{{ $student->class->code }} •
                                                ₹{{ number_format($student->pivot->amount, 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-600 text-xs font-semibold">No transactions recorded yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-800/60 text-center">
                    <a href="{{ route('school.payments.transactions') }}"
                        class="w-full inline-flex items-center justify-center gap-2 bg-slate-950/80 hover:bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white font-bold py-2.5 px-4 rounded-xl text-[11px] uppercase tracking-wider transition-all duration-200 cursor-pointer">
                        View More Activity
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="w-3 h-3 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection