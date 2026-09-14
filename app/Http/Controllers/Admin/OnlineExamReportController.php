<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OnlineExam;
use App\Models\OnlineExamAnswer;
use App\Models\OnlineExamResult;
use App\Services\ExamScoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OnlineExamReportController extends Controller
{
    public function __construct(
        protected ExamScoringService $scoringService
    ) {}

    /**
     * Display the results and item analysis for an online exam.
     */
    public function index(Request $request, OnlineExam $online_exam)
    {
        $exam = $online_exam->load('category');

        $query = OnlineExamResult::with(['student.school', 'student.class'])
            ->where('online_exam_id', $exam->id);

        if ($request->filled('status')) {
            if ($request->status === 'pass') {
                $query->where('is_passed', true);
            } elseif ($request->status === 'fail') {
                $query->where('is_passed', false);
            }
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('student', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('registration_number', 'like', "%{$term}%");
            });
        }

        $results = $query->orderBy('rank')->orderByDesc('final_score')->paginate(25);

        // Calculate summary metrics
        $totalCandidates = OnlineExamResult::where('online_exam_id', $exam->id)->count();
        $passedCount = OnlineExamResult::where('online_exam_id', $exam->id)->where('is_passed', true)->count();
        $avgScore = OnlineExamResult::where('online_exam_id', $exam->id)->avg('final_score') ?: 0;
        $highestScore = OnlineExamResult::where('online_exam_id', $exam->id)->max('final_score') ?: 0;

        // Question Item Analysis
        $examQuestions = $exam->examQuestions()->with('question')->get();
        $itemAnalysis = [];

        foreach ($examQuestions as $eq) {
            $answers = OnlineExamAnswer::whereHas('session', function ($q) use ($exam) {
                $q->where('online_exam_id', $exam->id);
            })->where('question_id', $eq->question_id)->get();

            $attempts = $answers->count();
            $correct = $answers->where('is_correct', true)->count();
            $wrong = $attempts - $correct;
            $accuracy = $attempts > 0 ? round(($correct / $attempts) * 100, 1) : 0;

            $itemAnalysis[] = [
                'question_id' => $eq->question_id,
                'sort_order' => $eq->sort_order,
                'question_text' => $eq->question->question_text,
                'type' => $eq->question->question_type->shortLabel(),
                'marks' => $eq->marks,
                'attempts' => $attempts,
                'correct' => $correct,
                'wrong' => $wrong,
                'accuracy' => $accuracy,
            ];
        }

        return view('admin.online-exams.results', compact(
            'exam',
            'results',
            'totalCandidates',
            'passedCount',
            'avgScore',
            'highestScore',
            'itemAnalysis'
        ));
    }

    /**
     * Recalculate and update ranks for all completed candidates.
     */
    public function recalculateRanks(OnlineExam $online_exam)
    {
        $this->scoringService->recalculateRanks($online_exam);

        return back()->with('success', 'Candidate merit ranks recalculated and updated successfully.');
    }

    /**
     * Export examination results to CSV.
     */
    public function exportCsv(OnlineExam $online_exam): StreamedResponse
    {
        $exam = $online_exam;
        $results = OnlineExamResult::with(['student.school'])
            ->where('online_exam_id', $exam->id)
            ->orderBy('rank')
            ->orderByDesc('final_score')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="online_exam_results_'.$exam->code.'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($results) {
            $handle = fopen('php://output', 'w');

            // Header Row
            fputcsv($handle, [
                'Rank',
                'Registration Number',
                'Student Name',
                'School / Centre',
                'Total Questions',
                'Attempted',
                'Correct',
                'Wrong',
                'Unanswered',
                'Raw Score',
                'Negative Marks',
                'Speed Bonus',
                'Final Score',
                'Total Marks',
                'Percentage',
                'Grade',
                'Result Status',
                'Time Taken (Seconds)',
                'Violations Count',
            ]);

            foreach ($results as $res) {
                fputcsv($handle, [
                    $res->rank ?: '-',
                    $res->student->registration_number,
                    $res->student->name,
                    $res->student->school->name ?? 'N/A',
                    $res->total_questions,
                    $res->attempted_questions_count,
                    $res->correct_answers_count,
                    $res->wrong_answers_count,
                    $res->unanswered_count,
                    $res->raw_score,
                    $res->negative_marks,
                    $res->speed_bonus_points,
                    $res->final_score,
                    $res->total_marks,
                    $res->percentage.'%',
                    $res->grade ?: '-',
                    $res->is_passed ? 'PASS' : 'FAIL',
                    $res->time_taken_seconds,
                    $res->session ? $res->session->violations_count : 0,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export examination results summary as PDF report.
     */
    public function exportPdf(OnlineExam $online_exam)
    {
        $exam = $online_exam->load(['category', 'creator']);
        $results = OnlineExamResult::with(['student.school'])
            ->where('online_exam_id', $exam->id)
            ->orderBy('rank')
            ->orderByDesc('final_score')
            ->get();

        $pdf = Pdf::loadView('admin.online-exams.reports.results-pdf', compact('exam', 'results'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("online_exam_merit_list_{$exam->code}.pdf");
    }
}
