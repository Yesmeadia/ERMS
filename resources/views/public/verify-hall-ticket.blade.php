<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hall Ticket Verification — ERMS</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #07090f;
            --surface: rgba(13, 18, 30, 0.75);
            --border:  rgba(255,255,255,0.06);
            --text:    #e2e8f0;
            --muted:   #64748b;
        }

        html, body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Outfit', sans-serif;
            color: var(--text);
            overflow-x: hidden;
        }

        /* Background */
        .bg-wrap { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .bg-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(0,212,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,212,255,0.025) 1px, transparent 1px);
            background-size: 48px 48px;
        }
        .orb {
            position: absolute; border-radius: 50%; filter: blur(80px);
            animation: orbDrift 18s ease-in-out infinite alternate;
        }
        .orb-1 { width: 480px; height: 480px; background: rgba(99,102,241,0.1);  top: -100px; left: -80px; }
        .orb-2 { width: 400px; height: 400px; background: rgba(168,85,247,0.08); bottom: -60px; right: -80px; animation-delay: -9s; }
        @keyframes orbDrift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(30px,-40px) scale(1.08); }
        }

        /* Layout */
        .page { position: relative; z-index: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* Nav */
        .nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; height: 64px;
            background: rgba(7,9,15,0.8); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 50;
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo   { width: 34px; height: 34px; border-radius: 9px; object-fit: contain; }
        .nav-logo-fallback { width: 34px; height: 34px; border-radius: 9px; background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.3); display: flex; align-items: center; justify-content: center; color: #818cf8; font-weight: 800; font-size: 16px; }
        .nav-name { font-size: 16px; font-weight: 800; background: linear-gradient(135deg,#fff,#a5b4fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .nav-sub  { font-size: 9px; color: var(--muted); font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; }

        .nav-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 10px;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            color: #94a3b8; font-size: 13px; font-weight: 600; text-decoration: none;
            transition: all 0.2s;
        }
        .nav-back:hover { background: rgba(255,255,255,0.09); color: #e2e8f0; border-color: rgba(255,255,255,0.12); }

        /* Main content */
        .main {
            flex: 1; display: flex; align-items: flex-start; justify-content: center;
            padding: 48px 24px 64px;
        }
        .container { width: 100%; max-width: 740px; }

        /* Animation */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.7s cubic-bezier(0.16,1,0.3,1) both; }
        .fade-up-2 { animation: fadeUp 0.7s 0.1s cubic-bezier(0.16,1,0.3,1) both; }
        .fade-up-3 { animation: fadeUp 0.7s 0.2s cubic-bezier(0.16,1,0.3,1) both; }

        /* Status header */
        .status-banner {
            border-radius: 20px; padding: 32px 28px; margin-bottom: 28px;
            display: flex; align-items: flex-start; gap: 20px;
            border: 1px solid; position: relative; overflow: hidden;
        }
        .status-banner::before {
            content: ''; position: absolute; inset: 0; border-radius: 20px;
            background: radial-gradient(ellipse at top left, rgba(255,255,255,0.04), transparent 60%);
            pointer-events: none;
        }
        .status-banner.verified   { background: rgba(5,46,22,0.4);  border-color: rgba(22,163,74,0.35); }
        .status-banner.not-found  { background: rgba(69,10,10,0.4); border-color: rgba(220,38,38,0.35); }
        .status-banner.pending    { background: rgba(30,27,75,0.4); border-color: rgba(99,102,241,0.35); }

        .status-icon {
            width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .status-icon.verified  { background: rgba(22,163,74,0.15); border: 1px solid rgba(22,163,74,0.3); color: #4ade80; }
        .status-icon.not-found { background: rgba(220,38,38,0.15); border: 1px solid rgba(220,38,38,0.3); color: #f87171; }
        .status-icon.pending   { background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.3); color: #a5b4fc; }

        .status-title  { font-size: 20px; font-weight: 800; letter-spacing: -0.01em; margin-bottom: 4px; }
        .status-desc   { font-size: 13.5px; line-height: 1.55; }
        .color-green   { color: #4ade80; }
        .color-red     { color: #f87171; }
        .color-indigo  { color: #a5b4fc; }
        .color-muted   { color: var(--muted); }

        /* Candidate card */
        .candidate-card {
            background: var(--surface);
            backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
            border: 1px solid var(--border);
            border-radius: 20px; overflow: hidden; margin-bottom: 20px;
        }

        /* Card header */
        .card-header {
            display: flex; align-items: center; gap: 20px;
            padding: 24px 28px;
            border-bottom: 1px solid var(--border);
            background: rgba(99,102,241,0.04);
        }
        .candidate-photo {
            width: 72px; height: 72px; border-radius: 14px; object-fit: cover;
            border: 2px solid rgba(99,102,241,0.3); flex-shrink: 0;
        }
        .candidate-name { font-size: 22px; font-weight: 800; color: #fff; letter-spacing: -0.01em; margin-bottom: 4px; }
        .candidate-reg  { font-size: 12px; color: var(--muted); font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }

        .status-pill {
            margin-left: auto; display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; border-radius: 100px; font-size: 12px; font-weight: 700;
            flex-shrink: 0;
        }
        .status-pill.approved    { background: rgba(22,163,74,0.15); color: #4ade80; border: 1px solid rgba(22,163,74,0.3); }
        .status-pill.issued      { background: rgba(99,102,241,0.15); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); }
        .status-pill.pending-st  { background: rgba(251,191,36,0.1); color: #fbbf24; border: 1px solid rgba(251,191,36,0.25); }
        .status-pill.rejected-st { background: rgba(220,38,38,0.12); color: #f87171; border: 1px solid rgba(220,38,38,0.25); }
        .status-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }

        /* Info grid */
        .info-grid {
            display: grid; grid-template-columns: repeat(2, 1fr);
            gap: 0;
        }
        @media (max-width: 560px) { .info-grid { grid-template-columns: 1fr; } }

        .info-item {
            padding: 18px 28px;
            border-bottom: 1px solid var(--border);
            border-right: 1px solid var(--border);
        }
        .info-item:nth-child(2n) { border-right: none; }
        .info-item:nth-last-child(-n+2) { border-bottom: none; }
        @media (max-width: 560px) {
            .info-item { border-right: none; }
            .info-item:last-child { border-bottom: none; }
            .info-item:nth-last-child(-n+2) { border-bottom: 1px solid var(--border); }
            .info-item:last-child { border-bottom: none; }
        }

        .info-label { font-size: 10.5px; font-weight: 700; color: var(--muted); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { font-size: 14.5px; font-weight: 600; color: #e2e8f0; }

        /* Hall Ticket detail */
        .ticket-box {
            background: var(--surface);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(99,102,241,0.25);
            border-radius: 20px; padding: 24px 28px;
            margin-bottom: 20px; position: relative; overflow: hidden;
        }
        .ticket-box::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,0.7), transparent);
        }
        .ticket-label { font-size: 10.5px; font-weight: 700; color: var(--muted); letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 6px; }
        .ticket-number {
            font-size: 26px; font-weight: 900; letter-spacing: 0.06em;
            background: linear-gradient(135deg, #a5b4fc, #c084fc);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .ticket-issued { font-size: 12px; color: var(--muted); margin-top: 4px; }

        /* Search again */
        .search-again {
            background: var(--surface);
            backdrop-filter: blur(18px);
            border: 1px solid var(--border);
            border-radius: 20px; padding: 24px 28px;
        }
        .search-title { font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 14px; }
        .search-form  { display: flex; gap: 10px; }
        .search-input {
            flex: 1; padding: 11px 16px; border-radius: 11px;
            background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08);
            color: #e2e8f0; font-size: 13.5px; font-family: 'Outfit', sans-serif; font-weight: 600;
            letter-spacing: 0.05em; text-transform: uppercase; outline: none;
            transition: border-color 0.2s;
        }
        .search-input:focus { border-color: rgba(99,102,241,0.5); }
        .search-input::placeholder { text-transform: none; letter-spacing: 0; font-weight: 400; color: #334155; }
        .search-btn {
            padding: 11px 22px; border-radius: 11px; cursor: pointer;
            background: rgba(99,102,241,0.2); border: 1px solid rgba(99,102,241,0.35); color: #a5b4fc;
            font-size: 13.5px; font-weight: 700; font-family: 'Outfit', sans-serif;
            transition: all 0.2s; white-space: nowrap;
        }
        .search-btn:hover { background: rgba(99,102,241,0.35); border-color: rgba(99,102,241,0.5); }

        /* Footer */
        .footer {
            padding: 20px 24px; border-top: 1px solid var(--border);
            background: rgba(7,9,15,0.5); text-align: center;
            font-size: 12px; color: var(--muted);
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 99px; }
    </style>
</head>
<body>

<div class="bg-wrap">
    <div class="bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
</div>

<div class="page">

    <!-- NAV -->
    <nav class="nav">
        <a class="nav-brand" href="{{ url('/') }}">
            @if(file_exists(public_path('logo-w.png')))
                <img src="{{ asset('logo-w.png') }}" alt="ERMS" class="nav-logo">
            @elseif(file_exists(public_path('icon.png')))
                <img src="{{ asset('icon.png') }}" alt="ERMS" class="nav-logo">
            @else
                <div class="nav-logo-fallback">E</div>
            @endif
            <div>
                <div class="nav-name">ERMS</div>
                <div class="nav-sub">Hall Ticket Verification</div>
            </div>
        </a>
        <a href="{{ url('/') }}" class="nav-back">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to Portal
        </a>
    </nav>

    <!-- MAIN -->
    <main class="main">
        <div class="container">

            @if(!$student)
                {{-- ═══════════════════════════════════ NOT FOUND ═══════════════════════════════════ --}}
                <div class="status-banner not-found fade-up">
                    <div class="status-icon not-found">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:26px;height:26px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                    </div>
                    <div>
                        <div class="status-title color-red">No Candidate Found</div>
                        <p class="status-desc color-muted">
                            No hall ticket or registration record matched <strong style="color:#e2e8f0;">{{ $number }}</strong>. Please double-check the number and try again.
                        </p>
                    </div>
                </div>

            @elseif($verified)
                {{-- ═══════════════════════════════════ VERIFIED ════════════════════════════════════ --}}
                <div class="status-banner verified fade-up">
                    <div class="status-icon verified">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:26px;height:26px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" /></svg>
                    </div>
                    <div>
                        <div class="status-title color-green">Verified &amp; Authenticated</div>
                        <p class="status-desc color-muted">This hall ticket is officially issued and board-verified. The candidate is cleared to appear for the examination.</p>
                    </div>
                </div>

                {{-- Hall Ticket Number Box --}}
                @if($student->hall_ticket_number)
                <div class="ticket-box fade-up-2">
                    <div class="ticket-label">Hall Ticket Number</div>
                    <div class="ticket-number">{{ $student->hall_ticket_number }}</div>
                    @if($student->hall_ticket_issued_at)
                        <div class="ticket-issued">Issued on {{ $student->hall_ticket_issued_at->format('d M Y, h:i A') }}</div>
                    @endif
                </div>
                @endif

                {{-- Candidate card --}}
                <div class="candidate-card fade-up-2">
                    <div class="card-header">
                        <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="candidate-photo">
                        <div style="flex:1;min-width:0;">
                            <div class="candidate-name">{{ $student->name }}</div>
                            {{-- Registration number intentionally omitted — public display (CWE-200) --}}
                            <div class="candidate-reg">Verified Candidate</div>
                        </div>
                        @php
                            $pillClass = match($student->status) {
                                'Hall Ticket Issued' => 'issued',
                                'Approved'           => 'approved',
                                'Rejected'           => 'rejected-st',
                                default              => 'pending-st',
                            };
                        @endphp
                        <div class="status-pill {{ $pillClass }}">
                            <span class="status-dot"></span>
                            {{ $student->status }}
                        </div>
                    </div>

                    <div class="info-grid">
                        {{-- Only non-sensitive fields displayed publicly (CWE-200 remediation) --}}
                        {{-- Father name, mother name, DOB, gender, registration number intentionally hidden --}}
                        <div class="info-item">
                            <div class="info-label">School</div>
                            <div class="info-value">{{ $student->school?->name ?? '—' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Class</div>
                            <div class="info-value">{{ $student->class?->name ?? '—' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Category</div>
                            <div class="info-value">{{ $student->category?->name ?? '—' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Examination</div>
                            <div class="info-value">{{ $student->examination?->name ?? '—' }}</div>
                        </div>
                    </div>

                    {{-- Privacy notice --}}
                    <div style="padding: 12px 28px; border-top: 1px solid var(--border); font-size: 11.5px; color: var(--muted); display: flex; align-items: center; gap: 7px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:13px;height:13px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                        Personal details (date of birth, parent names, registration number) are withheld from public display to protect candidate privacy.
                    </div>
                </div>

            @else
                {{-- ═══════════════════════════════════ FOUND BUT NOT VERIFIED ═══════════════════ --}}
                <div class="status-banner pending fade-up">
                    <div class="status-icon pending">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:26px;height:26px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                    <div>
                        <div class="status-title color-indigo">Registration Pending Approval</div>
                        <p class="status-desc color-muted">
                            A record for <strong style="color:#e2e8f0;">{{ $student->name }}</strong> was found, but the hall ticket has not yet been issued. Current status: <strong style="color:#e2e8f0;">{{ $student->status }}</strong>.
                        </p>
                    </div>
                </div>

                {{-- Partial info card --}}
                <div class="candidate-card fade-up-2">
                    <div class="card-header">
                        <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="candidate-photo">
                        <div>
                            <div class="candidate-name">{{ $student->name }}</div>
                            @if($student->registration_number)
                                <div class="candidate-reg">Reg. No. {{ $student->registration_number }}</div>
                            @endif
                        </div>
                        @php
                            $pillClass = match($student->status) {
                                'Hall Ticket Issued' => 'issued',
                                'Approved'           => 'approved',
                                'Rejected'           => 'rejected-st',
                                default              => 'pending-st',
                            };
                        @endphp
                        <div class="status-pill {{ $pillClass }}" style="margin-left:auto;">
                            <span class="status-dot"></span>
                            {{ $student->status }}
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">School</div>
                            <div class="info-value">{{ $student->school?->name ?? '—' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Examination</div>
                            <div class="info-value">{{ $student->examination?->name ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Search Again --}}
            <div class="search-again fade-up-3">
                <div class="search-title">Search Another Hall Ticket</div>
                <form class="search-form" action="{{ url('/verify/hall-ticket') }}" method="GET"
                    onsubmit="event.preventDefault(); var v=this.querySelector('input').value.trim(); if(v) window.location.href='/verify/hall-ticket/'+encodeURIComponent(v);">
                    <input type="text" class="search-input" placeholder="Enter Hall Ticket or Reg. Number" name="number">
                    <button type="submit" class="search-btn">Verify →</button>
                </form>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        &copy; {{ date('Y') }} YES INDIA FOUNDATION &nbsp;·&nbsp; All rights reserved
    </footer>

</div>

</body>
</html>
