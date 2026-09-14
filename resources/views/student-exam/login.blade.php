@extends('layouts.auth')

@section('page_title', 'Student Online Examination Login')
@section('page_description', 'Log in using your official ERMS Registration Number and Date of Birth to access your online examination.')

@section('content')
    <div>
        <!-- Title and Subtitle -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Online Examination Portal</h1>
            <p class="text-sm text-slate-400 mt-1.5">Enter details below to access your exam session</p>
        </div>

        <!-- Session Alert Banners -->
        @if (session('status'))
            <div
                class="mb-5 p-4 rounded-xl bg-emerald-950/40 border border-emerald-800/40 text-emerald-200 text-xs font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-5 p-4 rounded-xl bg-rose-950/40 border border-rose-800/40 text-rose-200 text-xs font-medium leading-relaxed">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-950/40 border border-rose-800/40 text-rose-200 text-xs font-medium">
                <p class="font-semibold text-xs uppercase tracking-wider mb-1">Access Notice</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('online-exam.login.submit') }}" id="examLoginForm" class="space-y-5">
            @csrf
            <input type="hidden" name="device_fingerprint" id="device_fingerprint" value="">

            <!-- Registration Number Input -->
            <div>
                <label for="registration_number" class="block text-sm font-semibold text-slate-300 mb-1.5">
                    Registration Number
                </label>
                <input id="registration_number" type="text" name="registration_number"
                    value="{{ old('registration_number') }}" required autofocus placeholder="e.g. 50021"
                    class="block w-full px-4 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 font-mono placeholder-slate-600 focus:outline-none transition-all duration-200 text-sm">
            </div>

            <!-- Date of Birth Input -->
            <div>
                <label for="dob" class="block text-sm font-semibold text-slate-300 mb-1.5">
                    Candidate Date of Birth (DOB)
                </label>
                <input id="dob" type="date" name="dob" value="{{ old('dob') }}" required
                    class="block w-full px-4 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none transition-all duration-200 text-sm">
            </div>

            <!-- Security & Single-Session Notice -->
            <div
                class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800/80 text-xs text-slate-400 leading-relaxed flex items-start gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>Only one active session is allowed per candidate. Camera and fullscreen permissions will be verified
                    prior to test start.</span>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submitBtn"
                class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-xl font-bold text-sm transition-all duration-200 shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2 cursor-pointer mt-2">
                <span>Verify & Access Exam</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </form>
    </div>

    <script @nonce>
        // Generate browser-level device fingerprint hash to enforce single active session
        document.addEventListener('DOMContentLoaded', () => {
            const fp = [
                navigator.userAgent,
                screen.width + 'x' + screen.height,
                screen.colorDepth,
                new Date().getTimezoneOffset(),
                navigator.hardwareConcurrency || 'unk'
            ].join('|');

            let hash = 0;
            for (let i = 0; i < fp.length; i++) {
                hash = ((hash << 5) - hash) + fp.charCodeAt(i);
                hash |= 0;
            }
            const input = document.getElementById('device_fingerprint');
            if (input) {
                input.value = 'dev_' + Math.abs(hash);
            }
        });
    </script>
@endsection