@extends('layouts.app')

@section('page_title', 'Result Release Timer')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-3 mb-1">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center"
             style="background: rgba(168,85,247,0.15); border: 1px solid rgba(168,85,247,0.3);">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                 stroke="#c084fc" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-white tracking-tight">Result Release Timer</h1>
            <p class="text-xs text-slate-400">Control when the public results portal becomes accessible to students</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="mb-5 p-4 rounded-xl bg-emerald-950/40 border border-emerald-700/40 text-emerald-300 text-sm font-medium flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-5 p-4 rounded-xl bg-rose-950/40 border border-rose-800/40 text-rose-300 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

    {{-- ─── LEFT: Live Countdown Clock ─────────────────────────────── --}}
    <div class="xl:col-span-3 rounded-2xl border p-6 flex flex-col gap-5"
         style="background: linear-gradient(145deg, rgba(168,85,247,0.06), rgba(8,12,22,0.95)); border-color: rgba(168,85,247,0.2);">

        {{-- Status badge --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Live Timer Preview</span>
            <span id="status-badge"
                  class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold border
                         {{ $released ? 'bg-emerald-950/50 text-emerald-300 border-emerald-700/40' : 'bg-purple-950/50 text-purple-300 border-purple-700/40' }}">
                <span class="w-2 h-2 rounded-full {{ $released ? 'bg-emerald-400' : 'bg-purple-400' }}"
                      style="{{ !$released ? 'animation: timerPulse 1.4s ease-in-out infinite;' : '' }}"></span>
                <span id="status-label">{{ $released ? 'Results Released' : 'Counting Down' }}</span>
            </span>
        </div>

        {{-- Release time label --}}
        <div class="text-center">
            <p class="text-xs text-slate-500 mb-1 uppercase tracking-wider font-semibold">Scheduled Release</p>
            <p class="text-lg font-extrabold text-white" id="release-label">
                {{ $releaseIst->format('d M Y, h:i A') }} IST
            </p>
        </div>

        {{-- Countdown tiles --}}
        <div id="countdown-wrap" class="grid grid-cols-4 gap-3 {{ $released ? 'opacity-40' : '' }}">
            @foreach(['days' => 'Days', 'hours' => 'Hours', 'mins' => 'Mins', 'secs' => 'Secs'] as $id => $label)
                <div class="rounded-2xl text-center py-5 px-2"
                     style="background: rgba(168,85,247,0.08); border: 1px solid rgba(168,85,247,0.18);">
                    <div id="cd-{{ $id }}" class="text-4xl font-black text-white tabular-nums">--</div>
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-2">{{ $label }}</div>
                </div>
            @endforeach
        </div>

        {{-- Released banner --}}
        <div id="released-banner"
             class="hidden rounded-2xl py-5 text-center"
             style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.25);">
            <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <p class="text-emerald-300 font-bold text-lg">Results Are Live!</p>
            <p class="text-emerald-400/70 text-sm mt-1">The public results portal is now accessible to students.</p>
        </div>

        {{-- Current time display --}}
        <p class="text-center text-xs text-slate-600">
            Server time: <span id="server-time" class="text-slate-400 font-mono tabular-nums"></span> IST
        </p>
    </div>

    {{-- ─── RIGHT: Controls ──────────────────────────────────────────── --}}
    <div class="xl:col-span-2 flex flex-col gap-5">

        {{-- Update form --}}
        <div class="rounded-2xl border p-6"
             style="background: rgba(8,12,22,0.9); border-color: rgba(255,255,255,0.07);">
            <h2 class="text-sm font-extrabold text-white mb-1">Update Release Time</h2>
            <p class="text-xs text-slate-500 mb-4">Set the exact IST datetime when students can access their results.</p>

            <form method="POST" action="{{ route('admin.result-timer.update') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">
                        Release Datetime <span class="text-purple-400">(IST — Asia/Kolkata)</span>
                    </label>
                    <input type="datetime-local"
                           id="release_datetime_ist"
                           name="release_datetime_ist"
                           value="{{ $releaseIst->format('Y-m-d\TH:i') }}"
                           required
                           class="block w-full px-4 py-2.5 rounded-xl text-sm text-slate-100 font-mono
                                  bg-slate-950/60 border border-slate-700 focus:border-purple-500
                                  focus:ring-1 focus:ring-purple-500 focus:outline-none transition-all"
                           style="color-scheme: dark;">
                    <p class="text-xs text-slate-600 mt-1.5">
                        Will be stored as: <span id="utc-preview" class="text-slate-400 font-mono">{{ $releaseUtc->format('Y-m-d H:i') }} UTC</span>
                    </p>
                </div>

                <button type="submit"
                        class="w-full py-2.5 px-4 rounded-xl text-sm font-bold text-white transition-all
                               bg-purple-600 hover:bg-purple-500 active:bg-purple-700
                               shadow-lg shadow-purple-900/30">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor" class="w-4 h-4 inline-block mr-1.5 -mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    Save Release Time
                </button>
            </form>
        </div>

        {{-- Force Release --}}
        <div class="rounded-2xl border p-6"
             style="background: rgba(239,68,68,0.04); border-color: rgba(239,68,68,0.18);">
            <h2 class="text-sm font-extrabold text-white mb-1 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                     stroke="#f87171" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                </svg>
                Force Release Now
            </h2>
            <p class="text-xs text-slate-500 mb-4">Immediately makes results accessible, bypassing the scheduled time.</p>

            <form method="POST" action="{{ route('admin.result-timer.force-release') }}"
                  onsubmit="return confirm('Force-release results NOW? Students will immediately be able to access their marksheets.')">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 px-4 rounded-xl text-sm font-bold text-white transition-all
                               bg-rose-700 hover:bg-rose-600 active:bg-rose-800 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                    </svg>
                    Force Release Results Now
                </button>
            </form>
        </div>

        {{-- Info box --}}
        <div class="rounded-2xl border p-5 text-xs text-slate-500 space-y-2.5"
             style="border-color: rgba(255,255,255,0.05); background: rgba(255,255,255,0.02);">
            <div>
                The countdown timer is shown on the <strong class="text-slate-400">Home Page</strong> and <strong class="text-slate-400">Results Check Page</strong>. Students cannot access the result form until the timer expires.
            </div>
            <div>
                All times stored in <strong class="text-slate-400">UTC</strong> internally. The UI displays in <strong class="text-slate-400">IST (Asia/Kolkata, UTC+5:30)</strong>.
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes timerPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.35; transform: scale(0.65); }
    }
