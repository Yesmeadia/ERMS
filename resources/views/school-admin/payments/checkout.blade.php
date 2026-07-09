@extends('layouts.app')

@section('page_title', 'Confirm Checkout')

@section('content')
<div class="w-full py-6" id="checkout-page-wrapper" x-data="{ processing: false }">
    {{-- Processing Loader Overlay --}}
    <template x-if="processing">
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
            <div
                class="bg-slate-900 border border-slate-800 rounded-3xl p-8 max-w-sm w-full text-center space-y-6 shadow-2xl">
                <div class="relative w-20 h-20 mx-auto flex items-center justify-center">
                    <div class="absolute inset-0 border-4 border-indigo-500/20 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-t-indigo-500 rounded-full animate-spin"></div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-8 h-8 text-indigo-400 animate-pulse">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <h4 class="text-base font-bold text-white">Processing Payment</h4>
                    <p class="text-xs text-slate-400">Verifying transaction with Razorpay. Please wait...</p>
                </div>
            </div>
        </div>
    </template>

    {{-- Back Link & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-3">
            <a href="{{ route('school.students.index') }}"
                class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:bg-slate-800 hover:text-white transition-all shadow-lg cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-extrabold text-white tracking-tight">Confirm checkout &amp; pay</h2>
                <p class="text-xs text-slate-400 mt-0.5">Verify candidate details before proceeding to Razorpay portal.
                </p>
            </div>
        </div>
        {{-- Razorpay Trust Badge --}}
        @isset($razorpayOrderId)
            <div
                class="flex items-center gap-1.5 bg-emerald-950/30 border border-emerald-800/30 text-emerald-400 text-[10px] font-bold px-4 py-2 rounded-full self-start sm:self-auto">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="w-3.5 h-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286z" />
                </svg>
                Secured by Razorpay
            </div>
        @endisset
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-950/30 border border-rose-800/40 text-rose-300 rounded-2xl text-xs">
            {{ session('error') }}
        </div>
    @endif

    {{-- ─── TWO-COLUMN RESPONSIVE LAYOUT ─── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left column: Candidates table ledger (Span 7) --}}
        <div
            class="lg:col-span-7 bg-slate-900/40 backdrop-blur-md border border-slate-800/60 rounded-3xl shadow-xl overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-800/60 bg-slate-950/20">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-200 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-4 h-4 text-indigo-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    Selected Candidates ({{ count($students) }})
                </h3>
            </div>
            <div class="overflow-x-auto max-h-[480px] overflow-y-auto pr-1">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-800 bg-slate-950/10">
                            <th class="py-3 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                Candidate</th>
                            <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">
                                Class</th>
                            <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">
                                Category</th>
                            <th class="py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">
                                Gender</th>
                            <th
                                class="py-3 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">
                                Fee (INR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/60">
                        @foreach($students as $student)
                            <tr class="hover:bg-slate-800/10 transition-colors">
                                <td class="py-3 px-6">
                                    <span class="font-bold text-slate-200 text-xs block">{{ $student->name }}</span>
                                    <span
                                        class="text-[9px] font-mono text-slate-500 block mt-0.5">{{ $student->registration_number ?? 'No Reg ID' }}</span>
                                </td>
                                <td class="py-3 text-center text-slate-350 font-mono">
                                    {{ $student->class->code }}
                                </td>
                                <td class="py-3 text-center text-slate-350">
                                    {{ $student->category->name ?? '—' }}
                                </td>
                                <td class="py-3 text-center text-slate-400 uppercase">
                                    {{ $student->gender }}
                                </td>
                                <td class="py-3 px-6 text-right font-mono font-bold text-slate-200">
                                    ₹{{ number_format($student->registration_fee, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right column: Payer Summary & Form Checkout controls (Span 5) --}}
        <div
            class="lg:col-span-5 bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <div
                class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500">
            </div>

            <div class="flex items-center justify-between border-b border-slate-800/80 pb-6 mb-6">
                <div>
                    <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-wider">Payer Details</span>
                    <h3 class="text-base font-bold text-white mt-1">{{ auth()->user()->school->name }}</h3>
                    <p class="text-xs text-slate-500 mt-1">School Code: <span
                            class="font-mono">{{ auth()->user()->school->code }}</span></p>
                </div>
            </div>

            {{-- Breakdown --}}
            <div class="space-y-4 mb-8">
                <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Class-wise Breakdown</h4>
                <div class="space-y-2">
                    @foreach($classBreakdown as $class)
                        <div
                            class="flex justify-between items-center py-2 px-4 bg-slate-950/20 border border-slate-800/60 rounded-xl">
                            <div>
                                <span class="text-xs font-bold text-slate-200">{{ $class['name'] }}</span>
                                <span class="text-[10px] text-slate-500 ml-1">({{ $class['count'] }} candidates x
                                    ₹{{ number_format($class['fee'], 0) }})</span>
                            </div>
                            <span
                                class="text-xs font-bold text-slate-350 font-mono">₹{{ number_format($class['total'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Order Summary --}}
            <div
                class="border-t border-slate-800/80 pt-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Amount Due</p>
                    <p class="text-3xl font-black text-white font-mono mt-1">₹{{ number_format($totalAmount, 2) }}</p>
                </div>
                <div class="text-xs text-slate-500 leading-normal max-w-xs">
                    @isset($razorpayOrderId)
                        <span class="block text-[10px] text-emerald-500 font-mono font-semibold mb-1">Order ID:
                            {{ $razorpayOrderId }}</span>
                        PCI-DSS compliant payment processing handled securely by Razorpay.
                    @else
                        Create order to generate payment gateway details.
                    @endisset
                </div>
            </div>

            {{-- ── STEP 1: First visit — show "Pay Now" which posts to initiate() ──────── --}}
            @empty($razorpayOrderId)
            <form method="POST" action="{{ route('school.payments.initiate') }}" @submit="processing = true"
                id="initiate-form">
                @csrf
                @foreach($students as $student)
                    <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
                @endforeach

                <div class="space-y-4">
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-lg shadow-indigo-600/30 text-sm cursor-pointer flex items-center justify-center gap-2 duration-200 active:scale-[0.98]">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                        Pay Now with Razorpay
                    </button>
                    <p class="text-[10px] text-slate-500 text-center">
                        This action redirects requests securely and generates a checkout session.
                    </p>
                </div>
            </form>
            @endisset

            {{-- ── STEP 2: Returned from initiate() — open Razorpay modal automatically ─ --}}
            @isset($razorpayOrderId)
                {{-- Hidden callback form — submitted by Razorpay JS on success --}}
                <form method="POST" action="{{ route('school.payments.callback') }}" id="razorpay-callback-form"
                    @submit="processing = true">
                    @csrf
                    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                    <input type="hidden" name="razorpay_order_id" id="razorpay_order_id" value="{{ $razorpayOrderId }}">
                    <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                    <input type="hidden" name="payment_db_id" value="{{ $paymentDbId }}">
                </form>

                <button type="button" id="razorpay-trigger-btn"
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-lg shadow-indigo-600/30 text-sm cursor-pointer flex items-center justify-center gap-2 duration-200 active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                    </svg>
                    Complete Payment — ₹{{ number_format($totalAmount, 2) }}
                </button>
                <p class="text-[10px] text-slate-500 text-center mt-3">
                    The Razorpay secure payment window is ready. Click above if it doesn't open automatically.
                </p>

                <script @nonce src="https://checkout.razorpay.com/v1/checkout.js"></script>
                <script @nonce>
                    const options = {
                        key: "{{ $razorpayKeyId }}",
                        amount: {{ $razorpayAmount }},
                        currency: "INR",
                        // Business name shown in modal header
                        name: "YES GENIUS NATIONAL LEVEL TALENT SEARCH",
                        // Full name visible below the header line
                        description: "YES GENIUS NATIONAL LEVEL TALENT SEARCH — Registration Fee",
                        // Organisation logo shown at top of modal
                        image: "{{ asset('icon.png') }}",
                        order_id: "{{ $razorpayOrderId }}",
                        prefill: {
                            name: "{{ $adminName }}",
                            email: "{{ $adminEmail }}",
                        },
                        // Make UPI the default / first shown method
                        config: {
                            display: {
                                blocks: {
                                    upi: {
                                        name: "Pay via UPI",
                                        instruments: [
                                            { method: "upi" }
                                        ]
                                    },
                                    other: {
                                        name: "Other Payment Methods",
                                        instruments: [
                                            { method: "card" },
                                            { method: "netbanking" },
                                            { method: "wallet" }
                                        ]
                                    }
                                },
                                sequence: ["block.upi", "block.other"],
                                preferences: {
                                    show_default_blocks: false
                                }
                            }
                        },
                        theme: {
                            color: "#23ed26ff"
                        },
                        handler: function (response) {
                            // Populate hidden form fields and submit for server-side verification
                            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                            document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                            document.getElementById('razorpay_signature').value = response.razorpay_signature;
                            document.getElementById('razorpay-callback-form').submit();
                        },
                        modal: {
                            ondismiss: function () {
                                console.log('Razorpay modal dismissed.');
                            }
                        }
                    };

                    const rzp = new Razorpay(options);

                    // Auto-open on page load
                    window.addEventListener('load', function () {
                        rzp.open();
                    });

                    // Also allow manual trigger via button
                    document.getElementById('razorpay-trigger-btn').addEventListener('click', function () {
                        rzp.open();
                    });
                </script>
            @endisset

        </div>
    </div>
</div>
@endsection