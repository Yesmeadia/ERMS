<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExamSessionStatus;
use App\Enums\ExamStatus;
use App\Http\Controllers\Controller;
use App\Models\OnlineExam;
use App\Models\OnlineExamResult;
use App\Models\OnlineExamSession;
use App\Models\OnlineExamStudent;
use App\Models\OnlineQuestion;

class OnlineExamDashboardController extends Controller
{
    /**
     * Show the Online Examination Management Dashboard.
     */
    public function index()
    {
        $today = now()->format('Y-m-d');

        $metrics = [
            'total_exams' => OnlineExam::count(),
            'active_today' => OnlineExam::where('exam_date', $today)
                ->whereIn('status', [ExamStatus::PUBLISHED, ExamStatus::ACTIVE])
                ->count(),
            'total_questions' => OnlineQuestion::count(),
            'total_enrolled' => OnlineExamStudent::where('is_eligible', true)->count(),
            'live_sessions' => OnlineExamSession::whereIn('status', [
                ExamSessionStatus::IN_PROGRESS,
                ExamSessionStatus::QUESTION_ACTIVE,
                ExamSessionStatus::ANSWERED,
            ])->count(),
            'total_results' => OnlineExamResult::count(),
        ];

        // Recent exams
        $recentExams = OnlineExam::with(['category', 'creator'])
            ->withCount([
                'examStudents as enrolled_count',
                'examQuestions as questions_count',
            ])
            ->latest()
            ->take(5)
            ->get();

        // Active live exams today
        $liveExams = OnlineExam::with('category')
            ->whereIn('status', [ExamStatus::PUBLISHED, ExamStatus::ACTIVE])
            ->where('exam_date', $today)
            ->withCount('sessions')
            ->get();

        return view('admin.online-exams.dashboard', compact('metrics', 'recentExams', 'liveExams'));
    }
}
