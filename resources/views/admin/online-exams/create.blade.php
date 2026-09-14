@extends('layouts.app')

@section('page_title', 'Create Online Examination')

@section('content')
<div class="space-y-6 pb-16 max-w-5xl mx-auto" x-data="{ speedBonusEnabled: false }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.online-exams.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                &larr; Back to Examinations
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-white mt-1">
                Create Online Examination
            </h1>
            <p class="text-sm text-slate-400 mt-0.5">
                Define examination parameters, schedule, anti-cheating signals, and capacity limits.
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

    <form method="POST" action="{{ route('admin.online-exams.store') }}" class="space-y-8">
        @csrf

        <!-- 1. Basic Exam Info -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
            <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Basic Examination Details
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Exam Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. YES GENIUS ONLINE COMPETITION - RAINBOW 3"
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Exam Code *</label>
                    <input type="text" name="code" value="{{ old('code', $defaultCode) }}" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Category *</label>
                    <select name="category_id" required class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="">-- Select ERMS Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Description / Notes</label>
                    <textarea name="description" rows="2" 
                              placeholder="Brief instructions or summary..."
                              class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">{{ old('description') }}</textarea>
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
                    <input type="date" name="exam_date" value="{{ old('exam_date', now()->format('Y-m-d')) }}" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Window Start Time *</label>
                    <input type="time" name="start_time" value="{{ old('start_time', '10:00') }}" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Window End Time *</label>
                    <input type="time" name="end_time" value="{{ old('end_time', '18:00') }}" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Student Duration (Minutes) *</label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 45) }}" min="1" max="360" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <span class="text-[11px] text-slate-500">Total allowed test time per student</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Question Time Limit (Seconds) *</label>
                    <input type="number" name="default_question_time_limit" value="{{ old('default_question_time_limit', 60) }}" min="5" max="600" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <span class="text-[11px] text-slate-500">Timer per question countdown</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Max Capacity (Students) *</label>
                    <input type="number" name="max_eligible_students" value="{{ old('max_eligible_students', 199) }}" min="1" max="199" required readonly
                           class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-amber-400 font-bold focus:outline-none cursor-not-allowed">
                    <span class="text-[11px] text-amber-400">Strictly locked at max 199 per exam</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Total Exam Marks *</label>
                    <input type="number" step="0.5" name="total_marks" value="{{ old('total_marks', 50.00) }}" min="1" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Passing Marks *</label>
                    <input type="number" step="0.5" name="pass_marks" value="{{ old('pass_marks', 20.00) }}" min="0" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>
        </div>

        <!-- 3. Security, Camera, Fullscreen & Anti-Cheating -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
            <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Anti-Cheating & Browser Controls
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Camera Check -->
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="enable_camera" name="enable_camera" value="1" {{ old('enable_camera', '1') == '1' ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="enable_camera" class="text-sm font-medium text-white cursor-pointer">Enable Camera Requirement</label>
                        <p class="text-xs text-slate-400 mt-0.5">Requires WebRTC camera access prior to starting. Detects and logs camera disconnection/stopping events.</p>
                    </div>
                </div>

                <!-- Fullscreen Check -->
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="enable_fullscreen" name="enable_fullscreen" value="1" {{ old('enable_fullscreen', '1') == '1' ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="enable_fullscreen" class="text-sm font-medium text-white cursor-pointer">Enforce Fullscreen Mode</label>
                        <p class="text-xs text-slate-400 mt-0.5">Detects fullscreen exits, tab switches, and window blurs. Warns student and tracks violation count.</p>
                    </div>
                </div>

                <!-- Max Violations -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Max Fullscreen Violations Before Auto-Termination *</label>
                    <input type="number" name="max_fullscreen_violations" value="{{ old('max_fullscreen_violations', 3) }}" min="1" max="10" required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <span class="text-[11px] text-slate-500">After this limit, session is terminated and answers evaluated.</span>
                </div>

                <!-- Allow Previous Question -->
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="allow_previous_question" name="allow_previous_question" value="1" {{ old('allow_previous_question') == '1' ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="allow_previous_question" class="text-sm font-medium text-white cursor-pointer">Allow Previous Question</label>
                        <p class="text-xs text-slate-400 mt-0.5">If disabled (recommended for competitive exams), students cannot return to submitted questions.</p>
                    </div>
                </div>

                <!-- Randomize Questions -->
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="randomize_questions" name="randomize_questions" value="1" {{ old('randomize_questions', '1') == '1' ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="randomize_questions" class="text-sm font-medium text-white cursor-pointer">Randomize Question Sequence</label>
                        <p class="text-xs text-slate-400 mt-0.5">Each student receives a unique randomized question order, persisted in their session.</p>
                    </div>
                </div>

                <!-- Randomize Options -->
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="randomize_options" name="randomize_options" value="1" {{ old('randomize_options', '1') == '1' ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="randomize_options" class="text-sm font-medium text-white cursor-pointer">Randomize Option Choices</label>
                        <p class="text-xs text-slate-400 mt-0.5">Randomizes the A, B, C, D choices to minimize screen copying.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Result & Feedback Visibility -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
            <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                Result & Feedback Visibility
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="show_result_immediately" name="show_result_immediately" value="1" {{ old('show_result_immediately', '1') == '1' ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="show_result_immediately" class="text-sm font-medium text-white cursor-pointer">Show Result Immediately</label>
                        <p class="text-xs text-slate-400 mt-0.5">Display marks upon student exam submission.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="show_correct_answers" name="show_correct_answers" value="1" {{ old('show_correct_answers') == '1' ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="show_correct_answers" class="text-sm font-medium text-white cursor-pointer">Show Correct Answer Explanations</label>
                        <p class="text-xs text-slate-400 mt-0.5">Show which options were correct on the completed screen.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="show_question_marks" name="show_question_marks" value="1" {{ old('show_question_marks', '1') == '1' ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="show_question_marks" class="text-sm font-medium text-white cursor-pointer">Show Question-wise Marks</label>
                        <p class="text-xs text-slate-400 mt-0.5">Display marks awarded per question during examination.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                    <input type="checkbox" id="show_rank" name="show_rank" value="1" {{ old('show_rank') == '1' ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="show_rank" class="text-sm font-medium text-white cursor-pointer">Show Overall Rank</label>
                        <p class="text-xs text-slate-400 mt-0.5">Display student's rank relative to category peers.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Speed Bonus (Optional V1) -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                <h2 class="text-base font-semibold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                    Speed Bonus Engine (Optional)
                </h2>
                <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="enable_speed_bonus" value="1" x-model="speedBonusEnabled"
                           class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    Enable Speed Bonus
                </label>
            </div>

            <div x-show="speedBonusEnabled" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Formula</label>
                    <select name="speed_bonus_formula" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2 text-sm text-white">
                        <option value="linear">Linear Degradation</option>
                        <option value="tier">Tier-Based Slabs</option>
                        <option value="percentage">Percentage of Base Marks</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Max Bonus Per Question</label>
                    <input type="number" step="0.05" name="max_bonus_per_question" value="{{ old('max_bonus_per_question', 0.50) }}" min="0" max="5"
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2 text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Max Total Exam Bonus Cap</label>
                    <input type="number" step="0.5" name="max_total_bonus" value="{{ old('max_total_bonus', 10.00) }}" min="0" max="50"
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2 text-sm text-white">
                </div>
            </div>
        </div>

        <!-- 6. Instructions -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-3">
            <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                Student Examination Instructions
            </h2>
            <p class="text-xs text-slate-400">These instructions will be displayed on the student's pre-exam checklist screen.</p>

            <textarea name="instructions" rows="6" 
                      class="w-full bg-slate-950 border border-slate-700/80 rounded-xl p-4 text-sm text-white focus:outline-none focus:border-indigo-500 font-sans">{{ old('instructions', "1. Camera permission is strictly required to begin and take the examination.\n2. You must remain in Fullscreen mode. Exiting fullscreen will be recorded as a rule violation.\n3. Question timer is strictly enforced. Answers must be submitted before the countdown expires.\n4. You must click [Submit Answer] first, then explicitly click [Next Question] to advance.\n5. Only one active login is permitted per Registration Number.") }}</textarea>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.online-exams.index') }}" 
               class="px-5 py-2.5 rounded-xl text-sm font-medium bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-all duration-200">
                Create & Continue to Assign Questions &rarr;
            </button>
        </div>
    </form>
</div>
@endsection
