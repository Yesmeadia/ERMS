@extends('layouts.app')
@section('page_title', 'Manage Schools')
@section('content')
{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-white">Schools</h2>
        <p class="text-sm text-slate-400 mt-0.5">Manage all registered schools in the system</p>
    </div>
    <a href="{{ route('admin.schools.create') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Add School
    </a>
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, code, zone…"
           class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 w-64">
    <select name="status" class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
        <option value="">All Status</option>
        <option value="active" @selected(request('status')==='active')>Active</option>
        <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
    </select>
    <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all cursor-pointer">Filter</button>
    @if(request()->hasAny(['search','status']))
        <a href="{{ route('admin.schools.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium px-4 py-2.5 rounded-xl transition-all">Clear</a>
    @endif
</form>

{{-- Table --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-800/60">
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">School</th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Zone</th>
                <th class="text-center px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Admins</th>
                <th class="text-center px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Students</th>
                <th class="text-center px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60">
            @forelse($schools as $school)
            <tr class="hover:bg-slate-800/20 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        @php
                            $admin = $school->admins->first();
                        @endphp
                        @if($admin && $admin->profile_image)
                            <img src="{{ asset('storage/' . $admin->profile_image) }}"
                                 class="w-10 h-10 rounded-xl object-cover border border-slate-700/60 shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700/60 text-slate-400 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                {{ mb_substr($school->name, 0, 2) }}
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-slate-200">{{ $school->name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $school->code }} · {{ $school->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-slate-400 hidden md:table-cell">{{ $school->zone }}, {{ $school->state }}</td>
                <td class="px-6 py-4 text-center hidden lg:table-cell">
                    <span class="text-slate-300 font-medium">{{ $school->admins_count }}</span>
                </td>
                <td class="px-6 py-4 text-center hidden lg:table-cell">
                    <span class="text-slate-300 font-medium">{{ $school->students_count }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                    @if($school->status)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Inactive
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.schools.show', $school) }}" class="p-2 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all" title="View">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </a>
                        <a href="{{ route('admin.schools.edit', $school) }}" class="p-2 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.schools.toggle-status', $school) }}">
                            @csrf
                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-amber-400 transition-all cursor-pointer" title="{{ $school->status ? 'Deactivate' : 'Activate' }}">
                                @if($school->status)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @endif
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.schools.destroy', $school) }}" onsubmit="return confirm('Delete this school? This will remove all associated data.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition-all cursor-pointer" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-700"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    No schools found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($schools->hasPages())
    <div class="px-6 py-4 border-t border-slate-800/60">{{ $schools->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
