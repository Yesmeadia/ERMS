@extends('layouts.auth')

@section('page_title', 'Check Exam Results')
@section('page_description', 'Retrieve and download your official examination statement of marks and score card.')

@section('content')
@php
    // Safe fallbacks in case controller has not passed them or old controller is cached
    $released = $released ?? (class_exists(\App\Models\AppSetting::class) ? \App\Models\AppSetting::resultsReleased() : false);
    $releaseIso = $releaseIso ?? (class_exists(\App\Models\AppSetting::class) ? \App\Models\AppSetting::resultReleaseDatetime()->toIso8601String() : '2026-09-16T12:00:00Z');
    $releaseIst = $releaseIst ?? (class_exists(\App\Models\AppSetting::class) ? \App\Models\AppSetting::resultReleaseDatetime()->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') : '16 Sep 2026, 05:30 PM');
    $examinations = $examinations ?? \App\Models\Examination::where('status', 'result published')->get();
@endphp
<div>

@if(!$released)
    {{-- ─── COUNTDOWN GATE ────────────────────────────────────────────── --}}
    <div class="text-center mb-2">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold border mb-5"
             style="background: rgba(168,85,247,0.1); border-color: rgba(168,85,247,0.3); color: #c084fc;">
            <span class="w-2 h-2 rounded-full bg-purple-400" style="animation: cdPulse 1.4s ease-in-out infinite;"></span>
            Results Not Yet Released
        </div>
    </div>

    <h1 class="text-3xl font-extrabold text-white tracking-tight text-center mb-2">Results Releasing Soon</h1>
    <p class="text-sm text-slate-400 text-center mb-6">
        Your official marksheet will be available on <strong class="text-purple-300">{{ $releaseIst }} IST</strong>
    </p>

    {{-- Countdown tiles --}}
    <div class="grid grid-cols-4 gap-3 mb-6" id="cd-grid">
        @foreach(['days' => 'Days', 'hours' => 'Hours', 'mins' => 'Mins', 'secs' => 'Secs'] as $id => $label)
            <div class="rounded-2xl text-center py-5 px-2"
                 style="background: rgba(168,85,247,0.08); border: 1px solid rgba(168,85,247,0.18);">
                <div id="rc-{{ $id }}" class="text-3xl font-black text-white tabular-nums">--</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    {{-- Will appear after timer ends --}}
    <div id="rc-form-wrap" class="hidden">
        {{-- revealed by JS --}}
    </div>

    <p class="text-center text-xs text-slate-600 mt-4" id="rc-server-time">
        Current time: <span class="text-slate-400 font-mono tabular-nums" id="rc-clock"></span> IST
    </p>

@else
    {{-- ─── FORM (released) ─────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold border mb-4"
             style="background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.3); color: #6ee7b7;">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            Results Released — {{ $releaseIst }} IST
        </div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Check Exam Results</h1>
        <p class="text-sm text-slate-400 mt-1.5">Enter details below to access your marksheet</p>
    </div>

    @include('public.results._check_form')
@endif

</div>

@if(!$released)
<style>
    @keyframes cdPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.3; transform: scale(0.6); }
    }
</style>
<script @nonce>
(function () {
    const releaseMs = new Date('{{ $releaseIso }}').getTime();
    const grid      = document.getElementById('cd-grid');
    const formWrap  = document.getElementById('rc-form-wrap');
    const clockEl   = document.getElementById('rc-clock');

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
        const diff = releaseMs - Date.now();
        const nowIst = new Date(Date.now() + 5.5 * 3600 * 1000);
        if (clockEl) {
            clockEl.textContent = nowIst.toISOString().replace('T', ' ').substring(0, 19);
        }

        if (diff <= 0) {
            // Timer expired — reload page so the full form is shown
            window.location.reload();
            return;
        }

        const days  = Math.floor(diff / 86400000);
        const hours = Math.floor((diff % 86400000) / 3600000);
        const mins  = Math.floor((diff % 3600000)  / 60000);
        const secs  = Math.floor((diff % 60000)    / 1000);

        document.getElementById('rc-days').textContent  = pad(days);
        document.getElementById('rc-hours').textContent = pad(hours);
        document.getElementById('rc-mins').textContent  = pad(mins);
        document.getElementById('rc-secs').textContent  = pad(secs);
    }

    tick();
    setInterval(tick, 1000);
})();
</script>
@endif
@endsection
