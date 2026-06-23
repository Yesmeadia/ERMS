@extends('layouts.app')

@section('page_title', 'Bulk Import Exam Results')

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('admin.results.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium flex items-center gap-1.5 mb-3 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            Back to Results Management
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-950/40 border border-rose-800/40 text-rose-200 text-sm">
            <h5 class="font-bold mb-2">Import Warnings / Errors:</h5>
            <ul class="list-disc pl-5 space-y-1 text-xs text-rose-300">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Import Form Card --}}
        <div class="lg:col-span-2 space-y-6">
            <form method="POST" action="{{ route('admin.results.import') }}" enctype="multipart/form-data" class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-5 shadow-xl">
                @csrf
                
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Select Examination Session</label>
                    <select name="examination_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50" required>
                        <option value="">-- Select Exam Session --</option>
                        @foreach($examinations as $exam)
                            <option value="{{ $exam->id }}" @selected(old('examination_id') == $exam->id)>{{ $exam->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Upload CSV File</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-800 border-dashed rounded-xl bg-slate-950/30 hover:bg-slate-950/50 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-600" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4-4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-slate-400">
                                <label for="csv_file" class="relative cursor-pointer bg-transparent rounded-md font-semibold text-indigo-400 hover:text-indigo-300 focus-within:outline-none">
                                    <span>Upload a file</span>
                                    <input id="csv_file" name="csv_file" type="file" accept=".csv" class="sr-only" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-slate-500">CSV file format only up to 2MB</p>
                        </div>
                    </div>
                    <span id="file-name-indicator" class="block mt-2 text-xs text-indigo-400 font-mono hidden"></span>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-600/10 cursor-pointer flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        Upload and Process Results
                    </button>
                </div>
            </form>
        </div>

        {{-- CSV specifications --}}
        <div class="space-y-6">
            <div class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">CSV File Guidelines</h4>
                
                <div class="space-y-3 text-xs text-slate-300 leading-relaxed">
                    <p>
                        The CSV file must contain a header row. Ensure you use the following columns (column headers are case-insensitive):
                    </p>
                    <ul class="list-disc pl-4 space-y-2 text-slate-400">
                        <li>
                            <strong class="text-slate-200">Registration Number</strong> or <strong class="text-slate-200">Hall Ticket Number</strong>: Used to find the registered candidate.
                        </li>
                        <li>
                            <strong class="text-slate-200">Marks Obtained</strong>: Integer score.
                        </li>
                        <li>
                            <strong class="text-slate-200">Max Marks</strong>: Maximum possible marks.
                        </li>
                        <li>
                            <strong class="text-slate-200">Grade</strong> (Optional): A+, A, B, etc. (Auto-calculated if blank).
                        </li>
                        <li>
                            <strong class="text-slate-200">Status</strong> (Optional): Pass, Fail, Absent, Withheld. (Auto-defaulted if blank).
                        </li>
                        <li>
                            <strong class="text-slate-200">Remarks</strong> (Optional): General performance comments.
                        </li>
                    </ul>
                </div>
            </div>

            <div class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Sample CSV Structure</h4>
                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 font-mono text-[10px] text-slate-400 overflow-x-auto whitespace-nowrap">
                    Registration Number,Marks Obtained,Max Marks,Remarks<br>
                    30051,410,500,Excellent performance<br>
                    30052,380,500,First class<br>
                    30053,120,500,Needs improvement
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('csv_file');
        const indicator = document.getElementById('file-name-indicator');

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const name = e.target.files[0].name;
                indicator.textContent = 'Selected: ' + name;
                indicator.classList.remove('hidden');
            } else {
                indicator.classList.add('hidden');
            }
        });
    });
</script>
@endsection
