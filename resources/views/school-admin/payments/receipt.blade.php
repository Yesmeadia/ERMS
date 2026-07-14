@extends('layouts.app')

@section('page_title', 'Payment Receipt')

@section('content')
    <div class="max-w-3xl mx-auto py-6" id="receipt-print-area">
        {{-- ── Back button (Hidden on Print) ─────────────────────────────────── --}}
        <div class="mb-6 flex justify-between items-center no-print">
            <a href="{{ route('school.payments.index') }}"
                class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Back to Payments
            </a>

            <button type="button" id="print-receipt-btn"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-950/20 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12-1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                </svg>
                Print Receipt
            </button>
        </div>

        <script @nonce>
            document.addEventListener('DOMContentLoaded', function() {
                const printBtn = document.getElementById('print-receipt-btn');
                if (printBtn) {
                    printBtn.addEventListener('click', function() {
                        window.print();
                    });
                }
            });
        </script>

        {{-- ── Receipt Document ─────────────────────────────────────────────── --}}
        <div
            class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden receipt-card">
            {{-- Deco line --}}
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500">
            </div>

            {{-- Top Info Row --}}
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 border-b border-slate-800/80 pb-6">
                <div>
                    <span class="text-xs text-indigo-400 font-bold uppercase tracking-wider">Official Payment Receipt</span>
                    <h1 class="text-2xl font-black text-white mt-1 tracking-tight">YES GENIUS EXAMINATION</h1>
                    <p class="text-xs text-slate-500 mt-1">Examination Registration Management System</p>
                </div>
                <div
                    class="sm:text-right flex sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-4">
                    <div class="text-left sm:text-right mt-1">
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Receipt / Payment ID</p>
                        <p class="text-sm font-mono font-bold text-slate-300 mt-0.5">{{ $payment->transaction_id ?? '—' }}</p>
                        @if($payment->cashfree_payment_id && $payment->cashfree_payment_id !== $payment->transaction_id)
                            <p class="text-[10px] text-slate-500 font-mono mt-0.5">Cashfree Ref: {{ $payment->cashfree_payment_id }}</p>
                        @elseif($payment->razorpay_payment_id && $payment->razorpay_payment_id !== $payment->transaction_id)
                            <p class="text-[10px] text-slate-500 font-mono mt-0.5">Gateway Ref: {{ $payment->razorpay_payment_id }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Meta details --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-800/80 text-xs">
                <div>
                    <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">Payer Details</h3>
                    <p class="text-sm font-bold text-slate-200">{{ $payment->school->name }}</p>
                    <p class="text-slate-400 mt-1">School Code: <span
                            class="font-mono font-bold text-slate-350">{{ $payment->school->code }}</span></p>
                    <p class="text-slate-400 mt-0.5">Admin: {{ auth()->user()->name }}</p>
                </div>
                <div class="sm:text-right">
                    <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">Transaction Details
                    </h3>
                    <p class="text-slate-300">Date: <strong
                            class="text-slate-200">{{ $payment->paid_at ? $payment->paid_at->format('d M Y, h:i A') : $payment->created_at->format('d M Y, h:i A') }}</strong>
                    </p>
                    <p class="text-slate-400 mt-1">Payment Method: <span>
                            {{ $payment->payment_method ?? 'Cashfree' }}
                        </span>
                    </p>
                    <p class="text-slate-400 mt-0.5">Status: <span
                            class="text-emerald-400 font-bold uppercase tracking-wider">Successful</span></p>
                </div>
            </div>

            {{-- Table --}}
            <div class="py-6">
                <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Candidates List
                    ({{ $payment->students->count() }})</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="border-b border-slate-800 bg-slate-950/20">
                                <th class="py-2.5 px-3 text-slate-500 font-bold uppercase tracking-wider">Candidate Name
                                </th>
                                <th class="py-2.5 px-3 text-slate-500 font-bold uppercase tracking-wider text-center">Class
                                </th>
                                <th class="py-2.5 px-3 text-slate-500 font-bold uppercase tracking-wider text-center">Gender
                                </th>
                                <th class="py-2.5 px-3 text-slate-500 font-bold uppercase tracking-wider text-right">Fee
                                    (INR)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40">
                            @foreach($payment->students as $student)
                                <tr class="border-b border-slate-850/50">
                                    <td class="py-3 px-3">
                                        <p class="font-bold text-slate-200">{{ $student->name }}</p>
                                        @if($student->registration_number)
                                            <p class="text-[9px] text-slate-500 font-mono mt-0.5">
                                                {{ $student->registration_number }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center text-slate-300 font-semibold font-mono">
                                        {{ $student->class->name ?? '—' }}</td>
                                    <td class="py-3 px-3 text-center text-slate-400 uppercase">{{ $student->gender }}</td>
                                    <td class="py-3 px-3 text-right text-slate-200 font-mono font-bold">
                                        ₹{{ number_format($student->pivot->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer summary --}}
            <div class="border-t border-slate-800/80 pt-6 flex justify-between items-start gap-4">
                <div class="text-[10px] text-slate-500 leading-normal max-w-sm">
                    <p class="font-semibold text-slate-455">Note:</p>
                    <p class="mt-0.5">This is a system generated registration fee receipt. These candidates have been
                        automatically submitted to the board for review and verification.</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Amount Paid</p>
                    <p class="text-2xl font-black text-emerald-400 font-mono mt-1">₹{{ number_format($payment->amount, 2) }}
                    </p>
                    <p class="text-[9px] text-slate-400 font-medium mt-1">Inclusive of all platform charges</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Printable CSS Overrides ─────────────────────────────────────────── --}}
    <style>
        @media print {

            /* Hide layout sidebar, header ribbon, notifications, chat components */
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
                background: #ffffff !important;
                color: #0f172a !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }

            #receipt-print-area {
                max-width: 100% !important;
                width: 100% !important;
                padding: 20px !important;
            }

            .receipt-card {
                background: #ffffff !important;
                border: 2px solid #e2e8f0 !important;
                color: #0f172a !important;
                box-shadow: none !important;
                border-radius: 0px !important;
                padding: 20px !important;
            }

            .receipt-card * {
                color: #0f172a !important;
            }

            .receipt-card .text-indigo-400,
            .receipt-card .text-emerald-400 {
                color: #1e3a8a !important;
                /* Dark blue for printing readability */
            }

            .receipt-card border-slate-800/80 {
                border-color: #cbd5e1 !important;
            }

            .print-badge-paid {
                background: #d1fae5 !important;
                color: #065f46 !important;
                border: 1px solid #a7f3d0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .receipt-card table thead tr {
                background-color: #f1f5f9 !important;
                border-bottom: 2px solid #cbd5e1 !important;
            }

            .receipt-card table tbody tr {
                border-bottom: 1px solid #e2e8f0 !important;
            }
        }
    </style>
@endsection