@extends('layouts.app')
@section('page_title', 'Create Examination')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.examinations.index') }}" class="p-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        </a>
        <h2 class="text-xl font-bold text-white">Create Examination Session</h2>
    </div>
    <form method="POST" action="{{ route('admin.examinations.store') }}" class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 space-y-5">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-300 mb-2">Examination Name <span class="text-rose-400">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('name') border-rose-500 @enderror"
                       placeholder="e.g. SSLC Examination 2027">
                @error('name')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Academic Year <span class="text-rose-400">*</span></label>
                <input type="text" name="academic_year" value="{{ old('academic_year') }}" required
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('academic_year') border-rose-500 @enderror"
                       placeholder="e.g. 2026-2027">
                @error('academic_year')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Status <span class="text-rose-400">*</span></label>
                <select name="status" required
                        class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('status') border-rose-500 @enderror">
                    <option value="Draft" @selected(old('status')==='Draft')>Draft</option>
                    <option value="Open" @selected(old('status')==='Open')>Open</option>
                    <option value="Closed" @selected(old('status')==='Closed')>Closed</option>
                </select>
                @error('status')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Registration Start Date <span class="text-rose-400">*</span></label>
                <input type="date" name="registration_start_date" value="{{ old('registration_start_date') }}" required
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('registration_start_date') border-rose-500 @enderror">
                @error('registration_start_date')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Registration End Date <span class="text-rose-400">*</span></label>
                <input type="date" name="registration_end_date" value="{{ old('registration_end_date') }}" required
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('registration_end_date') border-rose-500 @enderror">
                @error('registration_end_date')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-300 mb-2">Hall Ticket Release Date <span class="text-rose-400">*</span></label>
                <input type="date" name="hall_ticket_release_date" value="{{ old('hall_ticket_release_date') }}" required
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('hall_ticket_release_date') border-rose-500 @enderror">
                @error('hall_ticket_release_date')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex gap-4 pt-2">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition-all text-sm cursor-pointer">Create Examination</button>
            <a href="{{ route('admin.examinations.index') }}" class="bg-slate-700 hover:bg-slate-600 text-slate-300 font-semibold px-6 py-3 rounded-xl transition-all text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
