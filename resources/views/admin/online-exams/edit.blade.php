@extends('layouts.app')

@section('page_title', 'Edit Online Examination')

@section('content')
<div class="space-y-6 pb-16 max-w-5xl mx-auto" x-data="{ speedBonusEnabled: {{ $exam->enable_speed_bonus ? 'true' : 'false' }} }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.online-exams.show', $exam) }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                &larr; Back to Exam Details
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-white mt-1">
                Edit Examination: {{ $exam->name }}
            </h1>
            <p class="text-sm text-slate-400 mt-0.5">
                Update parameters, schedule, anti-cheating signals, and capacity limits.
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm">
            <p class="font-semibold mb-1">Please correct the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.online-exams.update', $exam) }}" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- 1. Basic Exam Info -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
            <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Basic Examination Details
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Exam Name *</label>
                    <input type="text" name="name" value="{{ old('name', $exam->name) }}" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Exam Code *</label>
                    <input type="text" name="code" value="{{ old('code', $exam->code) }}" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Category *</label>
                    <select name="category_id" required class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $exam->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Description / Notes</label>
                    <textarea name="description" rows="2" 
                              class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">{{ old('description', $exam->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 2. Schedule & Timing Rules -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
            <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Schedule & Timing Enforcements
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Exam Date *</label>
                    <input type="date" name="exam_date" value="{{ old('exam_date', $exam->exam_date->format('Y-m-d')) }}" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Window Start Time *</label>
                    <input type="time" name="start_time" value="{{ old('start_time', substr($exam->start_time, 0, 5)) }}" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Window End Time *</label>
                    <input type="time" name="end_time" value="{{ old('end_time', substr($exam->end_time, 0, 5)) }}" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Student Duration (Minutes) *</label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="1" max="360" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Question Time Limit (Seconds) *</label>
                    <input type="number" name="default_question_time_limit" value="{{ old('default_question_time_limit', $exam->default_question_time_limit) }}" min="5" max="600" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Max Capacity (Students) *</label>
                    <input type="number" name="max_eligible_students" value="199" min="1" max="199" required readonly
                           class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-amber-400 font-bold focus:outline-none cursor-not-allowed">
                    <span class="text-[11px] text-amber-400">Strictly locked at max 199</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Total Exam Marks *</label>
                    <input type="number" step="0.5" name="total_marks" value="{{ old('total_marks', $exam->total_marks) }}" min="1" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Passing Marks *</label>
                    <input type="number" step="0.5" name="pass_marks" value="{{ old('pass_marks', $exam->pass_marks) }}" min="0" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>
        </div>

        <!-- 3. Security & Anti-Cheating -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
            <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Anti-Cheating & Browser Controls
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="enable_camera" name="enable_camera" value="1" {{ old('enable_camera', $exam->enable_camera) ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="enable_camera" class="text-sm font-medium text-white cursor-pointer">Enable Camera Requirement</label>
                        <p class="text-xs text-slate-400 mt-0.5">Detects camera stream active, stopped, or interrupted.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="enable_fullscreen" name="enable_fullscreen" value="1" {{ old('enable_fullscreen', $exam->enable_fullscreen) ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="enable_fullscreen" class="text-sm font-medium text-white cursor-pointer">Enforce Fullscreen Mode</label>
                        <p class="text-xs text-slate-400 mt-0.5">Detects fullscreen exits, tab switches, and blur.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Max Fullscreen Violations *</label>
                    <input type="number" name="max_fullscreen_violations" value="{{ old('max_fullscreen_violations', $exam->max_fullscreen_violations) }}" min="1" max="10" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="allow_previous_question" name="allow_previous_question" value="1" {{ old('allow_previous_question', $exam->allow_previous_question) ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="allow_previous_question" class="text-sm font-medium text-white cursor-pointer">Allow Previous Question</label>
                        <p class="text-xs text-slate-400 mt-0.5">If disabled, students cannot return to previous questions.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="randomize_questions" name="randomize_questions" value="1" {{ old('randomize_questions', $exam->randomize_questions) ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="randomize_questions" class="text-sm font-medium text-white cursor-pointer">Randomize Questions</label>
                        <p class="text-xs text-slate-400 mt-0.5">Randomize sequence per student attempt.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="randomize_options" name="randomize_options" value="1" {{ old('randomize_options', $exam->randomize_options) ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="randomize_options" class="text-sm font-medium text-white cursor-pointer">Randomize Options</label>
                        <p class="text-xs text-slate-400 mt-0.5">Randomize option choices.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Result Visibility -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
            <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                Result & Feedback Visibility
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="show_result_immediately" name="show_result_immediately" value="1" {{ old('show_result_immediately', $exam->show_result_immediately) ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="show_result_immediately" class="text-sm font-medium text-white cursor-pointer">Show Result Immediately</label>
                        <p class="text-xs text-slate-400 mt-0.5">Display marks upon student exam submission.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="show_correct_answers" name="show_correct_answers" value="1" {{ old('show_correct_answers', $exam->show_correct_answers) ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="show_correct_answers" class="text-sm font-medium text-white cursor-pointer">Show Correct Answer Explanations</label>
                        <p class="text-xs text-slate-400 mt-0.5">Show which options were correct on completed screen.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="show_question_marks" name="show_question_marks" value="1" {{ old('show_question_marks', $exam->show_question_marks) ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="show_question_marks" class="text-sm font-medium text-white cursor-pointer">Show Question-wise Marks</label>
                        <p class="text-xs text-slate-400 mt-0.5">Display marks awarded per question.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="show_rank" name="show_rank" value="1" {{ old('show_rank', $exam->show_rank) ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="show_rank" class="text-sm font-medium text-white cursor-pointer">Show Overall Rank</label>
                        <p class="text-xs text-slate-400 mt-0.5">Display student's rank relative to category peers.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Instructions -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-3">
            <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                Student Instructions
            </h2>
            <textarea name="instructions" rows="6" 
                      class="w-full bg-slate-950 border border-slate-700/80 rounded-xl p-4 text-sm text-white focus:outline-none focus:border-indigo-500 font-sans">{{ old('instructions', $exam->instructions) }}</textarea>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.online-exams.show', $exam) }}" 
               class="px-5 py-2.5 rounded-xl text-sm font-medium bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-all duration-200">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
