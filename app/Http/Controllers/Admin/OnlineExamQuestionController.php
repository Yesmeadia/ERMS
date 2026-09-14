<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExamStatus;
use App\Http\Controllers\Controller;
use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineQuestion;
use Illuminate\Http\Request;

class OnlineExamQuestionController extends Controller
{
    /**
     * Display questions assigned to an exam and bank selector.
     */
    public function index(OnlineExam $online_exam)
    {
        $exam = $online_exam->load(['category', 'examQuestions.question.options', 'examQuestions.question.images']);
        $assignedQuestionIds = $exam->examQuestions->pluck('question_id')->toArray();

        // Available questions from bank for this category
        $bankQuestions = OnlineQuestion::with(['options', 'images'])
            ->where(function ($q) use ($exam) {
                $q->where('category_id', $exam->category_id)
                    ->orWhereNull('category_id');
            })
            ->whereNotIn('id', $assignedQuestionIds)
            ->where('is_active', true)
            ->latest()
            ->paginate(15);

        return view('admin.online-exams.questions.assign', compact('exam', 'bankQuestions', 'assignedQuestionIds'));
    }

    /**
     * Assign questions from question bank to the exam.
     */
    public function assign(Request $request, OnlineExam $online_exam)
    {
        if ($online_exam->status !== ExamStatus::DRAFT) {
            return back()->with('error', 'Cannot modify questions on a published or completed examination.');
        }

        $validated = $request->validate([
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['exists:online_questions,id'],
        ]);

        $maxSort = $online_exam->examQuestions()->max('sort_order') ?: 0;

        foreach ($validated['question_ids'] as $qId) {
            $question = OnlineQuestion::find($qId);
            $maxSort++;

            OnlineExamQuestion::firstOrCreate(
                [
                    'online_exam_id' => $online_exam->id,
                    'question_id' => $qId,
                ],
                [
                    'sort_order' => $maxSort,
                    'marks' => $question->default_marks,
                    'negative_marks' => $question->negative_marks,
                    'time_limit_seconds' => $question->time_limit_seconds ?: $online_exam->default_question_time_limit,
                ]
            );
        }

        // Recalculate total exam marks based on assigned questions
        $totalMarks = $online_exam->examQuestions()->sum('marks');
        $online_exam->update(['total_marks' => $totalMarks]);

        return back()->with('success', 'Questions successfully assigned to examination.');
    }

    /**
     * Remove question from an exam.
     */
    public function remove(OnlineExam $online_exam, OnlineQuestion $question)
    {
        if ($online_exam->status !== ExamStatus::DRAFT) {
            return back()->with('error', 'Cannot remove questions from a published or completed examination.');
        }

        OnlineExamQuestion::where('online_exam_id', $online_exam->id)
            ->where('question_id', $question->id)
            ->delete();

        // Recalculate total exam marks
        $totalMarks = $online_exam->examQuestions()->sum('marks');
        $online_exam->update(['total_marks' => $totalMarks]);

        return back()->with('success', 'Question removed from examination.');
    }

    /**
     * Update order and marks for assigned questions.
     */
    public function updateSettings(Request $request, OnlineExam $online_exam)
    {
        if ($online_exam->status !== ExamStatus::DRAFT) {
            return back()->with('error', 'Cannot update question settings on a published examination.');
        }

        $validated = $request->validate([
            'questions' => ['required', 'array'],
            'questions.*.id' => ['required', 'exists:online_exam_questions,id'],
            'questions.*.sort_order' => ['required', 'integer'],
            'questions.*.marks' => ['required', 'numeric', 'min:0.5'],
            'questions.*.negative_marks' => ['required', 'numeric', 'min:0'],
            'questions.*.time_limit_seconds' => ['nullable', 'integer', 'min:5'],
        ]);

        foreach ($validated['questions'] as $item) {
            OnlineExamQuestion::where('id', $item['id'])
                ->where('online_exam_id', $online_exam->id)
                ->update([
                    'sort_order' => $item['sort_order'],
                    'marks' => $item['marks'],
                    'negative_marks' => $item['negative_marks'],
                    'time_limit_seconds' => $item['time_limit_seconds'] ?? null,
                ]);
        }

        $totalMarks = $online_exam->examQuestions()->sum('marks');
        $online_exam->update(['total_marks' => $totalMarks]);

        return back()->with('success', 'Question ordering and marks saved successfully.');
    }
}
