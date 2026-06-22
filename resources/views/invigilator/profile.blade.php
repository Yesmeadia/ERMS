@extends('layouts.app')
@section('page_title', 'My Profile')
@section('content')
<div class="w-full">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white font-outfit">Personal Profile</h2>
        <p class="text-sm text-slate-400 mt-0.5">Your invigilator account details and center assignment.</p>
    </div>

    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 space-y-8">
        {{-- Profile Header with Photo and Core Info --}}
        <div class="flex flex-col sm:flex-row items-center gap-6 pb-6 border-b border-slate-800/60">
            <div class="w-20 h-20 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden shrink-0 shadow-lg">
                @if($user->profile_image)
                    <img src="{{ asset('storage/' . $user->profile_image) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-tr from-indigo-600 to-indigo-400 text-white flex items-center justify-center font-bold text-xl uppercase">
                        {{ mb_substr($user->name, 0, 2) }}
                    </div>
                @endif
            </div>
            
            <div class="text-center sm:text-left flex-1">
                <h3 class="text-2xl font-bold text-white font-outfit">{{ $user->name }}</h3>
                <p class="text-sm text-slate-400 mt-0.5">{{ $user->email }}</p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-3">
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wider uppercase bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        Invigilator / Staff
                    </span>
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wider uppercase {{ $user->school ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                        {{ $user->school ? 'School Level' : 'Board Level' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Center Assignment & Additional Details --}}
        <div class="space-y-6">
            <h4 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Assignment Details</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-slate-950/40 border border-slate-800/80 rounded-xl p-4">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold block mb-1">Full Name</span>
                    <span class="text-sm font-medium text-slate-200">{{ $user->name }}</span>
                </div>

                <div class="bg-slate-950/40 border border-slate-800/80 rounded-xl p-4">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold block mb-1">Email Address</span>
                    <span class="text-sm font-medium text-slate-200">{{ $user->email }}</span>
                </div>

                <div class="md:col-span-2 bg-slate-950/40 border border-slate-800/80 rounded-xl p-5">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold block mb-2">Assigned School / Center</span>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h18v18H3V3z" />
                            </svg>
                        </div>
                        <div>
                            @if($user->school)
                                <p class="text-sm font-semibold text-slate-200">{{ $user->school->name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">School Code: {{ $user->school->code }} | Zone: {{ $user->school->zone }} | State: {{ $user->school->state }}</p>
                            @else
                                <p class="text-sm font-semibold text-slate-200">Board Invigilator (Unassigned)</p>
                                <p class="text-xs text-slate-500 mt-0.5">Authorized to scan and verify hall tickets across all examination centers</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
