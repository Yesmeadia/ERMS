@extends('layouts.app')

@section('page_title', 'Edit Question')

@section('content')
    <div class="space-y-6 pb-16 w-full" x-data="questionEditForm()">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.online-questions.index') }}"
                    class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                    &larr; Back to Question Bank
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mt-1">
                    Edit Question
                </h1>
                <p class="text-sm text-slate-400 mt-0.5">
                    Update question content, choices, or media.
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

        <form method="POST" action="{{ route('admin.online-questions.update', $question) }}" enctype="multipart/form-data"
            class="space-y-6" novalidate @submit="if (!validateForm()) $event.preventDefault()">
            @csrf
            @method('PUT')

            <!-- Classification & Type -->
            <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
                <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3">Classification & Type</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="lg:col-span-2">
                        <label
                            class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Category</label>
                        <select name="category_id"
                            class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="">General / All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $question->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }} ({{ $cat->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Question
                            Type *</label>
                        <select name="question_type" x-model="questionType" required
                            class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="MCQ">Multiple Choice (Single Answer)</option>
                            <option value="TRUE_FALSE">True / False</option>
                            <option value="MULTIPLE_SELECT">Multiple Select (Multi Correct)</option>
                            <option value="FILL_IN_BLANK">Fill in the Blank</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Difficulty
                            *</label>
                        <select name="difficulty" required
                            class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="EASY" {{ old('difficulty', $question->difficulty) === 'EASY' ? 'selected' : '' }}>
                                Easy</option>
                            <option value="MEDIUM" {{ old('difficulty', $question->difficulty) === 'MEDIUM' ? 'selected' : '' }}>Medium</option>
                            <option value="HARD" {{ old('difficulty', $question->difficulty) === 'HARD' ? 'selected' : '' }}>
                                Hard</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject', $question->subject) }}"
                            class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Topic</label>
                        <input type="text" name="topic" value="{{ old('topic', $question->topic) }}"
                            class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Marks
                            *</label>
                        <input type="number" step="0.5" name="default_marks"
                            value="{{ old('default_marks', $question->default_marks) }}" min="0.5" max="50" required
                            class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Negative
                            Marks</label>
                        <input type="number" step="0.25" name="negative_marks"
                            value="{{ old('negative_marks', $question->negative_marks) }}" min="0" max="10"
                            class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Question Timer (Seconds)</label>
                        <input type="number" name="time_limit_seconds" value="{{ old('time_limit_seconds', $question->time_limit_seconds) }}"
                            min="5" max="600" placeholder="e.g. 50 (optional)"
                            class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <span class="text-[11px] text-slate-500">Individual timer (5-600s). Overrides exam default.</span>
                    </div>
                </div>
            </div>

            <!-- Question Text & Media -->
            <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-4">
                <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3">Question Text & Media</h2>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Question Text
                        *</label>
                    <textarea name="question_text" rows="5" required
                        class="w-full bg-slate-950 border border-slate-700/80 rounded-xl p-4 text-sm text-white focus:outline-none focus:border-indigo-500 font-mono">{{ old('question_text', $question->question_text) }}</textarea>
                </div>

                <!-- Existing and Upload Image -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Attach / Replace
                        Image (Optional)</label>

                    @if($question->images->count() > 0)
                        <div class="mb-4 p-3 bg-slate-950 rounded-xl border border-slate-800 inline-block shadow-sm">
                            <div class="flex items-center justify-between gap-4 mb-2 pb-1 border-b border-slate-800/80">
                                <span class="text-xs font-semibold text-slate-300">Current Attached Image:</span>
                                <label
                                    class="inline-flex items-center gap-1.5 text-xs text-rose-400 hover:text-rose-300 cursor-pointer">
                                    <input type="checkbox" name="remove_image" value="1"
                                        class="rounded border-slate-700 text-rose-600 focus:ring-rose-500">
                                    <span>Delete this image</span>
                                </label>
                            </div>
                            <a href="{{ $question->images->first()->url }}" target="_blank" title="Click to view full size">
                                <img src="{{ $question->images->first()->url }}" alt="Current Image"
                                    class="max-h-56 max-w-md object-contain rounded-lg border border-slate-800 bg-slate-900/50 p-1 hover:opacity-90 transition-opacity">
                            </a>
                            <p class="text-[10px] text-slate-500 mt-1 font-mono">{{ $question->images->first()->file_name }}</p>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <input type="file" name="question_image" id="question_image"
                            accept="image/jpeg,image/png,image/webp" @change="handleImage($event)"
                            class="block w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 cursor-pointer">
                        <button type="button" x-show="imagePreview" @click="clearImage()" x-cloak
                            class="px-3 py-2 text-xs font-semibold text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 rounded-xl transition-colors whitespace-nowrap">
                            Cancel New Image
                        </button>
                    </div>
                    <span class="text-[11px] text-slate-500 mt-1 block">Supported formats: JPG, JPEG, PNG, WEBP (Max: 3MB).
                        Stored securely.</span>

                    <!-- New Image Preview -->
                    <div x-show="imagePreview" x-cloak
                        class="mt-3 p-3 bg-slate-950 rounded-xl border border-slate-800 inline-block shadow-lg">
                        <div class="flex items-center justify-between gap-4 mb-2 pb-1 border-b border-slate-800/80">
                            <span class="text-xs font-semibold text-emerald-400">New Image Preview (Will replace
                                current):</span>
                            <span class="text-[10px] text-slate-500 font-mono" x-text="imageFileName"></span>
                        </div>
                        <img :src="imagePreview || ''" alt="Preview"
                            class="max-h-56 max-w-md object-contain rounded-lg border border-slate-800 bg-slate-900/50 p-1">
                    </div>
                </div>
            </div>

            <!-- Options Section -->
            <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                    <h2 class="text-base font-semibold text-white">Answer Options</h2>
                </div>

                <!-- True / False Template -->
                <div x-show="questionType === 'TRUE_FALSE'" class="space-y-3 py-2">
                    @php
                        $isTrueCorrect = $question->options->where('option_text', 'True')->first()?->is_correct ?? true;
                    @endphp
                    <div class="flex items-center gap-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="true_false_correct" value="True" {{ $isTrueCorrect ? 'checked' : '' }}
                                :disabled="questionType !== 'TRUE_FALSE'"
                                class="w-4 h-4 text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500">
                            <span class="text-sm text-white font-medium">True</span>
                        </label>

                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="true_false_correct" value="False" {{ !$isTrueCorrect ? 'checked' : '' }} :disabled="questionType !== 'TRUE_FALSE'"
                                class="w-4 h-4 text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500">
                            <span class="text-sm text-white font-medium">False</span>
                        </label>
                    </div>
                </div>

                <!-- Fill in the blank template -->
                <div x-show="questionType === 'FILL_IN_BLANK'" class="space-y-2 py-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Correct Text Answer (Case-Insensitive):</label>
                    <input type="text" name="fill_blank_answer" placeholder="e.g. Paris (or Paris | City of Light)"
                        :disabled="questionType !== 'FILL_IN_BLANK'"
                        value="{{ old('fill_blank_answer', $question->options->first()?->option_text ?? '') }}"
                        class="w-full max-w-md bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <p class="text-[11px] text-slate-400">Student answers will be evaluated without checking case sensitivity (e.g., "paris", "Paris", and "PARIS" will all be accepted). Separate multiple acceptable alternatives with a pipe (|) or comma.</p>
                </div>

                <!-- MCQ / Multiple Select Options -->
                <div x-show="questionType === 'MCQ' || questionType === 'MULTIPLE_SELECT'" class="space-y-3">
                    <template x-for="(option, index) in options" :key="index">
                        <div class="flex items-center gap-3">
                            <div class="shrink-0">
                                <!-- Radio for MCQ -->
                                <input x-show="questionType === 'MCQ'" :disabled="questionType !== 'MCQ'" type="radio"
                                    name="correct_option" :value="index" :checked="option.is_correct"
                                    class="w-4 h-4 text-indigo-600 bg-slate-950 border-slate-700 focus:ring-indigo-500 cursor-pointer">

                                <!-- Checkbox for MULTIPLE_SELECT -->
                                <input x-show="questionType === 'MULTIPLE_SELECT'"
                                    :disabled="questionType !== 'MULTIPLE_SELECT'" type="checkbox"
                                    :name="'options[' + index + '][is_correct]'" value="1" :checked="option.is_correct"
                                    class="w-4 h-4 text-indigo-600 bg-slate-950 border-slate-700 rounded focus:ring-indigo-500 cursor-pointer">
                            </div>

                            <span
                                class="w-7 h-7 rounded-lg bg-slate-800 text-xs font-bold text-slate-300 flex items-center justify-center shrink-0"
                                x-text="String.fromCharCode(65 + index)"></span>

                            <input type="text" :name="'options[' + index + '][text]'" x-model="option.text"
                                :disabled="questionType !== 'MCQ' && questionType !== 'MULTIPLE_SELECT'"
                                class="flex-1 bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">

                            <button type="button" @click="removeOption(index)" x-show="options.length > 2"
                                class="p-2 text-slate-500 hover:text-rose-400 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </template>

                    <div class="pt-2">
                        <button type="button" @click="addOption()" x-show="options.length < 8"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-indigo-300 border border-slate-700 transition-colors">
                            + Add Option
                        </button>
                    </div>
                </div>
            </div>

            <!-- Explanation -->
            <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-3">
                <h2 class="text-base font-semibold text-white border-b border-slate-800/80 pb-3">Explanation</h2>
                <textarea name="explanation" rows="3"
                    class="w-full bg-slate-950 border border-slate-700/80 rounded-xl p-4 text-sm text-white focus:outline-none focus:border-indigo-500">{{ old('explanation', $question->explanation) }}</textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('admin.online-questions.index') }}"
                    class="px-5 py-2.5 rounded-xl text-sm font-medium bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-all">
                    Update Question
                </button>
            </div>
        </form>
    </div>

    <script @nonce>
        function questionEditForm() {
            return {
                questionType: '{{ old('question_type', $question->question_type->value) }}',
                options: {{ Js::from($question->options->map(fn($o) => ['id' => $o->id, 'text' => $o->option_text, 'is_correct' => (bool) $o->is_correct])) }},
                addOption() {
                    if (this.options.length < 8) {
                        this.options.push({ id: null, text: '', is_correct: false });
                    }
                },
                removeOption(index) {
                    if (this.options.length > 2) {
                        this.options.splice(index, 1);
                    }
                },
                imagePreview: null,
                imageFileName: '',
                handleImage(e) {
                    const input = e ? (e.target || e) : document.getElementById('question_image');
                    const file = input && input.files ? input.files[0] : null;
                    if (file) {
                        this.imageFileName = file.name;
                        this.imagePreview = URL.createObjectURL(file);
                    } else {
                        this.imageFileName = '';
                        this.imagePreview = null;
                    }
                },
                clearImage() {
                    this.imagePreview = null;
                    this.imageFileName = '';
                    const input = document.getElementById('question_image');
                    if (input) input.value = '';
                },
                validateForm() {
                    const qText = document.querySelector('textarea[name="question_text"]');
                    if (!qText || !qText.value.trim()) {
                        alert('Please enter the question text.');
                        if (qText) qText.focus();
                        return false;
                    }
                    if (this.questionType === 'MCQ' || this.questionType === 'MULTIPLE_SELECT') {
                        const filled = this.options.filter(o => o.text && o.text.trim().length > 0);
                        if (filled.length < 2) {
                            alert('Please provide at least 2 non-empty answer options for this question.');
                            return false;
                        }
                    } else if (this.questionType === 'FILL_IN_BLANK') {
                        const blank = document.querySelector('input[name="fill_blank_answer"]');
                        if (!blank || !blank.value.trim()) {
                            alert('Please provide the correct answer for the fill-in-the-blank question.');
                            if (blank) blank.focus();
                            return false;
                        }
                    }
                    return true;
                }
            };
        }
    </script>
@endsection