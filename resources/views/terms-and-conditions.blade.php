<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Terms and Conditions of YASIN EDUCATION SERVICES INDIA FOUNDATION - Rules, examination guidelines, payment terms, and candidate portal regulations for YES Genius Talent Search.">
    <title>Terms and Conditions | YASIN EDUCATION SERVICES INDIA FOUNDATION</title>
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
            --amber: #f59e0b;
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
            background: rgba(168, 85, 247, 0.12);
            top: -100px;
            right: -100px;
        }

        .orb-2 {
            width: 450px;
            height: 450px;
            background: rgba(99, 102, 241, 0.10);
            bottom: 10%;
            left: -100px;
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
            background: rgba(168, 85, 247, 0.12);
            border: 1px solid rgba(168, 85, 247, 0.3);
            color: #c084fc;
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
            background: linear-gradient(135deg, #c084fc 0%, #818cf8 100%);
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
            background: rgba(168, 85, 247, 0.12);
            border: 1px solid rgba(168, 85, 247, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #c084fc;
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
            border-left: 3px solid var(--purple);
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
            background: var(--purple);
            box-shadow: 0 0 8px var(--purple);
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 36px 0;
        }

        .org-card {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.08) 0%, rgba(99, 102, 241, 0.05) 100%);
            border: 1px solid rgba(168, 85, 247, 0.2);
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
        <x-public-nav page="terms" />

        <!-- PAGE HEADER -->
        <header class="legal-header">
            <div class="badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" style="width:15px;height:15px;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Institutional Regulations & Terms of Service
            </div>
            <h1 class="legal-title">Terms & <span>Conditions</span></h1>
            <p class="legal-sub">
                Rules, policies, and regulations governing examination participation, school registrations, and portal
                usage under YASIN EDUCATION SERVICES INDIA FOUNDATION.
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
                        <li><a href="#acceptance" class="toc-link">1. Acceptance of Terms</a></li>
                        <li><a href="#eligibility" class="toc-link">2. Registration & Eligibility</a></li>
                        <li><a href="#examination-rules" class="toc-link">3. Exam & Hall Tickets</a></li>
                        <li><a href="#payments" class="toc-link">4. Fee & Payment Terms</a></li>
                        <li><a href="#results" class="toc-link">5. Evaluation & Results</a></li>
                        <li><a href="#code-of-conduct" class="toc-link">6. Code of Conduct</a></li>
                        <li><a href="#intellectual-property" class="toc-link">7. Intellectual Property</a></li>
                        <li><a href="#liability" class="toc-link">8. Limitation of Liability</a></li>
                        <li><a href="#governing-law" class="toc-link">9. Governing Law</a></li>
                        <li><a href="#contact" class="toc-link">10. Contact Details</a></li>
                    </ul>
                </aside>

                <!-- TERMS CONTENT -->
                <article class="content-card">
                    <!-- SECTION 1 -->
                    <section id="acceptance" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h2 class="section-title">1. Acceptance of Terms</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                By accessing, registering candidates on, or using the examination system operated by
                                <strong>YASIN EDUCATION SERVICES INDIA FOUNDATION</strong> ("Foundation"), participating
                                educational institutions, school administrators, guardians, and candidates agree to
                                comply with and be bound by these Terms and Conditions.
                            </p>
                            <p>
                                If any school, administrator, or candidate does not agree with any part of these terms,
                                they must refrain from utilizing the portal or participating in the competitive
                                examinations conducted by the Foundation.
                            </p>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 2 -->
                    <section id="eligibility" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                                </svg>
                            </div>
                            <h2 class="section-title">2. Registration & Candidate Eligibility</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                Registration for the YES Genius National Level Talent Search and related foundation
                                examinations must strictly adhere to the following rules:
                            </p>
                            <ul class="info-list">
                                <li><strong>Institutional Authorization:</strong> Candidate registrations must be
                                    submitted by authorized school administrators or official coordinators using
                                    verified institutional credentials.</li>
                                <li><strong>Accuracy of Information:</strong> The registering school or parent is solely
                                    responsible for ensuring that student names, dates of birth, class levels, category
                                    selections, and photos are true, accurate, and up to date.</li>
                                <li><strong>Category Allocation:</strong> Candidates must be registered in their
                                    respective legitimate academic categories and grade levels. Incorrect grade entries
                                    may lead to disqualification.</li>
                            </ul>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 3 -->
                    <section id="examination-rules" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                </svg>
                            </div>
                            <h2 class="section-title">3. Hall Tickets & Examination Conduct</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                Hall tickets are issued digitally upon verification and payment completion. Candidates
                                must strictly follow examination hall procedures:
                            </p>

                            <div class="feature-box">
                                <h4>Mandatory Hall Ticket Requirements:</h4>
                                <p>Candidates must bring a printed physical copy of their official Hall Ticket
                                    containing a legible QR verification code and student photograph to the assigned
                                    examination centre. Candidates without a valid hall ticket will not be permitted
                                    entry.</p>
                            </div>

                            <ul class="info-list">
                                <li><strong>Identity Verification:</strong> Invigilators will scan the Hall Ticket QR
                                    code at the examination gate to verify candidate authenticity and record attendance.
                                </li>
                                <li><strong>Centre Allocation:</strong> The Foundation reserves the right to assign or
                                    reallocate examination centres based on logistical feasibility, venue capacity, and
                                    safety considerations.</li>
                                <li><strong>Timing & Punctuality:</strong> Candidates must report to their designated
                                    exam centre at least 30 minutes prior to the commencement of the exam. Late arrivals
                                    may be denied entry.</li>
                            </ul>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 4 -->
                    <section id="payments" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                </svg>
                            </div>
                            <h2 class="section-title">4. Fee Payment & Non-Refundability Policy</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                All online registration fee transactions are processed securely through our authorized
                                payment partner, Cashfree Payments.
                            </p>
                            <ul class="info-list">
                                <li><strong>PCI DSS Compliance Standard:</strong> All online financial transactions strictly adhere to the Payment Card Industry Data Security Standard (PCI DSS). Payment checkout is handled directly through PCI DSS Level 1 certified gateways.</li>
                                <li><strong>Fee Non-Refundability:</strong> Examination registration fees paid to
                                    <strong>YASIN EDUCATION SERVICES INDIA FOUNDATION</strong> are strictly
                                    non-refundable and non-transferable under any circumstances once processed.
                                </li>
                                <li><strong>Duplicate Payments:</strong> In cases of double deduction due to network
                                    glitch or payment gateway failure, refund claims will be verified against payment
                                    gateway settlement records and processed back to the original source account.</li>
                                <li><strong>Official Receipts:</strong> Payment receipts are generated automatically
                                    upon successful payment settlement and can be accessed by school administrators via
                                    their portal dashboard.</li>
                            </ul>

                            <div class="feature-box" style="border-left-color: #10b981;">
                                <h4>Payment Card Industry Data Security Standard (PCI DSS) Compliance:</h4>
                                <p>Online fee transactions are encrypted and processed through Cashfree Payments under strict Payment Card Industry Data Security Standard (PCI DSS) compliance. YASIN EDUCATION SERVICES INDIA FOUNDATION does not collect, store, or transmit sensitive credit/debit card numbers, CVVs, net banking passwords, or UPI PINs on its servers.</p>
                            </div>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 5 -->
                    <section id="results" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-6.75a1.125 1.125 0 01-1.125-1.125V18.75m9 0H6.75" />
                                </svg>
                            </div>
                            <h2 class="section-title">5. Evaluation & Result Publication</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                Evaluation of OMR answer sheets and examination submissions is conducted using
                                standardized automated valuation keys under the supervision of the Examination Board.
                            </p>
                            <div class="feature-box">
                                <h4>Finality of Results:</h4>
                                <p>The evaluation, ranks, awards, and merit lists declared by YASIN EDUCATION SERVICES
                                    INDIA FOUNDATION are final, binding, and not subject to individual dispute or
                                    re-evaluation requests unless authorized by the Board.</p>
                            </div>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 6 -->
                    <section id="code-of-conduct" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <h2 class="section-title">6. Code of Conduct & Anti-Malpractice</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                The Foundation maintains a strict zero-tolerance policy towards academic dishonesty,
                                malpractice, or system misuse:
                            </p>
                            <ul class="info-list">
                                <li><strong>Prohibited Items:</strong> Electronic gadgets, mobile phones, smartwatches,
                                    calculators (unless permitted), and unauthorized study materials are strictly banned
                                    inside examination halls.</li>
                                <li><strong>Impersonation:</strong> Any attempt at candidate impersonation, forging of
                                    hall tickets, or tampering with QR verification codes will result in immediate
                                    disqualification and legal action under applicable laws.</li>
                                <li><strong>Portal Security:</strong> Any attempt to probe, scan, reverse-engineer, or
                                    breach system security will lead to permanent IP blockage and legal prosecution.
                                </li>
                            </ul>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 7 -->
                    <section id="intellectual-property" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                </svg>
                            </div>
                            <h2 class="section-title">7. Intellectual Property Rights</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                All content on this portal — including exam branding, YES Genius logos, question papers,
                                syllabus guides, software code, mark sheet designs, and text — is the exclusive
                                intellectual property of <strong>YASIN EDUCATION SERVICES INDIA FOUNDATION</strong>.
                            </p>
                            <p>
                                Unauthorized reproduction, redistribution, mirroring, or commercial exploitation of any
                                foundation assets without written consent is strictly prohibited.
                            </p>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 8 -->
                    <section id="liability" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m0 3.75h.007v.008H12v-.008zM12 3a9 9 0 100 18 9 9 0 000-18z" />
                                </svg>
                            </div>
                            <h2 class="section-title">8. Limitation of Liability & Force Majeure</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                While the Foundation makes every effort to ensure uninterrupted access and flawless exam
                                conduct, we shall not be held liable for disruptions resulting from force majeure
                                events, natural disasters, severe weather conditions, government restrictions, or
                                internet connectivity outages beyond our reasonable control.
                            </p>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 9 -->
                    <section id="governing-law" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m-0.002 0A11.95 11.95 0 0112 16.5c-3.17 0-6.07-1.233-8.216-3.247m0 0A8.959 8.959 0 013 12c0-.778.099-1.533.284-2.253" />
                                </svg>
                            </div>
                            <h2 class="section-title">9. Governing Law & Jurisdiction</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                These Terms and Conditions shall be governed by and construed in accordance with the
                                laws of India. Any legal dispute, claim, or proceeding arising out of or in connection
                                with these terms shall be subject to the exclusive jurisdiction of the competent courts
                                in India.
                            </p>
                        </div>
                    </section>

                    <div class="divider"></div>

                    <!-- SECTION 10 -->
                    <section id="contact" class="section-block">
                        <div class="section-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <h2 class="section-title">10. Contact Details</h2>
                        </div>
                        <div class="section-body">
                            <p>
                                For any questions, institutional inquiries, or clarification regarding these Terms &
                                Conditions, please contact:
                            </p>

                            <div class="org-card">
                                <div class="org-name">YASIN EDUCATION SERVICES INDIA FOUNDATION</div>
                                <p style="font-size:14px; color:#cbd5e1; margin-bottom:8px;">
                                    Official Examination Management & Talent Search Portal
                                </p>
                                <p style="font-size:13.5px; color:#94a3b8; margin-bottom:0;">
                                    Website: <a href="/"
                                        style="color:#c084fc; text-decoration:none;">{{ request()->getSchemeAndHttpHost() }}</a>
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
        <x-public-footer page="terms" />
    </div>
</body>

</html>