@extends('layouts.app')

@section('page_title', 'Student Registrations')

@section('content')
@php
    $unpaidCount = $students->filter(fn($s) => $s->payment_status === 'Unpaid' && in_array($s->status, ['Draft','Rejected']))->count();
    
    // Calculate page metrics for the school admin dashboard view
    $totalCount = $students->total();
    $unpaidTotal = \App\Models\Student::where('school_id', auth()->user()->school_id)
        ->where('payment_status', 'Unpaid')
        ->whereIn('status', ['Draft', 'Rejected'])
        ->count();
    $submittedCount = \App\Models\Student::where('school_id', auth()->user()->school_id)
        ->where('status', 'Submitted')
        ->count();
    $approvedCount = \App\Models\Student::where('school_id', auth()->user()->school_id)
        ->where('status', 'Approved')
        ->count();
@endphp

{{-- ─── KPI Stats Ribbon ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/80 rounded-2xl p-4 shadow-xl relative overflow-hidden group hover:border-slate-700 transition-all duration-300">
        <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-indigo-500/5 rounded-full blur-xl group-hover:bg-indigo-500/10 transition-all"></div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Registered</p>
        <div class="flex items-baseline gap-2 mt-2">
            <span class="text-2xl font-black text-white tracking-tight">{{ $totalCount }}</span>
            <span class="text-xs text-slate-400 font-medium">candidates</span>
        </div>
    </div>
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/80 rounded-2xl p-4 shadow-xl relative overflow-hidden group hover:border-slate-700 transition-all duration-300">
        <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-rose-500/5 rounded-full blur-xl group-hover:bg-rose-500/10 transition-all"></div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Awaiting Payment</p>
        <div class="flex items-baseline gap-2 mt-2">
            <span class="text-2xl font-black text-rose-400 tracking-tight">{{ $unpaidTotal }}</span>
            <span class="text-xs text-slate-400 font-medium">unpaid</span>
        </div>
    </div>
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/80 rounded-2xl p-4 shadow-xl relative overflow-hidden group hover:border-slate-700 transition-all duration-300">
        <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-amber-500/5 rounded-full blur-xl group-hover:bg-amber-500/10 transition-all"></div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Submitted to Board</p>
        <div class="flex items-baseline gap-2 mt-2">
            <span class="text-2xl font-black text-amber-400 tracking-tight">{{ $submittedCount }}</span>
            <span class="text-xs text-slate-400 font-medium">pending</span>
        </div>
    </div>
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/80 rounded-2xl p-4 shadow-xl relative overflow-hidden group hover:border-slate-700 transition-all duration-300">
        <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-emerald-500/5 rounded-full blur-xl group-hover:bg-emerald-500/10 transition-all"></div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Approved</p>
        <div class="flex items-baseline gap-2 mt-2">
            <span class="text-2xl font-black text-emerald-400 tracking-tight">{{ $approvedCount }}</span>
            <span class="text-xs text-slate-400 font-medium">verified</span>
        </div>
    </div>
</div>

{{-- ─── Page Header ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-extrabold text-white tracking-tight">Student Registrations</h2>
        <p class="text-xs text-slate-400 mt-1">Manage, verify, and settle fees for exam candidates in draft state.</p>
    </div>
    <div class="flex flex-wrap gap-3 items-center">
        @if($unpaidCount > 0)
            <form method="POST" action="{{ route('school.payments.checkout') }}" class="inline animate-fade-in">
                @csrf
                @foreach($students->filter(fn($s) => $s->payment_status === 'Unpaid' && in_array($s->status, ['Draft','Rejected'])) as $student)
                    <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
                @endforeach
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-450 text-white text-xs font-bold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-emerald-950/20 cursor-pointer active:scale-95 duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                    Pay All Unpaid ({{ $unpaidCount }})
                </button>
            </form>
        @endif

        <a href="{{ route('school.students.create') }}"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-550 text-white text-xs font-bold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-950/30 cursor-pointer active:scale-95 duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Register Student
        </a>
    </div>
</div>

{{-- ─── Filters + Bulk Excel Import ────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Search & Filters --}}
    <div class="lg:col-span-2 bg-slate-900/40 backdrop-blur-md border border-slate-800/60 rounded-2xl p-5 shadow-2xl">
        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-4">Search & Filters</h3>
        <form method="GET" action="{{ route('school.students.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Search Query</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Reg/Adm No..."
                       class="w-full bg-slate-950/50 border border-slate-800/80 rounded-xl px-4 py-2 text-xs text-slate-100 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition-all font-medium">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Status</label>
                <select name="status" class="w-full bg-slate-950/50 border border-slate-800/80 rounded-xl px-4 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 transition-all font-medium">
                    <option value="">All Statuses</option>
                    <option value="Draft"             @selected(request('status') === 'Draft')>Draft</option>
                    <option value="Submitted"         @selected(request('status') === 'Submitted')>Submitted</option>
                    <option value="Under Review"      @selected(request('status') === 'Under Review')>Under Review</option>
                    <option value="Approved"          @selected(request('status') === 'Approved')>Approved</option>
                    <option value="Rejected"          @selected(request('status') === 'Rejected')>Rejected</option>
                    <option value="Hall Ticket Issued" @selected(request('status') === 'Hall Ticket Issued')>Hall Ticket Issued</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Exam Session</label>
                <select name="examination_id" class="w-full bg-slate-950/50 border border-slate-800/80 rounded-xl px-4 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 transition-all font-medium">
                    <option value="">All Sessions</option>
                    @foreach($examinations as $exam)
                        <option value="{{ $exam->id }}" @selected(request('examination_id') == $exam->id)>{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-3 flex gap-2 justify-end mt-2">
                @if(request()->hasAny(['search', 'status', 'examination_id']))
                    <a href="{{ route('school.students.index') }}" class="bg-slate-800/50 hover:bg-slate-800 border border-slate-800/80 text-slate-300 text-xs font-semibold px-4 py-2 rounded-xl transition-all">Clear Filters</a>
                @endif
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-5 py-2 rounded-xl transition-all cursor-pointer">Apply Filter</button>
            </div>
        </form>
    </div>

    {{-- Bulk Excel Import --}}
    <div class="bg-slate-900/40 backdrop-blur-md border border-slate-800/60 rounded-2xl p-5 shadow-2xl flex flex-col justify-between" x-data="{ expanded: false }">
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Bulk Excel Import</h3>
                <a href="{{ route('school.students.import.template') }}"
                   class="text-[10px] text-indigo-400 hover:text-indigo-300 font-bold uppercase tracking-wider transition-colors flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Template
                </a>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed mb-4">Register students quickly by uploading a completed spreadsheet template.</p>
        </div>
        <div>
            @if($examinations->where('status', 'Open')->isEmpty())
                <div class="p-3 bg-rose-950/20 border border-rose-900/30 text-rose-300 rounded-xl text-xs text-center font-medium">
                    No active/open exam sessions for bulk import.
                </div>
            @else
                <button @click="expanded = !expanded"
                        class="w-full flex items-center justify-center gap-2 bg-slate-800/60 hover:bg-slate-800 border border-slate-850 text-slate-200 text-xs font-semibold py-2 rounded-xl transition-all cursor-pointer">
                    <span x-text="expanded ? 'Hide Import Form' : 'Show Import Form'">Show Import Form</span>
                </button>
                <div x-show="expanded" x-transition class="mt-4 pt-4 border-t border-slate-800/60">
                    <form method="POST" action="{{ route('school.students.import') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Import Session</label>
                            <select name="import_examination_id" required class="w-full bg-slate-950/50 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 font-medium">
                                <option value="">Select Examination</option>
                                @foreach($examinations->where('status', 'Open') as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-data="{ excelName: '' }">
                            <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Select Excel/CSV File</label>
                            <div class="flex items-center gap-3">
                                <input type="file" x-ref="excelInput" name="excel_file" required accept=".xlsx,.xls,.csv" class="hidden"
                                       @change="excelName = $event.target.files[0] ? $event.target.files[0].name : ''">
                                <button type="button" @click="$refs.excelInput.click()"
                                        class="px-4 py-2 bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-400 rounded-xl text-xs font-semibold cursor-pointer transition-all flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                                    Choose File
                                </button>
                                <span class="text-xs text-slate-400 truncate" x-text="excelName || 'No file chosen'"></span>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-indigo-650 hover:bg-indigo-600 text-white text-xs font-bold py-2 rounded-xl transition-all cursor-pointer">Upload and Import</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ─── Validation Errors from Import ──────────────────────────────────────── --}}
@if($errors->any())
    <div class="mb-6 p-4 bg-rose-950/40 border border-rose-800/40 text-rose-200 rounded-2xl shadow-xl">
        <h4 class="text-sm font-semibold mb-2 flex items-center gap-2 text-rose-300">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
            Import Failures / Validation Errors
        </h4>
        <ul class="list-disc list-inside text-xs space-y-1 text-rose-300/80 max-h-48 overflow-y-auto pr-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ─── Bulk Payment Banner (only when unpaid Draft/Rejected exist) ─────────── --}}
@if($unpaidCount > 0)
<div class="mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-gradient-to-r from-indigo-950/60 to-slate-900/60 border border-indigo-950 rounded-2xl px-5 py-4 shadow-xl">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-indigo-400"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-200">
                <span class="text-indigo-400 font-bold">{{ $unpaidCount }}</span> candidate(s) on this page are awaiting fee payment.
            </p>
            <p class="text-[11px] text-slate-400 mt-0.5">Select candidates using checkboxes below, then click <strong class="text-slate-350">Pay Selected</strong> to process registrations.</p>
        </div>
    </div>
    {{-- Quick "Select All Unpaid" convenience button --}}
    <button type="button" id="select-all-unpaid-btn"
            class="shrink-0 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all cursor-pointer shadow-lg shadow-indigo-600/20 active:scale-95 duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        Select All Unpaid
    </button>
</div>
@endif

{{-- ─── Student Table with AlpineJS Bulk Selection ──────────────────────────── --}}
<div x-data="{
    selectedIds: [],
    selectedFee: 0,

    toggleAll(checked) {
        this.selectedIds = [];
        this.selectedFee = 0;
        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.checked = checked && !cb.disabled;
            if (checked && !cb.disabled) {
                this.selectedIds.push(cb.value);
                this.selectedFee += parseFloat(cb.dataset.fee);
            }
        });
    },

    toggleStudent(id, fee, checked) {
        if (checked) {
            if (!this.selectedIds.includes(id)) {
                this.selectedIds.push(id);
                this.selectedFee += fee;
            }
        } else {
            const index = this.selectedIds.indexOf(id);
            if (index > -1) {
                this.selectedIds.splice(index, 1);
                this.selectedFee -= fee;
                this.selectedFee = Math.max(0, this.selectedFee);
            }
            // Uncheck master checkbox
            const masterCb = document.getElementById('select-all-checkbox');
            if (masterCb) masterCb.checked = false;
        }
    },

    clearSelection() {
        this.selectedIds = [];
        this.selectedFee = 0;
        document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
        const masterCb = document.getElementById('select-all-checkbox');
        if (masterCb) masterCb.checked = false;
    }
}" id="student-table-wrapper" class="w-full">

    {{-- Table Card --}}
    <div class="bg-slate-900/40 backdrop-blur-md border border-slate-800/60 rounded-2xl overflow-hidden mb-28 shadow-2xl w-full">

        {{-- Table Toolbar --}}
        <div class="px-5 py-4 border-b border-slate-800/60 flex items-center justify-between gap-3 bg-slate-950/20">
            <div class="flex items-center gap-3">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                    {{ $students->total() }} candidate(s) total
                </span>
                {{-- Live Selection Badge --}}
                <span x-show="selectedIds.length > 0"
                      x-transition
                      class="inline-flex items-center gap-1.5 bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-bold px-2.5 py-1 rounded-full shadow-md"
                      style="display:none;">
                    <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-ping"></span>
                    <span x-text="selectedIds.length"></span> selected
                    &mdash;
                    ₹<span x-text="selectedFee.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                </span>
            </div>
            {{-- Clear selection button --}}
            <button type="button" x-show="selectedIds.length > 0" @click="clearSelection()"
                    x-transition
                    class="text-xs text-slate-400 hover:text-rose-400 transition-colors flex items-center gap-1 cursor-pointer font-semibold"
                    style="display:none;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                Clear Selection
            </button>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-sm text-left table-auto">
                <thead>
                    <tr class="border-b border-slate-800/60 bg-slate-950/40">
                        <th class="px-4 py-4 text-center w-12">
                            <input type="checkbox" id="select-all-checkbox"
                                   @change="toggleAll($event.target.checked)"
                                   class="rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-4 h-4"
                                   title="Select / deselect all unpaid candidates">
                        </th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Candidate</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Mobile</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest hidden md:table-cell">Academic</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest hidden lg:table-cell">Exam Session</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Payment</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($students as $student)
                    @php
                        $isPayable = $student->payment_status === 'Unpaid' && in_array($student->status, ['Draft', 'Rejected']);
                        $fee = $student->class->registration_fee;
                    @endphp
                    <tr class="hover:bg-slate-850/30 transition-all duration-150 border-b border-slate-800/20"
                        :class="selectedIds.includes('{{ $student->id }}') ? 'bg-indigo-950/15 border-indigo-800/40' : ''">

                        {{-- Checkbox --}}
                        <td class="px-4 py-4 text-center">
                            @if($isPayable)
                                <input type="checkbox"
                                       class="student-checkbox rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition-all w-4 h-4"
                                       value="{{ $student->id }}"
                                       data-fee="{{ $fee }}"
                                       :checked="selectedIds.includes('{{ $student->id }}')"
                                       @change="toggleStudent('{{ $student->id }}', {{ $fee }}, $event.target.checked)">
                            @else
                                <input type="checkbox" disabled
                                       class="rounded bg-slate-900/30 border-slate-900 text-slate-800 cursor-not-allowed opacity-20 w-4 h-4"
                                       title="{{ $student->payment_status === 'Paid' ? 'Fee already paid' : 'Cannot pay in current status' }}">
                            @endif
                        </td>

                        {{-- Candidate --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $student->photo_url }}" alt="{{ $student->name }}"
                                     class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0 shadow-md group-hover:scale-105 transition-transform duration-200">
                                <div>
                                    <p class="font-bold text-slate-200 hover:text-white transition-colors">{{ $student->name }}</p>
                                    <p class="text-[10px] text-slate-500 font-medium mt-0.5 uppercase tracking-wider">{{ $student->gender }} · {{ $student->dob->format('d M Y') }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Mobile --}}
                        <td class="px-6 py-4 text-slate-300 font-mono text-xs font-semibold">{{ $student->mobile_number }}</td>

                        {{-- Academic Details --}}
                        <td class="px-6 py-4 hidden md:table-cell">
                            <p class="text-slate-200 font-bold text-xs">{{ $student->class->name }}</p>
                            <p class="text-[9px] text-slate-500 tracking-widest font-black uppercase mt-0.5">{{ $student->category->name }}</p>
                        </td>

                        {{-- Exam Session --}}
                        <td class="px-6 py-4 hidden lg:table-cell">
                            <div class="max-w-[220px] truncate">
                                <p class="text-slate-300 font-semibold text-xs" title="{{ $student->examination->name }}">{{ $student->examination->name }}</p>
                                @if($student->registration_number)
                                    <p class="text-[9px] text-indigo-400 font-mono font-bold uppercase tracking-wider mt-0.5">{{ $student->registration_number }}</p>
                                @endif
                            </div>
                        </td>

                        {{-- Payment Status --}}
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @if($student->payment_status === 'Paid')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-md shadow-emerald-400/50"></span>Paid
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-500/10 text-rose-450 border border-rose-500/20 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse shadow-md shadow-rose-400/50"></span>
                                    Unpaid · ₹{{ number_format($fee, 0) }}
                                </span>
                            @endif
                        </td>

                        {{-- Registration Status --}}
                        <td class="px-6 py-4 text-center whitespace-nowrap font-sans">
                            @php
                                $badges = [
                                    'Draft'              => 'bg-slate-800/40 text-slate-400 border-slate-700/60',
                                    'Submitted'          => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                                    'Under Review'       => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'Approved'           => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'Rejected'           => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                    'Hall Ticket Issued' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                ];
                                $badgeStyle = $badges[$student->status] ?? 'bg-slate-800/40 text-slate-400 border-slate-700/60';
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeStyle }} shadow-sm">
                                {{ $student->status }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1">
                                {{-- View --}}
                                <a href="{{ route('school.students.show', $student) }}"
                                   class="p-2 rounded-xl text-slate-400 hover:bg-slate-850 hover:text-white transition-all duration-150" title="View Profile">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </a>

                                @if(in_array($student->status, ['Draft', 'Rejected']))
                                    {{-- Edit --}}
                                    <a href="{{ route('school.students.edit', $student) }}"
                                       class="p-2 rounded-xl text-slate-400 hover:bg-slate-850 hover:text-white transition-all duration-150" title="Edit Student">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                    </a>

                                    @if($isPayable)
                                        {{-- Pay Individual --}}
                                        <form method="POST" action="{{ route('school.payments.checkout') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
                                            <button type="submit"
                                                    class="p-2 rounded-xl text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition-all duration-150 cursor-pointer"
                                                    title="Pay Fee & Submit (Individual)">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('school.students.destroy', $student) }}"
                                          onsubmit="return confirm('Are you sure you want to delete this draft registration?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="p-2 rounded-xl text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition-all duration-150 cursor-pointer"
                                                title="Delete Draft">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-700"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                            No registered students found matching the filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
        <div class="px-6 py-4 border-t border-slate-800/60 bg-slate-950/20">{{ $students->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- ─── Floating Bulk Payment Drawer ──────────────────────────────────────── --}}
    <div x-show="selectedIds.length > 0"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-24 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-24 opacity-0"
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-3xl"
         style="display: none;">

        <div class="bg-slate-900/90 border border-indigo-500/30 backdrop-blur-md rounded-2xl shadow-2xl shadow-indigo-950/60 p-4 sm:p-5
                    flex flex-col sm:flex-row items-center justify-between gap-4
                    ring-1 ring-inset ring-indigo-500/10">

            {{-- Left: Selection Info --}}
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="relative shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-indigo-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-md shadow-indigo-650/40"
                          x-text="selectedIds.length"></span>
                </div>
                <div>
                    <p class="text-sm font-bold text-white leading-tight">
                        <span x-text="selectedIds.length"></span>
                        candidate<span x-show="selectedIds.length !== 1">s</span> selected
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Total due:
                        <span class="text-indigo-300 font-bold">
                            ₹<span x-text="selectedFee.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </span>
                    </p>
                </div>
            </div>

            {{-- Right: Action Buttons --}}
            <div class="flex items-center gap-3 w-full sm:w-auto shrink-0">
                {{-- Deselect All --}}
                <button type="button" @click="clearSelection()"
                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5
                               px-4 py-2.5 bg-slate-800/80 hover:bg-slate-700 border border-slate-700/60
                               text-slate-350 text-xs font-semibold rounded-xl transition-all cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    Clear Selection
                </button>

                {{-- Pay Selected (Bulk Checkout Form) --}}
                <form method="POST" action="{{ route('school.payments.checkout') }}" class="flex-1 sm:flex-initial" id="bulk-payment-form">
                    @csrf
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="student_ids[]" :value="id">
                    </template>
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2
                                   px-6 py-2.5 bg-gradient-to-r from-indigo-650 to-purple-600 hover:from-indigo-600 hover:to-purple-550
                                   text-white text-xs font-bold rounded-xl
                                   transition-all shadow-lg shadow-indigo-600/30 cursor-pointer active:scale-95 duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Pay Selected &mdash;
                        ₹<span x-text="selectedFee.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ─── Select All Unpaid JS helper ────────────────────────────────────────── --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('select-all-unpaid-btn');
        if (btn) {
            btn.addEventListener('click', function () {
                const masterCb = document.getElementById('select-all-checkbox');
                if (masterCb) {
                    masterCb.checked = true;
                    masterCb.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }
    });
</script>

@endsection
