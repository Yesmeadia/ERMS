@extends('layouts.app')

@section('page_title', 'Axis-Freecharge PG Sandbox')

@section('content')
<div class="max-w-md mx-auto py-6" id="gateway-page-wrapper" x-data="{
    paymentMethod: 'card',
    cardNumber: '',
    cardExpiry: '',
    cardCvv: '',
    cardName: '',
    upiId: '',
    selectedBank: '',
    processing: false,
    initiateSuccess() {
        this.processing = true;
        document.getElementById('gateway-callback-status').value = 'success';
        setTimeout(() => {
            document.getElementById('gateway-callback-form').submit();
        }, 1500);
    },
    initiateFailure() {
        this.processing = true;
        document.getElementById('gateway-callback-status').value = 'failed';
        setTimeout(() => {
            document.getElementById('gateway-callback-form').submit();
        }, 1500);
    }
}">
    {{-- Redirection Processing Modal --}}
    <div x-show="processing" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4" style="display: none;">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 max-w-sm w-full text-center space-y-6 shadow-2xl">
            <div class="relative w-20 h-20 mx-auto flex items-center justify-center">
                <div class="absolute inset-0 border-4 border-emerald-500/20 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-t-emerald-500 rounded-full animate-spin"></div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-emerald-400"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="space-y-2">
                <h4 class="text-base font-bold text-white">Communicating Callback</h4>
                <p class="text-xs text-slate-400">Processing response and returning back to ERMS portal...</p>
            </div>
        </div>
    </div>

    {{-- Sandboxed Domain Header --}}
    <div class="mb-4 text-center">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-450 animate-ping"></span>
            PG Sandbox Environment
        </div>
        <p class="text-[10px] text-slate-500 font-mono mt-1.5">Domain: https://sandbox-axispg.freecharge.in</p>
    </div>

    {{-- Hosted Checkout Card --}}
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-3xl overflow-hidden shadow-2xl">
        {{-- Header --}}
        <div class="bg-slate-950/40 px-6 py-5 border-b border-slate-800/80 flex items-center justify-between">
            <div class="flex items-center gap-2">
                {{-- Axis / Freecharge themed mock logo --}}
                <div class="w-8 h-8 rounded-lg bg-rose-500 flex items-center justify-center font-black text-white text-sm tracking-tight shadow-md">
                    fc
                </div>
                <div>
                    <h3 class="text-xs font-black text-white tracking-widest uppercase">freecharge</h3>
                    <p class="text-[8px] font-bold text-slate-500 tracking-wider">BY AXIS BANK</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[8px] text-slate-500 uppercase tracking-widest font-semibold">Amount to Pay</p>
                <p class="text-lg font-black text-emerald-400 font-mono">₹{{ number_format($payment->amount, 2) }}</p>
            </div>
        </div>

        {{-- Merchant Details --}}
        <div class="px-6 py-3.5 bg-slate-950/20 border-b border-slate-800/60 flex items-center justify-between text-xs">
            <span class="text-slate-400 font-medium">Merchant: <strong class="text-slate-350">ERMS BOARD REGISTRATION</strong></span>
            <span class="text-slate-400 font-medium">ID: <strong class="text-slate-350 font-mono">M_TXN_{{ substr($payment->transaction_id, 4, 6) }}</strong></span>
        </div>

        {{-- Payment Methods Tab --}}
        <div class="p-6">
            <div class="flex border-b border-slate-800/80 mb-5">
                <button type="button" @click="paymentMethod = 'card'"
                        class="flex-1 pb-3 text-xs font-bold uppercase tracking-wider text-center cursor-pointer transition-all border-b-2"
                        :class="paymentMethod === 'card' ? 'border-indigo-500 text-white' : 'border-transparent text-slate-500 hover:text-slate-400'">
                    Card
                </button>
                <button type="button" @click="paymentMethod = 'upi'"
                        class="flex-1 pb-3 text-xs font-bold uppercase tracking-wider text-center cursor-pointer transition-all border-b-2"
                        :class="paymentMethod === 'upi' ? 'border-indigo-500 text-white' : 'border-transparent text-slate-500 hover:text-slate-400'">
                    UPI
                </button>
                <button type="button" @click="paymentMethod = 'netbanking'"
                        class="flex-1 pb-3 text-xs font-bold uppercase tracking-wider text-center cursor-pointer transition-all border-b-2"
                        :class="paymentMethod === 'netbanking' ? 'border-indigo-500 text-white' : 'border-transparent text-slate-500 hover:text-slate-400'">
                    NetBanking
                </button>
            </div>

            {{-- 1. Card Layout --}}
            <div x-show="paymentMethod === 'card'" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Card Number</label>
                    <input type="text" x-model="cardNumber" placeholder="4111 2222 3333 4444"
                           class="w-full bg-slate-950/40 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 font-mono tracking-widest">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Expiry</label>
                        <input type="text" x-model="cardExpiry" placeholder="MM/YY"
                               class="w-full bg-slate-950/40 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">CVV</label>
                        <input type="password" x-model="cardCvv" placeholder="•••"
                               class="w-full bg-slate-950/40 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 font-mono">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Name on Card</label>
                    <input type="text" x-model="cardName" placeholder="Cardholder Name"
                           class="w-full bg-slate-950/40 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 uppercase font-medium">
                </div>
            </div>

            {{-- 2. UPI Layout --}}
            <div x-show="paymentMethod === 'upi'" class="space-y-4" style="display: none;">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Enter VPA / UPI ID</label>
                    <input type="text" x-model="upiId" placeholder="e.g. schooladmin@axisbank"
                           class="w-full bg-slate-950/40 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 font-mono">
                </div>
                <p class="text-[9px] text-slate-500 leading-normal">A request will be sent to your UPI app for collection approval.</p>
            </div>

            {{-- 3. Netbanking Layout --}}
            <div x-show="paymentMethod === 'netbanking'" class="space-y-4" style="display: none;">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Select Bank</label>
                    <select x-model="selectedBank" class="w-full bg-slate-950/40 border border-slate-800/80 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500 font-medium">
                        <option value="">Choose Bank</option>
                        <option value="axis">Axis Bank</option>
                        <option value="hdfc">HDFC Bank</option>
                        <option value="icici">ICICI Bank</option>
                        <option value="sbi">State Bank of India</option>
                    </select>
                </div>
            </div>
            
            {{-- Simulator Actions --}}
            <div class="mt-8 pt-6 border-t border-slate-800/80 space-y-3">
                <button type="button" @click="initiateSuccess()"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg shadow-indigo-600/20 text-xs cursor-pointer flex items-center justify-center gap-1.5 active:scale-98">
                    Simulate Success Callback
                </button>
                <button type="button" @click="initiateFailure()"
                        class="w-full bg-slate-800 hover:bg-slate-700 text-rose-400 font-semibold py-2.5 px-6 rounded-xl transition-all border border-slate-700/60 text-xs cursor-pointer flex items-center justify-center gap-1.5 active:scale-98">
                    Simulate Failure Callback
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden Form to Post Callback to process --}}
    <form id="gateway-callback-form" method="POST" action="{{ route('school.payments.process') }}" class="hidden">
        @csrf
        <input type="hidden" name="payment_id" value="{{ $payment->id }}">
        <input type="hidden" name="status" id="gateway-callback-status" value="">
    </form>
</div>
@endsection
