@extends('layouts.app')

@section('page_title', 'Confirm Checkout')

@section('content')
<div class="max-w-2xl mx-auto py-6" id="checkout-page-wrapper" x-data="{ processing: false }">
    {{-- Processing Loader Overlay --}}
    <template x-if="processing">
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 max-w-sm w-full text-center space-y-6 shadow-2xl">
                <div class="relative w-20 h-20 mx-auto flex items-center justify-center">
                    <div class="absolute inset-0 border-4 border-indigo-500/20 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-t-indigo-500 rounded-full animate-spin"></div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-indigo-400 animate-pulse"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" /></svg>
                </div>
                
                <div class="space-y-2">
                    <h4 class="text-base font-bold text-white">Opening Gateway Session</h4>
                    <p class="text-xs text-slate-400">Connecting you to Axis Bank / Freecharge sandbox environment...</p>
                </div>
            </div>
        </div>
    </template>

    {{-- Back Link & Header --}}
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
            <a href="{{ route('school.students.index') }}" 
               class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h2 class="text-lg font-extrabold text-white tracking-tight">Confirm Checkout</h2>
                <p class="text-xs text-slate-400 mt-0.5">Verify details before proceeding to payment collections.</p>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-950/30 border border-rose-800/40 text-rose-300 rounded-2xl text-xs">
            {{ session('error') }}
        </div>
    @endif

    {{-- Invoice Card --}}
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500"></div>

        <div class="flex items-center justify-between border-b border-slate-800/80 pb-6 mb-6">
            <div>
                <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">Payer School</span>
                <h3 class="text-base font-bold text-white mt-1">{{ auth()->user()->school->name }}</h3>
                <p class="text-xs text-slate-500 mt-1">School Code: <span class="font-mono">{{ auth()->user()->school->code }}</span></p>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Candidates count</span>
                <p class="text-2xl font-black text-white font-mono mt-1">{{ count($students) }}</p>
            </div>
        </div>

        {{-- Breakdown --}}
        <div class="space-y-4 mb-8">
            <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Class-wise Breakdown</h4>
            <div class="space-y-2">
                @foreach($classBreakdown as $class)
                    <div class="flex justify-between items-center py-2 px-4 bg-slate-950/20 border border-slate-800/60 rounded-xl">
                        <div>
                            <span class="text-xs font-bold text-slate-200">{{ $class['name'] }}</span>
                            <span class="text-[10px] text-slate-500 ml-1">({{ $class['count'] }} candidates x ₹{{ number_format($class['fee'], 0) }})</span>
                        </div>
                        <span class="text-xs font-bold text-slate-300 font-mono">₹{{ number_format($class['total'], 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Order summary --}}
        <div class="border-t border-slate-800/80 pt-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Amount Due</p>
                <p class="text-3xl font-black text-white font-mono mt-1">₹{{ number_format($totalAmount, 2) }}</p>
            </div>
            <div class="text-xs text-slate-500 leading-normal max-w-xs">
                Axis-Freecharge PG sandbox portal will collect this transaction amount under sandbox credentials.
            </div>
        </div>

        {{-- Initiate Payment Form --}}
        <form method="POST" action="{{ route('school.payments.initiate') }}" @submit="processing = true">
            @csrf
            
            @foreach($students as $student)
                <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
            @endforeach

            <div class="space-y-4">
                <button type="submit"
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-550 text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-lg shadow-indigo-600/30 text-sm cursor-pointer flex items-center justify-center gap-2 duration-200 active:scale-98">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                    Proceed to Axis-Freecharge Gateway
                </button>
                <p class="text-[10px] text-slate-500 text-center">
                    Clicking above redirects you to the Axis Bank / Freecharge secure sandbox page.
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
