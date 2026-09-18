@extends('layouts.app')

@section('page_title', 'WebRTC & Proctoring Diagnostics: ' . $exam->name)

@section('content')
<div class="space-y-6 pb-20">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.online-exams.live', $exam) }}"
                   class="text-xs text-indigo-400 hover:text-indigo-300 font-medium inline-flex items-center gap-1.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Back to Live Proctoring Monitor</span>
                </a>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl sm:text-3xl font-bold text-white">WebRTC & Proctoring Diagnostics</h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 border border-amber-500/30 text-amber-400">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    Live Diagnostic Suite
                </span>
            </div>
            <p class="text-xs text-slate-400 font-mono mt-1">
                {{ $exam->name }} ({{ $exam->code }}) &bull; Enrolled: {{ $totalEnrolled }} &bull; Active Sessions: {{ $sessionsCount }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="loadAllDiagnostics()"
                    class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-all flex items-center gap-2 shadow-lg shadow-indigo-600/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span>Run Full Diagnostics</span>
            </button>
            <a href="{{ route('admin.online-exams.live', $exam) }}"
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition-colors">
                Return to Live View
            </a>
        </div>
    </div>

    <!-- Quick Diagnostics Summary Badges -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Badge 1: Metered TURN -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">TURN Relay (Metered)</span>
                <span id="badgeTurnPill" class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono {{ $hasTurnRelay ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                    {{ $hasTurnRelay ? 'TURN ACTIVE' : 'NO RELAY' }}
                </span>
            </div>
            <div class="mt-3">
                <div class="text-lg font-bold text-white font-mono" id="badgeTurnDetail">
                    {{ count($iceServers) }} ICE Servers
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5" id="badgeTurnSubtext">
                    {{ $hasTurnRelay ? 'Relay candidates active' : 'STUN only (Direct P2P may fail on NAT)' }}
                </div>
            </div>
        </div>

        <!-- Badge 2: Signaling Queue -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">WebRTC Signaling Bridge</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-sky-500/20 text-sky-400 border border-sky-500/30">
                    DATABASE QUEUE
                </span>
            </div>
            <div class="mt-3">
                <div class="text-lg font-bold text-sky-400 font-mono" id="badgeSignalsCount">
                    {{ $signalsCount }} Total Signals
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    Signaling exchange for this exam
                </div>
            </div>
        </div>

        <!-- Badge 3: Audit Events -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Anti-Cheating Events</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-amber-500/20 text-amber-400 border border-amber-500/30">
                    AUDIT LOG
                </span>
            </div>
            <div class="mt-3">
                <div class="text-lg font-bold text-amber-400 font-mono" id="badgeEventsCount">
                    {{ $eventsCount }} Recorded Events
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    Tab switches, blur & strikes
                </div>
            </div>
        </div>

        <!-- Badge 4: Live Polling Health -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Live Monitor Poll API</span>
                <span id="badgePollStatus" class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                    READY TO PROBE
                </span>
            </div>
            <div class="mt-3">
                <div class="text-lg font-bold text-white font-mono" id="badgePollLatency">
                    -- ms
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5" id="badgePollSubtext">
                    Testing endpoint latency
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Interactive Browser-Side WebRTC & ICE Gathering Tester -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800/80 pb-4 mb-4">
            <div>
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <span>Interactive Browser WebRTC & ICE Gathering Test</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Directly tests camera access, STUN resolution, and Metered TURN relay candidate allocation in this browser.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="runWebRtcBrowserTest()"
                        id="btnRunWebRtcTest"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition-all flex items-center gap-2 shadow-lg shadow-emerald-600/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z" />
                    </svg>
                    <span>Start WebRTC Test</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Video Preview & Test Controls -->
            <div class="space-y-4">
                <div class="relative bg-slate-950 rounded-2xl border border-slate-800 aspect-video overflow-hidden flex items-center justify-center">
                    <video id="debugLocalVideo" autoplay playsinline muted class="w-full h-full object-cover hidden"></video>
                    <div id="debugVideoPlaceholder" class="text-center p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-700 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <p class="text-xs text-slate-500 font-medium">Click "Start WebRTC Test" to test camera & ICE</p>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">Media Stream (getUserMedia)</span>
                        <span id="resMediaStream" class="font-mono font-bold text-slate-500">Not Tested</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">STUN Candidates (srflx)</span>
                        <span id="resStunCandidates" class="font-mono font-bold text-slate-500">0 gathered</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">TURN Relay Candidates (relay)</span>
                        <span id="resTurnCandidates" class="font-mono font-bold text-slate-500">0 gathered</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">ICE Gathering State</span>
                        <span id="resIceGatheringState" class="font-mono font-bold text-slate-500">new</span>
                    </div>
                </div>
            </div>

            <!-- Right: Candidate Stream Console -->
            <div class="lg:col-span-2 flex flex-col">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-300">Live ICE Candidate Log</span>
                    <span id="debugCandidateTimer" class="text-[11px] font-mono text-slate-500">Elapsed: 0.0s</span>
                </div>
                <div id="debugCandidateLog" class="flex-1 min-h-[220px] max-h-[300px] overflow-y-auto bg-slate-950 rounded-2xl border border-slate-800 p-3 font-mono text-[11px] space-y-1 text-slate-400">
                    <div class="text-slate-600">// Press "Start WebRTC Test" to start local capture and examine candidate allocation...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Server-Side Metered TURN & API Probe -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card 2A: Metered Configuration & Cache -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.62 1.022 1.072 1.395A8.995 8.995 0 0012 21a8.995 8.995 0 004.603-1.882c.452-.373.825-.845 1.072-1.395.401-.89.732-1.821.985-2.783m-6.66 0a24.255 24.255 0 010-9.18m0 9.18c.81 0 1.604-.043 2.376-.128m0-8.924A24.47 24.47 0 0012 3c-.81 0-1.604.043-2.376.128m6.66 0c.688.06 1.386.09 2.09.09h.75a4.5 4.5 0 010 9h-.75c-.704 0-1.402.03-2.09.09m0-9.18a24.255 24.255 0 010 9.18" />
                    </svg>
                    <span>Metered TURN Server Settings (.env)</span>
                </h2>
                <button type="button" onclick="probeMeteredApi()"
                        id="btnProbeMetered"
                        class="px-3 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-md shadow-sky-600/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                    <span>Probe Metered API</span>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-slate-400">WEBRTC_TURN_ENABLED</span>
                    <span class="font-mono font-bold {{ $webrtcConfig['turn_enabled'] ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ $webrtcConfig['turn_enabled'] ? 'true (Active)' : 'false (DISABLED in .env)' }}
                    </span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-slate-400">METERED_DOMAIN</span>
                    <span class="font-mono text-slate-200">{{ $webrtcConfig['metered_domain'] }}</span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-slate-400">METERED_PROJECT_API_KEY (for GET)</span>
                    <span class="font-mono {{ $webrtcConfig['has_project_api_key'] ? 'text-emerald-400' : 'text-slate-500' }}">
                        {{ $webrtcConfig['project_api_key_masked'] }}
                    </span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-slate-400">METERED_SECRET_KEY (for POST)</span>
                    <span class="font-mono {{ $webrtcConfig['has_secret_key'] ? 'text-emerald-400' : 'text-slate-500' }}">
                        {{ $webrtcConfig['secret_key_masked'] }}
                    </span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-slate-400">Cached Credentials</span>
                    <span class="font-mono text-indigo-300">
                        {{ $cachedInfo['is_cached'] ? ($cachedInfo['server_count'] . ' servers (' . round($cachedInfo['seconds_remaining'] / 3600, 1) . 'h left)') : 'None (Cold Cache)' }}
                    </span>
                </div>
            </div>

            <!-- Probe Result Box -->
            <div id="meteredProbeContainer" class="hidden p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-white">Live API Probe Result</span>
                    <span id="meteredProbePill" class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"></span>
                </div>
                <p id="meteredProbeSummary" class="text-xs text-slate-300 font-mono"></p>
                <div id="meteredProbeRaw" class="max-h-36 overflow-y-auto text-[11px] font-mono p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400"></div>
                <div id="meteredProbeFix" class="hidden p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs"></div>
            </div>
        </div>

        <!-- Card 2B: Generated ICE Server Config -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                    </svg>
                    <span>Current ICE Servers Dispatched to Clients</span>
                </h2>
                <span class="text-xs font-mono text-slate-400">{{ count($iceServers) }} Total</span>
            </div>

            <div class="max-h-[260px] overflow-y-auto space-y-2 pr-1">
                @forelse($iceServers as $server)
                    @php
                        $urls = (array) ($server['urls'] ?? []);
                        $firstUrl = $urls[0] ?? '';
                        $isTurn = str_starts_with($firstUrl, 'turn:') || str_starts_with($firstUrl, 'turns:');
                    @endphp
                    <div class="p-2.5 rounded-xl bg-slate-950 border {{ $isTurn ? 'border-emerald-500/30 bg-emerald-950/10' : 'border-slate-800' }} text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-semibold {{ $isTurn ? 'text-emerald-400' : 'text-sky-400' }}">
                                {{ $isTurn ? 'TURN RELAY' : 'STUN DIRECT' }}
                            </span>
                            @if($isTurn && isset($server['username']))
                                <span class="font-mono text-[10px] text-slate-400">User: {{ $server['username'] }}</span>
                            @endif
                        </div>
                        <div class="mt-1 font-mono text-[11px] text-slate-300 break-all">
                            @foreach($urls as $u)
                                <div>&bull; {{ $u }}</div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-rose-400 text-xs font-mono">
                        ⚠ Warning: No ICE servers configured. WebRTC connections will fail completely.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Section 3: Sessions & Live Status Card Reconciliation Matrix -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-3">
            <div>
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                    </svg>
                    <span>Session Status & Card Reconciliation Matrix</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Verifies why candidates appear under Terminated, Violated, Active, or Not Started in the Live Monitor.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="syncViolationsCount()"
                        id="btnSyncViolations"
                        class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-md shadow-amber-600/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Reconcile Violations Log</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-mono text-[11px]">
                        <th class="py-2.5 px-3">Candidate</th>
                        <th class="py-2.5 px-3">Session ID</th>
                        <th class="py-2.5 px-3 text-center">DB Status</th>
                        <th class="py-2.5 px-3 text-center">Card Mapping</th>
                        <th class="py-2.5 px-3 text-center">Violations (DB vs Log)</th>
                        <th class="py-2.5 px-3 text-center">Last Heartbeat</th>
                        <th class="py-2.5 px-3 text-center">Camera Status</th>
                        <th class="py-2.5 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="debugSessionsTableBody" class="divide-y divide-slate-800/60 font-mono text-[11px]">
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">Loading session matrix...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 4: WebRTC Signaling Queue Inspector -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 4A: WebRTC Signals Queue -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                    <span>Signaling Queue (Offers & Answers)</span>
                </h2>
                <span class="text-xs font-mono text-slate-400">Latest 50</span>
            </div>

            <div class="max-h-[300px] overflow-y-auto space-y-2 pr-1 font-mono text-[11px]" id="signalsListContainer">
                <div class="text-center py-8 text-slate-500">Loading signals...</div>
            </div>
        </div>

        <!-- 4B: Audit Events Timeline -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span>Live Audit Event Stream</span>
                </h2>
                <span class="text-xs font-mono text-slate-400">Latest 50</span>
            </div>

            <div class="max-h-[300px] overflow-y-auto space-y-2 pr-1 font-mono text-[11px]" id="eventsListContainer">
                <div class="text-center py-8 text-slate-500">Loading events...</div>
            </div>
        </div>
    </div>
</div>

<script @nonce>
    const DIAGNOSTICS_DATA_URL = "{{ route('admin.online-exams.debugger.data', $exam) }}";
    const TEST_METERED_URL = "{{ route('admin.online-exams.debugger.test-metered', $exam) }}";
    const TEST_SIGNAL_URL = "{{ route('admin.online-exams.debugger.test-signal', $exam) }}";
    const TEST_EVENT_URL = "{{ route('admin.online-exams.debugger.test-event', $exam) }}";
    const SYNC_VIOLATIONS_URL = "{{ route('admin.online-exams.debugger.sync-violations', $exam) }}";
    const POLL_API_URL = "{{ route('admin.online-exams.live.poll', $exam) }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";

    const RTC_CONFIG = {
        iceServers: @json($iceServers)
    };

    let browserPeerConnection = null;
    let localStream = null;
    let iceCandidateTimer = null;
    let iceStartTime = 0;

    async function loadAllDiagnostics() {
        testLivePollApi();
        try {
            const res = await fetch(DIAGNOSTICS_DATA_URL, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            renderSessionsMatrix(data.sessions || []);
            renderSignalsQueue(data.recent_signals || []);
            renderEventsLog(data.recent_events || []);
            updateBadges(data);
        } catch (err) {
            console.error('Failed loading diagnostics:', err);
        }
    }

    async function testLivePollApi() {
        const start = performance.now();
        const badge = document.getElementById('badgePollStatus');
        const latEl = document.getElementById('badgePollLatency');
        const subEl = document.getElementById('badgePollSubtext');

        try {
            const res = await fetch(POLL_API_URL, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const elapsed = Math.round(performance.now() - start);
            latEl.textContent = `${elapsed} ms`;

            if (res.ok) {
                const data = await res.json();
                badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
                badge.textContent = `200 OK (${elapsed}ms)`;
                subEl.textContent = `Active: ${data.stats?.in_progress || 0} | Terminated: ${data.stats?.terminated || 0} | Violations: ${data.stats?.violations_total || 0}`;
            } else {
                badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-rose-500/20 text-rose-400 border border-rose-500/30';
                badge.textContent = `HTTP ${res.status} ERROR`;
                subEl.textContent = `Endpoint failed. Live page cannot update cards.`;
            }
        } catch (err) {
            badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-rose-500/20 text-rose-400 border border-rose-500/30';
            badge.textContent = 'NETWORK FAIL';
            latEl.textContent = 'ERR';
            subEl.textContent = err.message;
        }
    }

    function updateBadges(data) {
        if (data.turn) {
            const pill = document.getElementById('badgeTurnPill');
            const det = document.getElementById('badgeTurnDetail');
            const sub = document.getElementById('badgeTurnSubtext');
            if (data.turn.has_turn_relay) {
                pill.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
                pill.textContent = 'TURN ACTIVE';
                det.textContent = `${data.turn.turn_servers.length} Relay Servers`;
                sub.textContent = 'Relay candidate allocation ready';
            } else {
                pill.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-rose-500/20 text-rose-400 border border-rose-500/30';
                pill.textContent = 'NO RELAY';
                det.textContent = `${data.turn.stun_servers.length} STUN Servers Only`;
                sub.textContent = 'Symmetric NAT traversal will fail!';
            }
        }
        if (data.recent_signals) {
            document.getElementById('badgeSignalsCount').textContent = `${data.recent_signals.length} Signals`;
        }
        if (data.recent_events) {
            document.getElementById('badgeEventsCount').textContent = `${data.recent_events.length} Events`;
        }
    }

    function renderSessionsMatrix(sessions) {
        const tbody = document.getElementById('debugSessionsTableBody');
        if (!sessions.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="py-6 text-center text-slate-500">No session rows found in database for this exam.</td></tr>';
            return;
        }

        tbody.innerHTML = sessions.map(s => {
            const statusColor = s.is_terminated ? 'text-rose-400' : (s.is_completed ? 'text-indigo-400' : (s.is_online ? 'text-emerald-400' : 'text-slate-400'));
            const violMismatch = s.has_violations_mismatch;
            return `
                <tr class="hover:bg-slate-800/30 transition-colors">
                    <td class="py-2.5 px-3">
                        <div class="font-bold text-white text-xs">${s.student_name}</div>
                        <div class="text-[10px] text-slate-400">${s.registration_number}</div>
                    </td>
                    <td class="py-2.5 px-3 font-mono text-slate-300">${s.session_id}</td>
                    <td class="py-2.5 px-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${statusColor} bg-slate-800">
                            ${s.status}
                        </span>
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <span class="text-xs font-semibold ${s.is_terminated ? 'text-rose-400' : (s.is_completed ? 'text-indigo-400' : (s.is_online ? 'text-emerald-400' : 'text-slate-400'))}">
                            ${s.is_terminated ? 'Terminated Card' : (s.is_completed ? 'Completed Card' : (s.is_online ? 'Active Now Card' : 'Attended/Offline'))}
                        </span>
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <span class="px-2 py-0.5 rounded ${violMismatch ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold' : 'text-slate-300'}">
                            DB: ${s.violations_count} | Log: ${s.log_violations_count}
                            ${violMismatch ? ' ⚠ MISMATCH' : ''}
                        </span>
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <span class="${s.is_online ? 'text-emerald-400 font-bold' : 'text-slate-500'}">
                            ${s.last_heartbeat_ago}
                        </span>
                    </td>
                    <td class="py-2.5 px-3 text-center font-mono text-[10px] ${s.camera_status === 'ACTIVE' ? 'text-emerald-400' : 'text-slate-500'}">
                        ${s.camera_status}
                    </td>
                    <td class="py-2.5 px-3 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button" onclick="sendTestSignal(${s.session_id})"
                                    class="px-2 py-1 rounded bg-sky-600/20 text-sky-300 hover:bg-sky-600/40 text-[10px] transition-colors"
                                    title="Send diagnostic signaling ping">
                                Ping Signal
                            </button>
                            <button type="button" onclick="sendTestEvent(${s.session_id}, 'TAB_SWITCH')"
                                    class="px-2 py-1 rounded bg-amber-600/20 text-amber-300 hover:bg-amber-600/40 text-[10px] transition-colors"
                                    title="Simulate Tab Switch Event">
                                Test Violation
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderSignalsQueue(signals) {
        const c = document.getElementById('signalsListContainer');
        if (!signals.length) {
            c.innerHTML = '<div class="text-center py-6 text-slate-500">No signals found in online_exam_webrtc_signals.</div>';
            return;
        }

        c.innerHTML = signals.map(s => `
            <div class="p-2 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between gap-2">
                <div>
                    <span class="font-bold text-white">#${s.id} [${s.type.toUpperCase()}]</span>
                    <span class="text-slate-400"> ${s.sender_type} &rarr; ${s.recipient_type}</span>
                    <div class="text-[10px] text-slate-500 mt-0.5">Sess: ${s.session_id} (${s.student_name})</div>
                </div>
                <div class="text-right">
                    <span class="px-1.5 py-0.5 rounded text-[10px] ${s.is_consumed ? 'bg-slate-800 text-slate-400' : 'bg-emerald-500/20 text-emerald-400'}">
                        ${s.is_consumed ? 'Consumed' : 'Pending'}
                    </span>
                    <div class="text-[10px] text-slate-500 mt-0.5">${s.created_at}</div>
                </div>
            </div>
        `).join('');
    }

    function renderEventsLog(events) {
        const c = document.getElementById('eventsListContainer');
        if (!events.length) {
            c.innerHTML = '<div class="text-center py-6 text-slate-500">No events found in online_exam_session_events.</div>';
            return;
        }

        c.innerHTML = events.map(e => `
            <div class="p-2 rounded-xl bg-slate-950 border ${e.is_violation ? 'border-rose-500/30 bg-rose-950/10' : 'border-slate-800'} flex items-center justify-between gap-2">
                <div>
                    <span class="font-bold ${e.is_violation ? 'text-rose-400' : 'text-slate-200'}">${e.event_type}</span>
                    ${e.is_violation ? '<span class="px-1 py-0.2 rounded text-[9px] bg-rose-500/20 text-rose-400 font-bold ml-1">STRIKE</span>' : ''}
                    <div class="text-[10px] text-slate-400 mt-0.5">${e.student_name} (${e.registration_number})</div>
                </div>
                <div class="text-right text-[10px] text-slate-500">
                    <div>${e.event_time}</div>
                    <div>${e.event_time_ago}</div>
                </div>
            </div>
        `).join('');
    }

    // In-Browser WebRTC Interactive Tester
    async function runWebRtcBrowserTest() {
        const btn = document.getElementById('btnRunWebRtcTest');
        const log = document.getElementById('debugCandidateLog');
        const resMedia = document.getElementById('resMediaStream');
        const resStun = document.getElementById('resStunCandidates');
        const resTurn = document.getElementById('resTurnCandidates');
        const resIce = document.getElementById('resIceGatheringState');
        const timerEl = document.getElementById('debugCandidateTimer');
        const video = document.getElementById('debugLocalVideo');
        const placeholder = document.getElementById('debugVideoPlaceholder');

        btn.disabled = true;
        btn.innerHTML = '<span>Testing ICE...</span>';
        log.innerHTML = '<div class="text-indigo-400">// Starting WebRTC Browser Diagnostic Session...</div>';

        let stunCount = 0;
        let turnCount = 0;
        let hostCount = 0;

        if (browserPeerConnection) {
            try { browserPeerConnection.close(); } catch (e) {}
            browserPeerConnection = null;
        }

        iceStartTime = performance.now();
        if (iceCandidateTimer) clearInterval(iceCandidateTimer);
        iceCandidateTimer = setInterval(() => {
            const elapsed = ((performance.now() - iceStartTime) / 1000).toFixed(1);
            timerEl.textContent = `Elapsed: ${elapsed}s`;
        }, 100);

        // 1. Test getUserMedia (Camera Access)
        try {
            log.innerHTML += '<div>[1/4] Requesting camera stream via getUserMedia...</div>';
            localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            resMedia.className = 'font-mono font-bold text-emerald-400';
            resMedia.textContent = 'PASS (Stream Active)';
            video.srcObject = localStream;
            video.classList.remove('hidden');
            placeholder.classList.add('hidden');
            log.innerHTML += '<div class="text-emerald-400">&check; Camera accessed successfully. Resolution: ' + (video.videoWidth || 'ready') + '</div>';
        } catch (camErr) {
            resMedia.className = 'font-mono font-bold text-amber-400';
            resMedia.textContent = 'Camera Blocked (' + camErr.name + ')';
            log.innerHTML += '<div class="text-amber-400">⚠ Camera access issue: ' + camErr.message + ' (Testing ICE without video tracks...)</div>';
        }

        // 2. Initialize RTCPeerConnection with current RTC_CONFIG
        try {
            log.innerHTML += '<div>[2/4] Initializing RTCPeerConnection with ' + RTC_CONFIG.iceServers.length + ' ICE servers...</div>';
            const pc = new RTCPeerConnection(RTC_CONFIG);
            browserPeerConnection = pc;

            if (localStream) {
                localStream.getTracks().forEach(t => pc.addTrack(t, localStream));
            } else {
                pc.addTransceiver('video', { direction: 'recvonly' });
            }

            pc.onicegatheringstatechange = () => {
                resIce.textContent = pc.iceGatheringState;
                log.innerHTML += `<div>[ICE] Gathering state &rarr; <span class="text-indigo-300 font-bold">${pc.iceGatheringState}</span></div>`;
                if (pc.iceGatheringState === 'complete') {
                    clearInterval(iceCandidateTimer);
                    finalizeIceResults(stunCount, turnCount, hostCount);
                }
            };

            pc.onicecandidate = (event) => {
                if (!event.candidate) {
                    log.innerHTML += '<div class="text-indigo-400">[ICE] All candidates gathered (&lt;null candidate&gt;)</div>';
                    return;
                }
                const c = event.candidate;
                const type = c.type; // 'host', 'srflx', 'relay'
                const proto = c.protocol ? c.protocol.toUpperCase() : 'UDP';
                const address = c.address || c.ip || 'hidden';
                const port = c.port;

                if (type === 'relay') {
                    turnCount++;
                    resTurn.textContent = `${turnCount} gathered`;
                    resTurn.className = 'font-mono font-bold text-emerald-400';
                    log.innerHTML += `<div class="text-emerald-400 font-bold">&check; [RELAY - TURN SUCCESS] ${proto} ${address}:${port}</div>`;
                } else if (type === 'srflx') {
                    stunCount++;
                    resStun.textContent = `${stunCount} gathered`;
                    resStun.className = 'font-mono font-bold text-sky-400';
                    log.innerHTML += `<div class="text-sky-400">&check; [SRFLX - STUN] ${proto} ${address}:${port}</div>`;
                } else {
                    hostCount++;
                    log.innerHTML += `<div class="text-slate-500">[HOST] ${proto} ${address}:${port}</div>`;
                }
                log.scrollTop = log.scrollHeight;
            };

            // 3. Create Offer to start candidate gathering
            log.innerHTML += '<div>[3/4] Creating local WebRTC offer to trigger ICE candidate gathering...</div>';
            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);

            // Safety timeout: 7 seconds
            setTimeout(() => {
                if (pc.iceGatheringState !== 'complete') {
                    clearInterval(iceCandidateTimer);
                    log.innerHTML += '<div class="text-amber-400">⏱ 7s timeout reached. Finalizing results...</div>';
                    finalizeIceResults(stunCount, turnCount, hostCount);
                }
            }, 7000);

        } catch (err) {
            log.innerHTML += '<div class="text-rose-400 font-bold">&cross; PeerConnection Error: ' + err.message + '</div>';
            btn.disabled = false;
            btn.innerHTML = '<span>Start WebRTC Test</span>';
        }
    }

    function finalizeIceResults(stunCount, turnCount, hostCount) {
        const btn = document.getElementById('btnRunWebRtcTest');
        const log = document.getElementById('debugCandidateLog');
        btn.disabled = false;
        btn.innerHTML = '<span>Restart WebRTC Test</span>';

        log.innerHTML += '<div class="border-t border-slate-800 my-2"></div>';
        log.innerHTML += `<div class="font-bold text-white">[4/4] ICE Diagnostic Summary: Host: ${hostCount} | STUN (srflx): ${stunCount} | TURN (relay): ${turnCount}</div>`;

        if (turnCount > 0) {
            log.innerHTML += '<div class="text-emerald-400 font-bold text-xs mt-1">&check; EXCELLENT: Metered TURN relay is operational! Candidates behind strict 4G/5G, university, and symmetric NAT firewalls will successfully stream video.</div>';
        } else if (stunCount > 0) {
            log.innerHTML += '<div class="text-amber-300 font-bold text-xs mt-1">⚠ WARNING: Only STUN candidates were gathered. No TURN relay candidate was obtained. Video will FAIL if students or proctors are on symmetric NAT, 4G/5G hotspots, or restrictive firewalls. Check Metered TURN credentials in .env!</div>';
        } else {
            log.innerHTML += '<div class="text-rose-400 font-bold text-xs mt-1">&cross; CRITICAL: No public STUN or TURN candidates gathered. WebRTC P2P cannot establish connections. Verify outbound UDP/TCP ports and STUN server reachability.</div>';
        }
        log.scrollTop = log.scrollHeight;
    }

    // Server-side Metered API Probe
    async function probeMeteredApi() {
        const btn = document.getElementById('btnProbeMetered');
        const container = document.getElementById('meteredProbeContainer');
        const pill = document.getElementById('meteredProbePill');
        const summary = document.getElementById('meteredProbeSummary');
        const raw = document.getElementById('meteredProbeRaw');
        const fix = document.getElementById('meteredProbeFix');

        btn.disabled = true;
        btn.innerHTML = '<span>Probing...</span>';
        container.classList.remove('hidden');
        pill.textContent = 'CONNECTING...';
        pill.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-indigo-500/20 text-indigo-400';
        summary.textContent = 'Sending real-time HTTP requests to Metered API from PHP server...';
        raw.textContent = '';
        fix.classList.add('hidden');

        try {
            const res = await fetch(TEST_METERED_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();

            if (data.overall_status === 'PASS') {
                pill.textContent = 'PASS';
                pill.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
                summary.textContent = data.summary;
            } else {
                pill.textContent = 'FAILED';
                pill.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30';
                summary.textContent = data.summary;
            }

            raw.textContent = JSON.stringify(data, null, 2);

            if (data.recommended_fix) {
                fix.textContent = 'Recommendation: ' + data.recommended_fix;
                fix.classList.remove('hidden');
            }
        } catch (err) {
            pill.textContent = 'ERROR';
            pill.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-rose-500/20 text-rose-400';
            summary.textContent = 'Diagnostic request error: ' + err.message;
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>Probe Metered API</span>';
        }
    }

    // Send Test Signal
    async function sendTestSignal(sessionId) {
        try {
            const res = await fetch(TEST_SIGNAL_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ session_id: sessionId, type: 'test_ping' })
            });
            const data = await res.json();
            alert(data.message || 'Signal sent');
            loadAllDiagnostics();
        } catch (err) {
            alert('Failed sending signal: ' + err.message);
        }
    }

    // Send Test Event
    async function sendTestEvent(sessionId, eventType) {
        try {
            const res = await fetch(TEST_EVENT_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ session_id: sessionId, event_type: eventType })
            });
            const data = await res.json();
            alert(data.message + ` Violations count: ${data.initial_violations} &rarr; ${data.new_violations_count}`);
            loadAllDiagnostics();
        } catch (err) {
            alert('Failed sending event: ' + err.message);
        }
    }

    // Reconcile Violations Count
    async function syncViolationsCount() {
        const btn = document.getElementById('btnSyncViolations');
        btn.disabled = true;
        btn.innerHTML = '<span>Syncing...</span>';
        try {
            const res = await fetch(SYNC_VIOLATIONS_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            alert(data.message);
            loadAllDiagnostics();
        } catch (err) {
            alert('Sync failed: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>Reconcile Violations Log</span>';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadAllDiagnostics();
    });
</script>
@endsection
