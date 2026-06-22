@extends('layouts.app')
@section('page_title', 'Add School')
@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.schools.index') }}"
                class="p-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-white">Add New School</h2>
                <p class="text-sm text-slate-400 mt-0.5">Create a new school registration</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.schools.store') }}"
            class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-2">School Name <span
                            class="text-rose-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 @error('name') border-rose-500 @enderror"
                        placeholder="e.g. Government High School, City Centre">
                    @error('name')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">School Code <span
                            class="text-rose-400">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" required
                        class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('code') border-rose-500 @enderror"
                        placeholder="e.g. SCH001">
                    @error('code')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Email <span
                            class="text-rose-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('email') border-rose-500 @enderror"
                        placeholder="school@example.com">
                    @error('email')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Address <span
                            class="text-rose-400">*</span></label>
                    <textarea name="address" rows="2" required
                        class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('address') border-rose-500 @enderror"
                        placeholder="Full school address">{{ old('address') }}</textarea>
                    @error('address')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Zone <span
                            class="text-rose-400">*</span></label>
                    <select name="zone" required
                        class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('zone') border-rose-500 @enderror">
                        <option value="" disabled {{ old('zone') ? '' : 'selected' }}>Select Zone</option>
                        @foreach(['Poonch', 'Mandi', 'Srinagar', 'Rajouri', 'Surankote', 'Jammu', 'Mendar', 'Doda', 'Rajasthan', 'South', 'North East', 'Maharashtra'] as $zone)
                            <option value="{{ $zone }}" @selected(old('zone') === $zone)>{{ $zone }}</option>
                        @endforeach
                    </select>
                    @error('zone')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">State <span
                            class="text-rose-400">*</span></label>
                    <select name="state" required
                        class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('state') border-rose-500 @enderror">
                        <option value="" disabled {{ old('state') ? '' : 'selected' }}>Select State</option>
                        @foreach(['Jammu and Kashmir', 'Rajasthan', 'Karnatka', 'West Bengal', 'Bihar', 'Andhra Pradesh', 'Kerala', 'Maharashtra',] as $state)
                            <option value="{{ $state }}" @selected(old('state') === $state)>{{ $state }}</option>
                        @endforeach
                    </select>
                    @error('state')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Contact Person <span
                            class="text-rose-400">*</span></label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" required
                        class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('contact_person') border-rose-500 @enderror"
                        placeholder="Principal or Head of School">
                    @error('contact_person')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Mobile Number <span
                            class="text-rose-400">*</span></label>
                    <input type="text" name="mobile_number" value="{{ old('mobile_number') }}" required
                        class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('mobile_number') border-rose-500 @enderror"
                        placeholder="e.g. 9876543210">
                    @error('mobile_number')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex gap-4 pt-2">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-lg shadow-indigo-600/20 text-sm cursor-pointer">
                    Create School
                </button>
                <a href="{{ route('admin.schools.index') }}"
                    class="bg-slate-700 hover:bg-slate-600 text-slate-300 font-semibold px-6 py-3 rounded-xl transition-all text-sm">Cancel</a>
            </div>
        </form>
    </div>
@endsection