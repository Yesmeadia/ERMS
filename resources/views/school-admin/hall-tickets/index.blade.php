@extends('layouts.app')

@section('page_title', 'Download Hall Tickets')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <p class="text-sm text-slate-400">Download candidate examination hall tickets individually or in bulk.</p>
    </div>
</div>

{{-- Top Row: Filters & Bulk Download Card --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Search and Filters --}}
    <div class="lg:col-span-2 bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Search & Filters</h3>
        <form method="GET" action="{{ route('school.hall-tickets.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Search Candidate</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Reg/HT Number..."
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Exam Session</label>
                <select name="examination_id" class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                    <option value="">All Sessions</option>
                    @foreach($examinations as $exam)
                        <option value="{{ $exam->id }}" @selected(request('examination_id') == $exam->id)>{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Category</label>
                <select name="category_id" class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-3 flex gap-2 justify-end mt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-all cursor-pointer">Filter</button>
                @if(request()->hasAny(['search', 'examination_id', 'category_id']))
                    <a href="{{ route('school.hall-tickets.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium px-5 py-2.5 rounded-xl transition-all">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Bulk Download Card --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex flex-col justify-between">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Bulk Download</h3>
            <p class="text-xs text-slate-400 leading-relaxed mb-4">Export all issued hall tickets for a specific examination session into a single PDF document.</p>
        </div>

        <div>
            <form method="GET" action="{{ route('school.hall-tickets.download-bulk') }}" class="space-y-3">
                <select name="examination_id" required class="w-full bg-slate-800/60 border border-slate-700/60 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Select Examination</option>
                    @foreach($examinations as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/15 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Download Bulk PDF
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Hall Tickets Table --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-slate-800/60">
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Candidate</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Reg. / HT Number</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Class & Category</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Exam Session</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Status</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40">
                @forelse($students as $student)
                <tr class="hover:bg-slate-800/20 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0">
                            <div>
                                <p class="font-semibold text-slate-200">{{ $student->name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $student->gender }} · DOB: {{ $student->dob->format('d M Y') }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell">
                        <div>
                            <p class="text-slate-300 font-semibold text-xs">Reg: {{ $student->registration_number ?? 'Pending' }}</p>
                            @if($student->hall_ticket_number)
                                <p class="text-indigo-400 font-mono font-bold text-[10px] tracking-wider mt-0.5">HT: {{ $student->hall_ticket_number }}</p>
                            @else
                                <p class="text-slate-500 font-mono text-[10px] tracking-wider mt-0.5">HT: Not Issued Yet</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <div>
                            <p class="text-slate-300 font-medium text-xs">{{ $student->class->name }}</p>
                            <p class="text-[10px] text-slate-500 tracking-wider font-semibold uppercase mt-0.5">{{ $student->category->name }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <div class="max-w-[180px] truncate">
                            <p class="text-slate-300 font-medium text-xs" title="{{ $student->examination->name }}">{{ $student->examination->name }}</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">{{ $student->examination->academic_year }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        @if($student->status === 'Hall Ticket Issued')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span> Issued
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Approved
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end">
                            @if($student->status === 'Hall Ticket Issued')
                                <a href="{{ route('school.hall-tickets.download-single', $student) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600/10 hover:bg-indigo-600 text-indigo-400 hover:text-white text-xs font-semibold rounded-lg transition-all cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                    Download PDF
                                </a>
                            @else
                                <span class="text-xs text-slate-500 italic">Pending Issue</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-700"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-12v5.25m0 3v3.75m9.75-3.75c0-.772-.646-1.399-1.425-1.399h-3.908c-.779 0-1.424.627-1.424 1.399v3.89c-.042.82.642 1.485 1.47 1.485h3.838c.828 0 1.493-.664 1.493-1.485V13.5zM12 5.25c0-.772-.646-1.399-1.425-1.399H6.666c-.779 0-1.424.627-1.424 1.399v3.89c-.042.82.642 1.485 1.47 1.485h3.838c.828 0 1.493-.664 1.493-1.485V5.25z" /></svg>
                        No approved or issued hall tickets found matching the filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
    <div class="px-6 py-4 border-t border-slate-800/60">{{ $students->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
