@extends('layouts.app')
@section('page_title', 'Examination Sessions')
@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-slate-400 mt-0.5">Manage examination sessions and registration windows</p>
        </div>
        <a href="{{ route('admin.examinations.create') }}"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Create Examination
        </a>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($examinations as $exam)
            <div
                class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 hover:border-slate-700/60 transition-all group">
                {{-- Header --}}
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-white truncate">{{ $exam->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Academic Year: {{ $exam->academic_year }}</p>
                    </div>
                    @php
                        $statusColors = [
                            'Draft' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                            'Registration Started' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                            'Registartion closed' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                            'Examination Ongoing' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                            'result published' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                        ];
                    @endphp
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border uppercase {{ $statusColors[$exam->status] ?? $statusColors['Draft'] }}">
                        <span
                            class="w-1.5 h-1.5 rounded-full {{ $exam->status === 'Registration Started' ? 'bg-emerald-400 animate-pulse' : ($exam->status === 'Examination Ongoing' ? 'bg-blue-400 animate-pulse' : ($exam->status === 'result published' ? 'bg-purple-400' : ($exam->status === 'Registartion closed' ? 'bg-amber-400' : 'bg-slate-400'))) }}"></span>
                        {{ strtoupper($exam->status) }}
                    </span>
                </div>

                {{-- Date Info --}}
                <div class="space-y-2 mb-5">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-500 w-28 shrink-0">Registration Start</span>
                        <span
                            class="text-slate-300">{{ \Carbon\Carbon::parse($exam->registration_start_date)->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-500 w-28 shrink-0">Registration End</span>
                        <span
                            class="text-slate-300">{{ \Carbon\Carbon::parse($exam->registration_end_date)->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-500 w-28 shrink-0">Hall Ticket Release</span>
                        <span
                            class="text-slate-300">{{ \Carbon\Carbon::parse($exam->hall_ticket_release_date)->format('d M Y') }}</span>
                    </div>
                </div>

                {{-- Students Count --}}
                <div class="flex items-center gap-2 mb-5 px-3 py-2 rounded-xl bg-slate-800/50 border border-slate-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4 text-indigo-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                    <span class="text-xs text-slate-400">{{ $exam->students_count }} students registered</span>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 pt-4 border-t border-slate-800/60">
                    <a href="{{ route('admin.examinations.show', $exam) }}"
                        class="flex-1 text-center py-2 px-3 rounded-xl text-xs font-medium text-slate-300 bg-slate-800/50 hover:bg-slate-700 transition-all">View</a>
                    <a href="{{ route('admin.examinations.edit', $exam) }}"
                        class="flex-1 text-center py-2 px-3 rounded-xl text-xs font-medium text-slate-300 bg-slate-800/50 hover:bg-slate-700 transition-all">Edit</a>

                    {{-- Quick Status Change --}}
                    <div class="flex-1" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="w-full text-center py-2 px-3 rounded-xl text-xs font-medium text-indigo-400 bg-indigo-600/10 hover:bg-indigo-600/20 transition-all cursor-pointer">
                            Status ▾
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute mt-1 bg-slate-800 border border-slate-700/60 rounded-xl shadow-xl z-10 overflow-hidden"
                            style="display: none;">
                            @foreach(['Draft', 'Registration Started', 'Registartion closed', 'Examination Ongoing', 'result published'] as $s)
                                @if($s !== $exam->status)
                                    <form method="POST" action="{{ route('admin.examinations.update-status', $exam) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $s }}">
                                        <button type="submit"
                                            class="w-full text-left px-4 py-2.5 text-xs text-slate-300 hover:bg-slate-700 transition-colors cursor-pointer">
                                            Set {{ $s }}
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl px-6 py-16 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
                        stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-700">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    <p class="text-slate-500 text-sm">No examination sessions created yet.</p>
                </div>
            </div>
        @endforelse
    </div>
@endsection