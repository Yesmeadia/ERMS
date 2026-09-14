<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Privacy Policy of YASIN EDUCATION SERVICES INDIA FOUNDATION - Learn how we collect, protect, and manage student, school, and examination data for YES Genius National Level Talent Search.">
    <title>Privacy Policy | YASIN EDUCATION SERVICES INDIA FOUNDATION</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script @nonce defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #060810;
            --surface: rgba(12, 16, 28, 0.80);
            --surface-card: rgba(15, 23, 42, 0.65);
            --border: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(99, 102, 241, 0.35);
            --text: #e2e8f0;
            --muted: #94a3b8;
            --heading: #f8fafc;
            --indigo: #6366f1;
            --purple: #a855f7;
            --cyan: #06b6d4;
        }

        html {
            background: var(--bg);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.7;
        }

        [x-cloak] {
            display: none !important;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 99px;
        }

        /* ── BACKGROUND AMBIENCE ── */
        .bg-wrap {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
            background-size: 52px 52px;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.6;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: rgba(99, 102, 241, 0.12);
            top: -100px;
            left: -100px;
        }

        .orb-2 {
            width: 450px;
            height: 450px;
            background: rgba(168, 85, 247, 0.10);
            bottom: 10%;
            right: -100px;
        }

        /* ── PAGE LAYOUT ── */
        .page {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .legal-header {
            padding: 56px 24px 32px;
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 9999px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            letter-spacing: 0.02em;
        }

        .legal-title {
            font-size: clamp(2.2rem, 4vw, 3.2rem);
            font-weight: 800;
            color: var(--heading);
            letter-spacing: -0.02em;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .legal-title span {
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .legal-sub {
            font-size: 16px;
            color: var(--muted);
            max-width: 680px;
            margin: 0 auto 8px;
        }

        .legal-meta {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .container {
            max-width: 1040px;
            margin: 0 auto;
            padding: 0 24px 80px;
            width: 100%;
        }

        .legal-grid {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 40px;
            align-items: start;
        }

        @media (max-width: 860px) {
            .legal-grid {
                grid-template-columns: 1fr;
            }

            .toc-card {
                position: static !important;
            }
        }

        /* ── STICKY TOC ── */
        .toc-card {
            position: sticky;
            top: 96px;
            background: var(--surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
        }

        .toc-heading {
            font-size: 14px;
            font-weight: 700;
            color: var(--heading);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .toc-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .toc-link {
            display: block;
            padding: 8px 12px;
            border-radius: 10px;
            color: var(--muted);
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .toc-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        /* ── LEGAL CONTENT ── */
        .content-card {
            background: var(--surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 640px) {
            .content-card {
                padding: 24px;
            }
        }

        .section-block {
            margin-bottom: 40px;
            scroll-margin-top: 110px;
        }

        .section-block:last-child {
            margin-bottom: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }

        .icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #818cf8;
            flex-shrink: 0;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--heading);
            letter-spacing: -0.01em;
        }

        .section-body {
            color: var(--text);
            font-size: 15px;
        }

        .section-body p {
            margin-bottom: 14px;
            color: #cbd5e1;
        }

        .section-body p:last-child {
            margin-bottom: 0;
        }

        .feature-box {
            background: var(--surface-card);
            border: 1px solid var(--border);
            border-left: 3px solid var(--indigo);
            border-radius: 14px;
            padding: 18px 22px;
            margin: 18px 0;
        }

        .feature-box h4 {
            font-size: 15px;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 6px;
        }

        .feature-box p {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 0;
        }

        .info-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 16px 0;
        }

        .info-list li {
            position: relative;
            padding-left: 24px;
            font-size: 14.5px;
            color: #cbd5e1;
        }

        .info-list li::before {
            content: '';
            position: absolute;
            left: 4px;
            top: 10px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--indigo);
            box-shadow: 0 0 8px var(--indigo);
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 36px 0;
        }

        .org-card {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(168, 85, 247, 0.05) 100%);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 16px;
            padding: 24px;
            margin-top: 20px;
        }

        .org-name {
            font-size: 17px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }
    </style>
</head>

<body>
    <!-- BACKGROUND AMBIENCE -->
    <x-public-background />

    <div class="page" x-data="{ menuOpen: false }">
        <!-- NAVIGATION -->
        <x-public-nav page="privacy" />

        <!-- PAGE HEADER -->
        <header class="legal-header">
            <div class="badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" style="width:15px;height:15px;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                Legal Transparency & Data Protection
            </div>
            <h1 class="legal-title">Privacy <span>Policy</span></h1>
            <p class="legal-sub">
                YASIN EDUCATION SERVICES INDIA FOUNDATION is committed to safeguarding the privacy and confidentiality
                of candidate, parent, and institutional data.
            </p>
            <div class="legal-meta">
                Official Document &bull; Last Revised: {{ date('F d, Y') }}
            </div>
        </header>

        <!-- MAIN CONTAINER -->
        <main class="container">
            <div class="legal-grid">
                <!-- TABLE OF CONTENTS -->
                <aside class="toc-card">
                    <div class="toc-heading">On This Page</div>
                    <ul class="toc-list">
                        <li><a href="#overview" class="toc-link">1. Overview & Scope</a></li>
                        <li><a href="#information-collect" class="toc-link">2. Data We Collect</a></li>
                        <li><a href="#how-we-use" class="toc-link">3. How We Use Data</a></li>
                        <li><a href="#qr-privacy" class="toc-link">4. QR Code & Identity Privacy</a></li>
                        <li><a href="#security" class="toc-link">5. Data Protection & Security</a></li>
                        <li><a href="#third-parties" class="toc-link">6. Third Parties & Gateways</a></li>
                        <li><a href="#data-rights" class="toc-link">7. Rights & Retention</a></li>
                        <li><a href="#contact" class="toc-link">8. Foundation Contact</a></li>
                    </ul>
                </aside>

                <!-- POLICY CONTENT -->
                <article class="content-card">
                    <!-- SECTION 1 -->
                    <section id="overview" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m-0.002 0A11.95 11.95 0 0112 16.5c-3.17 0-6.07-1.233-8.216-3.247m0 0A8.959 8.959 0 013 12c0-.778.099-1.533.284-2.253" />
                                </svg>
                            </div>
                            <h2 class="section-title">1. Overview & Scope</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                This Privacy Policy outlines how <strong>YASIN EDUCATION SERVICES INDIA
                                    FOUNDATION</strong> ("Foundation", "We", "Us", or "Our") collects, uses, stores, and
                                protects personal and institutional information obtained through our examination
                                registration system, official portal, and the YES Genius National Level Talent Search
                                platform.
                            </p>
                            <p>
                                By accessing our web application, registering candidates through participating schools,
                                or utilizing our verification and examination portals, you acknowledge and agree to the
                                data collection and management practices described herein.
                            </p>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 2 -->
                    <section id="information-collect" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <h2 class="section-title">2. Information We Collect</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                To efficiently conduct nationwide competitive examinations, issue hall tickets, assign
                                exam centres, and publish verified results, we process the following categories of data:
                            </p>
                            <ul class="info-list">
                                <li><strong>Candidate Personal Information:</strong> Candidate full name, date of birth,
                                    gender, photograph, parent/guardian details, class/grade level, and chosen medium of
                                    examination.</li>
                                <li><strong>School & Institutional Data:</strong> School name, affiliation code,
                                    principal/coordinator name, official contact number, email address, and physical
                                    address.</li>
                                <li><strong>Examination & Academic Records:</strong> Generated registration numbers,
                                    hall ticket numbers, assigned examination centre allocations, attendance status,
                                    subject scores, and percentile ranks.</li>
                                <li><strong>Payment & Transaction Information:</strong> Online registration fee payment
                                    status, Cashfree reference IDs, payment timestamps, and transaction order IDs.
                                    <em>Note: Sensitive payment credentials (credit/debit card numbers, CVVs, UPI PINs)
                                        are processed directly by PCI-DSS compliant payment gateways and are never
                                        stored on our servers.</em></li>
                                <li><strong>Technical & System Information:</strong> IP address, browser type, device
                                    identifiers, session cookies, access logs, and security verification logs.</li>
                            </ul>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 3 -->
                    <section id="how-we-use" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                            </div>
                            <h2 class="section-title">3. How We Use Your Information</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                Information collected by <strong>YASIN EDUCATION SERVICES INDIA FOUNDATION</strong> is
                                strictly utilized for academic administration, examination logistics, and candidate
                                verification.
                            </p>

                            <div class="feature-box">
                                <h4>Core Operational Uses:</h4>
                                <p>Processing candidate registrations, generating secure photo hall tickets, allocating
                                    examination centres, facilitating scanner-based attendance at exam venues,
                                    evaluating answer keys, and publishing merit lists.</p>
                            </div>

                            <ul class="info-list">
                                <li><strong>Communication:</strong> Sending hall ticket download notifications,
                                    examination schedule updates, venue announcements, and result publication alerts to
                                    registered schools and candidates.</li>
                                <li><strong>Security & Authentication:</strong> Verifying candidate identity at exam
                                    entry gates using QR code verification to prevent impersonation and malpractice.
                                </li>
                                <li><strong>Certificate & Merit Verification:</strong> Allowing authorized institutions
                                    and employers to verify authentic certificates issued by the Foundation.</li>
                            </ul>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 4 -->
                    <section id="qr-privacy" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                </svg>
                            </div>
                            <h2 class="section-title">4. QR Code & Identity Privacy</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                Every official Hall Ticket issued by the Foundation includes a secure QR verification
                                code designed specifically for invigilators and exam centre staff.
                            </p>
                            <div class="feature-box" style="border-left-color: var(--cyan);">
                                <h4>Privacy Protection by Design:</h4>
                                <p>To protect candidate privacy against public enumeration or unauthorized data
                                    harvesting, public QR scans display only non-sensitive verification details
                                    (Candidate Name, Roll Number, Category, Exam Centre). Personal contact details, DOB,
                                    and parent names are withheld from public display screens.</p>
                            </div>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 5 -->
                    <section id="security" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </div>
                            <h2 class="section-title">5. Data Protection & Safeguards</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                We employ robust technical and organizational security measures to prevent unauthorized
                                access, disclosure, alteration, or destruction of stored data:
                            </p>
                            <ul class="info-list">
                                <li><strong>PCI DSS Compliance:</strong> All online payment processing strictly adheres to Payment Card Industry Data Security Standard (PCI DSS) Level 1 guidelines through our certified payment gateway partner (Cashfree Payments). Sensitive payment credentials (card numbers, CVVs, UPI PINs) are never captured, processed, or stored on our servers.</li>
                                <li><strong>Encryption:</strong> All web communication is encrypted using
                                    industry-standard TLS/SSL (HTTPS) protocols.</li>
                                <li><strong>Role-Based Access Control:</strong> Access to sensitive administrative
                                    portals is restricted based on strict role authorization (Super Admin, School Admin,
                                    Invigilator).</li>
                                <li><strong>Multi-Factor Authentication (MFA):</strong> Super Admin and sensitive
                                    administrative accounts require mandatory MFA verification.</li>
                                <li><strong>Rate Limiting & Threat Protection:</strong> Public APIs, hall ticket
                                    lookups, and login portals are rate-limited to mitigate automated brute-force
                                    attacks.</li>
                            </ul>

                            <div class="feature-box" style="border-left-color: #10b981;">
                                <h4>Payment Card Industry Data Security Standard (PCI DSS) Certified:</h4>
                                <p>Our payment gateway infrastructure operates under full compliance with Payment Card Industry Data Security Standard (PCI DSS) mandates, ensuring end-to-end tokenization, bank-grade encryption, and absolute protection of financial information during transactions.</p>
                            </div>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 6 -->
                    <section id="third-parties" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a5.97 5.97 0 00-.942 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                            </div>
                            <h2 class="section-title">6. Third-Party Sharing & Gateways</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                <strong>YASIN EDUCATION SERVICES INDIA FOUNDATION</strong> does not sell, rent, trade,
                                or monetize personal or institutional data under any circumstances.
                            </p>
                            <p>
                                Information is shared exclusively with necessary operational partners under strict
                                confidentiality:
                            </p>
                            <ul class="info-list">
                                <li><strong>Payment Gateways:</strong> Cashfree Payments for processing registration
                                    fees.</li>
                                <li><strong>Designated Examination Centres:</strong> Verified list of candidates for
                                    hall ticket verification and seating arrangements.</li>
                                <li><strong>Legal & Regulatory Bodies:</strong> Disclosed only when required by
                                    mandatory law, court orders, or governmental directives in India.</li>
                            </ul>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 7 -->
                    <section id="data-rights" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                            </div>
                            <h2 class="section-title">7. Data Retention & Rights</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                Candidate academic records and examination results are retained securely in foundation
                                archives to facilitate future mark sheet verification, duplicate hall ticket requests,
                                and historical merit validation.
                            </p>
                            <p>
                                Participating schools and parents may request data corrections or update registration
                                records prior to the announced registration deadline by contacting their school
                                administration or sending an official request to the Foundation.
                            </p>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 8 -->
                    <section id="contact" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <h2 class="section-title">8. Foundation Contact Details</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                For any questions, clarification, or privacy inquiries regarding this policy or our data
                                practices, please contact:
                            </p>

                            <div class="org-card">
                                <div class="org-name">YASIN EDUCATION SERVICES INDIA FOUNDATION</div>
                                <p style="font-size:14px; color:#cbd5e1; margin-bottom:8px;">
                                    Official Examination Management & Talent Search Portal
                                </p>
                                <p style="font-size:13.5px; color:#94a3b8; margin-bottom:0;">
                                    Website: <a href="/"
                                        style="color:#818cf8; text-decoration:none;">{{ request()->getSchemeAndHttpHost() }}</a>
                                </p>
                                <p style="font-size:13.5px; color:#94a3b8; margin-bottom:0;">
                                    Contact us: <a href="https://wa.me/8899360060" target="_blank"
                                        style="color:#c084fc; text-decoration:none;">+91 8899360060</a>
                                </p>
                            </div>
                        </div>
                    </section>
                </article>
            </div>
        </main>

        <!-- FOOTER -->
        <x-public-footer page="privacy" />
    </div>
</body>

</html>