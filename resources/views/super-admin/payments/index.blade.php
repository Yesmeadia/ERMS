@extends('layouts.app')
@section('page_title', 'Payouts & Payments')
@section('page_description', 'Monitor school payouts, track collected fees, and view financial audits.')
@section('content')
    {{-- Header Area --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <p class="text-sm text-slate-400 mt-0.5">Monitor school payouts, track collected fees, and view financial
                audits.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.payments.export', request()->all()) }}"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export CSV Report
            </a>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Card 1 --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Fees Collected</p>
            <h3 class="text-3xl font-extrabold text-emerald-400 mt-2">₹{{ number_format($totalCollected, 2) }}</h3>
            <p class="text-[10px] text-slate-500 mt-4 font-semibold uppercase tracking-wider">Net board collections</p>
        </div>

        {{-- Card 2 --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Outstanding Balance</p>
            <h3 class="text-3xl font-extrabold text-rose-400 mt-2">₹{{ number_format($totalOutstanding, 2) }}</h3>
            <p class="text-[10px] text-slate-500 mt-4 font-semibold uppercase tracking-wider">Outstanding from unpaid drafts
            </p>
        </div>

        {{-- Card 3 --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Transaction Volume</p>
            <h3 class="text-3xl font-extrabold text-indigo-400 mt-2">{{ $paymentsCount }}</h3>
            <p class="text-[10px] text-slate-500 mt-4 font-semibold uppercase tracking-wider">Completed transactions</p>
        </div>

        {{-- Card 4 --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Contributing Schools</p>
            <h3 class="text-3xl font-extrabold text-white mt-2">{{ $activeSchoolsPaid }}</h3>
            <p class="text-[10px] text-slate-500 mt-4 font-semibold uppercase tracking-wider">Schools with completed
                payments</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-8">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Filter Transactions</h3>
        <form method="GET" action="{{ route('admin.payments.index') }}"
            class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div class="sm:col-span-1">
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Search Transaction</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="TXN ID..."
                    class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">School</label>
                <select name="school_id"
                    class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                    <option value="">All Schools</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" @selected(request('school_id') == $school->id)>{{ $school->name }}
                            ({{ $school->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Status</label>
                <select name="status"
                    class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="Paid" @selected(request('status') === 'Paid')>Paid</option>
                    <option value="Pending" @selected(request('status') === 'Pending')>Pending</option>
                    <option value="Failed" @selected(request('status') === 'Failed')>Failed</option>
                </select>
            </div>
            <div class="flex gap-2 justify-end mt-2">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-all cursor-pointer">Filter</button>
                @if(request()->hasAny(['search', 'school_id', 'status']))
                    <a href="{{ route('admin.payments.index') }}"
                        class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium px-5 py-2.5 rounded-xl transition-all flex items-center justify-center">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Transactions table --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden mb-12">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-800/60">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">School details
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Transaction ID
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">
                            Candidates</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">
                            Amount (INR)</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">
                            Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">
                            Breakdown</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-slate-800/10 transition-colors" x-data="{ expanded: false }">
                            <td class="px-6 py-4 text-slate-300">{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-200">{{ $payment->school->name }}</p>
                                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mt-0.5">Code:
                                    {{ $payment->school->code }}</p>
                            </td>
                            <td class="px-6 py-4 text-indigo-400 font-mono font-bold text-xs">{{ $payment->transaction_id }}
                            </td>
                            <td class="px-6 py-4 text-center font-mono font-semibold text-slate-300">
                                {{ $payment->students_count ?? $payment->students()->count() }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-200 font-mono">
                                ₹{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($payment->status === 'Paid')
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Paid
                                    </span>
                                @elseif($payment->status === 'Pending')
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Pending
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        Failed
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <button type="button" @click="expanded = !expanded"
                                    class="text-indigo-400 hover:text-indigo-300 font-semibold text-xs inline-flex items-center gap-0.5 cursor-pointer bg-indigo-600/10 hover:bg-indigo-600/20 px-3 py-1.5 rounded-lg border border-indigo-500/15 transition-all">
                                    <span x-text="expanded ? 'Hide List' : 'View Candidates'">View Candidates</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                        stroke="currentColor" class="w-3 h-3 transition-transform"
                                        :class="expanded ? 'rotate-180' : ''">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        {{-- Expanded Row for Candidates breakdown --}}
                        <tr x-show="expanded" style="display: none;" class="bg-slate-950/40">
                            <td colspan="7" class="px-8 py-4 border-l-4 border-indigo-500">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Candidates included
                                    in Transaction:</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-56 overflow-y-auto pr-2">
                                    @foreach($payment->students as $student)
                                        <div
                                            class="flex items-center gap-2 bg-slate-900/60 border border-slate-800/40 p-2 rounded-xl text-xs">
                                            <img src="{{ $student->photo_url }}" alt="{{ $student->name }}"
                                                class="w-6 h-6 rounded-lg object-cover shrink-0">
                                            <div class="min-w-0 flex-1">
                                                <p class="font-semibold text-slate-200 truncate">{{ $student->name }}</p>
                                                <p class="text-[9px] text-slate-500 font-mono truncate">Class:
                                                    {{ $student->class->name ?? '—' }} · Fee:
                                                    ₹{{ number_format($student->pivot->amount, 0) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-slate-500">No payment transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/60">{{ $payments->links() }}</div>
        @endif
    </div>
@endsection