</style>

<script @nonce>
(function () {
    // Release time in UTC (from server)
    const releaseMs = new Date('{{ $releaseIso }}').getTime();

    const els = {
        days:    document.getElementById('cd-days'),
        hours:   document.getElementById('cd-hours'),
        mins:    document.getElementById('cd-mins'),
        secs:    document.getElementById('cd-secs'),
        wrap:    document.getElementById('countdown-wrap'),
        banner:  document.getElementById('released-banner'),
        badge:   document.getElementById('status-badge'),
        label:   document.getElementById('status-label'),
        server:  document.getElementById('server-time'),
    };

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
        const nowMs = Date.now();
        const diff  = releaseMs - nowMs;

        // Current IST time
        const nowIst = new Date(nowMs + 5.5 * 3600 * 1000);
        els.server.textContent = nowIst.toISOString().replace('T', ' ').substring(0, 19);

        if (diff <= 0) {
            // Released
            els.days.textContent  = '00';
            els.hours.textContent = '00';
            els.mins.textContent  = '00';
            els.secs.textContent  = '00';
            els.wrap.classList.add('opacity-40');
            els.banner.classList.remove('hidden');
            els.badge.classList.replace('bg-purple-950/50', 'bg-emerald-950/50');
            els.badge.classList.replace('text-purple-300', 'text-emerald-300');
            els.badge.classList.replace('border-purple-700/40', 'border-emerald-700/40');
            els.label.textContent = 'Results Released';
            return;
        }

        const days  = Math.floor(diff / 86400000);
        const hours = Math.floor((diff % 86400000) / 3600000);
        const mins  = Math.floor((diff % 3600000)  / 60000);
        const secs  = Math.floor((diff % 60000)    / 1000);

        els.days.textContent  = pad(days);
        els.hours.textContent = pad(hours);
        els.mins.textContent  = pad(mins);
        els.secs.textContent  = pad(secs);
    }

    tick();
    setInterval(tick, 1000);

    // ── UTC preview updater ──────────────────────────────────────────────
    const picker = document.getElementById('release_datetime_ist');
    const utcEl  = document.getElementById('utc-preview');

    function updateUtcPreview() {
        const istStr = picker.value; // "YYYY-MM-DDTHH:MM"
        if (!istStr) return;
        const istMs  = new Date(istStr).getTime(); // treated as LOCAL — but we need IST offset
        // datetime-local gives us local TZ; recalculate assuming IST (+5:30)
        const localOffsetMs = new Date().getTimezoneOffset() * 60000;
        const istOffsetMs   = -330 * 60000; // IST = UTC+5:30 = -330 min offset
        const utcMs         = new Date(istStr).getTime() + localOffsetMs + istOffsetMs;
        const utcDate       = new Date(utcMs);
        const utcStr        = utcDate.toISOString().replace('T', ' ').substring(0, 16);
        utcEl.textContent   = utcStr + ' UTC';
    }

    if (picker) {
        picker.addEventListener('input', updateUtcPreview);
        updateUtcPreview();
    }
})();
</script>
@endsection
