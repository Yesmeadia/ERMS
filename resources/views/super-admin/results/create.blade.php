@extends('layouts.app')

@section('page_title', 'Enter Exam Result')

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

    {{-- Candidate Info Card --}}
    <div class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 mb-6 flex flex-col sm:flex-row gap-4 items-center shadow-lg">
        <div class="w-16 h-16 rounded-xl overflow-hidden border border-slate-800 bg-slate-950 shrink-0">
            <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
        </div>
        <div class="text-center sm:text-left">
            <h3 class="text-lg font-bold text-white leading-snug">{{ $student->name }}</h3>
            <p class="text-xs text-slate-400 mt-1">
                HT Number: <span class="font-mono text-slate-200 font-bold">{{ $student->hall_ticket_number ?? 'N/A' }}</span> | 
                Reg Number: <span class="font-mono text-slate-200 font-bold">{{ $student->registration_number }}</span>
            </p>
            <p class="text-xs text-slate-500 mt-0.5">
                School: {{ $student->school->name }} | Class: {{ $student->class->name }} | Category: {{ $student->category->name }}
            </p>
        </div>
    </div>

    {{-- Result Form --}}
    <form method="POST" action="{{ route('admin.results.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="student_id" value="{{ $student->id }}">

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-5 shadow-xl">
            <h4 class="text-sm font-semibold text-slate-200 border-b border-slate-800/60 pb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Result Score & Status
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Marks Obtained <span class="text-rose-400">*</span></label>
                    <input type="number" name="marks_obtained" id="marks_obtained" min="0" placeholder="e.g. 350" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Maximum Marks <span class="text-rose-400">*</span></label>
                    <input type="number" name="max_marks" id="max_marks" min="1" placeholder="e.g. 500" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Result Status</label>
                    <select name="status" id="status" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                        <option value="Pass">Pass</option>
                        <option value="Fail">Fail</option>
                        <option value="Absent">Absent</option>
                        <option value="Withheld">Withheld</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Grade (Optional)</label>
                    <input type="text" name="grade" id="grade" placeholder="e.g. A+ (Leave blank to auto-calculate)" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Remarks / Comments (Optional)</label>
                <textarea name="remarks" id="remarks" rows="2" placeholder="e.g. Qualified For Second Round Examination" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50"></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.results.index') }}" class="px-6 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl transition-all duration-200">
                Cancel
            </a>
            <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-600/10 cursor-pointer">
                Save Result
            </button>
        </div>
    </form>
</div>

<script @nonce>
document.addEventListener('DOMContentLoaded', function() {
    var statusSelect = document.getElementById('status');
    var remarksInput = document.getElementById('remarks');
    var defaultPass = 'Qualified For Second Round Examination';
    var defaultFail = 'Not Qualified For Second Round Examination';

    function updateRemarks() {
        if (!remarksInput.value || remarksInput.value === defaultPass || remarksInput.value === defaultFail) {
            if (statusSelect.value === 'Pass') {
                remarksInput.value = defaultPass;
            } else if (statusSelect.value === 'Fail') {
                remarksInput.value = defaultFail;
            } else {
                remarksInput.value = '';
            }
        }
    }

    statusSelect.addEventListener('change', updateRemarks);
    if (!remarksInput.value) {
        updateRemarks();
    }
});
</script>
@endsection
