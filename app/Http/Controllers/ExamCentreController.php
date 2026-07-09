<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Student;
use App\Models\Examination;
use Illuminate\Http\Request;

class ExamCentreController extends Controller
{
    /**
     * Display a listing of the exam centres and handles assignment.
     */
    public function index(Request $request)
    {
        // 1. List of designated centres
        $query = School::where('is_centre', true);
        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('zone', 'like', "%{$search}%");
            });
        }
        $centres = $query->latest()->paginate(10, ['*'], 'centres_page');

        // 2. All schools for designation list
        $schoolsQuery = School::query();
        if ($request->filled('school_search')) {
            $sSearch = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->school_search);
            $schoolsQuery->where(function($q) use ($sSearch) {
                $q->where('name', 'like', "%{$sSearch}%")
                  ->orWhere('code', 'like', "%{$sSearch}%");
            });
        }
        $allSchools = $schoolsQuery->latest()->paginate(10, ['*'], 'schools_page');

        // 3. Dropdowns for assignment form
        $registeredSchools = School::where('status', true)->get();
        $designatedCentres = School::where('is_centre', true)->where('status', true)->get();
        $examinations = Examination::all();

        // 4. Statistics
        $totalCentres = School::where('is_centre', true)->count();
        $totalAssigned = Student::whereNotNull('centre_id')->count();
        $totalPending = Student::whereNull('centre_id')->whereIn('status', ['Approved', 'Hall Ticket Issued'])->count();

        return view('super-admin.exam-centres.index', compact(
            'centres',
            'allSchools',
            'registeredSchools',
            'designatedCentres',
            'examinations',
            'totalCentres',
            'totalAssigned',
            'totalPending'
        ));
    }

    /**
     * Toggle the designated Exam Centre status of a school.
     */
    public function toggle(School $school)
    {
        $school->is_centre = !$school->is_centre;
        $school->save();

        $statusStr = $school->is_centre ? 'designated as an Exam Centre' : 'removed from Exam Centres';

        activity()
            ->performedOn($school)
            ->log("School '{$school->name}' was {$statusStr}.");

        return back()->with('success', "School '{$school->name}' is now {$statusStr}.");
    }

    /**
     * Bulk assign a Centre of Examination to students of a particular School and Examination.
     */
    public function assignCentres(Request $request)
    {
        $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'examination_id' => ['required', 'exists:examinations,id'],
            'centre_id' => ['required', 'exists:schools,id'],
        ]);

        $centre = School::where('id', $request->centre_id)->where('is_centre', true)->first();
        if (!$centre) {
            return back()->with('error', 'The selected school is not designated as an Exam Centre.');
        }

        // Get student count that will be updated
        $query = Student::where('school_id', $request->school_id)
            ->where('examination_id', $request->examination_id);

        $count = $query->count();

        if ($count === 0) {
            return back()->with('info', 'No registered students found for the selected School and Examination.');
        }

        // Assign the centre
        $query->update(['centre_id' => $centre->id]);

        $school = School::find($request->school_id);
        $exam = Examination::find($request->examination_id);

        activity()
            ->log("Assigned Exam Centre '{$centre->name}' to {$count} students of school '{$school->name}' for exam '{$exam->name}'.");

        return back()->with('success', "Successfully assigned Exam Centre '{$centre->name}' to {$count} students.");
    }

    /**
     * Clear the assigned exam centre for a student.
     */
    public function unassignCentre(Student $student)
    {
        $student->centre_id = null;
        $student->save();

        activity()
            ->performedOn($student)
            ->log("Cleared exam centre assignment for candidate: {$student->name}.");

        return back()->with('success', "Cleared centre assignment for student {$student->name}.");
    }

    /**
     * Assign Exam Centre to a single student.
     */
    public function assignSingle(Request $request, Student $student)
    {
        $request->validate([
            'centre_id' => ['required', 'exists:schools,id'],
        ]);

        $centre = School::where('id', $request->centre_id)->where('is_centre', true)->first();
        if (!$centre) {
            return back()->with('error', 'The selected school is not designated as an Exam Centre.');
        }

        $student->centre_id = $centre->id;
        $student->save();

        activity()
            ->performedOn($student)
            ->log("Assigned Exam Centre '{$centre->name}' to candidate: {$student->name}.");

        return back()->with('success', "Successfully assigned Exam Centre '{$centre->name}' to candidate {$student->name}.");
    }
}
