@extends('layouts.app')

@section('page_title', 'Payment Receipt')

@section('content')

    {{-- ── Screen-only toolbar ── --}}
    <div class="no-print max-w-4xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ request()->routeIs('admin.payments.*') ? route('admin.payments.index') : route('school.payments.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-slate-400 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Back to Payments
            </a>
            <button type="button" id="print-receipt-btn"
                class="inline-flex items-center gap-2 text-sm font-semibold text-white px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 cursor-pointer hover:bg-indigo-500"
                style="background: #4f46e5; border: none;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12-1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                </svg>
                Print / Save PDF
            </button>
        </div>
    </div>

    <script @nonce>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('print-receipt-btn');
            if (btn) btn.addEventListener('click', function () { window.print(); });
        });
    </script>

    {{-- ── Receipt Printable Area ── --}}
    <div id="receipt-print-area" class="max-w-4xl mx-auto px-4 pb-16">
        <div class="receipt-card"
            style="background:#111827; border-radius:20px; border:1px solid #1e293b; overflow:hidden; width:100%; box-sizing:border-box;">

            {{-- ═══ 1. HEADER SECTION ═══ --}}
            <div class="receipt-header" style="padding: 32px 40px 24px; border-bottom: 1px solid #1e293b;">
                <div class="receipt-header-row"
                    style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px; width:100%;">
                    <div class="receipt-header-left" style="text-align:left;">
                        <p class="receipt-label"
                            style="font-size:10px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#6b7280; margin:0 0 6px 0;">
                            Official Payment Receipt
                        </p>
                        <h1
                            style="font-size:22px; font-weight:900; color:#f9fafb; letter-spacing:-0.5px; line-height:1.2; margin:0;">
                            YES GENIUS TALENT SEARCH<br>SEASON-4
                        </h1>
                        <p style="font-size:11px; color:#6b7280; margin:6px 0 0 0;">
                            Examination Registration Management System
                        </p>
                    </div>
                    <div class="receipt-header-right" style="text-align:right;">
                        <p class="receipt-label"
                            style="font-size:10px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#6b7280; margin:0 0 6px 0;">
                            RECEIPT / PAYMENT ID
                        </p>
                        <p style="font-family:monospace; font-size:14px; font-weight:700; color:#818cf8; margin:0;">
                            {{ $payment->transaction_id ?? '—' }}
                        </p>
                        @if($payment->cashfree_payment_id && $payment->cashfree_payment_id !== $payment->transaction_id)
                            <p style="font-family:monospace; font-size:10px; color:#6b7280; margin:4px 0 0 0;">
                                Cashfree: {{ $payment->cashfree_payment_id }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══ 2. META INFO ═══ --}}
            <div class="receipt-meta" style="padding: 24px 40px; border-bottom: 1px solid #1e293b;">
                <div class="receipt-meta-row"
                    style="display:flex; justify-content:space-between; align-items:flex-start; gap:24px; width:100%; box-sizing:border-box;">
                    <div class="receipt-meta-left" style="text-align:left; flex:1;">
                        <p class="receipt-label"
                            style="font-size:10px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#6b7280; margin:0 0 6px 0;">
                            PAYER DETAILS
                        </p>
                        <p style="font-size:13px; font-weight:700; color:#f3f4f6; margin:0;">{{ $payment->school->name }}
                        </p>
                        <p style="font-size:11px; font-family:monospace; color:#818cf8; margin:3px 0 0 0;">
                            School Code: {{ $payment->school->code }}
                        </p>
                        <p style="font-size:11px; color:#6b7280; margin:2px 0 0 0;">Authorized by:
                            {{ auth()->user()->name }}</p>
                    </div>
                    <div class="receipt-meta-right" style="text-align:right; flex:1;">
                        <p class="receipt-label"
                            style="font-size:10px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#6b7280; margin:0 0 6px 0;">
                            TRANSACTION DETAILS
                        </p>
                        <p style="font-size:11px; color:#9ca3af; margin:0 0 4px 0;">
                            <span style="color:#6b7280;">Date:</span>
                            <strong
                                style="color:#e5e7eb;">{{ ($payment->paid_at ?? $payment->created_at)->format('d M Y, h:i A') }}</strong>
                        </p>
                        <p style="font-size:11px; color:#9ca3af; margin:0 0 4px 0;">
                            <span style="color:#6b7280;">Method:</span>
                            <strong style="color:#e5e7eb;">{{ $payment->payment_method ?? 'Cashfree' }}</strong>
                        </p>
                        <p style="font-size:11px; color:#9ca3af; margin:0;">
                            <span style="color:#6b7280;">Status:</span>
                            <strong style="color:#34d399;">Paid & Verified</strong>
                        </p>
                    </div>
                </div>
            </div>

            {{-- ═══ 3. CANDIDATES TABLE ═══ --}}
            <div class="receipt-table-wrapper" style="padding: 24px 40px 12px; width:100%; box-sizing:border-box;">
                <p class="receipt-label"
                    style="font-size:10px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#6b7280; margin:0 0 14px 0;">
                    Registered Candidates List ({{ $payment->students->count() }})
                </p>
                <table class="receipt-table"
                    style="width:100%; border-collapse:collapse; font-size:11px; table-layout:fixed;">
                    <thead>
                        <tr style="border-top:1px solid #1e293b; border-bottom:1px solid #1e293b; background:#1e293b;">
                            <th
                                style="padding:10px 8px; text-align:center; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#9ca3af; width:8%;">
                                #</th>
                            <th
                                style="padding:10px 12px; text-align:left; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#9ca3af; width:44%;">
                                Candidate Name</th>
                            <th
                                style="padding:10px 8px; text-align:center; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#9ca3af; width:16%;">
                                Class</th>
                            <th
                                style="padding:10px 8px; text-align:center; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#9ca3af; width:14%;">
                                Gender</th>
                            <th
                                style="padding:10px 12px; text-align:right; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#9ca3af; width:18%;">
                                Fee (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment->students as $i => $student)
                            <tr style="border-bottom:1px solid #1f2937;">
                                <td style="padding:10px 8px; text-align:center; color:#6b7280; font-family:monospace;">
                                    {{ $i + 1 }}</td>
                                <td style="padding:10px 12px; text-align:left;">
                                    <span class="student-name" style="font-weight:600; color:#f3f4f6;">{{ $student->name }}</span>
                                    @if($student->registration_number)
                                        <br><span class="student-reg-no"
                                            style="font-family:monospace; font-size:9px; color:#6b7280;">{{ $student->registration_number }}</span>
                                    @endif
                                </td>
                                <td style="padding:10px 8px; text-align:center; font-family:monospace; color:#9ca3af;">
                                    {{ $student->class->name ?? '—' }}
                                </td>
                                <td
                                    style="padding:10px 8px; text-align:center; text-transform:uppercase; font-size:10px; color:#9ca3af;">
                                    {{ $student->gender }}
                                </td>
                                <td class="student-fee"
                                    style="padding:10px 12px; text-align:right; font-family:monospace; font-weight:700; color:#f3f4f6;">
                                    ₹{{ number_format($student->pivot->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ═══ 4. TOTAL PAID ═══ --}}
            <div class="receipt-total"
                style="padding: 18px 40px 20px; border-top:1px solid #1e293b; width:100%; box-sizing:border-box;">
                @php
                    $receiptBaseAmount = $payment->base_amount > 0 ? $payment->base_amount : max(0, $payment->amount - $payment->fine_amount);
                @endphp
                <div class="receipt-total-line"
                    style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-size:12px; color:#9ca3af;">
                    <span>Base Registration Amount:</span>
                    <span
                        style="font-family:monospace; font-weight:600; color:#e5e7eb;">₹{{ number_format($receiptBaseAmount, 2) }}</span>
                </div>
                <div class="receipt-total-line"
                    style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; font-size:12px; color:#fbbf24;">
                    <span>Late Registration Fine Amount:</span>
                    <span
                        style="font-family:monospace; font-weight:700;">₹{{ number_format($payment->fine_amount, 2) }}</span>
                </div>
                <div class="receipt-total-grand"
                    style="display:flex; justify-content:space-between; align-items:center; border-top:1px dashed #374151; padding-top:10px;">
                    <p
                        style="font-size:10px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#6b7280; margin:0;">
                        Grand Total Amount Paid (INR)
                    </p>
                    <p
                        style="font-size:24px; font-weight:900; font-family:monospace; color:#34d399; margin:0; letter-spacing:-0.5px;">
                        ₹{{ number_format($payment->amount, 2) }}
                    </p>
                </div>
            </div>

            {{-- ═══ 5. FOOTER ═══ --}}
            <div class="receipt-footer"
                style="padding: 22px 40px 24px; border-top:1px solid #1e293b; width:100%; box-sizing:border-box;">
                <div class="receipt-footer-row"
                    style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:16px;">
                    <div class="receipt-footer-left" style="text-align:left; flex:1;">
                        <p style="font-size:10px; color:#6b7280; max-width:440px; line-height:1.6; margin:0;">
                            This is an official system-generated registration fee receipt. The candidates listed above are
                            verified and registered with the examination board.
                        </p>
                        <p class="receipt-gen-date" style="font-size:9px; color:#4b5563; font-family:monospace; margin:4px 0 0 0;">
                            Transaction Date: {{ ($payment->paid_at ?? $payment->created_at)->format('d M Y, h:i A') }}
                        </p>
                    </div>
                    <div class="receipt-footer-right" style="text-align:right; flex-shrink:0;">
                        <p class="receipt-org-title"
                            style="font-size:9.5px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#9ca3af; margin:0 0 4px 0;">
                            YES GENIUS TALENT SEARCH SEASON-4
                        </p>
                        <p class="receipt-contact" style="font-size:10px; color:#6b7280; margin:0; line-height:1.6;">
                            Support: <a href="mailto:hello@cyberduce.com"
                                style="color:#818cf8; text-decoration:none; font-weight:500;">hello@cyberduce.com</a>
                        </p>
                        <p class="receipt-contact"
                            style="font-size:10px; color:#6b7280; margin:2px 0 0 0; line-height:1.6;">
                            Web: <a href="{{ request()->getSchemeAndHttpHost() }}" target="_blank"
                                style="color:#818cf8; text-decoration:none; font-weight:500;">{{ request()->getSchemeAndHttpHost() }}</a>
                        </p>
                    </div>
                </div>

                {{-- Copyright sub-footer --}}
                <div class="receipt-copyright-row"
                    style="border-top:1px dashed #1e293b; padding-top:12px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; font-size:9.5px; color:#4b5563;">
                    <div class="receipt-copyright-left">
                        &copy; {{ date('Y') }} <span style="font-weight:600; color:#6b7280;">YASIN EDUCATION SERVICES INDIA
                            FOUNDATION</span>. All rights reserved.
                    </div>
                    <div class="receipt-copyright-right" style="display:flex; align-items:center; gap:12px;">
                        <span><a href="{{ request()->getSchemeAndHttpHost() }}"
                                style="color:#6b7280; text-decoration:none;">{{ parse_url(request()->getSchemeAndHttpHost(), PHP_URL_HOST) ?? request()->getHost() }}</a></span>
                        <span>&bull;</span>
                        <span><a href="mailto:hello@cyberduce.com"
                                style="color:#6b7280; text-decoration:none;">hello@cyberduce.com</a></span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── 6. PRINT & PDF SPECIFIC ALIGNMENT STYLES ── --}}
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm 10mm 12mm;
            }

            /* Hide interactive UI elements */
            nav,
            aside,
            header,
            .no-print,
            #chat-container,
            footer,
            .alert {
                display: none !important;
            }

            /* Reset page container without blanket 'div' selector that broke flex widths */
            html,
            body {
                background: #ffffff !important;
                color: #111827 !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow: visible !important;
                position: static !important;
                width: 100% !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            main,
            #app,
            .flex-1,
            .content-area {
                background: #ffffff !important;
                color: #111827 !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow: visible !important;
                position: static !important;
                float: none !important;
                box-shadow: none !important;
                width: 100% !important;
            }

            #receipt-print-area {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                position: static !important;
                box-sizing: border-box !important;
            }

            .receipt-card {
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 6px !important;
                box-shadow: none !important;
                overflow: visible !important;
                position: static !important;
                display: block !important;
                color: #111827 !important;
                width: 100% !important;
                box-sizing: border-box !important;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
            }

            /* ── Header ── */
            .receipt-header {
                padding: 18px 24px 14px !important;
                border-bottom: 2px solid #0f172a !important;
            }

            .receipt-header-row {
                display: table !important;
                width: 100% !important;
                table-layout: fixed !important;
            }

            .receipt-header-left {
                display: table-cell !important;
                width: 62% !important;
                vertical-align: top !important;
                text-align: left !important;
            }

            .receipt-header-right {
                display: table-cell !important;
                width: 38% !important;
                vertical-align: top !important;
                text-align: right !important;
            }

            .receipt-header h1 {
                color: #0f172a !important;
                font-size: 18px !important;
                margin: 0 !important;
            }

            .receipt-header p {
                color: #475569 !important;
            }

            /* ── Meta Section ── */
            .receipt-meta {
                padding: 14px 24px !important;
                border-bottom: 1px solid #cbd5e1 !important;
                background: #f8fafc !important;
            }

            .receipt-meta-row {
                display: table !important;
                width: 100% !important;
                table-layout: fixed !important;
            }

            .receipt-meta-left {
                display: table-cell !important;
                width: 52% !important;
                vertical-align: top !important;
                text-align: left !important;
            }

            .receipt-meta-right {
                display: table-cell !important;
                width: 48% !important;
                vertical-align: top !important;
                text-align: right !important;
            }

            .receipt-meta p {
                color: #334155 !important;
            }

            .receipt-meta strong {
                color: #0f172a !important;
            }

            /* ── Candidates Table ── */
            .receipt-table-wrapper {
                padding: 14px 24px 8px !important;
            }

            table.receipt-table {
                width: 100% !important;
                border-collapse: collapse !important;
                table-layout: fixed !important;
                page-break-inside: auto;
                break-inside: auto;
            }

            thead {
                display: table-header-group;
            }

            thead tr {
                background-color: #f1f5f9 !important;
                border-top: 1px solid #0f172a !important;
                border-bottom: 2px solid #0f172a !important;
            }

            thead th {
                color: #0f172a !important;
                font-weight: 700 !important;
                font-size: 9px !important;
                padding: 8px 10px !important;
            }

            tbody {
                display: table-row-group;
            }

            tbody tr {
                page-break-inside: avoid;
                break-inside: avoid;
                page-break-after: auto;
                border-bottom: 1px solid #e2e8f0 !important;
            }

            tbody tr:nth-child(even) {
                background-color: #f8fafc !important;
            }

            tbody td {
                padding: 8px 10px !important;
                font-size: 10.5px !important;
                color: #111827 !important;
            }

            tbody td * {
                color: #111827 !important;
            }

            tbody td .student-name {
                color: #000000 !important;
                font-weight: 700 !important;
            }

            tbody td .student-reg-no {
                color: #4b5563 !important;
            }

            tbody td.student-fee {
                color: #000000 !important;
                font-weight: 700 !important;
            }

            /* ── Total Paid ── */
            .receipt-total {
                padding: 14px 24px !important;
                border-top: 2px solid #0f172a !important;
                background: #f8fafc !important;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .receipt-total-line {
                display: table !important;
                width: 100% !important;
                table-layout: fixed !important;
                margin-bottom: 6px !important;
            }

            .receipt-total-line span:first-child {
                display: table-cell !important;
                text-align: left !important;
                color: #475569 !important;
                font-size: 11px !important;
            }

            .receipt-total-line span:last-child {
                display: table-cell !important;
                text-align: right !important;
                color: #0f172a !important;
                font-size: 12px !important;
                font-weight: 600 !important;
            }

            .receipt-total-grand {
                display: table !important;
                width: 100% !important;
                table-layout: fixed !important;
                border-top: 1px dashed #94a3b8 !important;
                padding-top: 8px !important;
                margin-top: 8px !important;
            }

            .receipt-total-grand p:first-child {
                display: table-cell !important;
                text-align: left !important;
                vertical-align: middle !important;
                color: #0f172a !important;
                font-size: 10.5px !important;
                font-weight: 700 !important;
                margin: 0 !important;
            }

            .receipt-total-grand p:last-child {
                display: table-cell !important;
                text-align: right !important;
                vertical-align: middle !important;
                color: #0f172a !important;
                font-size: 20px !important;
                font-weight: 900 !important;
                margin: 0 !important;
            }

            /* ── Footer ── */
            .receipt-footer {
                border-top: 1px solid #cbd5e1 !important;
                padding: 14px 24px 16px !important;
                page-break-inside: avoid;
                break-inside: avoid;
                background: #ffffff !important;
            }

            .receipt-footer-row {
                display: table !important;
                width: 100% !important;
                table-layout: fixed !important;
                margin-bottom: 12px !important;
            }

            .receipt-footer-left {
                display: table-cell !important;
                width: 62% !important;
                vertical-align: top !important;
                text-align: left !important;
                padding-right: 16px !important;
                box-sizing: border-box !important;
            }

            .receipt-footer-left p {
                max-width: 100% !important;
                font-size: 9.5px !important;
                line-height: 1.55 !important;
                color: #475569 !important;
                margin: 0 !important;
                white-space: normal !important;
                word-wrap: break-word !important;
            }

            .receipt-footer-left .receipt-gen-date {
                font-size: 8.5px !important;
                color: #64748b !important;
                margin-top: 5px !important;
            }

            .receipt-footer-right {
                display: table-cell !important;
                width: 38% !important;
                vertical-align: top !important;
                text-align: right !important;
                box-sizing: border-box !important;
                white-space: normal !important;
            }

            .receipt-footer-right p {
                color: #475569 !important;
                font-size: 9.5px !important;
                margin: 0 !important;
                line-height: 1.55 !important;
            }

            .receipt-footer-right .receipt-org-title {
                font-size: 9px !important;
                font-weight: 700 !important;
                color: #0f172a !important;
                margin-bottom: 3px !important;
                letter-spacing: 0.04em !important;
            }

            .receipt-footer a {
                color: #2563eb !important;
                text-decoration: none !important;
            }

            /* ── Copyright Row ── */
            .receipt-copyright-row {
                display: table !important;
                width: 100% !important;
                table-layout: fixed !important;
                border-top: 1px dashed #cbd5e1 !important;
                padding-top: 10px !important;
                margin-top: 8px !important;
            }

            .receipt-copyright-left {
                display: table-cell !important;
                width: 60% !important;
                vertical-align: middle !important;
                text-align: left !important;
                font-size: 9px !important;
                color: #64748b !important;
            }

            .receipt-copyright-right {
                display: table-cell !important;
                width: 40% !important;
                vertical-align: middle !important;
                text-align: right !important;
                font-size: 9px !important;
                color: #64748b !important;
                white-space: nowrap !important;
            }

            .receipt-copyright-right a {
                color: #64748b !important;
                text-decoration: none !important;
            }

            .receipt-label {
                color: #64748b !important;
            }
        }
    </style>

@endsection