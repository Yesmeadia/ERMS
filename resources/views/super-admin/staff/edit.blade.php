@extends('layouts.app')
@section('page_title', 'Edit Staff / Invigilator')
@section('content')
    <div class="max-w-3xl">
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('admin.staff.index') }}"
                class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition-colors mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Staff List
            </a>
            <h2 class="text-xl font-bold text-white">Edit Staff: {{ $staff->name }}</h2>
            <p class="text-sm text-slate-400">Modify credentials or assigned school</p>
        </div>

        {{-- Password Reset Quick Action Card --}}
        <div
            class="mb-6 bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 lg:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div>
                    <h3 class="text-sm font-semibold text-slate-200">Send Password Reset Email</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Send a secure link for the staff member to create or reset
                        their password</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.staff.reset-password', $staff->id) }}"
                onsubmit="return confirm('Send a password reset email to {{ $staff->email }}?')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/20 text-xs font-semibold transition-all cursor-pointer shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span>Send Reset Link</span>
                </button>
            </form>
        </div>

        {{-- Form --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 lg:p-8">
            <form method="POST" action="{{ route('admin.staff.update', $staff->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $staff->name) }}" required
                            placeholder="e.g. Muhammed"
                            class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none">
                        @error('name')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $staff->email) }}" required
                            placeholder="staff@example.com"
                            class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none">
                        @error('email')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="school_id" class="block text-sm font-medium text-slate-300 mb-2">Assigned School
                        (Optional)</label>
                    <select name="school_id" id="school_id"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none">
                        <option value="">-- Board Invigilator (Unassigned to any specific school) --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" @selected(old('school_id', $staff->school_id) == $school->id)>
                                {{ $school->name }} ({{ $school->code }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-1.5">Unassigned staff operate as board-level invigilators and can
                        scan tickets across all centers.</p>
                    @error('school_id')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-4 rounded-xl bg-slate-950/80 border border-slate-800/60">
                    <h3 class="text-sm font-semibold text-slate-300 mb-1">Set Password Manually (Optional)</h3>
                    <p class="text-xs text-slate-500 mb-4">Leave blank to keep the current password or use the "Send Reset
                        Link" button above.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-300 mb-2">New Password</label>
                            <input type="password" name="password" id="password"
                                class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none"
                                placeholder="Min 8 characters">
                            @error('password')
                                <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Confirm
                                New Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none"
                                placeholder="Confirm password">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800/60 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.staff.index') }}"
                        class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium px-5 py-2.5 rounded-xl transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 cursor-pointer">
                        Update Account
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection