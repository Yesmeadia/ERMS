@extends('layouts.app')

@section('page_title', 'Question Bank')

@section('content')
    <div class="space-y-6 pb-12">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.online-exams.dashboard') }}"
                        class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                        &larr; Back to Dashboard
                    </a>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mt-1">
                    Question Bank
                </h1>
                <p class="text-sm text-slate-400 mt-0.5">
                    Create and curate reusable questions with images, formulas, and multiple choice options.
                </p>
            </div>

            <a href="{{ route('admin.online-questions.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add New Question
            </a>
        </div>

        <!-- Filters -->
        <form method="GET" action="{{ route('admin.online-questions.index') }}"
            class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-4 shadow-sm flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search question text, subject, or topic..."
                    class="w-full bg-slate-950/80 border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </div>

            <div class="w-44">
                <select name="category_id"
                    class="w-full bg-slate-950/80 border border-slate-700/80 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-44">
                <select name="question_type"
                    class="w-full bg-slate-950/80 border border-slate-700/80 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">All Question Types</option>
                    <option value="MCQ" {{ request('question_type') === 'MCQ' ? 'selected' : '' }}>MCQ (Single)</option>
                    <option value="TRUE_FALSE" {{ request('question_type') === 'TRUE_FALSE' ? 'selected' : '' }}>True / False
                    </option>
                    <option value="MULTIPLE_SELECT" {{ request('question_type') === 'MULTIPLE_SELECT' ? 'selected' : '' }}>
                        Multiple Select</option>
                    <option value="FILL_IN_BLANK" {{ request('question_type') === 'FILL_IN_BLANK' ? 'selected' : '' }}>Fill in
                        Blank</option>
                </select>
            </div>

            <div class="w-36">
                <select name="difficulty"
                    class="w-full bg-slate-950/80 border border-slate-700/80 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">All Difficulties</option>
                    <option value="EASY" {{ request('difficulty') === 'EASY' ? 'selected' : '' }}>Easy</option>
                    <option value="MEDIUM" {{ request('difficulty') === 'MEDIUM' ? 'selected' : '' }}>Medium</option>
                    <option value="HARD" {{ request('difficulty') === 'HARD' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>

            <button type="submit"
                class="px-4 py-2 rounded-xl text-sm font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors">
                Filter
            </button>

            @if(request()->hasAny(['search', 'category_id', 'question_type', 'difficulty']))
                <a href="{{ route('admin.online-questions.index') }}"
                    class="px-3 py-2 text-xs text-slate-400 hover:text-slate-200">
                    Reset
                </a>
            @endif
        </form>

        <!-- Questions Table -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-950/60 text-xs uppercase text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Question Details</th>
                            <th class="px-5 py-3.5 font-semibold">Type</th>
                            <th class="px-5 py-3.5 font-semibold">Category</th>
                            <th class="px-5 py-3.5 font-semibold">Marks</th>
                            <th class="px-5 py-3.5 font-semibold">Options / Answer</th>
                            <th class="px-5 py-3.5 font-semibold">Used in</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($questions as $q)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-4 max-w-md">
                                    <div class="font-medium text-white line-clamp-2">
                                        {!! strip_tags($q->question_text) !!}
                                    </div>
                                    <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                                        @if($q->subject) <span>{{ $q->subject }}</span> &bull; @endif
                                        @if($q->topic) <span>{{ $q->topic }}</span> &bull; @endif
                                        <span class="capitalize">{{ strtolower($q->difficulty) }}</span>
                                        @if($q->images->count() > 0)
                                            <a href="{{ $q->images->first()->url }}" target="_blank"
                                                class="text-indigo-400 hover:text-indigo-300 inline-flex items-center gap-1 font-medium transition-colors"
                                                title="Click to view attached diagram">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                View Image
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $q->question_type->shortLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-300">
                                    {{ $q->category ? $q->category->name : 'All Categories' }}
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    <span class="text-white font-medium">+{{ $q->default_marks }}</span>
                                    @if($q->negative_marks > 0)
                                        <span class="text-rose-400 block">-{{ $q->negative_marks }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    @if($q->question_type === \App\Enums\QuestionType::FILL_IN_BLANK)
                                        <span class="text-slate-400 block">Blank Answer:</span>
                                        <span class="text-emerald-400 font-mono font-medium block truncate max-w-[150px]" title="{{ $q->options->first()?->option_text }}">
                                            {{ $q->options->first()?->option_text ?? 'N/A' }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">{{ $q->options->count() }} options</span>
                                        <span class="text-emerald-400 block">{{ $q->correctOptions->count() }} correct</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-400">
                                    {{ $q->exams_count }} {{ Str::plural('exam', $q->exams_count) }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.online-questions.edit', $q) }}"
                                            class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('admin.online-questions.destroy', $q) }}"
                                            onsubmit="return confirm('Delete this question from question bank?');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-500">
                                    No questions found in Question Bank.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($questions->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $questions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection