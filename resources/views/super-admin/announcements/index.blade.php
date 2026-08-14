@extends('layouts.app')

@section('page_title', 'Broadcast Announcements')

@section('content')
<div class="space-y-8" x-data="{ showReadsModal: false, activeTitle: '', activeReads: [] }">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-900/60 p-6 rounded-2xl border border-slate-800/60 backdrop-blur-xl">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-500/10 text-blue-400 rounded-xl border border-blue-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.38-.09-2.072-.09H7.5A3.75 3.75 0 013.75 12V8.25A3.75 3.75 0 017.5 4.5h.768c.693 0 1.384-.03 2.072-.09m0 11.43c.277.015.556.027.835.037m-1.07-11.467c.279-.01.558-.022.835-.037m0 0a24.16 24.16 0 018.318 0m-8.318 0a24.16 24.16 0 000 11.467m8.318-11.467a24.16 24.16 0 010 11.467m0 0A24.16 24.16 0 0019.5 12V8.25A3.75 3.75 0 0015.75 4.5h-.768M10.34 15.84V18.75a2.25 2.25 0 002.25 2.25h1.5a2.25 2.25 0 002.25-2.25v-2.91" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Broadcast Announcements</h1>
                    <p class="text-sm text-slate-400">Send pop-up notifications directly to all active School Admins.</p>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-sm flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- New Announcement Form -->
        <div class="lg:col-span-1 bg-slate-900/60 p-6 rounded-2xl border border-slate-800/60 backdrop-blur-xl">
            <h2 class="text-lg font-semibold text-white mb-1 flex items-center gap-2">
                <span>Create Announcement</span>
            </h2>
            <p class="text-xs text-slate-400 mb-6">This message will immediately display as a modal for all active School Admins.</p>

            <form action="{{ route('admin.announcements.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Announcement Title</label>
                    <input type="text" name="title" id="title" required
                        placeholder="e.g. Urgent: Hall Ticket Printing Schedule Update"
                        class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                        value="{{ old('title') }}">
                    @error('title')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Announcement Message</label>
                    <textarea name="message" id="message" rows="5" required
                        placeholder="Type the announcement message details here..."
                        class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-blue-600/20 hover:shadow-blue-600/30 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                    Send Announcement
                </button>
            </form>
        </div>

        <!-- Announcement History Table -->
        <div class="lg:col-span-2 bg-slate-900/60 p-6 rounded-2xl border border-slate-800/60 backdrop-blur-xl">
            <h2 class="text-lg font-semibold text-white mb-4">Announcement History</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-950/60 text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Title & Message</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Reads List</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse ($announcements as $announcement)
                            @php
                                $readsData = $announcement->users->map(function($user) {
                                    return [
                                        'school_name' => optional($user->school)->name ?? 'System Admin',
                                        'school_code' => optional($user->school)->code ?? '-',
                                        'user_name' => $user->name,
                                        'user_email' => $user->email,
                                        'read_at' => optional($user->pivot)->read_at ? \Carbon\Carbon::parse($user->pivot->read_at)->format('M d, Y H:i:s') : '-',
                                    ];
                                })->values();
                            @endphp
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-4 py-4 max-w-xs">
                                    <div class="font-semibold text-white truncate">{{ $announcement->title }}</div>
                                    <div class="text-xs text-slate-400 line-clamp-2 mt-0.5">{{ $announcement->message }}</div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if ($announcement->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-slate-800 text-slate-400 rounded-full">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <button type="button"
                                        @click="showReadsModal = true; activeTitle = '{{ addslashes($announcement->title) }}'; activeReads = {{ json_encode($readsData) }};"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-xl hover:bg-blue-500/20 transition-all cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.573 16.49 16.638 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Reads ({{ $announcement->reads_count }})
                                    </button>
                                </td>
                                <td class="px-4 py-4 text-xs text-slate-400 whitespace-nowrap">
                                    {{ $announcement->created_at ? $announcement->created_at->format('M d, Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-4 text-right whitespace-nowrap space-x-2">
                                    <form action="{{ route('admin.announcements.toggle', $announcement) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-800 transition-colors">
                                            {{ $announcement->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="inline" onsubmit="return confirm('Delete this announcement?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/20 transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    No announcements created yet. Create one using the form.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $announcements->links() }}
            </div>
        </div>
    </div>

    <!-- Reads List Modal for Super Admin -->
    <div x-show="showReadsModal" x-cloak
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
        style="display: none;">
        <div class="relative w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden p-6 sm:p-7">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-800/80">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-blue-400 bg-blue-500/10 px-2.5 py-0.5 rounded-full">
                        Reads Report
                    </span>
                    <h3 class="text-lg font-bold text-white mt-1" x-text="activeTitle"></h3>
                </div>
                <button type="button" @click="showReadsModal = false" class="text-slate-400 hover:text-white transition-colors cursor-pointer p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Reads Table Body -->
            <div class="mt-5 max-h-96 overflow-y-auto">
                <template x-if="activeReads.length === 0">
                    <div class="py-12 text-center text-slate-500 text-sm font-medium">
                        No schools have read this announcement yet.
                    </div>
                </template>
                <template x-if="activeReads.length > 0">
                    <table class="w-full text-left text-xs text-slate-300 divide-y divide-slate-800/60">
                        <thead class="uppercase bg-slate-950/80 text-slate-400 font-semibold">
                            <tr>
                                <th class="px-4 py-3">School Name & Code</th>
                                <th class="px-4 py-3">Admin User</th>
                                <th class="px-4 py-3 text-right">Read Date & Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40">
                            <template x-for="(read, idx) in activeReads" :key="idx">
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="px-4 py-3 font-semibold text-white">
                                        <div x-text="read.school_name"></div>
                                        <div class="text-[11px] text-blue-400 font-mono" x-text="'Code: ' + read.school_code"></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-200" x-text="read.user_name"></div>
                                        <div class="text-[11px] text-slate-400" x-text="read.user_email"></div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-emerald-400 whitespace-nowrap" x-text="read.read_at"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="mt-6 pt-4 border-t border-slate-800/80 flex justify-end">
                <button type="button" @click="showReadsModal = false"
                    class="px-5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium text-xs rounded-xl transition-colors cursor-pointer">
                    Close Report
                </button>
            </div>
        </div>
    </div>
</div>
@endsection