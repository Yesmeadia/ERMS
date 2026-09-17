<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExamStatus;
use App\Http\Controllers\Controller;
use App\Models\OnlineExam;
use App\Models\OnlineExamStudent;
use App\Models\School;
use App\Models\Student;
use App\Services\OnlineExamService;
use Illuminate\Http\Request;

class OnlineExamStudentController extends Controller
{
    public function __construct(
        protected OnlineExamService $examService
    ) {}

    /**
     * Display enrolled students and student picker.
     */
    public function index(Request $request, OnlineExam $online_exam)
    {
        $this->authorize('manageSubResource', $online_exam);

        $exam = $online_exam->load('category');

        // Currently enrolled students
        $enrolledStudents = OnlineExamStudent::with(['student.school', 'student.class'])
            ->where('online_exam_id', $exam->id)
            ->paginate(20, ['*'], 'enrolled_page');

        $enrolledCount = OnlineExamStudent::where('online_exam_id', $exam->id)->count();
        $enrolledStudentIds = OnlineExamStudent::where('online_exam_id', $exam->id)->pluck('student_id')->toArray();

        // Available students from existing ERMS matching this exam's category
        $availableQuery = Student::with(['school', 'class'])
            ->where('category_id', $exam->category_id)
            ->whereNotNull('registration_number')
            ->whereNotIn('id', $enrolledStudentIds);

        if ($request->filled('school_id')) {
            $availableQuery->where('school_id', $request->school_id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $availableQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('registration_number', 'like', "%{$term}%");
            });
        }

        $availableStudents = $availableQuery->paginate(25, ['*'], 'available_page');
        $schools = School::where('status', true)->orderBy('name')->get();

        return view('admin.online-exams.students.index', compact(
            'exam',
            'enrolledStudents',
            'enrolledCount',
            'availableStudents',
            'schools'
        ));
    }

    /**
     * Enroll selected students into the exam.
     */
    public function enroll(Request $request, OnlineExam $online_exam)
    {
        $this->authorize('manageSubResource', $online_exam);

        if ($online_exam->status !== ExamStatus::DRAFT) {
            return back()->with('error', 'Cannot modify student enrollments on a published examination.');
        }

        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        $result = $this->examService->enrollStudents($online_exam, $validated['student_ids'], auth()->user());

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Remove student enrollment from the exam.
     */
    public function remove(OnlineExam $online_exam, Student $student)
    {
        $this->authorize('manageSubResource', $online_exam);

        if ($online_exam->status !== ExamStatus::DRAFT) {
            return back()->with('error', 'Cannot remove students from a published examination.');
        }

        OnlineExamStudent::where('online_exam_id', $online_exam->id)
            ->where('student_id', $student->id)
            ->delete();

        return back()->with('success', "Student {$student->name} ({$student->registration_number}) removed from examination.");
    }
}
