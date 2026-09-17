<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryMaster;
use App\Models\OnlineQuestion;
use App\Models\OnlineQuestionImage;
use App\Models\OnlineQuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OnlineQuestionBankController extends Controller
{
    /**
     * Display a listing of question bank items.
     */
    public function index(Request $request)
    {
        $query = OnlineQuestion::with(['category', 'options', 'images', 'creator'])
            ->withCount('exams');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('question_type')) {
            $query->where('question_type', $request->question_type);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('question_text', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%")
                    ->orWhere('topic', 'like', "%{$term}%");
            });
        }

        $questions = $query->latest()->paginate(15);
        $categories = CategoryMaster::where('status', true)->get();

        return view('admin.online-exams.questions.index', compact('questions', 'categories'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create()
    {
        $categories = CategoryMaster::where('status', true)->get();

        return view('admin.online-exams.questions.create', compact('categories'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'category_id' => ['nullable', 'exists:categories,id'],
            'subject' => ['nullable', 'string', 'max:128'],
            'topic' => ['nullable', 'string', 'max:128'],
            'difficulty' => ['required', 'string', 'in:EASY,MEDIUM,HARD'],
            'question_type' => ['required', 'string', 'in:MCQ,TRUE_FALSE,MULTIPLE_SELECT,FILL_IN_BLANK'],
            'question_text' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'default_marks' => ['required', 'numeric', 'min:0.5', 'max:50'],
            'negative_marks' => ['nullable', 'numeric', 'min:0'],
            'time_limit_seconds' => ['nullable', 'integer', 'min:5', 'max:600'],
            'multiple_select_criteria' => ['nullable', 'string', 'in:ALL_CORRECT,PARTIAL_WITH_PENALTY,PARTIAL_NO_PENALTY'],
            'options' => ['nullable', 'array'],
            'options.*.text' => ['nullable', 'string'],
            'options.*.is_correct' => ['nullable'],
            'correct_option' => ['nullable'],
            'true_false_correct' => ['nullable', 'string', 'in:True,False'],
            'fill_blank_answer' => ['nullable', 'string', 'max:500'],
            'question_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];

        $validated = $request->validate($rules);

        $qType = $validated['question_type'];
        if (in_array($qType, ['MCQ', 'MULTIPLE_SELECT'])) {
            $optionsData = $request->input('options', []);
            $nonEmptyOptions = array_filter($optionsData, fn ($opt) => ! empty(trim($opt['text'] ?? '')));
            if (count($nonEmptyOptions) < 2) {
                return back()->withInput()->withErrors(['options' => 'Please provide at least 2 non-empty options for multiple choice questions.']);
            }
            if ($qType === 'MULTIPLE_SELECT') {
                $hasCorrect = collect($nonEmptyOptions)->contains(fn ($opt) => ! empty($opt['is_correct']));
                if (! $hasCorrect) {
                    return back()->withInput()->withErrors(['options' => 'Please select at least one correct option for Multiple Select.']);
                }
            }
        } elseif ($qType === 'FILL_IN_BLANK') {
            if (empty(trim($request->input('fill_blank_answer', '')))) {
                return back()->withInput()->withErrors(['fill_blank_answer' => 'Please provide the expected correct answer for fill-in-the-blank.']);
            }
        }

        return DB::transaction(function () use ($request, $validated, $qType) {
            $question = OnlineQuestion::create([
                'category_id' => $validated['category_id'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'topic' => $validated['topic'] ?? null,
                'difficulty' => $validated['difficulty'],
                'question_type' => $validated['question_type'],
                'question_text' => $validated['question_text'],
                'explanation' => $validated['explanation'] ?? null,
                'default_marks' => $validated['default_marks'],
                'negative_marks' => $validated['negative_marks'] ?? 0.00,
                'time_limit_seconds' => $validated['time_limit_seconds'] ?? null,
                'multiple_select_criteria' => $validated['multiple_select_criteria'] ?? 'ALL_CORRECT',
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            // Handle Options
            $optionsData = $request->input('options', []);
            $identifiers = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

            if ($qType === 'TRUE_FALSE') {
                $selectedCorrect = $request->input('true_false_correct', 'True');
                OnlineQuestionOption::create([
                    'question_id' => $question->id,
                    'option_identifier' => 'A',
                    'option_text' => 'True',
                    'is_correct' => ($selectedCorrect === 'True'),
                    'sort_order' => 1,
                ]);
                OnlineQuestionOption::create([
                    'question_id' => $question->id,
                    'option_identifier' => 'B',
                    'option_text' => 'False',
                    'is_correct' => ($selectedCorrect === 'False'),
                    'sort_order' => 2,
                ]);
            } elseif ($qType === 'MCQ') {
                $correctIndex = (int) $request->input('correct_option', 0);
                $optIndex = 0;
                foreach ($optionsData as $idx => $opt) {
                    $text = trim($opt['text'] ?? '');
                    if ($text !== '') {
                        OnlineQuestionOption::create([
                            'question_id' => $question->id,
                            'option_identifier' => $identifiers[$optIndex] ?? chr(65 + $optIndex),
                            'option_text' => $text,
                            'is_correct' => ($idx === $correctIndex),
                            'sort_order' => $optIndex + 1,
                        ]);
                        $optIndex++;
                    }
                }
            } elseif ($qType === 'MULTIPLE_SELECT') {
                $optIndex = 0;
                foreach ($optionsData as $idx => $opt) {
                    $text = trim($opt['text'] ?? '');
                    if ($text !== '') {
                        OnlineQuestionOption::create([
                            'question_id' => $question->id,
                            'option_identifier' => $identifiers[$optIndex] ?? chr(65 + $optIndex),
                            'option_text' => $text,
                            'is_correct' => ! empty($opt['is_correct']),
                            'sort_order' => $optIndex + 1,
                        ]);
                        $optIndex++;
                    }
                }
            } elseif ($qType === 'FILL_IN_BLANK') {
                $blankAnswer = trim($request->input('fill_blank_answer', ''));
                if ($blankAnswer !== '') {
                    OnlineQuestionOption::create([
                        'question_id' => $question->id,
                        'option_identifier' => 'A',
                        'option_text' => $blankAnswer,
                        'is_correct' => true,
                        'sort_order' => 1,
                    ]);
                }
            }

            // Handle Question Image Upload
            if ($request->hasFile('question_image')) {
                $file = $request->file('question_image');
                $ext = $file->guessExtension() ?: 'jpg';
                $filename = 'q_'.$question->id.'_'.Str::random(10).'.'.$ext;
                $path = $file->storeAs('online-exam/questions', $filename, 'public');

                OnlineQuestionImage::create([
                    'question_id' => $question->id,
                    'image_path' => $path,
                    'disk' => 'public',
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }

            return redirect()->route('admin.online-questions.index')
                ->with('success', 'Question successfully created in Question Bank.');
        });
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(OnlineQuestion $online_question)
    {
        $question = $online_question->load(['options', 'images']);
        $categories = CategoryMaster::where('status', true)->get();

        return view('admin.online-exams.questions.edit', compact('question', 'categories'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, OnlineQuestion $online_question)
    {
        $rules = [
            'category_id' => ['nullable', 'exists:categories,id'],
            'subject' => ['nullable', 'string', 'max:128'],
            'topic' => ['nullable', 'string', 'max:128'],
            'difficulty' => ['required', 'string', 'in:EASY,MEDIUM,HARD'],
            'question_type' => ['required', 'string', 'in:MCQ,TRUE_FALSE,MULTIPLE_SELECT,FILL_IN_BLANK'],
            'question_text' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'default_marks' => ['required', 'numeric', 'min:0.5', 'max:50'],
            'negative_marks' => ['nullable', 'numeric', 'min:0'],
            'time_limit_seconds' => ['nullable', 'integer', 'min:5', 'max:600'],
            'multiple_select_criteria' => ['nullable', 'string', 'in:ALL_CORRECT,PARTIAL_WITH_PENALTY,PARTIAL_NO_PENALTY'],
            'options' => ['nullable', 'array'],
            'options.*.text' => ['nullable', 'string'],
            'options.*.is_correct' => ['nullable'],
            'correct_option' => ['nullable'],
            'true_false_correct' => ['nullable', 'string', 'in:True,False'],
            'fill_blank_answer' => ['nullable', 'string', 'max:500'],
            'question_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];

        $validated = $request->validate($rules);

        $qType = $validated['question_type'];
        if (in_array($qType, ['MCQ', 'MULTIPLE_SELECT'])) {
            $optionsData = $request->input('options', []);
            $nonEmptyOptions = array_filter($optionsData, fn ($opt) => ! empty(trim($opt['text'] ?? '')));
            if (count($nonEmptyOptions) < 2) {
                return back()->withInput()->withErrors(['options' => 'Please provide at least 2 non-empty options for multiple choice questions.']);
            }
            if ($qType === 'MULTIPLE_SELECT') {
                $hasCorrect = collect($nonEmptyOptions)->contains(fn ($opt) => ! empty($opt['is_correct']));
                if (! $hasCorrect) {
                    return back()->withInput()->withErrors(['options' => 'Please select at least one correct option for Multiple Select.']);
                }
            }
        } elseif ($qType === 'FILL_IN_BLANK') {
            if (empty(trim($request->input('fill_blank_answer', '')))) {
                return back()->withInput()->withErrors(['fill_blank_answer' => 'Please provide the expected correct answer for fill-in-the-blank.']);
            }
        }

        return DB::transaction(function () use ($request, $validated, $online_question, $qType) {
            $online_question->update([
                'category_id' => $validated['category_id'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'topic' => $validated['topic'] ?? null,
                'difficulty' => $validated['difficulty'],
                'question_type' => $validated['question_type'],
                'question_text' => $validated['question_text'],
                'explanation' => $validated['explanation'] ?? null,
                'default_marks' => $validated['default_marks'],
                'negative_marks' => $validated['negative_marks'] ?? 0.00,
                'time_limit_seconds' => $validated['time_limit_seconds'] ?? null,
                'multiple_select_criteria' => $validated['multiple_select_criteria'] ?? 'ALL_CORRECT',
            ]);

            // Rebuild options cleanly
            $online_question->options()->delete();
            $optionsData = $request->input('options', []);
            $identifiers = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

            if ($qType === 'TRUE_FALSE') {
                $selectedCorrect = $request->input('true_false_correct', 'True');
                OnlineQuestionOption::create([
                    'question_id' => $online_question->id,
                    'option_identifier' => 'A',
                    'option_text' => 'True',
                    'is_correct' => ($selectedCorrect === 'True'),
                    'sort_order' => 1,
                ]);
                OnlineQuestionOption::create([
                    'question_id' => $online_question->id,
                    'option_identifier' => 'B',
                    'option_text' => 'False',
                    'is_correct' => ($selectedCorrect === 'False'),
                    'sort_order' => 2,
                ]);
            } elseif ($qType === 'MCQ') {
                $correctIndex = (int) $request->input('correct_option', 0);
                $optIndex = 0;
                foreach ($optionsData as $idx => $opt) {
                    $text = trim($opt['text'] ?? '');
                    if ($text !== '') {
                        OnlineQuestionOption::create([
                            'question_id' => $online_question->id,
                            'option_identifier' => $identifiers[$optIndex] ?? chr(65 + $optIndex),
                            'option_text' => $text,
                            'is_correct' => ($idx === $correctIndex),
                            'sort_order' => $optIndex + 1,
                        ]);
                        $optIndex++;
                    }
                }
            } elseif ($qType === 'MULTIPLE_SELECT') {
                $optIndex = 0;
                foreach ($optionsData as $idx => $opt) {
                    $text = trim($opt['text'] ?? '');
                    if ($text !== '') {
                        OnlineQuestionOption::create([
                            'question_id' => $online_question->id,
                            'option_identifier' => $identifiers[$optIndex] ?? chr(65 + $optIndex),
                            'option_text' => $text,
                            'is_correct' => ! empty($opt['is_correct']),
                            'sort_order' => $optIndex + 1,
                        ]);
                        $optIndex++;
                    }
                }
            } elseif ($qType === 'FILL_IN_BLANK') {
                $blankAnswer = trim($request->input('fill_blank_answer', ''));
                if ($blankAnswer !== '') {
                    OnlineQuestionOption::create([
                        'question_id' => $online_question->id,
                        'option_identifier' => 'A',
                        'option_text' => $blankAnswer,
                        'is_correct' => true,
                        'sort_order' => 1,
                    ]);
                }
            }

            // Delete previous images if requested or replacing with a new image
            if ($request->boolean('remove_image') || $request->hasFile('question_image')) {
                foreach ($online_question->images as $img) {
                    Storage::disk($img->disk)->delete($img->image_path);
                    $img->delete();
                }
            }

            // Save new image if uploaded
            if ($request->hasFile('question_image')) {
                $file = $request->file('question_image');
                $ext = $file->guessExtension() ?: 'jpg';
                $filename = 'q_'.$online_question->id.'_'.Str::random(10).'.'.$ext;
                $path = $file->storeAs('online-exam/questions', $filename, 'public');

                OnlineQuestionImage::create([
                    'question_id' => $online_question->id,
                    'image_path' => $path,
                    'disk' => 'public',
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }

            return redirect()->route('admin.online-questions.index')
                ->with('success', 'Question updated successfully.');
        });
    }

    /**
     * Remove the specified question from Question Bank.
     */
    public function destroy(OnlineQuestion $online_question)
    {
        if ($online_question->exams()->exists()) {
            return back()->with('error', 'Cannot delete a question that is currently assigned to examinations.');
        }

        $online_question->delete();

        return redirect()->route('admin.online-questions.index')
            ->with('success', 'Question deleted successfully.');
    }

    /**
     * Toggle active status of a question.
     */
    public function toggleStatus(OnlineQuestion $online_question)
    {
        $online_question->update(['is_active' => ! $online_question->is_active]);

        return back()->with('success', 'Question status updated.');
    }
}
