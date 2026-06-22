@extends('layouts.app')
@section('page_title', 'School Details')
@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.schools.index') }}" class="p-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
    </a>
    <div class="flex-1">
        <h2 class="text-xl font-bold text-white">{{ $school->name }}</h2>
        <p class="text-sm text-slate-400 mt-0.5">{{ $school->code }} · {{ $school->zone }}, {{ $school->state }}</p>
    </div>
    <a href="{{ route('admin.schools.edit', $school) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
        Edit
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- School Info --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">School Information</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-slate-500">School Name</p><p class="text-slate-200 font-medium mt-0.5">{{ $school->name }}</p></div>
                <div><p class="text-slate-500">School Code</p><p class="text-slate-200 font-medium mt-0.5">{{ $school->code }}</p></div>
                <div><p class="text-slate-500">Email</p><p class="text-slate-200 font-medium mt-0.5">{{ $school->email }}</p></div>
                <div><p class="text-slate-500">Mobile</p><p class="text-slate-200 font-medium mt-0.5">{{ $school->mobile_number }}</p></div>
                <div><p class="text-slate-500">Contact Person</p><p class="text-slate-200 font-medium mt-0.5">{{ $school->contact_person }}</p></div>
                <div><p class="text-slate-500">Status</p>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium mt-0.5 {{ $school->status ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $school->status ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                        {{ $school->status ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="col-span-2"><p class="text-slate-500">Address</p><p class="text-slate-200 font-medium mt-0.5">{{ $school->address }}, {{ $school->zone }}, {{ $school->state }}</p></div>
            </div>
        </div>

        {{-- School Admins --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">School Admins</h3>
            @forelse($school->admins as $admin)
            <div class="flex items-center justify-between py-3 border-b border-slate-800/60 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-sm font-bold">
                        {{ mb_substr($admin->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-200">{{ $admin->name }}</p>
                        <p class="text-xs text-slate-500">{{ $admin->email }}</p>
                    </div>
                </div>
                {{-- Reset Password Form --}}
                <button onclick="document.getElementById('resetForm{{ $admin->id }}').classList.toggle('hidden')"
                        class="text-xs text-amber-400 hover:text-amber-300 transition-colors cursor-pointer">Reset Password</button>
            </div>
            <div id="resetForm{{ $admin->id }}" class="hidden mt-3 p-4 bg-slate-800/50 rounded-xl border border-slate-700/60">
                <form method="POST" action="{{ route('admin.schools.reset-password', $school) }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $admin->id }}">
                    <div class="grid grid-cols-2 gap-3">
                        <input type="password" name="new_password" placeholder="New password (min 8)" required
                               class="col-span-2 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-slate-100 text-sm focus:outline-none focus:border-indigo-500">
                        <input type="password" name="new_password_confirmation" placeholder="Confirm password" required
                               class="col-span-2 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-slate-100 text-sm focus:outline-none focus:border-indigo-500">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium px-4 py-2 rounded-lg cursor-pointer transition-all">Update</button>
                    </div>
                </form>
            </div>
            @empty
            <p class="text-slate-500 text-sm">No admins assigned yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Assign Admin Panel --}}
    <div class="space-y-6">
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Assign New Admin</h3>
            <form method="POST" action="{{ route('admin.schools.assign-admin', $school) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Admin Name</label>
                    <input type="text" name="admin_name" required
                           class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-3 py-2.5 text-slate-100 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Email</label>
                    <input type="email" name="admin_email" required
                           class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-3 py-2.5 text-slate-100 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Password</label>
                    <input type="password" name="admin_password" required minlength="8"
                           class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-3 py-2.5 text-slate-100 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Confirm Password</label>
                    <input type="password" name="admin_password_confirmation" required
                           class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-3 py-2.5 text-slate-100 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-xl text-sm transition-all cursor-pointer">
                    Assign Admin
                </button>
            </form>
        </div>

        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 text-sm">
            <h3 class="font-semibold text-slate-300 mb-3">Statistics</h3>
            <div class="space-y-2">
                <div class="flex justify-between"><span class="text-slate-500">Total Students</span><span class="text-slate-200 font-medium">{{ $school->students->count() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Submitted</span><span class="text-slate-200 font-medium">{{ $school->students->where('status', 'Submitted')->count() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Approved</span><span class="text-emerald-400 font-medium">{{ $school->students->where('status', 'Approved')->count() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Rejected</span><span class="text-rose-400 font-medium">{{ $school->students->where('status', 'Rejected')->count() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Hall Tickets</span><span class="text-purple-400 font-medium">{{ $school->students->where('status', 'Hall Ticket Issued')->count() }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
