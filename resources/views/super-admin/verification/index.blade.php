@extends('layouts.app')
@section('page_title', 'Verification & Approval')
@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-white">Verification & Approval</h2>
            <p class="text-sm text-slate-400 mt-0.5">Review, approve, or reject student registrations</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, reg no, admission no…"
            class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 w-64">
        <select name="status"
            class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
            <option value="">All Status</option>
            @foreach(['Submitted', 'Under Review', 'Approved', 'Rejected', 'Hall Ticket Issued'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
            @endforeach
        </select>
        <select name="school_id"
            class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
            <option value="">All Schools</option>
            @foreach($schools as $school)
                <option value="{{ $school->id }}" @selected(request('school_id') == $school->id)>{{ $school->name }}</option>
            @endforeach
        </select>
        <select name="class_id"
            class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
            <option value="">All Classes</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
            @endforeach
        </select>
        <select name="category_id"
            class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
            <option value="">All Categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button type="submit"
            class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all cursor-pointer">Filter</button>
        @if(request()->hasAny(['search', 'status', 'school_id', 'class_id', 'category_id']))
            <a href="{{ route('admin.verification.index') }}"
                class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium px-4 py-2.5 rounded-xl transition-all">Clear</a>
        @endif
    </form>

    @php
        $statusColors = [
            'Submitted' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
            'Under Review' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            'Approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'Rejected' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
            'Hall Ticket Issued' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
        ];
    @endphp

    {{-- Table --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800/60">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Student
                    </th>
                    <th
                        class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">
                        School</th>
                    <th
                        class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">
                        Class</th>
                    <th
                        class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">
                        Category</th>
                    <th
                        class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">
                        Examination</th>
                    <th class="text-center px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status
                    </th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($students as $student)
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-semibold text-slate-200">{{ $student->name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $student->registration_number ?? 'No Reg. #' }} ·
                                    {{ $student->admission_number }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-xs hidden md:table-cell">{{ $student->school->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-xs hidden lg:table-cell">{{ $student->class->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-xs hidden lg:table-cell">{{ $student->category->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-xs hidden lg:table-cell">
                            {{ $student->examination->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColors[$student->status] ?? 'bg-slate-500/10 text-slate-400 border-slate-500/20' }}">
                                {{ $student->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.verification.show', $student) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-400 bg-indigo-600/10 hover:bg-indigo-600/20 transition-all">
                                    Review
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
                                stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-700">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            No registrations found for verification.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($students->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/60">{{ $students->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection
