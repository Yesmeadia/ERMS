@extends('layouts.app')

@section('page_title', 'Exam Centres Management')
@section('page_description', 'Manage exam centres and allocate candidates to centres before issuing hall tickets')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <p class="text-sm text-slate-400 mt-0.5">Designate schools as examination venues and allocate candidates to
                centres before issuing hall tickets.</p>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Designated Centres</p>
                    <p class="text-2xl font-black text-indigo-400 mt-1 font-mono">{{ $totalCentres }}</p>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
            </div>

            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Assigned Candidates</p>
                    <p class="text-2xl font-black text-emerald-400 mt-1 font-mono">{{ $totalAssigned }}</p>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Pending Assignment</p>
                    <p class="text-2xl font-black text-amber-400 mt-1 font-mono">{{ $totalPending }}</p>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Main Workspace Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Section 1: Designate/Manage Centres (Left side - 2 cols wide) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Registered Schools List with toggle actions --}}
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800/60 pb-3">
                        <h3 class="text-base font-semibold text-white">Registered Schools Directory</h3>
                        <span class="text-xs text-slate-500">Toggle Centre Designation status</span>
                    </div>

                    {{-- Schools Search form --}}
                    <form method="GET" class="flex gap-3">
                        <input type="text" name="school_search" value="{{ request('school_search') }}"
                            placeholder="Search schools to designate..."
                            class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 w-full sm:w-80">
                        <button type="submit"
                            class="bg-slate-750 hover:bg-slate-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all cursor-pointer">Filter</button>
                        @if(request('school_search'))
                            <a href="{{ route('admin.exam-centres.index') }}"
                                class="bg-slate-800 hover:bg-slate-750 text-slate-400 text-xs font-semibold px-4 py-2 rounded-xl transition-all flex items-center justify-center">Clear</a>
                        @endif
                    </form>

                    <div class="overflow-hidden border border-slate-800/80 rounded-xl">
                        <table class="w-full text-xs text-left">
                            <thead>
                                <tr class="border-b border-slate-800 bg-slate-950/20 text-slate-400">
                                    <th class="py-3 px-4 font-semibold uppercase">School Info</th>
                                    <th class="py-3 px-4 font-semibold uppercase text-center">Location</th>
                                    <th class="py-3 px-4 font-semibold text-right uppercase">Designation Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/40 text-slate-350">
                                @forelse($allSchools as $school)
                                    <tr class="hover:bg-slate-800/20 transition-colors">
                                        <td class="py-3.5 px-4">
                                            <p class="font-bold text-slate-200">{{ $school->name }}</p>
                                            <p class="text-[10px] text-slate-500 mt-0.5">{{ $school->code }} ·
                                                {{ $school->email }}
                                            </p>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            {{ $school->state }}, {{ $school->zone }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center justify-end">
                                                <form method="POST" action="{{ route('admin.exam-centres.toggle', $school) }}">
                                                    @csrf
                                                    @if($school->is_centre)
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1.5 bg-indigo-500/15 hover:bg-rose-500/10 hover:text-rose-400 hover:border-rose-500/20 text-indigo-400 border border-indigo-500/25 px-2.5 py-1.5 rounded-lg font-bold transition-all cursor-pointer">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Exam Centre
                                                        </button>
                                                    @else
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-400 border border-slate-700/60 px-2.5 py-1.5 rounded-lg font-semibold transition-all cursor-pointer">
                                                            Set Exam Centre
                                                        </button>
                                                    @endif
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-8 px-4 text-center text-slate-500 italic">No schools matched
                                            the filter parameters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($allSchools->hasPages())
                        <div class="pt-2">
                            {{ $allSchools->appends(request()->except('schools_page'))->links() }}
                        </div>
                    @endif
                </div>

                {{-- Assigned Centres Directory --}}
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800/60 pb-3">
                        <h3 class="text-base font-semibold text-white">Active Examination Venues</h3>
                        <span
                            class="text-xs text-indigo-400 font-bold uppercase tracking-wider font-mono bg-indigo-500/10 border border-indigo-500/20 px-2 py-0.5 rounded">Active
                            Centres</span>
                    </div>

                    <form method="GET" class="flex gap-3">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search active venues by name, zone..."
                            class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 w-full sm:w-80">
                        <button type="submit"
                            class="bg-slate-750 hover:bg-slate-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all cursor-pointer">Search</button>
                        @if(request('search'))
                            <a href="{{ route('admin.exam-centres.index') }}"
                                class="bg-slate-800 hover:bg-slate-750 text-slate-400 text-xs font-semibold px-4 py-2 rounded-xl transition-all flex items-center justify-center font-semibold">Clear</a>
                        @endif
                    </form>

                    <div class="overflow-hidden border border-slate-800/80 rounded-xl">
                        <table class="w-full text-xs text-left">
                            <thead>
                                <tr class="border-b border-slate-800 bg-slate-950/20 text-slate-400">
                                    <th class="py-3 px-4 font-semibold uppercase">Exam Centre Venue</th>
                                    <th class="py-3 px-4 font-semibold text-center uppercase">Code</th>
                                    <th class="py-3 px-4 font-semibold text-center uppercase">Zone / State</th>
                                    <th class="py-3 px-4 font-semibold text-right uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/40 text-slate-350">
                                @forelse($centres as $centre)
                                    <tr class="hover:bg-slate-800/20 transition-colors">
                                        <td class="py-3.5 px-4 font-bold text-slate-200">
                                            {{ $centre->name }}
                                            <p class="text-[10px] text-slate-550 font-normal mt-0.5">{{ $centre->address }}</p>
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-mono text-indigo-400 font-bold uppercase">
                                            {{ $centre->code }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            {{ $centre->zone }}, {{ $centre->state }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center justify-end">
                                                <form method="POST" action="{{ route('admin.exam-centres.toggle', $centre) }}"
                                                    onsubmit="return confirm('Are you sure you want to revoke Exam Centre status? This will preserve existing students but they won\'t assign to this school in new bulk allocations.')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-2 bg-slate-800 hover:bg-rose-500/10 hover:text-rose-400 text-slate-400 rounded-lg border border-slate-750 transition-all cursor-pointer"
                                                        title="Revoke Centre Designation">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 px-4 text-center text-slate-550 italic">No designated
                                            Examination Centres found. Use the directory list above to designate schools.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($centres->hasPages())
                        <div class="pt-2">
                            {{ $centres->appends(request()->except('centres_page'))->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Section 2: Allocation & Assignment Form (Right side - 1 col wide) --}}
            <div class="space-y-6">
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-5">
                    <div class="border-b border-slate-800/60 pb-3">
                        <h3 class="text-base font-semibold text-white flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5 text-indigo-400">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                            </svg>
                            Bulk Centre Allocation
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-1">Assign an Exam Centre venue to all candidates registered
                            under a specific school-examination filter group.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.exam-centres.assign') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="school_id" class="block text-xs font-semibold text-slate-400 mb-1.5">Registered
                                School</label>
                            <select name="school_id" id="school_id" required
                                class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-3 py-2.5 text-slate-100 text-xs focus:outline-none focus:border-indigo-500">
                                <option value="" disabled selected>Select School...</option>
                                @foreach($registeredSchools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }} ({{ $school->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="examination_id"
                                class="block text-xs font-semibold text-slate-400 mb-1.5">Examination Session</label>
                            <select name="examination_id" id="examination_id" required
                                class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-3 py-2.5 text-slate-100 text-xs focus:outline-none focus:border-indigo-500">
                                <option value="" disabled selected>Select Exam...</option>
                                @foreach($examinations as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="centre_id" class="block text-xs font-semibold text-slate-400 mb-1.5">Target Venue
                                (Exam Centre)</label>
                            <select name="centre_id" id="centre_id" required
                                class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-3 py-2.5 text-slate-100 text-xs focus:outline-none focus:border-indigo-500">
                                <option value="" disabled selected>Select Active Venue...</option>
                                @forelse($designatedCentres as $centre)
                                    <option value="{{ $centre->id }}">{{ $centre->name }} ({{ $centre->code }})</option>
                                @empty
                                    <option value="" disabled>No centres designated yet!</option>
                                @endforelse
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl transition-all shadow-md shadow-indigo-600/10 text-xs cursor-pointer">
                            Allocate Exam Centre
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection