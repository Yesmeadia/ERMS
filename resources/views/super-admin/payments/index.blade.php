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

    {{-- KPI cards + Speedometer --}}
    @php
        $gaugeTotal = $totalCollected + $totalOutstanding;
        $gaugePct = $gaugeTotal > 0 ? round(($totalCollected / $gaugeTotal) * 100, 1) : 0;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-5 mb-8">
        {{-- Card 1: Total Collected --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Collected</p>
            <h3 class="text-2xl font-extrabold text-emerald-400 mt-1.5">₹{{ number_format($totalCollected, 2) }}</h3>
            <p class="text-[10px] text-slate-500 mt-3 font-semibold uppercase tracking-wider">Gross revenue</p>
        </div>

        {{-- Card 2: Base Fees Collected --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group">
            <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-400">Base Fees Collected</p>
            <h3 class="text-2xl font-extrabold text-indigo-300 mt-1.5">₹{{ number_format($totalBaseCollected, 2) }}</h3>
            <p class="text-[10px] text-slate-500 mt-3 font-semibold uppercase tracking-wider">Base registration fees</p>
        </div>

        {{-- Card 3: Fine Amount Collected --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group">
            <p class="text-[11px] font-bold uppercase tracking-wider text-amber-400">Fine Amount Collected</p>
            <h3 class="text-2xl font-extrabold text-amber-400 mt-1.5">₹{{ number_format($totalFineCollected, 2) }}</h3>
            <p class="text-[10px] text-slate-500 mt-3 font-semibold uppercase tracking-wider">Late fine penalties</p>
        </div>

        {{-- Card 4: Total Outstanding --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group">
            <p class="text-[11px] font-bold uppercase tracking-wider text-rose-400">Total Outstanding</p>
            <h3 class="text-2xl font-extrabold text-rose-400 mt-1.5">₹{{ number_format($totalOutstanding, 2) }}</h3>
            <p class="text-[10px] text-slate-500 mt-3 font-semibold uppercase tracking-wider">Base: ₹{{ number_format($totalOutstandingBase, 0) }} · Fine: ₹{{ number_format($totalOutstandingFine, 0) }}</p>
        </div>

        {{-- Card 5: Active Schools --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Schools Paid / Volume</p>
            <h3 class="text-2xl font-extrabold text-white mt-1.5">{{ $activeSchoolsPaid }} <span class="text-xs text-slate-400 font-normal">({{ $paymentsCount }} txns)</span></h3>
            <p class="text-[10px] text-slate-500 mt-3 font-semibold uppercase tracking-wider">Contributing schools</p>
        </div>

        {{-- Speedometer card --}}
        <div
            class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-3 flex flex-col items-center justify-center relative overflow-hidden">
            <div class="relative" style="width:160px;height:85px">
                <canvas id="speedometerCanvas" width="160" height="85" style="width:160px;height:85px"></canvas>
            </div>
            <div class="flex items-center gap-2 mt-1">
                <span class="inline-flex items-center gap-1 text-[8px] font-bold text-emerald-400"><span
                        class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>Collected</span>
                <span class="inline-flex items-center gap-1 text-[8px] font-bold text-rose-400"><span
                        class="w-1.5 h-1.5 rounded-full bg-rose-400 inline-block"></span>Outstanding</span>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-8">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Filter Transactions</h3>
        <form method="GET" action="{{ route('admin.payments.index') }}"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <div>
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
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Date</label>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
            </div>
            <div class="flex gap-2 justify-end mt-2">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-all cursor-pointer">Filter</button>
                @if(request()->hasAny(['search', 'school_id', 'status', 'date']))
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
                        <th class="px-4 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">
                            Candidates</th>
                        <th class="px-4 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">
                            Base Fee</th>
                        <th class="px-4 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right text-amber-400">
                            Fine Amount</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">
                            Total Paid</th>
                        <th class="px-4 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">
                            Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">
                            Actions</th>
                    </tr>
                </thead>
                @forelse($payments as $payment)
                    @php
                        $computedBaseFee = $payment->base_amount > 0 ? $payment->base_amount : max(0, $payment->amount - $payment->fine_amount);
                    @endphp
                    <tbody class="divide-y divide-slate-800/40 border-b border-slate-800/40" x-data="{ expanded: false }">
                        <tr class="hover:bg-slate-800/10 transition-colors">
                            <td class="px-6 py-4 text-slate-300">
                                {{ ($payment->paid_at ?? $payment->created_at)->format('d M Y, h:i A') }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-200">{{ $payment->school->name }}</p>
                                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mt-0.5">Code:
                                    {{ $payment->school->code }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-indigo-400 font-mono font-bold text-xs">{{ $payment->transaction_id ?? $payment->cashfree_order_id ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-4 text-center font-mono font-semibold text-slate-300">
                                {{ $payment->students_count ?? $payment->students->count() }}
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-slate-300 font-mono">
                                ₹{{ number_format($computedBaseFee, 2) }}</td>
                            <td class="px-4 py-4 text-right font-bold text-amber-400 font-mono">
                                ₹{{ number_format($payment->fine_amount, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-100 font-mono">
                                ₹{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
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
                                <div class="flex items-center justify-end gap-2">
                                    @if($payment->status === 'Paid')
                                        <a href="{{ route('admin.payments.receipt', $payment->id) }}"
                                            class="text-emerald-400 hover:text-emerald-300 font-semibold text-xs inline-flex items-center gap-1.5 cursor-pointer bg-emerald-500/10 hover:bg-emerald-500/20 px-3 py-1.5 rounded-lg border border-emerald-500/20 transition-all"
                                            title="View / Print Receipt">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3h7.5M6.75 21h10.5a2.25 2.25 0 002.25-2.25V7.5a2.25 2.25 0 00-2.25-2.25H8.25A2.25 2.25 0 006 7.5v11.25A2.25 2.25 0 006.75 21z" />
                                            </svg>
                                            Receipt
                                        </a>
                                    @endif
                                    <button type="button" @click="expanded = !expanded"
                                        class="text-indigo-400 hover:text-indigo-300 font-semibold text-xs inline-flex items-center gap-0.5 cursor-pointer bg-indigo-600/10 hover:bg-indigo-600/20 px-3 py-1.5 rounded-lg border border-indigo-500/15 transition-all">
                                        <span x-text="expanded ? 'Hide List' : 'View Candidates'">View Candidates</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2.5" stroke="currentColor" class="w-3 h-3 transition-transform"
                                            :class="expanded ? 'rotate-180' : ''">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Expanded Row for Candidates breakdown --}}
                        <tr x-show="expanded" style="display: none;" class="bg-slate-950/40 font-normal">
                            <td colspan="9" class="px-8 py-4 border-l-4 border-indigo-500">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Candidates included
                                    in Transaction ({{ $payment->students->count() }}):</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-56 overflow-y-auto pr-2">
                                    @forelse($payment->students as $student)
                                        <div
                                            class="flex items-center gap-2 bg-slate-900/60 border border-slate-800/40 p-2 rounded-xl text-xs">
                                            <img src="{{ $student->photo_url }}" alt="{{ $student->name }}"
                                                class="w-6 h-6 rounded-lg object-cover shrink-0">
                                            <div class="min-w-0 flex-1">
                                                <p class="font-semibold text-slate-200 truncate">{{ $student->name }}</p>
                                                <p class="text-[9px] text-slate-500 font-mono truncate">Class:
                                                    {{ $student->class->name ?? '—' }} · Base: ₹{{ number_format($student->pivot->base_amount ?? $student->registration_fee, 0) }} · Fine: ₹{{ number_format($student->pivot->fine_amount ?? 0, 0) }}
                                                </p>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-500 italic col-span-3">No candidates linked to this transaction
                                            record.</p>
                                    @endforelse
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody class="divide-y divide-slate-800/40">
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center text-slate-500">No payment transactions found.</td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>
        @if($payments->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/60">{{ $payments->links() }}</div>
        @endif
    </div>
    @push('scripts')
        <script @nonce>
            (function () {
                const targetPct = {{ $gaugePct }};
                const canvas = document.getElementById('speedometerCanvas');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                const W = canvas.width, H = canvas.height;
                const cx = W / 2, cy = H - 14;
                const R = Math.min(W, (H - 18) * 2) / 2;

                // 0% = Math.PI (left, 9 o'clock), 50% = 1.5*PI (top, 12 o'clock), 100% = 2*PI (right, 3 o'clock)
                const pctToAngle = p => Math.PI + (p / 100) * Math.PI;

                function drawGauge(currentPct) {
                    ctx.clearRect(0, 0, W, H);

                    // 1. Color zones - Red (0-40%), Amber (40-70%), Green (70-100%)
                    const zones = [
                        { from: 0,  to: 40,  color: '#f43f5e' }, // Rose Red
                        { from: 40, to: 70,  color: '#f59e0b' }, // Amber Yellow
                        { from: 70, to: 100, color: '#10b981' }, // Emerald Green
                    ];

                    zones.forEach(z => {
                        const aStart = pctToAngle(z.from);
                        const aEnd = pctToAngle(z.to);
                        ctx.beginPath();
                        ctx.arc(cx, cy, R, aStart, aEnd, false); // false = clockwise in top half
                        ctx.lineWidth = 14;
                        ctx.strokeStyle = z.color;
                        ctx.stroke();
                    });

                    // 2. Active collection progress accent arc
                    if (currentPct > 0) {
                        const activeEnd = pctToAngle(currentPct);
                        ctx.beginPath();
                        ctx.arc(cx, cy, R - 9, Math.PI, activeEnd, false);
                        ctx.lineWidth = 3;
                        ctx.strokeStyle = '#38bdf8';
                        ctx.shadowBlur = 6;
                        ctx.shadowColor = '#38bdf8';
                        ctx.stroke();
                        ctx.shadowBlur = 0;
                    }

                    // 3. Ticks & Labels
                    [0, 25, 50, 75, 100].forEach(p => {
                        const a = pctToAngle(p);
                        const innerR = R - 8;
                        const outerR = R + 4;
                        ctx.beginPath();
                        ctx.moveTo(cx + innerR * Math.cos(a), cy + innerR * Math.sin(a));
                        ctx.lineTo(cx + outerR * Math.cos(a), cy + outerR * Math.sin(a));
                        ctx.lineWidth = 1.5;
                        ctx.strokeStyle = '#ffffff';
                        ctx.stroke();

                        const labelR = R - 20;
                        ctx.font = 'bold 8px monospace';
                        ctx.fillStyle = '#cbd5e1';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(p + '%', cx + labelR * Math.cos(a), cy + labelR * Math.sin(a));
                    });

                    // 4. Needle
                    const needleAngle = pctToAngle(currentPct);
                    const nLen = R - 4;
                    const nX = cx + nLen * Math.cos(needleAngle);
                    const nY = cy + nLen * Math.sin(needleAngle);

                    ctx.beginPath();
                    ctx.moveTo(cx, cy);
                    ctx.lineTo(nX, nY);
                    ctx.lineWidth = 3;
                    ctx.strokeStyle = '#ffffff';
                    ctx.shadowBlur = 8;
                    ctx.shadowColor = '#ffffff';
                    ctx.lineCap = 'round';
                    ctx.stroke();
                    ctx.shadowBlur = 0;

                    // 5. Hub
                    ctx.beginPath();
                    ctx.arc(cx, cy, 5, 0, Math.PI * 2);
                    ctx.fillStyle = '#ffffff';
                    ctx.shadowBlur = 8;
                    ctx.shadowColor = '#6366f1';
                    ctx.fill();
                    ctx.shadowBlur = 0;
                }

                let current = 0;
                const step = targetPct / 60;
                function animate() {
                    if (current < targetPct) {
                        current = Math.min(current + step, targetPct);
                        drawGauge(current);
                        requestAnimationFrame(animate);
                    } else {
                        drawGauge(targetPct);
                    }
                }
                setTimeout(animate, 300);
            })();
        </script>
    @endpush
@endsection