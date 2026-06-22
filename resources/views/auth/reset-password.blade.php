@extends('layouts.auth')

@section('content')
<div>
    <!-- Title and Subtitle -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Create new password</h1>
        <p class="text-sm text-slate-400 mt-1.5">Set a new secure password for your account</p>
    </div>

    <!-- Error Alert Banners -->
    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-rose-950/40 border border-rose-800/40 text-rose-200 text-xs font-medium">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Reset Password Form -->
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5" x-data="{ showPassword: false }">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <!-- Readonly Account Email Display -->
        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Account Email</label>
            <input type="text" value="{{ $email }}" disabled 
                class="block w-full px-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-400 text-sm select-none focus:outline-none">
        </div>

        <!-- New Password Input -->
        <div>
            <label for="password" class="block text-sm font-semibold text-slate-300 mb-1.5">New Password</label>
            <div class="relative">
                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autofocus placeholder="••••••••"
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

        <!-- Confirm New Password Input -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-300 mb-1.5">Confirm New Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••"
                class="block w-full px-4 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none transition-all duration-200 text-sm">
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-xl font-bold text-sm transition-all duration-200 shadow-md shadow-indigo-600/10 flex items-center justify-center gap-2 cursor-pointer mt-2">
            Update Password
        </button>
    </form>
</div>
@endsection
