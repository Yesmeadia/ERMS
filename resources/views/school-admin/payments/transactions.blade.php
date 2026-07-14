@extends('layouts.app')

@section('page_title', 'Payment History & Activities')

@section('content')

    {{-- ─── Page Header & Back button ─── --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('school.payments.index') }}"
                class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:bg-slate-800 hover:text-white transition-all shadow-lg cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-extrabold text-white tracking-tight mt-1.5">Transaction Activity Ledger</h2>
            </div>
        </div>
        <p class="text-xs text-slate-400">View and verify all registration transaction records initiated by your school.</p>
    </div>

    {{-- ─── Main Ledger Card ─── --}}
    <div class="bg-slate-900/40 backdrop-blur-md border border-slate-800/60 rounded-3xl shadow-xl overflow-hidden"
        x-data="{ activeRow: null }">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 py-5 border-b border-slate-800/60 bg-slate-950/20">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-200 flex items-center gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="w-4 h-4 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                All Recorded Payments
            </h3>
            <span
                class="text-[10px] text-slate-500 font-bold bg-slate-800/60 px-2.5 py-1 rounded border border-slate-700/50">
                Total count: {{ $payments->total() }} records
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-950/10">
                        <th class="py-3 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Transaction /
                            Order ID</th>
                        <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Date &
                            Time</th>
                        <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">
                            Candidates</th>
                        <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Method
                        </th>
                        <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Status
                        </th>
                        <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Amount
                            (INR)</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/60">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-slate-800/15 transition-all duration-150">
                            <td class="py-4 px-6">
                                <span class="font-mono font-bold text-slate-200 block truncate max-w-[200px]"
                                    title="{{ $payment->transaction_id ?? $payment->cashfree_order_id }}">
                                    {{ $payment->transaction_id ?? ($payment->cashfree_order_id ?? 'Pending...') }}
                                </span>
                                @if($payment->cashfree_order_id && $payment->transaction_id !== $payment->cashfree_order_id)
                                    <span class="text-[9px] text-slate-500 font-mono mt-0.5 block">Order:
                                        {{ $payment->cashfree_order_id }}</span>
                                @endif
                            </td>
                            <td class="py-4 text-center">
                                <span
                                    class="text-slate-300 block font-semibold">{{ $payment->created_at->format('d M Y') }}</span>
                                <span
                                    class="text-[9px] text-slate-500 font-medium block mt-0.5">{{ $payment->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="py-4 text-center">
                                <span
                                    class="inline-flex items-center justify-center min-w-[2rem] text-xs font-black font-mono text-indigo-400 bg-indigo-500/8 border border-indigo-500/15 px-2 py-0.5 rounded-lg">
                                    {{ $payment->students_count }}
                                </span>
                            </td>
                            <td class="py-4 text-center">
                                <span
                                    class="text-[10px] font-bold text-slate-400 bg-slate-800/60 px-2 py-0.5 rounded border border-slate-700/50">
                                    {{ $payment->payment_method ?? 'Cashfree' }}
                                </span>
                            </td>
                            <td class="py-4 text-center">
                                @if($payment->status === 'Paid')
                                    <span
                                        class="inline-flex items-center text-[9px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-500/10 border border-emerald-500/15 px-2.5 py-0.5 rounded-full">
                                        Paid
                                    </span>
                                @elseif($payment->status === 'Pending')
                                    <span
                                        class="inline-flex items-center text-[9px] font-bold uppercase tracking-wider text-amber-400 bg-amber-500/10 border border-amber-500/15 px-2.5 py-0.5 rounded-full">
                                        Pending
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center text-[9px] font-bold uppercase tracking-wider text-rose-400 bg-rose-500/10 border border-rose-500/15 px-2.5 py-0.5 rounded-full">
                                        Failed
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 text-right font-black font-mono text-slate-100 text-xs">
                                ₹{{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="py-4 px-6 text-right shrink-0">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                        @click="activeRow = activeRow === {{ $payment->id }} ? null : {{ $payment->id }}"
                                        class="text-indigo-400 hover:text-indigo-300 font-bold text-[10px] uppercase tracking-wider flex items-center gap-0.5 cursor-pointer">
                                        <span
                                            x-text="activeRow === {{ $payment->id }} ? 'Hide' : 'Candidates'">Candidates</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2.5" stroke="currentColor" class="w-3 h-3 transition-transform"
                                            :class="activeRow === {{ $payment->id }} ? 'rotate-180' : ''">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                    @if($payment->status === 'Paid')
                                        <span class="text-slate-800 font-bold">|</span>
                                        <a href="{{ route('school.payments.receipt', $payment->id) }}"
                                            class="text-emerald-400 hover:text-emerald-300 font-bold text-[10px] uppercase tracking-wider flex items-center gap-0.5">
                                            Receipt
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Expanded Row for Candidates list --}}
                        <tr x-show="activeRow === {{ $payment->id }}" x-transition style="display: none;"
                            class="bg-slate-950/20 border-b border-slate-850/60">
                            <td colspan="7" class="py-4 px-6">
                                <div class="bg-slate-950/60 border border-slate-850 p-4 rounded-2xl">
                                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-3">Paid
                                        Registration Candidates ({{ $payment->students->count() }})</p>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        @foreach($payment->students as $student)
                                            <div
                                                class="flex items-center justify-between gap-3 bg-slate-900/50 rounded-xl px-3 py-2.5 border border-slate-850/60">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <div
                                                        class="w-6 h-6 rounded-lg bg-indigo-500/10 border border-indigo-500/15 flex items-center justify-center shrink-0">
                                                        <span
                                                            class="text-[9px] font-black text-indigo-400">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <span
                                                            class="text-xs font-bold text-slate-200 truncate block">{{ $student->name }}</span>
                                                        <span
                                                            class="text-[9px] text-slate-500 font-mono block mt-0.5">{{ $student->registration_number ?? 'No Reg No.' }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <span
                                                        class="text-[9px] font-mono text-slate-400 bg-slate-800/80 px-1.5 py-0.5 rounded border border-slate-700/50 block">{{ $student->class->name ?? '—' }}</span>
                                                    <span
                                                        class="text-xs font-bold font-mono text-emerald-400 block mt-1">₹{{ number_format($student->pivot->amount, 0) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                No payment transactions recorded for this school yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/60 bg-slate-950/20 pagination-sm">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

@endsection