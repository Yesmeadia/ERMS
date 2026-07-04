@extends('layouts.app')
@section('page_title', 'Edit Category')
@section('content')
<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.categories.index') }}" class="p-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        </a>
        <h2 class="text-xl font-bold text-white">Edit Category — {{ $category->name }}</h2>
    </div>
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 space-y-5">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Category Name <span class="text-rose-400">*</span></label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                   class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('name') border-rose-500 @enderror">
            @error('name')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Category Code <span class="text-rose-400">*</span></label>
            <input type="text" name="code" value="{{ old('code', $category->code) }}" required
                   class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('code') border-rose-500 @enderror">
            @error('code')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Registration Fee (INR) <span class="text-rose-400">*</span></label>
            <input type="number" name="registration_fee" value="{{ old('registration_fee', $category->registration_fee) }}" required step="0.01" min="0"
                   class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('registration_fee') border-rose-500 @enderror">
            @error('registration_fee')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            <p class="mt-1 text-[11px] text-slate-500">Set as 0.00 to fall back to the Class-level registration fee.</p>
        </div>
        <div class="flex gap-4 pt-2">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition-all text-sm cursor-pointer">Update Category</button>
            <a href="{{ route('admin.categories.index') }}" class="bg-slate-700 hover:bg-slate-600 text-slate-300 font-semibold px-6 py-3 rounded-xl transition-all text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
