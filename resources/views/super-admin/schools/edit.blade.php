@extends('layouts.app')
@section('page_title', 'Edit School')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.schools.index') }}" class="p-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-white">Edit School</h2>
            <p class="text-sm text-slate-400 mt-0.5">{{ $school->code }} · {{ $school->name }}</p>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.schools.update', $school) }}" class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-300 mb-2">School Name <span class="text-rose-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $school->name) }}" required
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('name') border-rose-500 @enderror">
                @error('name')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">School Code <span class="text-rose-400">*</span></label>
                <input type="text" name="code" value="{{ old('code', $school->code) }}" required
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('code') border-rose-500 @enderror">
                @error('code')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Email <span class="text-rose-400">*</span></label>
                <input type="email" name="email" value="{{ old('email', $school->email) }}" required
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('email') border-rose-500 @enderror">
                @error('email')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-300 mb-2">Address <span class="text-rose-400">*</span></label>
                <textarea name="address" rows="2" required
                          class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 @error('address') border-rose-500 @enderror">{{ old('address', $school->address) }}</textarea>
                @error('address')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">State <span class="text-rose-400">*</span></label>
                <select name="state" required
                        class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('state') border-rose-500 @enderror">
                    @php
                        $defaultStates = ['Kerala', 'Tamil Nadu', 'Karnataka', 'Andhra Pradesh', 'Telangana', 'Maharashtra'];
                        $currentState = old('state', $school->state);
                        if ($currentState && !in_array($currentState, $defaultStates)) {
                            $defaultStates[] = $currentState;
                        }
                    @endphp
                    @foreach($defaultStates as $state)
                        <option value="{{ $state }}" @selected($currentState === $state)>{{ $state }}</option>
                    @endforeach
                </select>
                @error('state')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Zone <span class="text-rose-400">*</span></label>
                <select name="zone" required
                        class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('zone') border-rose-500 @enderror">
                    @php
                        $defaultZones = ['South Zone', 'Central Zone', 'North Zone', 'East Zone', 'West Zone', 'Trivandrum', 'Ernakulam'];
                        $currentZone = old('zone', $school->zone);
                        if ($currentZone && !in_array($currentZone, $defaultZones)) {
                            $defaultZones[] = $currentZone;
                        }
                    @endphp
                    @foreach($defaultZones as $zone)
                        <option value="{{ $zone }}" @selected($currentZone === $zone)>{{ $zone }}</option>
                    @endforeach
                </select>
                @error('zone')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Contact Person <span class="text-rose-400">*</span></label>
                <input type="text" name="contact_person" value="{{ old('contact_person', $school->contact_person) }}" required
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('contact_person') border-rose-500 @enderror">
                @error('contact_person')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Mobile Number <span class="text-rose-400">*</span></label>
                <input type="text" name="mobile_number" value="{{ old('mobile_number', $school->mobile_number) }}" required
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-3 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 @error('mobile_number') border-rose-500 @enderror">
                @error('mobile_number')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2 flex items-center gap-3 bg-slate-950/40 border border-slate-800 rounded-xl p-4 mt-2">
                <input type="checkbox" id="is_centre" name="is_centre" value="1" @checked(old('is_centre', $school->is_centre))
                       class="accent-indigo-500 w-4 h-4 rounded border-slate-700/60 bg-slate-800/50 cursor-pointer">
                <label for="is_centre" class="text-sm font-medium text-slate-350 select-none cursor-pointer">
                    Designate as a Centre of Examination
                </label>
            </div>
        </div>
        <div class="flex gap-4 pt-2">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition-all text-sm cursor-pointer">Save Changes</button>
            <a href="{{ route('admin.schools.index') }}" class="bg-slate-700 hover:bg-slate-600 text-slate-300 font-semibold px-6 py-3 rounded-xl transition-all text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
