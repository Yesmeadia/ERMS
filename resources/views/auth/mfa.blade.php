@extends('layouts.auth')

@section('content')
<div>
    <!-- Title and Subtitle -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Two-Factor Authentication</h1>
        <p class="text-sm text-slate-400 mt-1.5">Please enter the 6-digit verification code from your authenticator app to access your account.</p>
    </div>

    <!-- Session Alert Banners -->
    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-rose-950/40 border border-rose-800/40 text-rose-200 text-xs font-medium">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- MFA Form -->
    <form method="POST" action="{{ route('login.mfa') }}" class="space-y-5">
        @csrf
        
        <!-- Code Input -->
        <div>
            <label for="code" class="block text-sm font-semibold text-slate-300 mb-1.5">Verification Code</label>
            <input id="code" type="text" name="code" required autofocus placeholder="000000" maxlength="6" autocomplete="one-time-code"
                class="block w-full px-4 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none transition-all duration-200 text-center text-xl font-mono tracking-widest">
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-xl font-bold text-sm transition-all duration-200 shadow-md shadow-indigo-600/10 flex items-center justify-center gap-2 cursor-pointer mt-2">
            Verify & Proceed
        </button>

        <!-- Back Link -->
        <div class="text-center pt-2">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-400 transition-colors">
                Back to Sign In
            </a>
        </div>
    </form>
</div>
@endsection
