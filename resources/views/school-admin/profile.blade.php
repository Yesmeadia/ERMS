@extends('layouts.app')
@section('page_title', 'My Profile')
@section('content')
<div class="w-full">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white font-outfit">School Profile & Admin Settings</h2>
        <p class="text-sm text-slate-400 mt-0.5">Manage your personal admin account and update school profile information.</p>
    </div>

    <form method="POST" action="{{ route('school.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- User Profile Details Form -->
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 space-y-6">
            <h3 class="text-lg font-semibold text-white border-b border-slate-800/60 pb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Admin User Account Information
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
                    <label class="block text-sm font-medium text-slate-500 mb-2">Admin Name (Read-Only)</label>
                    <input type="text" value="{{ $user->name }}" disabled readonly
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-2">Admin Email Address (Read-Only)</label>
                    <input type="email" value="{{ $user->email }}" disabled readonly
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed">
                </div>
            </div>
        </div>

        <!-- School Information Details Form -->
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 space-y-6">
            <h3 class="text-lg font-semibold text-white border-b border-slate-800/60 pb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h18v18H3V3z" />
                </svg>
                School Details
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-2">School Name <span class="text-rose-400">*</span></label>
                    <input type="text" name="school_name" value="{{ old('school_name', $school->name) }}" required
                           class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('school_name') border-rose-500 @enderror">
                    @error('school_name')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-slate-500 mb-2">School Code (Read-Only)</label>
                    <input type="text" value="{{ $school->code }}" disabled readonly
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-500 mb-2">Address (Read-Only)</label>
                    <textarea rows="2" disabled readonly
                              class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed">{{ $school->address }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-2">Zone (Read-Only)</label>
                    <input type="text" value="{{ $school->zone }}" disabled readonly
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-2">State (Read-Only)</label>
                    <input type="text" value="{{ $school->state }}" disabled readonly
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-2">Contact Person (Read-Only)</label>
                    <input type="text" value="{{ $school->contact_person }}" disabled readonly
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-2">Mobile Number (Read-Only)</label>
                    <input type="text" value="{{ $school->mobile_number }}" disabled readonly
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed">
                </div>
            </div>

            <div class="flex gap-4 pt-2 border-t border-slate-800/60">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition-all text-sm cursor-pointer shadow-md shadow-indigo-600/10">Save All Changes</button>
            </div>
        </div>
    </form>

    <!-- Security & Password Panel -->
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 space-y-6 mt-6">
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
</div>
@endsection
