@extends('layouts.auth')

@section('content')
<div>
    <!-- Title and Subtitle -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Reset password</h1>
        <p class="text-sm text-slate-400 mt-1.5">Enter your email and we'll send you a password recovery link</p>
    </div>

    <!-- Status Alert Banners -->
    @if (session('status'))
        <div class="mb-5 p-4 rounded-xl bg-emerald-950/40 border border-emerald-800/40 text-emerald-200 text-xs font-medium">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-rose-950/40 border border-rose-800/40 text-rose-200 text-xs font-medium">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Forgot Password Form -->
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        
        <!-- Email Input -->
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-300 mb-1.5">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email"
                class="block w-full px-4 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none transition-all duration-200 text-sm">
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-xl font-bold text-sm transition-all duration-200 shadow-md shadow-indigo-600/10 flex items-center justify-center gap-2 cursor-pointer mt-2">
            Send Reset Link
        </button>
    </form>

    <!-- Back to Login -->
    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-slate-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Back to Login
        </a>
    </div>
</div>
@endsection
