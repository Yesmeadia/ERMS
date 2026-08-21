@extends('layouts.app')

@section('page_title', ' Exam Centre Management')
@section('page_description', 'View candidate allocations, category & gender distributions, and 33-per-page seat planner desk slips.')

@php
    $isSchoolAdmin = auth()->user()->hasRole('school-admin');
    $studentsPdfRoute = $isSchoolAdmin
        ? route('school.exam-centre.students-pdf', request()->all())
        : route('admin.exam-centres.students-pdf', array_merge(['school' => $school->id], request()->all()));
    $seatPlannerPdfRoute = $isSchoolAdmin
        ? route('school.exam-centre.seat-planner-pdf', request()->all())
        : route('admin.exam-centres.seat-planner-pdf', array_merge(['school' => $school->id], request()->all()));
    $showFormRoute = $isSchoolAdmin
        ? route('school.exam-centre.show')
        : route('admin.exam-centres.show', $school);
@endphp

@section('content')
    <div class="space-y-6">
        {{-- ─── Stats Row (Matching System Aesthetic) ─── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Candidates --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-indigo-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 text-indigo-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ number_format($totalStudents) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Assigned Candidates</p>
                </div>
            </div>

            {{-- Gender Ratio --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-blue-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 text-blue-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-white">{{ $maleCount }}</span>
                        <span class="text-xs font-semibold text-blue-400">M</span>
                        <span class="text-slate-600 font-bold">/</span>
                        <span class="text-2xl font-bold text-white">{{ $femaleCount }}</span>
                        <span class="text-xs font-semibold text-pink-400">F</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $totalStudents > 0 ? round(($maleCount / $totalStudents) * 100) : 0 }}% M ·
                        {{ $totalStudents > 0 ? round(($femaleCount / $totalStudents) * 100) : 0 }}% F
                    </p>
                </div>
            </div>

            {{-- Categories Count --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-purple-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 text-purple-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ count($categoryBreakdown) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Exam Categories</p>
                </div>
            </div>

            {{-- Origin Schools --}}
            <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 text-emerald-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ count($schoolBreakdown) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Origin Institutions</p>
                </div>
            </div>
        </div>

        {{-- ─── Filters Form Card ─── --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5">
            <form method="GET" action="{{ $showFormRoute }}" class="space-y-4">
                {{-- Dropdown Filters Row --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    {{-- Examination Filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Exam Session</label>
                        <select name="examination_id"
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                            <option value="">All Examinations</option>
                            @foreach($examinations as $exam)
                                <option value="{{ $exam->id }}" {{ (string) $selectedExamId === (string) $exam->id ? 'selected' : '' }}>
                                    {{ $exam->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Category Filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Category</label>
                        <select name="category_id"
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                            <option value="">All Categories</option>
                            @foreach($filterCategories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Class Filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Class</label>
                        <select name="class_id"
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                            <option value="">All Classes</option>
                            @foreach($filterClasses as $cls)
                                <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Gender Filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Gender</label>
                        <select name="gender"
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                            <option value="">All Genders</option>
                            <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    {{-- Origin School Filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Origin School</label>
                        <select name="school_id"
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                            <option value="">All Origin Schools</option>
                            @foreach($filterSchools as $s)
                                <option value="{{ $s->id }}" {{ request('school_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} ({{ $s->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort By --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Sort Order</label>
                        <select name="sort_by"
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                            <option value="reg_asc" {{ $sortBy === 'reg_asc' ? 'selected' : '' }}>Register No (Asc)</option>
                            <option value="reg_desc" {{ $sortBy === 'reg_desc' ? 'selected' : '' }}>Register No (Desc)
                            </option>
                            <option value="name_asc" {{ $sortBy === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                            <option value="category_asc" {{ $sortBy === 'category_asc' ? 'selected' : '' }}>Category
                                Precedence</option>
                            <option value="school_asc" {{ $sortBy === 'school_asc' ? 'selected' : '' }}>Origin School</option>
                        </select>
                    </div>
                </div>

                {{-- Search & Action Buttons Row --}}
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-4 border-t border-slate-800/60">
                    <div class="relative flex-1 max-w-xl">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search by student name, register number, hall ticket..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500/50">
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        @if(request()->hasAny(['examination_id', 'category_id', 'class_id', 'gender', 'school_id', 'status', 'search', 'sort_by']))
                            <a href="{{ $showFormRoute }}"
                                class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-xl transition-colors cursor-pointer">
                                Clear Filters
                            </a>
                        @endif
                        <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors shadow-lg shadow-indigo-600/20 cursor-pointer">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ─── Segmented Tabs Navigation ─── --}}
        <div x-data="{ activeTab: 'students' }" class="space-y-6">
            <div
                class="bg-slate-900/60 p-1.5 rounded-2xl border border-slate-800/60 inline-flex flex-wrap items-center gap-1.5">
                <button @click="activeTab = 'students'"
                    :class="activeTab === 'students' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span>Assigned Students ({{ $totalStudents }})</span>
                </button>

                <button @click="activeTab = 'breakdown'"
                    :class="activeTab === 'breakdown' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.143 2.143L15.75 6" />
                    </svg>
                    <span>Category & Institution Analytics</span>
                </button>

                <button @click="activeTab = 'planner'"
                    :class="activeTab === 'planner' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    <span>Seat Planner Preview (33 / Page)</span>
                </button>
            </div>

            {{-- ─── TAB 1: Assigned Students List ─── --}}
            <div x-show="activeTab === 'students'" class="space-y-4">
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden shadow-sm">
                    <div
                        class="p-4 sm:p-5 border-b border-slate-800/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-950/20">
                        <div>
                            <h3 class="text-sm font-bold text-white">Candidates Allocated to Venue</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Showing records sorted by your selected preferences.
                            </p>
                        </div>
                        <a href="{{ $studentsPdfRoute }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-750 text-indigo-300 border border-indigo-500/20 rounded-xl text-xs font-semibold transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            <span>Download List (PDF)</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead>
                                <tr
                                    class="border-b border-slate-800/80 bg-slate-800/40 text-slate-400 uppercase tracking-wider text-[10px] font-semibold">
                                    <th class="py-3 px-4 text-center">Seat #</th>
                                    <th class="py-3 px-4">Reg. Number</th>
                                    <th class="py-3 px-4">Candidate Details</th>
                                    <th class="py-3 px-4 text-center">Gender</th>
                                    <th class="py-3 px-4">Category</th>
                                    <th class="py-3 px-4">Class</th>
                                    <th class="py-3 px-4">Origin School</th>
                                    <th class="py-3 px-4 text-center">Hall Ticket</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    @if(!$isSchoolAdmin)
                                        <th class="py-3 px-4 text-right">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50 text-slate-300 font-medium">
                                @forelse($students as $index => $student)
                                    <tr class="hover:bg-slate-800/25 transition-colors">
                                        {{-- Seat Number --}}
                                        <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-500">
                                            #{{ str_pad($students->firstItem() + $index, 3, '0', STR_PAD_LEFT) }}
                                        </td>

                                        {{-- Registration Number --}}
                                        <td class="py-3.5 px-4 font-mono">
                                            @if($student->registration_number)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-300 font-bold border border-indigo-500/20 font-mono">
                                                    {{ $student->registration_number }}
                                                </span>
                                            @else
                                                <span class="text-slate-500 italic">Pending</span>
                                            @endif
                                        </td>

                                        {{-- Candidate Name & Guardian --}}
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-slate-100 uppercase">{{ $student->name }}</div>
                                            <div class="text-[10px] text-slate-500 mt-0.5">
                                                @if($student->father_name) S/D/O {{ $student->father_name }} · @endif
                                                {{ $student->dob ? $student->dob->format('d M Y') : 'DOB N/A' }}
                                            </div>
                                        </td>

                                        {{-- Gender --}}
                                        <td class="py-3.5 px-4 text-center">
                                            @if(strtolower($student->gender) === 'male')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                    Male
                                                </span>
                                            @elseif(strtolower($student->gender) === 'female')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-500/10 text-pink-400 border border-pink-500/20">
                                                    Female
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700/50">
                                                    {{ ucfirst($student->gender ?: 'N/A') }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Category --}}
                                        <td class="py-3.5 px-4">
                                            <span
                                                class="font-semibold text-slate-200">{{ $student->category?->name ?? 'General' }}</span>
                                        </td>

                                        {{-- Class --}}
                                        <td class="py-3.5 px-4 text-slate-400">
                                            {{ $student->class?->name ?? 'N/A' }}
                                        </td>

                                        {{-- Origin School --}}
                                        <td class="py-3.5 px-4">
                                            <div class="font-medium text-slate-200 truncate max-w-xs">
                                                {{ $student->school?->name ?? 'N/A' }}</div>
                                            <div class="text-[10px] text-slate-500 font-mono">
                                                {{ $student->school?->code ?? '-' }}</div>
                                        </td>

                                        {{-- Hall Ticket --}}
                                        <td class="py-3.5 px-4 text-center font-mono">
                                            @if($student->hall_ticket_number)
                                                <span
                                                    class="text-emerald-400 font-bold uppercase">{{ $student->hall_ticket_number }}</span>
                                            @else
                                                <span class="text-slate-600 italic text-[11px]">Not issued</span>
                                            @endif
                                        </td>

                                        {{-- Status --}}
                                        <td class="py-3.5 px-4 text-center">
                                            @if($student->status === 'Hall Ticket Issued')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> HT Issued
                                                </span>
                                            @elseif($student->status === 'Approved')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Approved
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-400 border border-slate-700/60">
                                                    {{ $student->status }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Action --}}
                                        @if(!$isSchoolAdmin)
                                            <td class="py-3.5 px-4 text-right">
                                                <form method="POST" action="{{ route('admin.exam-centres.unassign', $student) }}"
                                                    onsubmit="return confirm('Remove {{ $student->name }} from this exam centre?')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-slate-500 hover:text-rose-400 transition-colors p-1"
                                                        title="Unassign candidate from this centre">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isSchoolAdmin ? 9 : 10 }}" class="py-12 text-center text-slate-500">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-600">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                </svg>
                                                <span>No candidates found matching the selected filters.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    @if($students->hasPages())
                        <div class="px-6 py-4 border-t border-slate-800/60">
                            {{ $students->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- ─── TAB 2: Category & Origin Analytics ─── --}}
            <div x-show="activeTab === 'breakdown'" class="space-y-6" style="display: none;">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Category Breakdown Card --}}
                    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800/60 pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-white">Category Distribution</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Custom sequence defined for seat allocations</p>
                            </div>
                            <span
                                class="text-xs font-mono text-indigo-400 font-bold bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-1 rounded-xl">
                                {{ count($categoryBreakdown) }} Categories
                            </span>
                        </div>

                        <div class="overflow-hidden border border-slate-800/80 rounded-xl">
                            <table class="w-full text-xs text-left">
                                <thead>
                                    <tr
                                        class="border-b border-slate-800 bg-slate-800/40 text-slate-400 text-[10px] uppercase font-semibold">
                                        <th class="py-3 px-4">Category Name</th>
                                        <th class="py-3 px-4 text-center">Code</th>
                                        <th class="py-3 px-4 text-center">Candidates</th>
                                        <th class="py-3 px-4 text-right">Share (%)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/50 text-slate-300 font-medium">
                                    @forelse($categoryBreakdown as $cat)
                                        <tr class="hover:bg-slate-800/20">
                                            <td class="py-3 px-4 font-bold text-slate-200">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-indigo-400 shrink-0"></span>
                                                    <span>{{ $cat['name'] }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center font-mono text-indigo-400">{{ $cat['code'] ?? '-' }}</td>
                                            <td class="py-3 px-4 text-center font-mono font-bold text-white">
                                                {{ number_format($cat['count']) }}</td>
                                            <td class="py-3 px-4 text-right">
                                                <div class="flex items-center justify-end gap-2 font-mono text-slate-400">
                                                    <span>{{ $cat['percentage'] ?? 0 }}%</span>
                                                    <div class="w-12 bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-indigo-500 h-1.5 rounded-full"
                                                            style="width: {{ $cat['percentage'] ?? 0 }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-6 px-4 text-center text-slate-500 italic">No category data available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Origin Schools Breakdown Card --}}
                    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800/60 pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-white">Origin Feeder Institutions</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Students allocated from different schools</p>
                            </div>
                            <span
                                class="text-xs font-mono text-emerald-400 font-bold bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-xl">
                                {{ count($schoolBreakdown) }} Institutions
                            </span>
                        </div>

                        <div class="overflow-hidden border border-slate-800/80 rounded-xl">
                            <table class="w-full text-xs text-left">
                                <thead>
                                    <tr
                                        class="border-b border-slate-800 bg-slate-800/40 text-slate-400 text-[10px] uppercase font-semibold">
                                        <th class="py-3 px-4">Institution Name</th>
                                        <th class="py-3 px-4 text-center">Code</th>
                                        <th class="py-3 px-4 text-center">Candidates</th>
                                        <th class="py-3 px-4 text-right">Share (%)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/50 text-slate-300 font-medium">
                                    @forelse($schoolBreakdown as $sch)
                                        @php
                                            $schShare = $totalStudents > 0 ? round(($sch['count'] / $totalStudents) * 100, 1) : 0;
                                        @endphp
                                        <tr class="hover:bg-slate-800/20">
                                            <td class="py-3 px-4 font-bold text-slate-200">{{ $sch['name'] }}</td>
                                            <td class="py-3 px-4 text-center font-mono text-indigo-400">{{ $sch['code'] }}</td>
                                            <td class="py-3 px-4 text-center font-mono font-bold text-white">
                                                {{ number_format($sch['count']) }}</td>
                                            <td class="py-3 px-4 text-right">
                                                <div class="flex items-center justify-end gap-2 font-mono text-slate-400">
                                                    <span>{{ $schShare }}%</span>
                                                    <div class="w-12 bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-emerald-400 h-1.5 rounded-full"
                                                            style="width: {{ $schShare }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-6 px-4 text-center text-slate-500 italic">No origin school
                                                data available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── TAB 3: Seat Planner & Desk Slips Preview (33 / Page) ─── --}}
            <div x-show="activeTab === 'planner'" class="space-y-6" style="display: none;">
                {{-- Info Notice Banner --}}
                <div
                    class="bg-indigo-950/40 border border-indigo-800/50 rounded-2xl p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div>
                            <h4 class="text-sm font-bold text-white">A4 Seat Planner & Desk Slips (33 Candidates Per Sheet)
                            </h4>
                            <p class="text-xs text-indigo-200/70 mt-0.5">
                                Each printed A4 page contains exactly 33 desk slips formatted in 3 columns x 11 rows with
                                enlarged Register Number, Student Name, Category, and School Name.
                            </p>
                        </div>
                    </div>

                    <a href="{{ $seatPlannerPdfRoute }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/20 transition-all cursor-pointer shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Download Complete Seat Planner PDF (33 / Page)</span>
                    </a>
                </div>

                {{-- 33-Slip Sample Page Grid Preview --}}
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800/60 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-white">Desk Slips Preview (First 33 Candidates on Sheet 1)
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Clean 3x11 layout with enlarged typography & centered alignment</p>
                        </div>
                        <span class="text-xs text-slate-500 font-mono">Page 1 of
                            {{ ceil(max(1, $totalStudents) / 33) }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                        @forelse($students->take(33) as $idx => $student)
                            <div
                                class="bg-slate-950/60 hover:bg-slate-950/90 border-2 border-indigo-500/30 hover:border-indigo-500/70 rounded-xl p-3.5 text-center transition-all shadow-sm">
                                {{-- Category Heading --}}
                                <div class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-400 mb-1">
                                    CATEGORY: {{ $student->category?->name ?? 'GENERAL' }}
                                </div>

                                {{-- Registration Number (Enlarged) --}}
                                <div class="text-xl sm:text-2xl font-black font-mono text-indigo-300 tracking-wider mb-1">
                                    {{ $student->registration_number ?: 'NOT ISSUED' }}
                                </div>

                                {{-- Candidate Name (Enlarged) --}}
                                <div class="text-sm sm:text-base font-bold text-white uppercase truncate mb-1">
                                    {{ $student->name }}
                                </div>

                                {{-- School Name --}}
                                <div class="text-xs font-semibold text-slate-400 truncate">
                                    {{ $student->school?->name ?? 'N/A' }}
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 py-12 text-center text-slate-500 italic">
                                No candidate slips to preview. Please adjust your filters or allocate students to this centre.
                            </div>
                        @endforelse
                    </div>

                    @if($totalStudents > 33)
                        <div class="text-center pt-4 border-t border-slate-800/60">
                            <p class="text-xs text-slate-400">
                                Showing preview of first 33 slips. The complete PDF will generate all {{ $totalStudents }} slips
                                across {{ ceil($totalStudents / 33) }} pages.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection