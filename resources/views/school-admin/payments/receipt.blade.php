@extends('layouts.app')

@section('page_title', 'Payment Receipt')

@section('content')

    {{-- ── Screen-only toolbar ── --}}
    <div class="no-print max-w-3xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between">
            <a href="{{ request()->routeIs('admin.payments.*') ? route('admin.payments.index') : route('school.payments.index') }}"
                class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Back to Payments
            </a>
            <button type="button" id="print-receipt-btn"
                class="inline-flex items-center gap-2 text-sm font-semibold text-white px-4 py-2 rounded-lg transition-all cursor-pointer hover:opacity-90"
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

    {{-- ── Receipt ── --}}
    <div id="receipt-print-area" class="max-w-3xl mx-auto px-4 pb-14">
        <div class="receipt-card"
            style="background:#111827; border-radius:16px; border:1px solid #1e293b; overflow:hidden;">

            {{-- ═══ HEADER ═══ --}}
            <div class="receipt-header" style="padding: 40px 44px 32px; border-bottom: 1px solid #1e293b;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:24px; flex-wrap:wrap;">

                    {{-- Organisation --}}
                    <div>
                        <p class="receipt-label"
                            style="font-size:10px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#6b7280; margin-bottom:10px;">
                            Payment Receipt
                        </p>
                        <h1
                            style="font-size:22px; font-weight:900; color:#f9fafb; letter-spacing:-0.5px; line-height:1.2; margin:0;">
                            YES GENIUS TALENT SEARCH<br>SEASON-4
                        </h1>
                        <p style="font-size:11px; color:#4b5563; margin-top:8px; margin-bottom:0;">
                            Examination Registration Management System
                        </p>
                    </div>

                    {{-- Receipt meta --}}
                    <div style="text-align:right;">
                        <p class="receipt-label"
                            style="font-size:10px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#6b7280; margin-bottom:6px;">
                            RECEIPT / PAYMENT ID
                        </p>
                        <p style="font-family:monospace; font-size:13px; font-weight:700; color:#e5e7eb; margin:0;">
                            {{ $payment->transaction_id ?? '—' }}
                        </p>
                        @if($payment->cashfree_payment_id && $payment->cashfree_payment_id !== $payment->transaction_id)
                            <p style="font-family:monospace; font-size:10px; color:#4b5563; margin-top:4px;">
                                Cashfree: {{ $payment->cashfree_payment_id }}
                            </p>
                        @elseif($payment->razorpay_payment_id && $payment->razorpay_payment_id !== $payment->transaction_id)
                            <p style="font-family:monospace; font-size:10px; color:#4b5563; margin-top:4px;">
                                Gateway: {{ $payment->razorpay_payment_id }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══ META INFO ═══ --}}
            <div
                style="padding: 28px 44px; border-bottom: 1px solid #1e293b; display:grid; grid-template-columns: repeat(2,1fr); gap: 28px;">

                <div>
                    <p class="receipt-label"
                        style="font-size:10px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#6b7280; margin-bottom:6px;">
                        PAYER DETAILS</p>
                    <p style="font-size:13px; font-weight:600; color:#e5e7eb; margin:0;">{{ $payment->school->name }}</p>
                    <p style="font-size:11px; font-family:monospace; color:#4b5563; margin-top:4px;">
                        {{ $payment->school->code }}
                    </p>
                    <p style="font-size:11px; color:#4b5563; margin-top:2px;">{{ auth()->user()->name }}</p>
                </div>

                <div style="text-align:right;">
                    <p class="receipt-label"
                        style="font-size:10px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#6b7280; margin-bottom:8px;">
                        TRANSACTION DETAILS</p>
                    <div style="display:flex; flex-direction:column; gap:5px;">
                        <div style="display:flex; gap:8px; align-items:baseline; justify-content:flex-end;">
                            <span style="font-size:10px; color:#6b7280;">Date</span>
                            <span style="font-size:12px; font-weight:600; color:#e5e7eb;">
                                {{ $payment->paid_at
        ? $payment->paid_at->format('d M Y, h:i A')
        : $payment->created_at->format('d M Y, h:i A') }}
                            </span>
                        </div>
                        <div style="display:flex; gap:8px; align-items:baseline; justify-content:flex-end;">
                            <span style="font-size:10px; color:#6b7280;">Method</span>
                            <span
                                style="font-size:12px; font-weight:600; color:#e5e7eb;">{{ $payment->payment_method ?? 'Cashfree' }}</span>
                        </div>
                        <div style="display:flex; gap:8px; align-items:baseline; justify-content:flex-end;">
                            <span style="font-size:10px; color:#6b7280;">Status</span>
                            <span style="font-size:12px; font-weight:600; color:#e5e7eb;">Successful</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ═══ TABLE ═══ --}}
            <div style="padding: 28px 44px 8px;">
                <p class="receipt-label"
                    style="font-size:10px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#6b7280; margin-bottom:16px;">
                    Candidates
                </p>
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr style="border-top:1px solid #1e293b; border-bottom:1px solid #1e293b;">
                            <th
                                style="padding:10px 8px; text-align:left; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#6b7280; width:32px;">
                                #</th>
                            <th
                                style="padding:10px 8px; text-align:left; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#6b7280;">
                                Name</th>
                            <th
                                style="padding:10px 8px; text-align:center; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#6b7280;">
                                Class</th>
                            <th
                                style="padding:10px 8px; text-align:center; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#6b7280;">
                                Gender</th>
                            <th
                                style="padding:10px 8px; text-align:right; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#6b7280;">
                                Fee (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment->students as $i => $student)
                            <tr style="border-bottom:1px solid #1a2433;">
                                <td style="padding:12px 8px; color:#4b5563;">{{ $i + 1 }}</td>
                                <td style="padding:12px 8px;">
                                    <span style="font-weight:600; color:#e5e7eb;">{{ $student->name }}</span>
                                    @if($student->registration_number)
                                        <br><span
                                            style="font-family:monospace; font-size:9px; color:#4b5563;">{{ $student->registration_number }}</span>
                                    @endif
                                </td>
                                <td style="padding:12px 8px; text-align:center; font-family:monospace; color:#9ca3af;">
                                    {{ $student->class->name ?? '—' }}
                                </td>
                                <td
                                    style="padding:12px 8px; text-align:center; text-transform:uppercase; font-size:11px; color:#6b7280;">
                                    {{ $student->gender }}
                                </td>
                                <td
                                    style="padding:12px 8px; text-align:right; font-family:monospace; font-weight:600; color:#e5e7eb;">
                                    ₹{{ number_format($student->pivot->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ═══ TOTAL ═══ --}}
            <div
                style="padding: 20px 44px; border-top:1px solid #1e293b; display:flex; justify-content:flex-end; align-items:center; gap:32px;">
                <p
                    style="font-size:11px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#6b7280; margin:0;">
                    Total Amount Paid</p>
                <p
                    style="font-size:30px; font-weight:900; font-family:monospace; color:#f9fafb; margin:0; letter-spacing:-1px;">
                    ₹{{ number_format($payment->amount, 2) }}</p>
            </div>
            <p style="font-size:10px; color:#4b5563; text-align:right; padding:0 44px 16px; margin:0;">Inclusive of all
                platform charges</p>

            {{-- ═══ FOOTER ═══ --}}
            <div
                style="padding: 20px 44px 36px; border-top:1px solid #1e293b; display:flex; justify-content:space-between; align-items:flex-end; gap:24px; flex-wrap:wrap;">
                <p style="font-size:10px; color:#4b5563; max-width:420px; line-height:1.7; margin:0;">
                    This is a system-generated registration fee receipt. The listed candidates have been automatically
                    submitted to the examination board for review and verification.
                    <br><span style="color:#374151; margin-top:4px; display:inline-block;">Generated:
                        {{ now()->format('d M Y, h:i A') }}</span>
                </p>
                <p
                    style="font-size:9px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#374151; margin:0; text-align:right;">
                    YES GENIUS TALENT SEARCH SEASON-4
                </p>
            </div>

        </div>
    </div>

    {{-- ── Print styles ── --}}
    <style>
        @media print {

            nav,
            aside,
            header,
            .no-print,
            #chat-container,
            footer {
                display: none !important;
            }

            body,
            main,
            #app,
            .content-area {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            #receipt-print-area {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .receipt-card {
                background: #fff !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .receipt-card * {
                color: #111827 !important;
            }

            .receipt-label {
                color: #6b7280 !important;
            }

            table tr {
                border-color: #e5e7eb !important;
            }
        }
    </style>

@endsection