@extends('layouts.auth')

@section('content')
<div>
    <!-- Title and Subtitle -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Welcome back</h1>
        <p class="text-sm text-slate-400 mt-1.5">Please enter your details</p>
    </div>

    <!-- Session Alert Banners -->
    @if (session('status'))
        <div class="mb-5 p-4 rounded-xl bg-emerald-950/40 border border-emerald-800/40 text-emerald-200 text-xs font-medium">
            {{ session('status') }}
        </div>
    @endif

    @if (session('success'))
        <div class="mb-5 p-4 rounded-xl bg-emerald-950/40 border border-emerald-800/40 text-emerald-200 text-xs font-medium">
            {{ session('success') }}
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

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false }">
        @csrf
        
        <!-- Email Input -->
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-300 mb-1.5">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email"
                class="block w-full px-4 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none transition-all duration-200 text-sm">
        </div>

        <!-- Password Input -->
        <div>
            <label for="password" class="block text-sm font-semibold text-slate-300 mb-1.5">Password</label>
            <div class="relative">
                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required placeholder="••••••••"
                    class="block w-full pl-4 pr-12 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none transition-all duration-200 text-sm">
                
                <!-- Eye Toggle Button -->
                <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition-colors cursor-pointer"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'">
                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.5 12c1.735 4.338 5.945 7.5 10.5 7.5 1.658 0 3.24-.383 4.649-1.075M6.228 6.228A10.45 10.45 0 0112 4.5c4.555 0 8.765 3.162 10.5 7.5a10.51 10.51 0 01-4.293 5.57M6.228 6.228L3 3m3.228 3.228l11.544 11.544M9.879 9.879A3 3 0 1014.12 14.12" />
                    </svg>
                    <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75A3.75 3.75 0 1012 8.25a3.75 3.75 0 000 7.5z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Controls Row -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox"
                    class="h-4 w-4 rounded border-slate-800 bg-slate-950 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900 cursor-pointer">
                <label for="remember" class="ml-2.5 block text-sm font-medium text-slate-400 cursor-pointer">Remember for 30 days</label>
            </div>
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-indigo-400 hover:text-indigo-300 hover:underline cursor-pointer">Forgot password</a>
        </div>

        <!-- Turnstile Captcha -->
        @if(config('services.cloudflare.turnstile_site_key'))
            <div class="flex justify-center pt-2">
                <div class="cf-turnstile" data-sitekey="{{ config('services.cloudflare.turnstile_site_key') }}" data-theme="dark"></div>
            </div>
        @endif

        <!-- Submit Button -->
        <button type="submit"
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-xl font-bold text-sm transition-all duration-200 shadow-md shadow-indigo-600/10 flex items-center justify-center gap-2 cursor-pointer mt-2">
            Sign in
        </button>
    </form>

    @if(config('services.cloudflare.turnstile_site_key'))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
</div>
@endsection
