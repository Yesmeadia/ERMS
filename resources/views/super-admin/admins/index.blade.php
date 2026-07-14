@extends('layouts.app')
@section('page_title', 'Manage Board Admins')
@section('page_description', 'Manage system administrators with board-level permissions')
@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-slate-400 mt-0.5">Manage system administrators with board-level permissions</p>
        </div>
        @if($canCreateAdmin)
            <a href="{{ route('admin.admins.create') }}"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                    class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Board Admin
            </a>
        @else
            <div
                class="inline-flex items-center gap-2 bg-slate-800 text-slate-400 text-xs font-semibold px-4 py-2.5 rounded-xl border border-slate-700/60 shadow-inner">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Limit Reached ({{ $totalAdmins }}/2 Admins)
            </div>
        @endif
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email…"
            class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 w-64">
        <button type="submit"
            class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all cursor-pointer">Filter</button>
        @if(request()->has('search'))
            <a href="{{ route('admin.admins.index') }}"
                class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium px-4 py-2.5 rounded-xl transition-all">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800/60">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Admin
                        Details</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Role /
                        Status</th>
                    <th
                        class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden sm:table-cell">
                        Created At</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($admins as $admin)
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($admin->profile_image)
                                    <img src="{{ asset('storage/' . $admin->profile_image) }}"
                                        class="w-9 h-9 rounded-xl object-cover border border-slate-700/60 shrink-0">
                                @else
                                    <div
                                        class="w-9 h-9 rounded-xl bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                        {{ mb_substr($admin->name, 0, 2) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-slate-200">{{ $admin->name }}</p>
                                        @if($admin->id === auth()->id())
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-indigo-600/20 text-indigo-400 border border-indigo-500/20">You</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $admin->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700/60">
                                Super Admin
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-400 hidden sm:table-cell">{{ $admin->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @if($admin->id === auth()->id())
                                    <a href="{{ route('admin.profile.edit') }}"
                                        class="text-xs text-slate-500 hover:text-indigo-400 px-3 py-1.5 rounded-lg hover:bg-indigo-600/10 border border-transparent hover:border-indigo-500/20 transition-all font-medium"
                                        title="Edit via My Profile">
                                        My Profile &rarr;
                                    </a>
                                @else
                                    <a href="{{ route('admin.admins.edit', $admin->id) }}"
                                        class="p-2 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all"
                                        title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.admins.destroy', $admin->id) }}"
                                        onsubmit="return confirm('Delete this Super Admin user account?')">
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
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
                                stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-700">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            No board administrators found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($admins->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/60">{{ $admins->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection