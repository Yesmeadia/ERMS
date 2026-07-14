@extends('layouts.auth')

@section('content')
<div x-data="{ mode: 'totp' }">
    <!-- Title and Subtitle -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Two-Factor Authentication</h1>
        <p class="text-sm text-slate-400 mt-1.5" x-show="mode === 'totp'">
            Enter the 6-digit verification code from your authenticator app.
        </p>
        <p class="text-sm text-slate-400 mt-1.5" x-show="mode === 'backup'" style="display:none;">
            Enter one of your 8-character backup recovery codes.
        </p>
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

        <!-- TOTP Code Input -->
        <div x-show="mode === 'totp'">
            <label for="code_totp" class="block text-sm font-semibold text-slate-300 mb-1.5">Authenticator Code</label>
            <input id="code_totp" type="text" name="code" x-bind:required="mode === 'totp'" x-bind:disabled="mode === 'backup'"
                autofocus placeholder="000000" maxlength="6" autocomplete="one-time-code" inputmode="numeric"
                class="block w-full px-4 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none transition-all duration-200 text-center text-xl font-mono tracking-widest">
        </div>

        <!-- Backup Code Input -->
        <div x-show="mode === 'backup'" style="display:none;">
            <label for="code_backup" class="block text-sm font-semibold text-slate-300 mb-1.5">Backup Recovery Code</label>
            <input id="code_backup" type="text" name="code" x-bind:required="mode === 'backup'" x-bind:disabled="mode === 'totp'"
                placeholder="XXXXXXXX" maxlength="8" autocomplete="off" autocapitalize="characters"
                class="block w-full px-4 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none transition-all duration-200 text-center text-xl font-mono tracking-widest uppercase">
            <p class="mt-2 text-xs text-slate-500">Each backup code can only be used once.</p>
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-xl font-bold text-sm transition-all duration-200 shadow-md shadow-indigo-600/10 flex items-center justify-center gap-2 cursor-pointer mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            <span x-text="mode === 'totp' ? 'Verify & Proceed' : 'Use Recovery Code'">Verify &amp; Proceed</span>
        </button>

        <!-- Toggle Link -->
        <div class="text-center pt-1">
            <button type="button" @click="mode = (mode === 'totp' ? 'backup' : 'totp')"
                class="text-xs font-semibold text-slate-500 hover:text-indigo-400 transition-colors cursor-pointer bg-transparent border-0 p-0">
                <span x-show="mode === 'totp'">
                    Can't access your authenticator app? <span class="text-indigo-400 underline">Use a backup code</span>
                </span>
                <span x-show="mode === 'backup'" style="display:none;">
                    Have your authenticator app? <span class="text-indigo-400 underline">Use authenticator code</span>
                </span>
            </button>
        </div>

        <!-- Back Link -->
        <div class="text-center pt-1">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-400 transition-colors">
                Back to Sign In
            </a>
        </div>
    </form>
</div>

<script>
    // Sync the active input's value to the disabled one when toggling, so only one "code" field is submitted.
    // Alpine handles disabled state, but we ensure the right name field is always populated.
    document.addEventListener('alpine:initialized', () => {
        // When form submits, clear the inactive input so only active one is sent
        document.querySelector('form').addEventListener('submit', function () {
            const totpInput = document.getElementById('code_totp');
            const backupInput = document.getElementById('code_backup');
            if (totpInput.disabled) totpInput.removeAttribute('name');
            if (backupInput.disabled) backupInput.removeAttribute('name');
        });
    });
</script>
@endsection
