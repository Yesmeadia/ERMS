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
    <div class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 mb-6 flex flex-col sm:flex-row gap-4 items-center">
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

        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-5">
            <h4 class="text-sm font-semibold text-slate-200 border-b border-slate-800/60 pb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Result Score & Status
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Marks Obtained</label>
                    <input type="number" name="marks_obtained" id="marks_obtained" min="0" placeholder="e.g. 350" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Maximum Marks</label>
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
                    <input type="text" name="grade" placeholder="e.g. A+ (Leave blank to auto-calculate)" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Remarks / Comments (Optional)</label>
                <textarea name="remarks" rows="2" placeholder="Enter general comments about candidate performance..." class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50"></textarea>
            </div>
        </div>

        {{-- Subject-wise Details --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 space-y-5">
            <div class="border-b border-slate-800/60 pb-3 flex items-center justify-between">
                <h4 class="text-sm font-semibold text-slate-200 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-indigo-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 0A48.536 48.536 0 0112 3m0 0c2.917 0 5.747.294 8.5.862" />
                    </svg>
                    Subject-wise Scores (Optional)
                </h4>
                <button type="button" onclick="addSubjectRow()" class="px-3 py-1.5 rounded-lg border border-indigo-500/30 hover:border-indigo-500/60 hover:bg-indigo-500/10 text-indigo-400 text-xs font-bold transition-all duration-200 flex items-center gap-1 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Subject
                </button>
            </div>

            <div id="subject-container" class="space-y-3">
                {{-- Dynamic rows will be inserted here --}}
            </div>

            <p class="text-[11px] text-slate-500 italic">
                Note: Subject-wise marks are optional. If added, they will show on the candidate's marksheet certificate.
            </p>
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

<script>
    function addSubjectRow(name = '', marks = '', max = '100') {
        const container = document.getElementById('subject-container');
        const rowId = 'row-' + Date.now() + Math.random().toString(36).substr(2, 5);
        
        const rowHTML = `
            <div id="${rowId}" class="flex flex-col sm:flex-row gap-3 items-center bg-slate-800/30 border border-slate-850 p-3 rounded-xl">
                <div class="flex-1 w-full">
                    <label class="block text-slate-500 text-[10px] mb-1 font-semibold">Subject Name</label>
                    <input type="text" name="subject_names[]" value="${name}" placeholder="e.g. Mathematics" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50" required>
                </div>
                <div class="w-full sm:w-28">
                    <label class="block text-slate-500 text-[10px] mb-1 font-semibold">Marks Obtained</label>
                    <input type="number" name="subject_marks[]" value="${marks}" placeholder="85" min="0" class="subject-obtained-input w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50" required>
                </div>
                <div class="w-full sm:w-28">
                    <label class="block text-slate-500 text-[10px] mb-1 font-semibold">Max Marks</label>
                    <input type="number" name="subject_max[]" value="${max}" placeholder="100" min="1" class="subject-max-input w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50" required>
                </div>
                <div class="self-end pb-1 w-full sm:w-auto flex justify-end">
                    <button type="button" onclick="document.getElementById('${rowId}').remove(); calculateTotals();" class="p-2 rounded-lg bg-slate-900 hover:bg-rose-950/40 text-rose-400 hover:text-rose-300 transition-colors border border-rose-950/20 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', rowHTML);

        // Attach event listeners for calculations
        const row = document.getElementById(rowId);
        row.querySelector('.subject-obtained-input').addEventListener('input', calculateTotals);
        row.querySelector('.subject-max-input').addEventListener('input', calculateTotals);
    }

    // Auto calculate sum of marks
    function calculateTotals() {
        const obtainedInputs = document.querySelectorAll('.subject-obtained-input');
        const maxInputs = document.querySelectorAll('.subject-max-input');
        
        let totalObtained = 0;
        let totalMax = 0;

        obtainedInputs.forEach(input => {
            if (input.value) totalObtained += parseInt(input.value);
        });

        maxInputs.forEach(input => {
            if (input.value) totalMax += parseInt(input.value);
        });

        // Set aggregate values if there are subjects
        if (obtainedInputs.length > 0) {
            document.getElementById('marks_obtained').value = totalObtained;
            document.getElementById('max_marks').value = totalMax;
            
            // Auto update Pass/Fail status
            const pct = (totalObtained / totalMax) * 100;
            const statusDropdown = document.getElementById('status');
            if (pct >= 35) {
                statusDropdown.value = 'Pass';
            } else {
                statusDropdown.value = 'Fail';
            }
        }
    }

    // Pre-add 3 defaults
    document.addEventListener('DOMContentLoaded', () => {
        addSubjectRow('English', '', '100');
        addSubjectRow('Mathematics', '', '100');
        addSubjectRow('Science', '', '100');
    });
</script>
@endsection
