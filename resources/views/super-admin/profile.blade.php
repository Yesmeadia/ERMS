@extends('layouts.app')
@section('page_title', 'My Profile')
@section('content')
<div class="w-full">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white font-outfit">Personal Profile</h2>
        <p class="text-sm text-slate-400 mt-0.5">Manage your super admin account details and security settings.</p>
    </div>

    <div class="space-y-6">
        <!-- Profile Details Form -->
        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <h3 class="text-lg font-semibold text-white border-b border-slate-800/60 pb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Account Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Profile Image field -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Profile Photograph</label>
                    <div class="flex items-center gap-6" x-data="{ imgPreview: '{{ $user->profile_image ? asset('storage/' . $user->profile_image) : '' }}' }">
                        <div class="w-20 h-20 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden shrink-0 shadow-inner">
                            <template x-if="imgPreview">
                                <img :src="imgPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!imgPreview">
                                <div class="w-full h-full bg-gradient-to-tr from-indigo-600 to-indigo-400 text-white flex items-center justify-center font-bold text-xl">
                                    {{ mb_substr($user->name, 0, 2) }}
                                </div>
                            </template>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="profile_image" accept="image/jpeg,image/png,image/jpg"
                                   @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { imgPreview = e.target.result; }; reader.readAsDataURL(file); }"
                                   class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 file:text-indigo-400 hover:file:bg-indigo-600/20 file:cursor-pointer">
                            <p class="text-xs text-slate-500 mt-2">Max size: 2MB. Formats: JPEG, JPG, PNG.</p>
                        </div>
                    </div>
                    @error('profile_image')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Name <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('name') border-rose-500 @enderror">
                    @error('name')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Email Address <span class="text-rose-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('email') border-rose-500 @enderror">
                    @error('email')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-4 pt-2 border-t border-slate-800/60">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition-all text-sm cursor-pointer shadow-md shadow-indigo-600/10">Save Details</button>
            </div>
        </form>

        <!-- Security & Password Panel -->
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 space-y-6">
            <h3 class="text-lg font-semibold text-white border-b border-slate-800/60 pb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                Update Password
            </h3>
            
            <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-slate-300 mb-2">Current Password <span class="text-rose-400">*</span></label>
                        <input type="password" id="current_password" name="current_password" required
                               class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('current_password') border-rose-500 @enderror"
                               placeholder="Current password">
                        @error('current_password')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">New Password <span class="text-rose-400">*</span></label>
                        <input type="password" id="password" name="password" required
                               class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('password') border-rose-500 @enderror"
                               placeholder="Min 8 characters">
                        @error('password')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Confirm New Password <span class="text-rose-400">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500"
                               placeholder="Confirm new password">
                    </div>
                </div>
                <div class="flex gap-4 pt-2 border-t border-slate-800/60">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition-all text-sm cursor-pointer shadow-md shadow-indigo-600/10">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Multi-Factor Authentication Panel -->
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 space-y-6">
            <h3 class="text-lg font-semibold text-white border-b border-slate-800/60 pb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Multi-Factor Authentication (2FA)
            </h3>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-200 font-outfit">2FA Protection</p>
                    <p class="text-xs text-slate-400 mt-1">
                        @if($user->two_factor_enabled)
                            Two-Factor Authentication is currently <span class="text-emerald-400 font-bold">Enabled</span>.
                        @else
                            Two-Factor Authentication is currently <span class="text-rose-400 font-bold">Disabled</span>.
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.mfa.setup') }}" 
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl text-xs transition-all shadow-md shadow-indigo-600/10 cursor-pointer">
                    Configure 2FA settings &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
