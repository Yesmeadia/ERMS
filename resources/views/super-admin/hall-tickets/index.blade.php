@extends('layouts.app')

@section('page_title', 'Hall Ticket Management')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <p class="text-sm text-slate-400">Generate and manage hall tickets for approved students.</p>
</div>

{{-- Filters --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-6">
    <form method="GET" action="{{ route('admin.hall-tickets.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, HT No, Reg No..."
                   class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/20">
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
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Examination</label>
            <select name="examination_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Examinations</option>
                @foreach($examinations as $exam)
                    <option value="{{ $exam->id }}" {{ request('examination_id') == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                @endforeach
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
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Status</label>
            <select name="status" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Status</option>
                <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved (Pending HT)</option>
                <option value="Hall Ticket Issued" {{ request('status') === 'Hall Ticket Issued' ? 'selected' : '' }}>Hall Ticket Issued</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors cursor-pointer">Filter</button>
            <a href="{{ route('admin.hall-tickets.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-xl transition-colors">Reset</a>
        </div>
    </form>
</div>

{{-- Bulk Generate Section --}}
<div x-data="{ showBulk: false }" class="mb-6">
    <button @click="showBulk = !showBulk" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded-xl transition-colors cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />
        </svg>
        Bulk Generate Hall Tickets
    </button>

    <div x-show="showBulk" x-transition class="mt-4 bg-slate-900/60 border border-emerald-800/30 rounded-2xl p-5" style="display: none;">
        <form method="POST" action="{{ route('admin.hall-tickets.generate-bulk') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">School *</label>
                <select name="bulk_school_id" required class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-emerald-500/50">
                    <option value="">Select School</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Examination *</label>
                <select name="bulk_examination_id" required class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-emerald-500/50">
                    <option value="">Select Examination</option>
                    @foreach($examinations as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded-xl transition-colors cursor-pointer">
                    Generate All Approved
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Print Bulk Section --}}
<div x-data="{ showPrint: false }" class="mb-6">
    <button @click="showPrint = !showPrint" class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 hover:bg-purple-500 text-white text-sm font-medium rounded-xl transition-colors cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
        </svg>
        Print Bulk Hall Tickets (PDF)
    </button>

    <div x-show="showPrint" x-transition class="mt-4 bg-slate-900/60 border border-purple-800/30 rounded-2xl p-5" style="display: none;">
        <form method="GET" action="{{ route('admin.hall-tickets.print-bulk') }}" target="_blank" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">School *</label>
                <select name="school_id" required class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-purple-500/50">
                    <option value="">Select School</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Examination *</label>
                <select name="examination_id" required class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-purple-500/50">
                    <option value="">Select Examination</option>
                    @foreach($examinations as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-500 text-white text-sm font-medium rounded-xl transition-colors cursor-pointer">
                    Print PDF
                </button>
            </div>
        </form>
    </div>
</div>

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
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Examination</th>
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
                    <td class="px-6 py-4 text-slate-300 text-xs">{{ $student->examination->name ?? '—' }}</td>
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
                                {{-- Re-issue (regenerate) --}}
                                <form method="POST" action="{{ route('admin.hall-tickets.generate-single', $student) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-medium rounded-lg transition-colors cursor-pointer" title="Re-Issue HT">
                                        Re-Issue
                                    </button>
                                </form>
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
