@extends('layouts.app')
@section('page_title', 'Manage Staff')
@section('page_description', 'Manage all active exam staff and invigilators')
@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-slate-400 mt-0.5">Manage all active exam staff and invigilators</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.staff.export-pdf', request()->query()) }}"
                class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/60 hover:border-slate-600 text-sm font-semibold px-4 py-2.5 rounded-xl transition-all shadow-sm"
                title="Download invigilators list as PDF">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-4 h-4 text-rose-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                Export PDF
            </a>
            <a href="{{ route('admin.staff.create') }}"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                    class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Staff / Invigilator
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email…"
            class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 w-64">
        <select name="status" class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            <option value="">All Statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active Only</option>
            <option value="banned" @selected(request('status') === 'banned')>Banned Only</option>
        </select>
        <select name="login_status" class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            <option value="">All Login States</option>
            <option value="logged_in" @selected(request('login_status') === 'logged_in')>Logged In</option>
            <option value="not_logged_in" @selected(request('login_status') === 'not_logged_in')>Not Logged In</option>
        </select>
        <button type="submit"
            class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all cursor-pointer">Filter</button>
        @if(request()->has('search') || request()->has('status') || request()->has('login_status'))
            <a href="{{ route('admin.staff.index') }}"
                class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium px-4 py-2.5 rounded-xl transition-all">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800/60">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Staff Details</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Assigned Examination Center</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Account Logged In</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden sm:table-cell">Created At</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($staffMembers as $staff)
                    @php
                        $isActive = $staff->is_active ?? true;
                        $hasLoggedIn = !is_null($staff->last_login_at);
                    @endphp
                    <tr class="hover:bg-slate-800/20 transition-colors {{ !$isActive ? 'bg-rose-950/10' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($staff->profile_image)
                                    <img src="{{ asset('storage/' . $staff->profile_image) }}"
                                        class="w-9 h-9 rounded-xl object-cover border border-slate-700/60 shrink-0">
                                @else
                                    <div
                                        class="w-9 h-9 rounded-xl {{ $isActive ? 'bg-indigo-600/10 border-indigo-500/20 text-indigo-400' : 'bg-rose-600/10 border-rose-500/20 text-rose-400' }} border flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                        {{ mb_substr($staff->name, 0, 2) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-slate-200">{{ $staff->name }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $staff->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($staff->school)
                                <span class="text-slate-300 font-medium">{{ $staff->school->name }}</span>
                                <p class="text-xs text-slate-500 mt-0.5">Center Code: {{ $staff->school->code }}</p>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700/60">
                                    Board Invigilator (All Centers)
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($hasLoggedIn)
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                    Logged In
                                </div>
                                <p class="text-xs text-slate-500 mt-1">{{ $staff->last_login_at->format('d M Y, h:i A') }}</p>
                            @else
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                                    </svg>
                                    Not Logged In
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Never accessed</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($isActive)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                    Banned
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-400 hidden sm:table-cell">{{ $staff->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Ban / Activate Toggle Button --}}
                                <form method="POST" action="{{ route('admin.staff.toggle-status', $staff->id) }}"
                                      onsubmit="return confirm('{{ $isActive ? 'Are you sure you want to ban/deactivate ' . addslashes($staff->name) . '? They will be logged out and unable to access the system.' : 'Activate ' . addslashes($staff->name) . ' account?' }}')">
                                    @csrf
                                    @if($isActive)
                                        <button type="submit"
                                            class="p-2 rounded-lg text-slate-400 hover:bg-amber-500/10 hover:text-amber-400 transition-all cursor-pointer"
                                            title="Ban / Deactivate Account">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>
                                    @else
                                        <button type="submit"
                                            class="p-2 rounded-lg text-emerald-400 hover:bg-emerald-500/10 hover:text-emerald-300 transition-all cursor-pointer"
                                            title="Activate Account">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    @endif
                                </form>

                                <a href="{{ route('admin.staff.edit', $staff->id) }}"
                                    class="p-2 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all"
                                    title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.staff.destroy', $staff->id) }}"
                                    onsubmit="return confirm('Delete this staff user account?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="p-2 rounded-lg text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition-all cursor-pointer"
                                        title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
                                stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-700">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            No staff members found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($staffMembers->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/60">{{ $staffMembers->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection