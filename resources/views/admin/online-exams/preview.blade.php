<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $exam->name }} | YES INDIA ERMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script @nonce defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col antialiased" 
      x-data="{ 
        currentIdx: 0,
        questions: {{ Js::from($questions->map(fn($eq) => [
            'id' => $eq->question->id,
            'text' => $eq->question->question_text,
            'type' => $eq->question->question_type->value,
            'type_label' => $eq->question->question_type->shortLabel(),
            'marks' => $eq->marks,
            'negative' => $eq->negative_marks,
            'time_limit' => $eq->time_limit_seconds ?: $exam->default_question_time_limit,
            'options' => $eq->question->options->map(fn($o) => ['identifier' => $o->option_identifier, 'text' => $o->option_text, 'is_correct' => (bool) $o->is_correct])->values()->toArray(),
            'images' => $eq->question->images->map(fn($i) => ['url' => $i->url])->values()->toArray(),
        ])) }},
        selectedOption: null,
        selectedOptions: [],
        textAnswer: '',
        answerSubmitted: false,
        canSubmit() {
            if (!this.questions || !this.questions[this.currentIdx]) return false;
            const q = this.questions[this.currentIdx];
            if (q.type === 'FILL_IN_BLANK') {
                return this.textAnswer.trim().length > 0;
            } else if (q.type === 'MULTIPLE_SELECT') {
                return this.selectedOptions.length > 0;
            } else {
                return this.selectedOption !== null;
            }
        },
        toggleOption(id) {
            if (this.answerSubmitted) return;
            const idx = this.selectedOptions.indexOf(id);
            if (idx > -1) {
                this.selectedOptions.splice(idx, 1);
            } else {
                this.selectedOptions.push(id);
            }
        },
        submitAnswer() {
            if (!this.canSubmit()) return;
            this.answerSubmitted = true;
        },
        resetInputs() {
            this.selectedOption = null;
            this.selectedOptions = [];
            this.textAnswer = '';
            this.answerSubmitted = false;
        },
        nextQuestion() {
            if (this.currentIdx < this.questions.length - 1) {
                this.currentIdx++;
                this.resetInputs();
            }
        },
        prevQuestion() {
            if (this.currentIdx > 0) {
                this.currentIdx--;
                this.resetInputs();
            }
        },
        isFillBlankCorrect() {
            const q = this.questions[this.currentIdx];
            if (!q || q.type !== 'FILL_IN_BLANK') return false;
            const correctOpt = q.options.find(o => o.is_correct) || q.options[0];
            if (!correctOpt) return false;
            const actual = (this.textAnswer || '').trim().replace(/\s+/g, ' ').toLowerCase();
            const expectedRaw = (correctOpt.text || '').trim();
            if (!actual || !expectedRaw) return false;
            const alternatives = expectedRaw.split(/[,|;]/).map(s => s.trim().replace(/\s+/g, ' ').toLowerCase());
            alternatives.push(expectedRaw.trim().replace(/\s+/g, ' ').toLowerCase());
            return alternatives.includes(actual);
        }
      }">

    <!-- Preview Warning Banner -->
    <div class="bg-amber-500/20 border-b border-amber-500/30 px-4 py-2 text-center text-xs font-semibold text-amber-300">
        ADMIN PREVIEW MODE — This is a simulated preview. No examination session is recorded, and no answers are saved.
    </div>

    <!-- Header -->
    <header class="h-16 border-b border-slate-800 bg-slate-900/80 px-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            <span class="text-sm font-bold text-white tracking-wide uppercase">YES INDIA ERMS</span>
            <span class="text-slate-600">|</span>
            <span class="text-xs text-slate-400 font-medium">{{ $exam->name }}</span>
        </div>

        <div class="flex items-center gap-6">
            <div class="text-right">
                <span class="text-[10px] text-slate-400 uppercase tracking-widest block">Exam Duration</span>
                <span class="text-xs font-bold text-white font-mono">{{ $exam->duration_minutes }}:00 Remaining</span>
            </div>

            <!-- Simulated Camera Feed Box -->
            <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-emerald-400 relative" title="Simulated Camera Stream">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <span class="w-2 h-2 rounded-full bg-emerald-400 absolute top-1 right-1"></span>
            </div>
        </div>
    </header>

    <!-- Question View Engine -->
    <main class="flex-1 max-w-4xl w-full mx-auto p-6 flex flex-col justify-between">
        <template x-if="questions.length > 0">
            <div class="space-y-6">
                <!-- Question Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-800/80">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Question <span x-text="currentIdx + 1"></span> of <span x-text="questions.length"></span>
                        </span>
                        <span class="ml-2 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-800 text-slate-300" x-text="questions[currentIdx].type_label"></span>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="text-xs text-slate-400 font-medium">
                            Marks: <strong class="text-white" x-text="'+' + questions[currentIdx].marks"></strong>
                            <template x-if="questions[currentIdx].negative > 0">
                                <span class="text-rose-400" x-text="' / -' + questions[currentIdx].negative"></span>
                            </template>
                        </span>

                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 font-mono text-sm font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="'00:' + (questions[currentIdx].time_limit < 10 ? '0' : '') + questions[currentIdx].time_limit"></span>
                        </div>
                    </div>
                </div>

                <!-- Question Text -->
                <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
                    <div class="text-base sm:text-lg font-medium text-white leading-relaxed" x-html="questions[currentIdx].text"></div>

                    <!-- Images if attached -->
                    <template x-for="(img, imgIdx) in (questions[currentIdx].images || [])" :key="imgIdx">
                        <div class="pt-2">
                            <div class="p-2 bg-slate-950/80 rounded-2xl border border-slate-800 inline-block shadow-lg">
                                <img :src="img.url" alt="Question media" class="max-h-80 max-w-full rounded-xl object-contain">
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Fill in the Blank Input -->
                <template x-if="questions[currentIdx].type === 'FILL_IN_BLANK'">
                    <div class="space-y-3">
                        <label class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Your Answer</label>
                        <input type="text" x-model="textAnswer"
                               :disabled="answerSubmitted"
                               @keydown.enter.prevent="if (canSubmit() && !answerSubmitted) submitAnswer()"
                               placeholder="Type your answer here (not case-sensitive)..."
                               autocomplete="off"
                               class="w-full bg-slate-950 border border-slate-700 rounded-2xl px-5 py-4 text-base text-white font-mono placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors"
                               :class="answerSubmitted ? 'opacity-75 cursor-not-allowed' : ''">
                        
                        <p class="text-[11px] text-slate-400">Answer is case-insensitive (e.g., "paris" matches "Paris").</p>

                        <!-- Admin indicator showing the correct answer -->
                        <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 flex items-center justify-between text-xs mt-2">
                            <span class="text-slate-400 font-medium">Expected Answer (Admin Only):</span>
                            <span class="font-mono font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-500/20"
                                  x-text="(questions[currentIdx].options.find(o => o.is_correct) || questions[currentIdx].options[0])?.text || 'Not configured'"></span>
                        </div>
                    </div>
                </template>

                <!-- Multiple Select Checkboxes -->
                <template x-if="questions[currentIdx].type === 'MULTIPLE_SELECT'">
                    <div class="space-y-3">
                        <template x-for="opt in questions[currentIdx].options" :key="opt.identifier">
                            <label class="flex items-center gap-4 p-4 rounded-xl border transition-all cursor-pointer"
                                   :class="selectedOptions.includes(opt.identifier) ? 'bg-indigo-600/10 border-indigo-500 text-white shadow-sm' : 'bg-slate-900/40 border-slate-800/80 text-slate-300 hover:bg-slate-900/80'">
                                <input type="checkbox" :value="opt.identifier" 
                                       :checked="selectedOptions.includes(opt.identifier)"
                                       @change="toggleOption(opt.identifier)"
                                       :disabled="answerSubmitted"
                                       class="w-4 h-4 text-indigo-600 rounded bg-slate-950 border-slate-700 focus:ring-indigo-500">
                                
                                <span class="w-7 h-7 rounded-lg bg-slate-800 text-xs font-bold text-slate-300 flex items-center justify-center shrink-0"
                                      x-text="opt.identifier"></span>

                                <span class="text-sm font-medium" x-text="opt.text"></span>

                                <template x-if="opt.is_correct">
                                    <span class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">
                                        Correct Option (Admin Only)
                                    </span>
                                </template>
                            </label>
                        </template>
                    </div>
                </template>

                <!-- Single Choice Radio (MCQ / TRUE_FALSE) -->
                <template x-if="questions[currentIdx].type === 'MCQ' || questions[currentIdx].type === 'TRUE_FALSE'">
                    <div class="space-y-3">
                        <template x-for="opt in questions[currentIdx].options" :key="opt.identifier">
                            <label class="flex items-center gap-4 p-4 rounded-xl border transition-all cursor-pointer"
                                   :class="selectedOption === opt.identifier ? 'bg-indigo-600/10 border-indigo-500 text-white shadow-sm' : 'bg-slate-900/40 border-slate-800/80 text-slate-300 hover:bg-slate-900/80'">
                                <input type="radio" name="preview_option" :value="opt.identifier" x-model="selectedOption"
                                       :disabled="answerSubmitted"
                                       class="w-4 h-4 text-indigo-600 bg-slate-950 border-slate-700 focus:ring-indigo-500">
                                
                                <span class="w-7 h-7 rounded-lg bg-slate-800 text-xs font-bold text-slate-300 flex items-center justify-center shrink-0"
                                      x-text="opt.identifier"></span>

                                <span class="text-sm font-medium" x-text="opt.text"></span>

                                <template x-if="opt.is_correct">
                                    <span class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">
                                        Correct Answer (Admin Only)
                                    </span>
                                </template>
                            </label>
                        </template>
                    </div>
                </template>

                <!-- Saved Banner Confirmation -->
                <div x-show="answerSubmitted" x-transition class="space-y-2">
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm font-medium flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        <span>✓ Answer Saved Successfully. Click [Next Question] to continue.</span>
                    </div>

                    <!-- In Fill in Blank, show case-insensitive verification preview for Admin -->
                    <template x-if="questions[currentIdx].type === 'FILL_IN_BLANK'">
                        <div class="p-3 rounded-xl text-xs font-mono flex items-center justify-between border"
                             :class="isFillBlankCorrect() ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-rose-500/10 border-rose-500/30 text-rose-300'">
                            <span x-text="isFillBlankCorrect() ? '✓ Matches expected answer (Case-Insensitive Match)' : '✗ Does not match expected answer'"></span>
                            <span class="text-slate-400 font-sans text-[11px]">Simulated Evaluation</span>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="questions.length === 0">
            <div class="p-12 text-center text-slate-500">
                No questions assigned to this examination yet.
            </div>
        </template>

        <!-- Footer Navigation Controls -->
        <footer class="pt-6 border-t border-slate-800/80 flex items-center justify-between">
            <div>
                @if($exam->allow_previous_question)
                    <button type="button" @click="prevQuestion()" :disabled="currentIdx === 0"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 disabled:opacity-40 disabled:cursor-not-allowed">
                        &larr; Previous Question
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <!-- Submit Button -->
                <button type="button" @click="submitAnswer()" :disabled="answerSubmitted || !canSubmit()"
                        class="px-5 py-2.5 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                    Submit Answer
                </button>

                <!-- Next Question Button -->
                <button type="button" @click="nextQuestion()" :disabled="currentIdx >= questions.length - 1"
                        class="px-5 py-2.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/20 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                    Next Question &rarr;
                </button>
            </div>
        </footer>
    </main>
</body>
</html>
