@extends('layouts.app')

@section('page_title', 'Bulk Import Exam Results')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Breadcrumb & Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-800/60">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                    <a href="{{ route('admin.results.index') }}"
                        class="hover:text-indigo-400 transition-colors flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Results Management
                    </a>
                    <span>/</span>
                    <span class="text-slate-200 font-medium">Bulk Import</span>
                </div>
                <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2.5">
                    Bulk Import Exam Results
                </h1>
                <p class="text-xs text-slate-400 mt-1">Upload marks spreadsheets to automatically publish scores, grades,
                    and remarks for candidates.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.results.import.template', ['format' => 'excel', 'examination_id' => $selectedExamId ?? '']) }}"
                    id="top-download-excel-btn"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-emerald-600/10 transition-all duration-200 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download Excel Template
                </a>
                <a href="{{ route('admin.results.index') }}"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                    Back to List
                </a>
            </div>
        </div>

        {{-- Error / Warning Alert Box --}}
        @if($errors->any())
            <div
                class="p-5 rounded-2xl bg-rose-950/40 border border-rose-800/40 text-rose-200 shadow-xl shadow-rose-950/20 animate-fadeIn">
                <div class="flex items-start gap-3">
                    <div
                        class="w-8 h-8 rounded-lg bg-rose-500/20 flex items-center justify-center shrink-0 text-rose-400 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h5 class="font-bold text-sm text-rose-200">Please correct the following spreadsheet errors:</h5>
                        <ul class="mt-2 space-y-1.5 text-xs text-rose-300 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Grid Layout (7 cols / 5 cols) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- Left Column: Upload Form (7 cols) --}}
            <div class="lg:col-span-7 space-y-6">
                <form method="POST" action="{{ route('admin.results.import') }}" enctype="multipart/form-data"
                    id="bulk-import-form"
                    class="bg-slate-900/60 border border-slate-800/60 rounded-3xl p-6 sm:p-7 space-y-6 shadow-xl backdrop-blur-sm">
                    @csrf

                    {{-- Step 1: Exam Session Selection --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="examination_id"
                                class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                <span
                                    class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-[10px] font-extrabold">1</span>
                                Target Examination Session
                            </label>
                            <span class="text-[11px] text-slate-500">Required</span>
                        </div>
                        <div class="relative">
                            <select name="examination_id" id="examination_id"
                                onchange="updateTemplateDownloadLinks(this.value)"
                                class="w-full px-4 py-3 rounded-xl bg-slate-950/70 border border-slate-800 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/80 focus:ring-2 focus:ring-indigo-500/20 transition-all cursor-pointer"
                                required>
                                <option value="">-- Choose Examination Session --</option>
                                @foreach($examinations as $exam)
                                    <option value="{{ $exam->id }}" @selected(old('examination_id', $selectedExamId) == $exam->id)>
                                        {{ $exam->name }} ({{ $exam->academic_year }}) — {{ $exam->status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Step 2: Drag & Drop File Upload Zone --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label
                                class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                <span
                                    class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-[10px] font-extrabold">2</span>
                                Upload Result Spreadsheet
                            </label>
                            <span class="text-[11px] text-slate-500">Max: 5 MB (.xlsx, .xls, .csv)</span>
                        </div>

                        {{-- Dropzone Container --}}
                        <div id="drop-zone"
                            class="relative group border-2 border-dashed border-slate-800 hover:border-indigo-500/60 rounded-2xl p-6 text-center bg-slate-950/40 hover:bg-slate-950/70 transition-all duration-200 cursor-pointer">
                            <input type="file" id="result_file" name="result_file" accept=".xlsx,.xls,.csv"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required>

                            <div id="upload-prompt" class="space-y-3 pointer-events-none">
                                <div
                                    class="w-14 h-14 mx-auto rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-lg shadow-indigo-500/5">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.75" stroke="currentColor" class="w-7 h-7">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-200">
                                        <span
                                            class="text-indigo-400 underline decoration-indigo-500/30 underline-offset-4">Click
                                            to browse</span> or drag and drop file here
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1">Supports Microsoft Excel (.xlsx, .xls) and
                                        Comma-Separated (.csv)</p>
                                </div>
                                <div class="flex items-center justify-center gap-2 pt-1">
                                    <span
                                        class="px-2 py-0.5 text-[10px] font-mono font-semibold uppercase rounded bg-slate-800/80 text-emerald-400 border border-emerald-500/20">.XLSX</span>
                                    <span
                                        class="px-2 py-0.5 text-[10px] font-mono font-semibold uppercase rounded bg-slate-800/80 text-teal-400 border border-teal-500/20">.XLS</span>
                                    <span
                                        class="px-2 py-0.5 text-[10px] font-mono font-semibold uppercase rounded bg-slate-800/80 text-sky-400 border border-sky-500/20">.CSV</span>
                                </div>
                            </div>

                            {{-- Selected File Display (Hidden initially) --}}
                            <div id="file-info-preview" class="hidden py-2 space-y-3">
                                <div
                                    class="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-indigo-500/30">
                                    <div class="flex items-center gap-3 text-left">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 6 3 3m0 0 3-3m-3 3v-6" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p id="selected-file-name"
                                                class="text-sm font-semibold text-slate-100 truncate"></p>
                                            <p id="selected-file-size" class="text-xs text-slate-500"></p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="clearSelectedFile(event)"
                                        class="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-950/50 text-slate-400 hover:text-rose-400 transition-colors cursor-pointer z-20"
                                        title="Remove file">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-xs text-indigo-400 font-medium">✓ File ready for processing</p>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3: Overwrite Toggle Option --}}
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80 flex items-start gap-3.5">
                        <div class="pt-0.5">
                            <input type="checkbox" name="overwrite_existing" id="overwrite_existing" value="1"
                                class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500/40 cursor-pointer">
                        </div>
                        <label for="overwrite_existing" class="text-xs text-slate-300 cursor-pointer select-none">
                            <strong class="text-slate-100 block font-semibold mb-0.5">Overwrite existing candidate
                                results</strong>
                            If checked, marks and grades for students who already have recorded results in this exam session
                            will be updated with the spreadsheet data.
                        </label>
                    </div>

                    {{-- Submit Button & Status --}}
                    <div class="pt-2">
                        <button type="submit" id="submit-btn"
                            class="w-full py-3.5 px-6 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-2xl transition-all duration-200 shadow-md shadow-indigo-600/10 flex items-center justify-center gap-2 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            <span>Validate and Process Results Import</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Right Column: Sample Files, Instructions & Live Preview (5 cols) --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- Download Sample Template Card --}}
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-3xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center gap-2.5 pb-2 border-b border-slate-800/60">
                        <div
                            class="w-8 h-8 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-100">Sample Templates</h3>
                            <p class="text-[11px] text-slate-500">Download formatted files ready for data entry</p>
                        </div>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        Download the pre-formatted template with standard headers. You can fill it in Excel or Google Sheets
                        and upload it directly.
                    </p>

                    {{-- Action Download Buttons --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        {{-- Excel Template Button --}}
                        <a href="{{ route('admin.results.import.template', ['format' => 'excel', 'examination_id' => $selectedExamId ?? '']) }}"
                            id="download-excel-link"
                            class="flex items-center gap-2.5 p-3 rounded-xl bg-emerald-950/30 border border-emerald-500/30 hover:border-emerald-500/60 hover:bg-emerald-950/50 text-emerald-300 transition-all duration-200 group">
                            <div
                                class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                            </div>
                            <div class="min-w-0 text-left">
                                <p class="text-xs font-bold text-slate-200 leading-tight">Excel (.xlsx)</p>
                                <p class="text-[10px] text-emerald-400/80 mt-0.5 font-medium">Recommended</p>
                            </div>
                        </a>

                        {{-- CSV Template Button --}}
                        <a href="{{ route('admin.results.import.template', ['format' => 'csv', 'examination_id' => $selectedExamId ?? '']) }}"
                            id="download-csv-link"
                            class="flex items-center gap-2.5 p-3 rounded-xl bg-sky-950/30 border border-sky-500/30 hover:border-sky-500/60 hover:bg-sky-950/50 text-sky-300 transition-all duration-200 group">
                            <div
                                class="w-8 h-8 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                            </div>
                            <div class="min-w-0 text-left">
                                <p class="text-xs font-bold text-slate-200 leading-tight">CSV (.csv)</p>
                                <p class="text-[10px] text-sky-400/80 mt-0.5 font-medium">Plain Text</p>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Column Format Guidelines Card --}}
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-3xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center gap-2.5 pb-2 border-b border-slate-800/60">
                        <div
                            class="w-8 h-8 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-100">Column Guidelines</h3>
                            <p class="text-[11px] text-slate-500">Headers are case-insensitive</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-start gap-2.5">
                            <span
                                class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 shrink-0">Required</span>
                            <div class="text-slate-300">
                                <strong class="text-slate-100">Registration Number</strong>
                                <p class="text-[11px] text-slate-500 mt-0.5">Unique candidate registration number.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <span
                                class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 shrink-0">Required</span>
                            <div class="text-slate-300">
                                <strong class="text-slate-100">Marks Obtained</strong> & <strong class="text-slate-100">Max
                                    Marks</strong>
                                <p class="text-[11px] text-slate-500 mt-0.5">Integer scores where Marks Obtained &le; Max
                                    Marks.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <span
                                class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700 shrink-0">Optional</span>
                            <div class="text-slate-300">
                                <strong class="text-slate-100">Status</strong>
                                <p class="text-[11px] text-slate-500 mt-0.5">Defaults to <code
                                        class="text-emerald-400">Pass</code> (&ge;35%) or <code
                                        class="text-rose-400">Fail</code>.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Automatic Calculations Notice --}}
                    <div class="p-3.5 rounded-2xl bg-indigo-950/40 border border-indigo-500/20 space-y-2 text-xs">
                        <div class="flex items-center gap-2 text-indigo-300 font-bold">
                            Automatic System Processing
                        </div>
                        <ul class="space-y-1 text-[11px] text-slate-300">
                            <li class="flex items-start gap-1.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span><strong>Grade Calculation:</strong> Automatically assigned (<code
                                        class="text-indigo-300">A+</code>, <code class="text-indigo-300">A</code>, <code
                                        class="text-indigo-300">B</code>, <code class="text-indigo-300">C</code>, <code
                                        class="text-indigo-300">D</code>, <code class="text-indigo-300">E</code>, <code
                                        class="text-indigo-300">F</code>) based on percentage.</span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span><strong>Auto Remarks:</strong>
                                    <br><span class="text-emerald-400 font-semibold">• Pass:</span> <em>"Qualified For
                                        Second Round Examination"</em>
                                    <br><span class="text-rose-400 font-semibold">• Fail:</span> <em>"Not Qualified For
                                        Second Round Examination"</em>
                                </span>
                            </li>
                            <li class="flex items-start gap-1.5 text-slate-400 pt-1">
                                <span>ℹ️ <em>Only Registration Number, Marks Obtained, Max Marks, and Status are needed in the Excel file.</em></span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Live Sample Table Preview --}}
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-3xl p-6 shadow-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Sample Spreadsheet Preview
                        </h3>
                        <span class="text-[10px] text-indigo-400 font-medium">4 Columns</span>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-950/80">
                        <table class="w-full text-left text-[11px] font-mono">
                            <thead class="bg-slate-900 border-b border-slate-800 text-slate-400">
                                <tr>
                                    <th class="p-2.5 whitespace-nowrap">Registration Number</th>
                                    <th class="p-2.5 whitespace-nowrap text-center">Marks Obtained</th>
                                    <th class="p-2.5 whitespace-nowrap text-center">Max Marks</th>
                                    <th class="p-2.5 whitespace-nowrap text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50 text-slate-300">
                                @if(isset($previewCandidates) && $previewCandidates->count() > 0)
                                    @foreach($previewCandidates->take(3) as $idx => $candidate)
                                        <tr class="hover:bg-slate-800/20">
                                            <td class="p-2.5 text-indigo-300 font-semibold">
                                                {{ $candidate->registration_number ?? 'REG' . (10001 + $idx) }}
                                            </td>
                                            <td class="p-2.5 text-center font-bold text-emerald-400">{{ 420 - ($idx * 45) }}</td>
                                            <td class="p-2.5 text-center text-slate-400">500</td>
                                            <td class="p-2.5 text-center"><span
                                                    class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Pass</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="hover:bg-slate-800/20">
                                        <td class="p-2.5 text-indigo-300 font-semibold">REG20260001</td>
                                        <td class="p-2.5 text-center font-bold text-emerald-400">425</td>
                                        <td class="p-2.5 text-center text-slate-400">500</td>
                                        <td class="p-2.5 text-center"><span
                                                class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Pass</span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-slate-800/20">
                                        <td class="p-2.5 text-indigo-300 font-semibold">REG20260002</td>
                                        <td class="p-2.5 text-center font-bold text-emerald-400">380</td>
                                        <td class="p-2.5 text-center text-slate-400">500</td>
                                        <td class="p-2.5 text-center"><span
                                                class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Pass</span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-slate-800/20">
                                        <td class="p-2.5 text-indigo-300 font-semibold">REG20260003</td>
                                        <td class="p-2.5 text-center font-bold text-rose-400">140</td>
                                        <td class="p-2.5 text-center text-slate-400">500</td>
                                        <td class="p-2.5 text-center"><span
                                                class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20">Fail</span>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Interactive Scripts for Drag & Drop and Dynamic Download URLs --}}
    <script @nonce>
        function updateTemplateDownloadLinks(examId) {
            const baseUrl = "{{ route('admin.results.import.template') }}";
            const excelBtn = document.getElementById('top-download-excel-btn');
            const excelLink = document.getElementById('download-excel-link');
            const csvLink = document.getElementById('download-csv-link');

            const excelUrl = `${baseUrl}?format=excel&examination_id=${encodeURIComponent(examId)}`;
            const csvUrl = `${baseUrl}?format=csv&examination_id=${encodeURIComponent(examId)}`;

            if (excelBtn) excelBtn.href = excelUrl;
            if (excelLink) excelLink.href = excelUrl;
            if (csvLink) csvLink.href = csvUrl;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function clearSelectedFile(e) {
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }
            const input = document.getElementById('result_file');
            input.value = '';
            document.getElementById('upload-prompt').classList.remove('hidden');
            document.getElementById('file-info-preview').classList.add('hidden');
            document.getElementById('drop-zone').classList.remove('border-indigo-500', 'bg-indigo-950/20');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const fileInput = document.getElementById('result_file');
            const dropZone = document.getElementById('drop-zone');
            const uploadPrompt = document.getElementById('upload-prompt');
            const fileInfoPreview = document.getElementById('file-info-preview');
            const fileNameEl = document.getElementById('selected-file-name');
            const fileSizeEl = document.getElementById('selected-file-size');
            const form = document.getElementById('bulk-import-form');
            const submitBtn = document.getElementById('submit-btn');

            function handleFileSelection(file) {
                if (!file) return;

                const validExtensions = ['.xlsx', '.xls', '.csv'];
                const fileName = file.name.toLowerCase();
                const isValid = validExtensions.some(ext => fileName.endsWith(ext));

                if (!isValid) {
                    alert('Please select a valid Excel (.xlsx, .xls) or CSV (.csv) file.');
                    clearSelectedFile();
                    return;
                }

                fileNameEl.textContent = file.name;
                fileSizeEl.textContent = formatFileSize(file.size);

                uploadPrompt.classList.add('hidden');
                fileInfoPreview.classList.remove('hidden');
                dropZone.classList.add('border-indigo-500', 'bg-indigo-950/20');
            }

            fileInput.addEventListener('change', (e) => {
                if (e.target.files && e.target.files.length > 0) {
                    handleFileSelection(e.target.files[0]);
                }
            });

            // Drag and drop events
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.add('border-indigo-500', 'bg-indigo-950/30', 'scale-[1.01]');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.remove('border-indigo-500', 'bg-indigo-950/30', 'scale-[1.01]');
                }, false);
            });

            dropZone.addEventListener('drop', (e) => {
                if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                    fileInput.files = e.dataTransfer.files;
                    handleFileSelection(e.dataTransfer.files[0]);
                }
            });

            // Submit button loading state
            form.addEventListener('submit', () => {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing & Parsing Spreadsheet...
                    `;
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            });
        });
    </script>
@endsection