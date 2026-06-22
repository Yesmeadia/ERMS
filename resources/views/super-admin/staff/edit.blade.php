@extends('layouts.app')
@section('page_title', 'Edit Staff / Invigilator')
@section('content')
<div class="max-w-3xl">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('admin.staff.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition-colors mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Back to Staff List
        </a>
        <h2 class="text-xl font-bold text-white">Edit Staff: {{ $staff->name }}</h2>
        <p class="text-sm text-slate-400">Modify credentials or assigned school</p>
    </div>

    {{-- Form --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 lg:p-8">
        <form method="POST" action="{{ route('admin.staff.update', $staff->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Profile Image field -->
            <div class="pb-4 border-b border-slate-800/40">
                <label class="block text-sm font-medium text-slate-300 mb-2">Profile Photograph</label>
                <div class="flex items-center gap-6" x-data="{ imgPreview: '{{ $staff->profile_image ? asset('storage/' . $staff->profile_image) : '' }}' }">
                    <div class="w-20 h-20 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden shrink-0 shadow-inner">
                        <template x-if="imgPreview">
                            <img :src="imgPreview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!imgPreview">
                            <div class="w-full h-full bg-gradient-to-tr from-indigo-600 to-indigo-400 text-white flex items-center justify-center font-bold text-xl uppercase">
                                {{ mb_substr($staff->name, 0, 2) }}
                            </div>
                        </template>
                    </div>
                    <div class="flex-1">
                        <label class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 rounded-xl text-xs font-semibold text-indigo-400 cursor-pointer transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            <span>Choose Photo</span>
                            <input type="file" name="profile_image" accept="image/jpeg,image/png,image/jpg" class="hidden"
                                   @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { imgPreview = e.target.result; }; reader.readAsDataURL(file); }">
                        </label>
                        <p class="text-xs text-slate-500 mt-2">Max size: 2MB. Formats: JPEG, JPG, PNG.</p>
                    </div>
                </div>
                @error('profile_image')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $staff->name) }}" required
                           class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none">
                    @error('name')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $staff->email) }}" required
                           class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none">
                    @error('email')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/60">
                <h3 class="text-sm font-semibold text-slate-300 mb-1">Change Password</h3>
                <p class="text-xs text-slate-500 mb-4">Leave password fields blank to keep the current password.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">New Password</label>
                        <input type="password" name="password" id="password"
                               class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none">
                        @error('password')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none">
                    </div>
                </div>
            </div>

            <div>
                <label for="school_id" class="block text-sm font-medium text-slate-300 mb-2">Assigned School (Optional)</label>
                <select name="school_id" id="school_id"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none">
                    <option value="">-- Board Invigilator (Unassigned to any specific school) --</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" @selected(old('school_id', $staff->school_id) == $school->id)>
                            {{ $school->name }} ({{ $school->code }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1.5">Unassigned staff operate as board-level invigilators and can scan tickets across all centers.</p>
                @error('school_id')
                    <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                @enderror
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
