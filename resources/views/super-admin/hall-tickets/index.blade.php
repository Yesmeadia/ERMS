@extends('layouts.app')

@section('page_title', 'Hall Ticket Management')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <p class="text-sm text-slate-400">Generate and manage hall tickets for approved students.</p>
</div>

{{-- Filters & Bulk Actions --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-6">
    <form method="GET" action="{{ route('admin.hall-tickets.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 items-end">
        @csrf
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, HT No, Reg No..."
                   class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500/50">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">School</label>
            <select name="school_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Gender</label>
            <select name="gender" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Genders</option>
                <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                <option value="Other" {{ request('gender') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Category</label>
            <select name="category_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Examination</label>
            <select name="examination_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Examinations</option>
                @foreach($examinations as $exam)
                    <option value="{{ $exam->id }}" {{ request('examination_id') == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Status</label>
            <select name="status" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Status</option>
                <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved (Pending HT)</option>
                <option value="Hall Ticket Issued" {{ request('status') === 'Hall Ticket Issued' ? 'selected' : '' }}>Hall Ticket Issued</option>
            </select>
        </div>
        <div class="xl:col-span-6 flex justify-between items-center gap-3 mt-4 pt-4 border-t border-slate-800/60 w-full flex-wrap">
            <div class="flex flex-wrap gap-2">
                <span class="text-xs font-semibold text-slate-400 self-center mr-2">Bulk Actions:</span>
                <button type="submit" formaction="{{ route('admin.hall-tickets.generate-bulk') }}" formmethod="POST"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl transition-colors cursor-pointer shadow-md shadow-emerald-600/10">
                    Bulk Issue HT Numbers
                </button>
                <button type="submit" formaction="{{ route('admin.hall-tickets.print-bulk') }}" formmethod="POST"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold rounded-xl transition-colors cursor-pointer shadow-md shadow-purple-600/10">
                    Generate Bulk Hall Tickets (PDF)
                </button>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-colors cursor-pointer">Filter</button>
                <a href="{{ route('admin.hall-tickets.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition-colors">Reset</a>
            </div>
        </div>
    </form>
</div>

@if(isset($recentBatches) && $recentBatches->isNotEmpty())
    {{-- Dedicated Recent Generation Batches Card --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-6 shadow-sm" x-data="{ expanded: true }">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3 flex-wrap">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-200">Recent Generation Batches</h3>
                    <p class="text-xs text-slate-400">Background PDF compilation tasks & download links</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700/60">
                    {{ $recentBatches->count() }} Total
                </span>
                @php
                    $activeCount = $recentBatches->whereIn('status', ['pending', 'processing'])->count();
                @endphp
                @if($activeCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                        {{ $activeCount }} Processing
                    </span>
                @endif
            </div>

            <button type="button" @click="expanded = !expanded" class="text-xs text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 cursor-pointer">
                <span x-text="expanded ? 'Hide Batches' : 'Show All (' + {{ $recentBatches->count() }} + ')'"></span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': expanded }">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
        </div>

        <div x-show="expanded" x-transition class="mt-4 pt-4 border-t border-slate-800/60">
            <div class="flex flex-wrap gap-2">
                @foreach($recentBatches as $b)
                    @php
                        $isCompleted = in_array($b->status, ['completed', 'completed_with_errors']);
                        $isProcessing = $b->status === 'processing';
                        $isFailed = $b->status === 'failed';
                        $pct = $b->progress_percentage;
                    @endphp
                    <a href="{{ route('admin.hall-tickets.batches.show', $b) }}"
                       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 hover:border-indigo-500/40 text-xs transition-all group">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $isCompleted ? 'bg-emerald-400' : ($isProcessing ? 'bg-indigo-400 animate-ping' : ($isFailed ? 'bg-rose-400' : 'bg-amber-400')) }}"></span>
                        <span class="font-medium text-slate-200 group-hover:text-indigo-200 transition-colors">#{{ $b->id }} {{ $b->school->name ?? 'All Schools' }}</span>
                        <span class="text-slate-500 font-mono text-[10px]">({{ $b->completed_students }}/{{ $b->total_students }})</span>
                        @if($isProcessing)
                            <span class="text-indigo-400 text-[10px] font-semibold">{{ $pct }}%</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif

{{-- Students Table --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-slate-800/60">
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Reg. Number</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">School</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Class</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Exam Centre</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">HT Number</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40">
                @forelse($students as $student)
                <tr class="hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="w-9 h-9 rounded-lg object-cover border border-slate-700/60">
                            <span class="text-slate-200 font-medium">{{ $student->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-400 font-mono text-xs">{{ $student->registration_number ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-300 text-xs">{{ $student->school->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-300">{{ $student->class->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-300 text-xs">{{ $student->centre->name ?? '—' }}</td>
                    <td class="px-6 py-4">
                        @if($student->hall_ticket_number)
                            <span class="font-mono text-xs text-purple-400">{{ $student->hall_ticket_number }}</span>
                        @else
                            <span class="text-slate-500">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($student->status === 'Approved')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-950/40 text-emerald-400 border border-emerald-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Approved
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-950/40 text-purple-400 border border-purple-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span> HT Issued
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if($student->status === 'Approved')
                                <form method="POST" action="{{ route('admin.hall-tickets.generate-single', $student) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-medium rounded-lg transition-colors cursor-pointer" title="Generate HT">
                                        Generate
                                    </button>
                                </form>
                            @endif
                            @if($student->status === 'Hall Ticket Issued')
                                <a href="{{ route('admin.hall-tickets.print-single', $student) }}" target="_blank"
                                   class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded-lg transition-colors" title="Print HT">
                                    Print
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mx-auto text-slate-700 mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-12v5.25m0 3v3.75" />
                        </svg>
                        <p class="text-slate-500 font-medium">No approved students found</p>
                        <p class="text-xs text-slate-600 mt-1">Approve student registrations first from the Verification page.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
    <div class="px-6 py-4 border-t border-slate-800/60">
        {{ $students->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
