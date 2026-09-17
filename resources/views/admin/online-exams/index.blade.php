@extends('layouts.app')

@section('page_title', 'Manage Online Examinations')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.online-exams.dashboard') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                    &larr; Back to Dashboard
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white mt-1">
                Online Examinations
            </h1>
            <p class="text-sm text-slate-400 mt-0.5">
                Configure, manage, publish, and monitor category-wise online examinations.
            </p>
        </div>

        <a href="{{ route('admin.online-exams.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Create New Exam
        </a>
    </div>

    <!-- Filters & Search -->
    <form method="GET" action="{{ route('admin.online-exams.index') }}" class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-4 shadow-sm flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search by exam name or code..." 
                   class="w-full bg-slate-950/80 border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors">
        </div>

        <div class="w-48">
            <select name="category_id" class="w-full bg-slate-950/80 border border-slate-700/80 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="w-40">
            <select name="status" class="w-full bg-slate-950/80 border border-slate-700/80 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                <option value="">All Statuses</option>
                <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                <option value="PUBLISHED" {{ request('status') === 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                <option value="RESULT_PUBLISHED" {{ request('status') === 'RESULT_PUBLISHED' ? 'selected' : '' }}>Results Published</option>
            </select>
        </div>

        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors">
            Filter
        </button>

        @if(request()->hasAny(['search', 'category_id', 'status']))
            <a href="{{ route('admin.online-exams.index') }}" class="px-3 py-2 text-xs text-slate-400 hover:text-slate-200">
                Reset
            </a>
        @endif
    </form>

    <!-- Examinations List -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/60 text-xs uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Exam & Code</th>
                        <th class="px-5 py-3.5 font-semibold">Category</th>
                        <th class="px-5 py-3.5 font-semibold">Date & Window</th>
                        <th class="px-5 py-3.5 font-semibold">Duration & Q-Time</th>
                        <th class="px-5 py-3.5 font-semibold">Enrolled / Cap</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($exams as $exam)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.online-exams.show', $exam) }}" class="font-semibold text-white hover:text-indigo-400 transition-colors">
                                    {{ $exam->name }}
                                </a>
                                <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $exam->code }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800/90 text-slate-200 border border-slate-700/60">
                                    {{ $exam->category->name }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <div class="font-medium text-slate-200">{{ $exam->exam_date->format('d M Y') }}</div>
                                <div class="text-slate-400">{{ $exam->start_time }} – {{ $exam->end_time }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <span class="text-white font-medium">{{ $exam->duration_minutes }} mins</span>
                                <div class="text-slate-500">{{ $exam->default_question_time_limit }}s / question</div>
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <span class="font-bold {{ $exam->exam_students_count >= 199 ? 'text-amber-400' : 'text-emerald-400' }}">
                                    {{ $exam->exam_students_count }}
                                </span>
                                <span class="text-slate-500">/ 199 max</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $exam->status->badgeClass() }}">
                                    {{ $exam->status->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.online-exams.preview', $exam) }}" target="_blank"
                                       class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-200 transition-colors"
                                       title="Preview Exam as Student">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>

                                    <a href="{{ route('admin.online-exams.show', $exam) }}" 
                                       class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700">
                                        Manage
                                    </a>

                                    <a href="{{ route('admin.online-exams.live', $exam) }}" 
                                       class="px-2.5 py-1 text-xs font-medium rounded-lg bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30">
                                        Live
                                    </a>

                                    @can('delete', $exam)
                                        <form method="POST" action="{{ route('admin.online-exams.destroy', $exam) }}" class="inline"
                                              onsubmit="return confirm('PERMANENT ACTION: Delete this exam \'{{ addslashes($exam->name) }}\'?\n\nAll attended students\' marks, events, answers, and video recordings will be permanently removed. This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 border border-rose-500/20 transition-colors"
                                                    title="Delete Examination & All Records">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-500">
                                No online examinations match your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($exams->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $exams->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
