<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExamStatus;
use App\Http\Controllers\Controller;
use App\Models\CategoryMaster;
use App\Models\OnlineExam;
use App\Services\OnlineExamService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnlineExamController extends Controller
{
    public function __construct(
        protected OnlineExamService $examService
    ) {}

    /**
     * Display a listing of online exams.
     */
    public function index(Request $request)
    {
        $query = OnlineExam::with(['category', 'creator'])
            ->withCount(['examStudents', 'examQuestions', 'sessions', 'results']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
            });
        }

        $exams = $query->latest('exam_date')->latest('start_time')->paginate(15);
        $categories = CategoryMaster::where('status', true)->get();

        return view('admin.online-exams.index', compact('exams', 'categories'));
    }

    /**
     * Show the form for creating a new online exam.
     */
    public function create()
    {
        $categories = CategoryMaster::where('status', true)->get();
        $defaultCode = 'EXAM-'.strtoupper(Str::random(6));

        return view('admin.online-exams.create', compact('categories', 'defaultCode'));
    }

    /**
     * Store a newly created online exam in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:64', 'unique:online_exams,code'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'exam_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:360'],
            'default_question_time_limit' => ['required', 'integer', 'min:5', 'max:600'],
            'total_marks' => ['required', 'numeric', 'min:1', 'max:1000'],
            'pass_marks' => ['required', 'numeric', 'min:0', 'lte:total_marks'],
            'enable_camera' => ['nullable', 'boolean'],
            'enable_fullscreen' => ['nullable', 'boolean'],
            'max_fullscreen_violations' => ['required', 'integer', 'min:1', 'max:10'],
            'allow_previous_question' => ['nullable', 'boolean'],
            'randomize_questions' => ['nullable', 'boolean'],
            'randomize_options' => ['nullable', 'boolean'],
            'show_result_immediately' => ['nullable', 'boolean'],
            'show_correct_answers' => ['nullable', 'boolean'],
            'show_question_marks' => ['nullable', 'boolean'],
            'show_rank' => ['nullable', 'boolean'],
            'enable_speed_bonus' => ['nullable', 'boolean'],
            'speed_bonus_formula' => ['nullable', 'string', 'in:linear,tier,percentage'],
            'max_bonus_per_question' => ['nullable', 'numeric', 'min:0'],
            'max_total_bonus' => ['nullable', 'numeric', 'min:0'],
            'max_eligible_students' => ['required', 'integer', 'min:1', 'max:199'],
            'instructions' => ['nullable', 'string'],
        ]);

        $validated['enable_camera'] = $request->boolean('enable_camera', true);
        $validated['enable_fullscreen'] = $request->boolean('enable_fullscreen', true);
        $validated['allow_previous_question'] = $request->boolean('allow_previous_question', false);
        $validated['randomize_questions'] = $request->boolean('randomize_questions', true);
        $validated['randomize_options'] = $request->boolean('randomize_options', true);
        $validated['show_result_immediately'] = $request->boolean('show_result_immediately', true);
        $validated['show_correct_answers'] = $request->boolean('show_correct_answers', false);
        $validated['show_question_marks'] = $request->boolean('show_question_marks', true);
        $validated['show_rank'] = $request->boolean('show_rank', false);
        $validated['enable_speed_bonus'] = $request->boolean('enable_speed_bonus', false);

        $validated['status'] = ExamStatus::DRAFT;
        $validated['created_by'] = auth()->id();

        $exam = OnlineExam::create($validated);

        return redirect()->route('admin.online-exams.show', $exam)
            ->with('success', "Examination '{$exam->name}' created successfully. Now assign questions and enroll eligible students.");
    }

    /**
     * Display the specified online exam.
     */
    public function show(OnlineExam $online_exam)
    {
        $exam = $online_exam->load(['category', 'creator'])
            ->loadCount(['examStudents', 'examQuestions', 'sessions', 'results']);

        $enrolledCount = $exam->exam_students_count;
        $questionsCount = $exam->exam_questions_count;
        [$canPublish, $publishWarning] = $exam->canPublish();

        return view('admin.online-exams.show', compact('exam', 'enrolledCount', 'questionsCount', 'canPublish', 'publishWarning'));
    }

    /**
     * Show the form for editing the online exam.
     */
    public function edit(OnlineExam $online_exam)
    {
        $categories = CategoryMaster::where('status', true)->get();

        return view('admin.online-exams.edit', ['exam' => $online_exam, 'categories' => $categories]);
    }

    /**
     * Update the specified online exam.
     */
    public function update(Request $request, OnlineExam $online_exam)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:64', 'unique:online_exams,code,'.$online_exam->id],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'exam_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:360'],
            'default_question_time_limit' => ['required', 'integer', 'min:5', 'max:600'],
            'total_marks' => ['required', 'numeric', 'min:1', 'max:1000'],
            'pass_marks' => ['required', 'numeric', 'min:0', 'lte:total_marks'],
            'enable_camera' => ['nullable', 'boolean'],
            'enable_fullscreen' => ['nullable', 'boolean'],
            'max_fullscreen_violations' => ['required', 'integer', 'min:1', 'max:10'],
            'allow_previous_question' => ['nullable', 'boolean'],
            'randomize_questions' => ['nullable', 'boolean'],
            'randomize_options' => ['nullable', 'boolean'],
            'show_result_immediately' => ['nullable', 'boolean'],
            'show_correct_answers' => ['nullable', 'boolean'],
            'show_question_marks' => ['nullable', 'boolean'],
            'show_rank' => ['nullable', 'boolean'],
            'enable_speed_bonus' => ['nullable', 'boolean'],
            'speed_bonus_formula' => ['nullable', 'string', 'in:linear,tier,percentage'],
            'max_bonus_per_question' => ['nullable', 'numeric', 'min:0'],
            'max_total_bonus' => ['nullable', 'numeric', 'min:0'],
            'max_eligible_students' => ['required', 'integer', 'min:1', 'max:199'],
            'instructions' => ['nullable', 'string'],
        ]);

        $validated['enable_camera'] = $request->boolean('enable_camera', true);
        $validated['enable_fullscreen'] = $request->boolean('enable_fullscreen', true);
        $validated['allow_previous_question'] = $request->boolean('allow_previous_question', false);
        $validated['randomize_questions'] = $request->boolean('randomize_questions', true);
        $validated['randomize_options'] = $request->boolean('randomize_options', true);
        $validated['show_result_immediately'] = $request->boolean('show_result_immediately', true);
        $validated['show_correct_answers'] = $request->boolean('show_correct_answers', false);
        $validated['show_question_marks'] = $request->boolean('show_question_marks', true);
        $validated['show_rank'] = $request->boolean('show_rank', false);
        $validated['enable_speed_bonus'] = $request->boolean('enable_speed_bonus', false);

        $online_exam->update($validated);

        return redirect()->route('admin.online-exams.show', $online_exam)
            ->with('success', 'Examination details updated successfully.');
    }

    /**
     * Remove the specified online exam from storage.
     */
    public function destroy(OnlineExam $online_exam)
    {
        if ($online_exam->sessions()->whereNotIn('status', ['NOT_STARTED'])->exists()) {
            return back()->with('error', 'Cannot delete an exam with active student attempts.');
        }

        $online_exam->delete();

        return redirect()->route('admin.online-exams.index')->with('success', 'Exam deleted successfully.');
    }

    /**
     * Publish the exam after validating <= 199 students.
     */
    public function publish(OnlineExam $online_exam)
    {
        $result = $this->examService->publishExam($online_exam, auth()->user());

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Unpublish the exam.
     */
    public function unpublish(OnlineExam $online_exam)
    {
        $result = $this->examService->unpublishExam($online_exam, auth()->user());

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Preview exam as student without recording results or sessions.
     */
    public function preview(OnlineExam $online_exam)
    {
        $exam = $online_exam->load(['category', 'examQuestions.question.options', 'examQuestions.question.images']);
        $questions = $exam->examQuestions;

        return view('admin.online-exams.preview', compact('exam', 'questions'));
    }
}
