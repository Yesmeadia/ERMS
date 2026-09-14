@extends('layouts.app')

@section('page_title', 'Assign Questions - ' . $exam->name)

@section('content')
<div class="space-y-6 pb-16">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.online-exams.show', $exam) }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                    &larr; Back to {{ $exam->name }}
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white mt-1">
                Assign Exam Questions
            </h1>
            <p class="text-sm text-slate-400 mt-0.5">
                Category: <span class="text-indigo-300 font-medium">{{ $exam->category->name }}</span> | 
                Currently Assigned: <strong class="text-white">{{ $exam->examQuestions->count() }}</strong> questions 
                (Total Marks: <strong class="text-emerald-400">{{ $exam->total_marks }}</strong>)
            </p>
        </div>

        @if($exam->status === \App\Enums\ExamStatus::DRAFT)
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.online-questions.create') }}" target="_blank"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors">
                    + Create New Question
                </a>
            </div>
        @else
            <div class="px-3.5 py-2 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-medium">
                Questions are frozen because this examination is {{ $exam->status->label() }}.
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- 1. Currently Assigned Questions Table -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-800/80 flex items-center justify-between">
            <h2 class="text-base font-semibold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Assigned Questions ({{ $exam->examQuestions->count() }})
            </h2>
        </div>

        @if($exam->examQuestions->count() > 0)
            <form method="POST" action="{{ route('admin.online-exams.questions.update-settings', $exam) }}">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-950/60 text-xs uppercase text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="px-4 py-3 font-semibold w-16">Order</th>
                                <th class="px-4 py-3 font-semibold">Question Content</th>
                                <th class="px-4 py-3 font-semibold">Type</th>
                                <th class="px-4 py-3 font-semibold w-24">Marks</th>
                                <th class="px-4 py-3 font-semibold w-24">Negative</th>
                                <th class="px-4 py-3 font-semibold w-28">Time Limit</th>
                                <th class="px-4 py-3 font-semibold text-right w-20">Remove</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach($exam->examQuestions as $index => $eq)
                                <tr class="hover:bg-slate-800/30 transition-colors">
                                    <td class="px-4 py-3">
                                        <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $eq->id }}">
                                        <input type="number" name="questions[{{ $index }}][sort_order]" value="{{ $eq->sort_order }}" min="1"
                                               {{ $exam->status !== \App\Enums\ExamStatus::DRAFT ? 'readonly' : '' }}
                                               class="w-14 bg-slate-950 border border-slate-700 rounded-lg px-2 py-1 text-xs text-white text-center font-bold">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-white font-medium line-clamp-2">
                                            {!! strip_tags($eq->question->question_text) !!}
                                        </div>
                                        @if($eq->question->images->count() > 0)
                                            <span class="text-[11px] text-indigo-400 mt-0.5 inline-block">&bull; Image attached</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs text-slate-400">{{ $eq->question->question_type->shortLabel() }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" step="0.5" name="questions[{{ $index }}][marks]" value="{{ $eq->marks }}" min="0.5"
                                               {{ $exam->status !== \App\Enums\ExamStatus::DRAFT ? 'readonly' : '' }}
                                               class="w-20 bg-slate-950 border border-slate-700 rounded-lg px-2 py-1 text-xs text-white">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" step="0.25" name="questions[{{ $index }}][negative_marks]" value="{{ $eq->negative_marks }}" min="0"
                                               {{ $exam->status !== \App\Enums\ExamStatus::DRAFT ? 'readonly' : '' }}
                                               class="w-20 bg-slate-950 border border-slate-700 rounded-lg px-2 py-1 text-xs text-rose-300">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="questions[{{ $index }}][time_limit_seconds]" value="{{ $eq->time_limit_seconds }}" placeholder="{{ $exam->default_question_time_limit }}s" min="5"
                                               {{ $exam->status !== \App\Enums\ExamStatus::DRAFT ? 'readonly' : '' }}
                                               class="w-24 bg-slate-950 border border-slate-700 rounded-lg px-2 py-1 text-xs text-white">
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($exam->status === \App\Enums\ExamStatus::DRAFT)
                                            <button type="button" 
                                                    onclick="window.confirmDialog('Remove this question?').then(ok => { if(ok) document.getElementById('remove-q-{{ $eq->question_id }}').submit(); })"
                                                    class="p-1.5 text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-600">Locked</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($exam->status === \App\Enums\ExamStatus::DRAFT)
                    <div class="p-4 border-t border-slate-800 flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors">
                            Save Ordering & Marks
                        </button>
                    </div>
                @endif
            </form>

            @foreach($exam->examQuestions as $eq)
                <form id="remove-q-{{ $eq->question_id }}" method="POST" action="{{ route('admin.online-exams.questions.remove', [$exam, $eq->question_id]) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @else
            <div class="p-8 text-center text-slate-500">
                No questions assigned yet. Select from the Question Bank pool below to add questions.
            </div>
        @endif
    </div>

    <!-- 2. Available Questions Pool from Bank -->
    @if($exam->status === \App\Enums\ExamStatus::DRAFT)
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden shadow-sm" x-data="{ selectAll: false }">
            <div class="p-5 border-b border-slate-800/80 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Available Questions from Question Bank (Category: {{ $exam->category->name }})
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Select questions to add to this examination</p>
                </div>
            </div>

            @if($bankQuestions->count() > 0)
                <form method="POST" action="{{ route('admin.online-exams.questions.assign', $exam) }}">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-300">
                            <thead class="bg-slate-950/60 text-xs uppercase text-slate-400 border-b border-slate-800">
                                <tr>
                                    <th class="px-5 py-3.5 w-12 text-center">
                                        <input type="checkbox" x-model="selectAll" 
                                               @change="document.querySelectorAll('.bank-q-checkbox').forEach(c => c.checked = selectAll)"
                                               class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-5 py-3.5 font-semibold">Question Text</th>
                                    <th class="px-5 py-3.5 font-semibold">Type</th>
                                    <th class="px-5 py-3.5 font-semibold">Difficulty</th>
                                    <th class="px-5 py-3.5 font-semibold">Default Marks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                @foreach($bankQuestions as $bq)
                                    <tr class="hover:bg-slate-800/30 transition-colors">
                                        <td class="px-5 py-3.5 text-center">
                                            <input type="checkbox" name="question_ids[]" value="{{ $bq->id }}" class="bank-q-checkbox w-4 h-4 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="text-white font-medium line-clamp-2">{!! strip_tags($bq->question_text) !!}</div>
                                            <div class="text-xs text-slate-500 mt-0.5">
                                                @if($bq->subject) {{ $bq->subject }} &bull; @endif
                                                {{ $bq->options->count() }} options
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5 text-xs">
                                            <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 border border-slate-700">{{ $bq->question_type->shortLabel() }}</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-xs capitalize text-slate-400">
                                            {{ strtolower($bq->difficulty) }}
                                        </td>
                                        <td class="px-5 py-3.5 text-xs text-white font-medium">
                                            {{ $bq->default_marks }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t border-slate-800 flex items-center justify-between">
                        <div class="text-xs text-slate-400">
                            {{ $bankQuestions->links() }}
                        </div>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-all">
                            Add Selected Questions to Exam
                        </button>
                    </div>
                </form>
            @else
                <div class="p-8 text-center text-slate-500">
                    No remaining unassigned questions in the Question Bank for this category.
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
