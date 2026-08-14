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
        <div class="receipt-card" style="background:#111827; border-radius:20px; border:1px solid #1e293b; overflow:hidden; width:100%; box-sizing:border-box;">

            {{-- ═══ 1. HEADER SECTION ═══ --}}
            <div class="receipt-header" style="padding: 32px 40px 24px; border-bottom: 1px solid #1e293b;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px; width:100%;">
                    <div style="text-align:left;">
                        <p class="receipt-label" style="font-size:10px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#6b7280; margin:0 0 6px 0;">
                            Official Payment Receipt
                        </p>
                        <h1 style="font-size:22px; font-weight:900; color:#f9fafb; letter-spacing:-0.5px; line-height:1.2; margin:0;">
                            YES GENIUS TALENT SEARCH<br>SEASON-4
                        </h1>
                        <p style="font-size:11px; color:#6b7280; margin:6px 0 0 0;">
                            Examination Registration Management System
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <p class="receipt-label" style="font-size:10px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#6b7280; margin:0 0 6px 0;">
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
            <div class="receipt-meta" style="padding: 24px 40px; border-bottom: 1px solid #1e293b; display:flex; justify-content:space-between; align-items:flex-start; gap:24px; width:100%; box-sizing:border-box;">
                <div style="text-align:left; flex:1;">
                    <p class="receipt-label" style="font-size:10px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#6b7280; margin:0 0 6px 0;">
                        PAYER DETAILS
                    </p>
                    <p style="font-size:13px; font-weight:700; color:#f3f4f6; margin:0;">{{ $payment->school->name }}</p>
                    <p style="font-size:11px; font-family:monospace; color:#818cf8; margin:3px 0 0 0;">
                        School Code: {{ $payment->school->code }}
                    </p>
                    <p style="font-size:11px; color:#6b7280; margin:2px 0 0 0;">Authorized by: {{ auth()->user()->name }}</p>
                </div>
                <div style="text-align:right; flex:1;">
                    <p class="receipt-label" style="font-size:10px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#6b7280; margin:0 0 6px 0;">
                        TRANSACTION DETAILS
                    </p>
                    <p style="font-size:11px; color:#9ca3af; margin:0 0 4px 0;">
                        <span style="color:#6b7280;">Date:</span> 
                        <strong style="color:#e5e7eb;">{{ ($payment->paid_at ?? $payment->created_at)->format('d M Y, h:i A') }}</strong>
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

            {{-- ═══ 3. CANDIDATES TABLE ═══ --}}
            <div class="receipt-table-wrapper" style="padding: 24px 40px 12px; width:100%; box-sizing:border-box;">
                <p class="receipt-label" style="font-size:10px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#6b7280; margin:0 0 14px 0;">
                    Registered Candidates List ({{ $payment->students->count() }})
                </p>
                <table class="receipt-table" style="width:100%; border-collapse:collapse; font-size:11px; table-layout:fixed;">
                    <thead>
                        <tr style="border-top:1px solid #1e293b; border-bottom:1px solid #1e293b; background:#1e293b;">
                            <th style="padding:10px 8px; text-align:center; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#9ca3af; width:8%;">#</th>
                            <th style="padding:10px 12px; text-align:left; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#9ca3af; width:44%;">Candidate Name</th>
                            <th style="padding:10px 8px; text-align:center; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#9ca3af; width:16%;">Class</th>
                            <th style="padding:10px 8px; text-align:center; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#9ca3af; width:14%;">Gender</th>
                            <th style="padding:10px 12px; text-align:right; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#9ca3af; width:18%;">Fee (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment->students as $i => $student)
                            <tr style="border-bottom:1px solid #1f2937;">
                                <td style="padding:10px 8px; text-align:center; color:#6b7280; font-family:monospace;">{{ $i + 1 }}</td>
                                <td style="padding:10px 12px; text-align:left;">
                                    <span style="font-weight:600; color:#f3f4f6;">{{ $student->name }}</span>
                                    @if($student->registration_number)
                                        <br><span style="font-family:monospace; font-size:9px; color:#6b7280;">{{ $student->registration_number }}</span>
                                    @endif
                                </td>
                                <td style="padding:10px 8px; text-align:center; font-family:monospace; color:#9ca3af;">
                                    {{ $student->class->name ?? '—' }}
                                </td>
                                <td style="padding:10px 8px; text-align:center; text-transform:uppercase; font-size:10px; color:#9ca3af;">
                                    {{ $student->gender }}
                                </td>
                                <td style="padding:10px 12px; text-align:right; font-family:monospace; font-weight:700; color:#f3f4f6;">
                                    ₹{{ number_format($student->pivot->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ═══ 4. TOTAL PAID ═══ --}}
            <div class="receipt-total" style="padding: 18px 40px 20px; border-top:1px solid #1e293b; display:flex; justify-content:space-between; align-items:center; width:100%; box-sizing:border-box;">
                <p style="font-size:10px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#6b7280; margin:0;">
                    Grand Total Amount Paid (INR)
                </p>
                <p style="font-size:26px; font-weight:900; font-family:monospace; color:#34d399; margin:0; letter-spacing:-0.5px;">
                    ₹{{ number_format($payment->amount, 2) }}
                </p>
            </div>

            {{-- ═══ 5. FOOTER ═══ --}}
            <div class="receipt-footer" style="padding: 20px 40px 28px; border-top:1px solid #1e293b; display:flex; justify-content:space-between; align-items:flex-end; gap:20px; width:100%; box-sizing:border-box;">
                <div style="text-align:left;">
                    <p style="font-size:10px; color:#6b7280; max-width:420px; line-height:1.6; margin:0;">
                        This is an official system-generated registration fee receipt. The candidates listed above are verified and registered with the examination board.
                    </p>
                    <p style="font-size:9px; color:#4b5563; font-family:monospace; margin:4px 0 0 0;">
                        Generated on {{ now()->format('d M Y, h:i A') }}
                    </p>
                </div>
                <div style="text-align:right;">
                    <p style="font-size:9px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#4b5563; margin:0;">
                        YES GENIUS TALENT SEARCH SEASON-4
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- ── 6. PRINT & PDF SPECIFIC ALIGNMENT STYLES ── --}}
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm 12mm 12mm 12mm;
            }

            nav, aside, header, .no-print, #chat-container, footer {
                display: none !important;
            }

            html, body, main, #app, div, .flex-1, .content-area {
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
                border: 1px solid #d1d5db !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                overflow: visible !important;
                position: static !important;
                display: block !important;
                color: #111827 !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .receipt-header {
                padding: 20px 24px 16px !important;
                border-bottom: 2px solid #111827 !important;
            }

            .receipt-header h1 {
                color: #111827 !important;
                font-size: 20px !important;
            }

            .receipt-meta {
                padding: 16px 24px !important;
                border-bottom: 1px solid #d1d5db !important;
                background: #f8fafc !important;
            }

            .receipt-meta * {
                color: #111827 !important;
            }

            .receipt-meta p {
                color: #374151 !important;
            }

            .receipt-table-wrapper {
                padding: 16px 24px 8px !important;
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
                border-top: 1px solid #111827 !important;
                border-bottom: 2px solid #111827 !important;
            }

            thead th {
                color: #111827 !important;
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
                border-bottom: 1px solid #e5e7eb !important;
            }

            tbody tr:nth-child(even) {
                background-color: #f9fafc !important;
            }

            tbody td {
                padding: 8px 10px !important;
                font-size: 10.5px !important;
                color: #111827 !important;
            }

            .receipt-total {
                padding: 14px 24px !important;
                border-top: 2px solid #111827 !important;
                background: #f8fafc !important;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .receipt-total p {
                color: #111827 !important;
            }

            .receipt-footer {
                border-top: 1px solid #d1d5db !important;
                padding: 14px 24px 20px !important;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .receipt-label {
                color: #6b7280 !important;
            }
        }
    </style>

@endsection