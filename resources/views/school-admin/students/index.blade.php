@extends('layouts.app')

@section('page_title', 'Student Registrations')

@section('content')
{{-- Header Area --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <p class="text-sm text-slate-400">Manage and register students for examination sessions.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('school.students.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Register Student
        </a>
    </div>
</div>

{{-- Top Row: Filters & Bulk Excel Import Card --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Search & Filters Card --}}
    <div class="lg:col-span-2 bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Search & Filters</h3>
        <form method="GET" action="{{ route('school.students.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div class="sm:col-span-1">
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Search Query</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Reg/Adm No..."
                       class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Status</label>
                <select name="status" class="w-full bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="Draft" @selected(request('status') === 'Draft')>Draft</option>
                    <option value="Submitted" @selected(request('status') === 'Submitted')>Submitted</option>
                    <option value="Under Review" @selected(request('status') === 'Under Review')>Under Review</option>
                    <option value="Approved" @selected(request('status') === 'Approved')>Approved</option>
                    <option value="Rejected" @selected(request('status') === 'Rejected')>Rejected</option>
                    <option value="Hall Ticket Issued" @selected(request('status') === 'Hall Ticket Issued')>Hall Ticket Issued</option>
                </select>
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
            <div class="sm:col-span-3 flex gap-2 justify-end mt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-all cursor-pointer">Filter</button>
                @if(request()->hasAny(['search', 'status', 'examination_id']))
                    <a href="{{ route('school.students.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium px-5 py-2.5 rounded-xl transition-all">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Bulk Excel Import Card --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex flex-col justify-between" x-data="{ expanded: false }">
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Bulk Excel Import</h3>
                <a href="{{ route('school.students.import.template') }}" 
                   class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Download Template
                </a>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed mb-4">Register students quickly by uploading a completed spreadsheet template.</p>
        </div>

        <div>
            @if($examinations->where('status', 'Open')->isEmpty())
                <div class="p-3 bg-rose-950/20 border border-rose-800/40 text-rose-300 rounded-xl text-xs text-center font-medium">
                    No active/open exam sessions for bulk import.
                </div>
            @else
                <button @click="expanded = !expanded" 
                        class="w-full flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold py-2.5 rounded-xl transition-all cursor-pointer">
                    <span x-text="expanded ? 'Hide Import Form' : 'Show Import Form'">Show Import Form</span>
                </button>
                <div x-show="expanded" x-transition class="mt-4 pt-4 border-t border-slate-800/60">
                    <form method="POST" action="{{ route('school.students.import') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Import Session</label>
                            <select name="import_examination_id" required class="w-full bg-slate-800/60 border border-slate-700/60 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                                <option value="">Select Examination</option>
                                @foreach($examinations->where('status', 'Open') as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-data="{ excelName: '' }">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Select Excel/CSV File</label>
                            <div class="flex items-center gap-3">
                                <input type="file" x-ref="excelInput" name="excel_file" required accept=".xlsx,.xls,.csv" class="hidden"
                                       @change="excelName = $event.target.files[0] ? $event.target.files[0].name : ''">
                                <button type="button" @click="$refs.excelInput.click()"
                                        class="px-4 py-2.5 bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-400 rounded-xl text-xs font-semibold cursor-pointer transition-all flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                                    Choose File
                                </button>
                                <span class="text-xs text-slate-400 truncate" x-text="excelName || 'No file chosen'"></span>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold py-2.5 rounded-xl transition-all cursor-pointer">Upload and Import</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Validation Errors from Import --}}
@if($errors->any())
    <div class="mb-6 p-4 bg-rose-950/40 border border-rose-800/40 text-rose-200 rounded-2xl">
        <h4 class="text-sm font-semibold mb-2 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-rose-400"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
            Import Failures/Validation Errors
        </h4>
        <ul class="list-disc list-inside text-xs space-y-1 text-rose-300/95 max-h-48 overflow-y-auto pr-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Student Table --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-slate-800/60">
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Candidate</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Mobile Number</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Academic Details</th>
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
                                <p class="text-xs text-slate-500 mt-0.5">{{ $student->gender }} · {{ $student->dob->format('d M Y') }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-300 font-medium">{{ $student->mobile_number }}</td>
                    <td class="px-6 py-4 hidden md:table-cell">
                        <div>
                            <p class="text-slate-300 font-medium text-xs">{{ $student->class->name }}</p>
                            <p class="text-[10px] text-slate-500 tracking-wider font-semibold uppercase mt-0.5">{{ $student->category->name }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <div class="max-w-[200px] truncate">
                            <p class="text-slate-300 font-medium text-xs" title="{{ $student->examination->name }}">{{ $student->examination->name }}</p>
                            @if($student->registration_number)
                                <p class="text-[10px] text-slate-500 tracking-wider font-mono font-semibold uppercase mt-0.5">{{ $student->registration_number }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        @php
                        $badges = [
                            'Draft' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                            'Submitted' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                            'Under Review' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                            'Approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                            'Rejected' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                            'Hall Ticket Issued' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                        ];
                        $badgeStyle = $badges[$student->status] ?? 'bg-slate-500/10 text-slate-400 border-slate-500/20';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeStyle }}">
                            {{ $student->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('school.students.show', $student) }}" class="p-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all" title="View Profile">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </a>
                            @if(in_array($student->status, ['Draft', 'Rejected']))
                                <a href="{{ route('school.students.edit', $student) }}" class="p-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all" title="Edit Student">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                </a>
                                
                                {{-- Submit Action --}}
                                <form method="POST" action="{{ route('school.students.submit', $student) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-slate-400 hover:bg-indigo-600/10 hover:text-indigo-400 transition-all cursor-pointer" title="Submit Registration">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                                    </button>
                                </form>
                                
                                {{-- Delete Action --}}
                                <form method="POST" action="{{ route('school.students.destroy', $student) }}" onsubmit="return confirm('Are you sure you want to delete this draft registration?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition-all cursor-pointer" title="Delete Draft">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-700"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                        No registered students found matching the filters.
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
