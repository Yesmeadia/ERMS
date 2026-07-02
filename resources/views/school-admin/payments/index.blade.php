@extends('layouts.app')

@section('page_title', 'Payments & Balance Sheet')

@section('content')
{{-- ─── Page Header ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-xl font-extrabold text-white tracking-tight">Financial Dashboard</h2>
        <p class="text-xs text-slate-400 mt-1">Track payouts, check class-wise outstanding registrations, and view transaction receipts.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-950/40 border border-emerald-800/40 text-emerald-200 rounded-2xl text-xs font-semibold shadow-lg shadow-emerald-950/20">
        {{ session('success') }}
    </div>
@endif

{{-- ─── KPI Cards Ribbon ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    {{-- Card 1: Registered --}}
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/60 rounded-2xl p-5 shadow-xl relative overflow-hidden group hover:border-slate-700 transition-all duration-300">
        <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-indigo-500/5 rounded-full blur-xl group-hover:bg-indigo-500/10 transition-all"></div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Registered Candidates</p>
        <h3 class="text-3xl font-black text-white mt-3 tracking-tight">{{ $totalRegistered }}</h3>
        <div class="mt-4 flex gap-4 text-xs font-bold">
            <span class="text-emerald-400 bg-emerald-500/5 px-2.5 py-1 rounded-lg border border-emerald-500/10">{{ $totalPaidCount }} Paid</span>
            <span class="text-rose-400 bg-rose-500/5 px-2.5 py-1 rounded-lg border border-rose-500/10">{{ $totalOutstandingCount }} Unpaid</span>
        </div>
    </div>

    {{-- Card 2: Received --}}
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/60 rounded-2xl p-5 shadow-xl relative overflow-hidden group hover:border-slate-700 transition-all duration-300">
        <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-emerald-500/5 rounded-full blur-xl group-hover:bg-emerald-500/10 transition-all"></div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Fees Paid</p>
        <h3 class="text-3xl font-black text-emerald-400 mt-3 tracking-tight">₹{{ number_format($totalPaidAmount, 2) }}</h3>
        <p class="text-xs text-slate-400 mt-4 font-medium">Successful credit card transactions</p>
    </div>

    {{-- Card 3: Pending --}}
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/60 rounded-2xl p-5 shadow-xl relative overflow-hidden group hover:border-slate-700 transition-all duration-300">
        <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-rose-500/5 rounded-full blur-xl group-hover:bg-rose-500/10 transition-all"></div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Outstanding Balance</p>
        <h3 class="text-3xl font-black text-rose-450 mt-3 tracking-tight">₹{{ number_format($totalOutstandingAmount, 2) }}</h3>
        <p class="text-xs text-slate-400 mt-4 font-medium">Awaiting payment for drafts</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Balance Sheet (Left side - Span 2) --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-slate-900/40 backdrop-blur-md border border-slate-800/60 rounded-2xl p-6 shadow-2xl">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-indigo-400"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                Class-wise Balance Sheet
            </h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-slate-800/60 bg-slate-950/20">
                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Class Name</th>
                            <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Fee (INR)</th>
                            <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Candidates</th>
                            <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center text-emerald-450">Paid</th>
                            <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center text-rose-455">Unpaid</th>
                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Outstanding</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50">
                        @forelse($balanceSheet as $item)
                            <tr class="hover:bg-slate-800/20 border-b border-slate-800/20 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-slate-200">{{ $item['class_name'] }}</td>
                                <td class="py-3.5 text-center text-slate-350 font-mono text-xs">₹{{ number_format($item['fee'], 0) }}</td>
                                <td class="py-3.5 text-center text-slate-400 font-mono font-semibold">{{ $item['total_count'] }}</td>
                                <td class="py-3.5 text-center text-emerald-400 font-bold font-mono">{{ $item['paid_count'] }}</td>
                                <td class="py-3.5 text-center text-rose-450 font-bold font-mono">{{ $item['unpaid_count'] }}</td>
                                <td class="py-3.5 px-4 text-right font-bold text-rose-450 font-mono">₹{{ number_format($item['unpaid_amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">No student registrations found to display sheet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($balanceSheet) > 0)
                        <tfoot>
                            <tr class="border-t border-slate-850 font-black bg-slate-950/30 text-slate-200">
                                <td class="py-4 px-4 uppercase tracking-wider text-[10px] font-bold text-slate-400">Total Summary</td>
                                <td class="py-4 text-center text-slate-500">&mdash;</td>
                                <td class="py-4 text-center font-mono text-slate-300">{{ $totalRegistered }}</td>
                                <td class="py-4 text-center font-mono text-emerald-400">{{ $totalPaidCount }}</td>
                                <td class="py-4 text-center font-mono text-rose-450">{{ $totalOutstandingCount }}</td>
                                <td class="py-4 px-4 text-right font-mono text-rose-450">₹{{ number_format($totalOutstandingAmount, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Payment Log (Right side - Span 1) --}}
    <div class="lg:col-span-1">
        <div class="bg-slate-900/40 backdrop-blur-md border border-slate-800/60 rounded-2xl p-6 shadow-2xl h-full flex flex-col justify-between">
            <div>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-indigo-400"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                    Recent Transactions
                </h3>

                <div class="space-y-4 max-h-[480px] overflow-y-auto pr-1">
                    @forelse($payments as $payment)
                        <div class="bg-slate-950/20 border border-slate-850 p-4 rounded-xl space-y-3" x-data="{ expanded: false }">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs font-mono font-bold text-indigo-400">{{ $payment->transaction_id }}</p>
                                    <p class="text-[10px] text-slate-500 mt-1 font-medium">{{ $payment->created_at->format('d M Y, h:i A') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-black text-emerald-400 font-mono">₹{{ number_format($payment->amount, 0) }}</p>
                                    <span class="inline-flex items-center text-[9px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/15 mt-1">Paid</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between text-xs text-slate-400 border-t border-slate-800/60 pt-2.5">
                                <span>Candidates: <strong class="text-slate-200">{{ $payment->students_count }}</strong></span>
                                <div class="flex gap-2">
                                    <button type="button" @click="expanded = !expanded" class="text-slate-400 hover:text-slate-200 font-semibold text-[10px] uppercase tracking-wider flex items-center gap-0.5 cursor-pointer">
                                        <span x-text="expanded ? 'Hide' : 'List'">List</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-180' : ''"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                    </button>
                                    <span class="text-slate-800">|</span>
                                    <a href="{{ route('school.payments.receipt', $payment->id) }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-[10px] uppercase tracking-wider flex items-center gap-1 cursor-pointer">
                                        Receipt
                                    </a>
                                </div>
                            </div>

                            {{-- Expanded list --}}
                            <div x-show="expanded" x-transition class="pt-2 border-t border-slate-850 text-[11px] text-slate-400 space-y-1.5 bg-slate-900/30 p-2 rounded-lg max-h-40 overflow-y-auto" style="display: none;">
                                @foreach($payment->students as $student)
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-300 truncate max-w-[120px] font-semibold">{{ $student->name }}</span>
                                        <span class="text-slate-500 font-mono font-medium">{{ $student->class->code }} · ₹{{ number_format($student->pivot->amount, 0) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-600 text-xs font-semibold">No transactions recorded yet.</div>
                    @endforelse
                </div>
            </div>

            @if($payments->hasPages())
                <div class="mt-4 pt-4 border-t border-slate-850 pagination-sm">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
