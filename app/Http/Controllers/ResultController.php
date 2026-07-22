<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\School;
use App\Models\ClassMaster;
use App\Models\Examination;
use App\Models\StudentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResultController extends Controller
{
    /**
     * Display a listing of candidates with their result statuses (Super Admin).
     */
    public function adminIndex(Request $request)
    {
        $query = Student::with(['class', 'category', 'school', 'examination', 'result', 'attendances']);

        // Filter by Examination
        if ($request->filled('examination_id')) {
            $query->where('examination_id', $request->examination_id);
        }

        // Filter by School
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by Class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by Result Status
        if ($request->filled('result_status')) {
            if ($request->result_status === 'entered') {
                $query->has('result');
            } elseif ($request->result_status === 'pending') {
                $query->doesntHave('result');
            }
        }

        // Search name/reg/hall ticket
        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('hall_ticket_number', 'like', "%{$search}%");
            });
        }

        // Only show students whose registrations are approved/hall tickets issued
        $query->whereIn('status', ['Approved', 'Hall Ticket Issued']);

        $students = $query->latest()->paginate(20);
        $examinations = Examination::all();
        $schools = School::all();
        $classes = ClassMaster::all();

        return view('super-admin.results.index', compact('students', 'examinations', 'schools', 'classes'));
    }

    /**
     * Show form to enter result for a specific student.
     */
    public function create(Student $student)
    {
        if ($student->result) {
            return redirect()->route('admin.results.index')->with('error', 'Result already exists for this student. Use edit instead.');
        }

        return view('super-admin.results.create', compact('student'));
    }

    /**
     * Store result in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'marks_obtained' => ['required', 'integer', 'min:0'],
            'max_marks' => ['required', 'integer', 'min:1', 'gt:marks_obtained'],
            'grade' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:Pass,Fail,Absent,Withheld'],
            'remarks' => ['nullable', 'string'],
            'subject_names' => ['nullable', 'array'],
            'subject_marks' => ['nullable', 'array'],
            'subject_max' => ['nullable', 'array'],
        ]);

        $student = Student::findOrFail($request->student_id);

        // Process Subject Marks
        $subjectDetails = [];
        if ($request->filled('subject_names')) {
            foreach ($request->subject_names as $index => $name) {
                if (empty($name))
                    continue;

                $marks = isset($request->subject_marks[$index]) ? (int) $request->subject_marks[$index] : 0;
                $max = isset($request->subject_max[$index]) ? (int) $request->subject_max[$index] : 100;

                $subjectDetails[$name] = [
                    'marks' => $marks,
                    'max' => $max
                ];
            }
        }

        $percentage = round(($request->marks_obtained / $request->max_marks) * 100, 2);

        // Determine grade if not provided
        $grade = $request->grade;
        if (empty($grade)) {
            if ($percentage >= 90)
                $grade = 'A+';
            elseif ($percentage >= 80)
                $grade = 'A';
            elseif ($percentage >= 70)
                $grade = 'B';
            elseif ($percentage >= 60)
                $grade = 'C';
            elseif ($percentage >= 50)
                $grade = 'D';
            elseif ($percentage >= 40)
                $grade = 'E';
            else
                $grade = 'F';
        }

        $result = StudentResult::create([
            'student_id' => $student->id,
            'examination_id' => $student->examination_id,
            'marks_obtained' => $request->marks_obtained,
            'max_marks' => $request->max_marks,
            'percentage' => $percentage,
            'grade' => $grade,
            'status' => $request->status,
            'subject_marks' => $subjectDetails,
            'remarks' => $request->remarks,
        ]);

        activity()
            ->performedOn($result)
            ->log("Entered exam result for student: {$student->name} (Reg No: {$student->registration_number})");

        return redirect()->route('admin.results.index')->with('success', 'Exam result saved successfully.');
    }

    /**
     * Show form to edit result.
     */
    public function edit(StudentResult $result)
    {
        $student = $result->student;
        return view('super-admin.results.edit', compact('result', 'student'));
    }

    /**
     * Update result.
     */
    public function update(Request $request, StudentResult $result)
    {
        $validated = $request->validate([
            'marks_obtained' => ['required', 'integer', 'min:0'],
            'max_marks' => ['required', 'integer', 'min:1', 'gt:marks_obtained'],
            'grade' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:Pass,Fail,Absent,Withheld'],
            'remarks' => ['nullable', 'string'],
            'subject_names' => ['nullable', 'array'],
            'subject_marks' => ['nullable', 'array'],
            'subject_max' => ['nullable', 'array'],
        ]);

        // Process Subject Marks
        $subjectDetails = [];
        if ($request->filled('subject_names')) {
            foreach ($request->subject_names as $index => $name) {
                if (empty($name))
                    continue;

                $marks = isset($request->subject_marks[$index]) ? (int) $request->subject_marks[$index] : 0;
                $max = isset($request->subject_max[$index]) ? (int) $request->subject_max[$index] : 100;

                $subjectDetails[$name] = [
                    'marks' => $marks,
                    'max' => $max
                ];
            }
        }

        $percentage = round(($request->marks_obtained / $request->max_marks) * 100, 2);

        // Determine grade if not provided
        $grade = $request->grade;
        if (empty($grade)) {
            if ($percentage >= 90)
                $grade = 'A+';
            elseif ($percentage >= 80)
                $grade = 'A';
            elseif ($percentage >= 70)
                $grade = 'B';
            elseif ($percentage >= 60)
                $grade = 'C';
            elseif ($percentage >= 50)
                $grade = 'D';
            elseif ($percentage >= 40)
                $grade = 'E';
            else
                $grade = 'F';
        }

        $result->update([
            'marks_obtained' => $request->marks_obtained,
            'max_marks' => $request->max_marks,
            'percentage' => $percentage,
            'grade' => $grade,
            'status' => $request->status,
            'subject_marks' => $subjectDetails,
            'remarks' => $request->remarks,
        ]);

        activity()
            ->performedOn($result)
            ->log("Updated exam result for student: {$result->student->name}");

        return redirect()->route('admin.results.index')->with('success', 'Exam result updated successfully.');
    }

    /**
     * Delete result.
     */
    public function destroy(StudentResult $result)
    {
        $studentName = $result->student->name;

        activity()
            ->performedOn($result)
            ->log("Deleted exam result for student: {$studentName}");

        $result->delete();

        return redirect()->route('admin.results.index')->with('success', "Exam result for {$studentName} deleted successfully.");
    }

    /**
     * Show CSV import form.
     */
    public function showImportForm()
    {
        $examinations = Examination::all();
        return view('super-admin.results.import', compact('examinations'));
    }

    /**
     * Import results from CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'examination_id' => ['required', 'exists:examinations,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $exam = Examination::findOrFail($request->examination_id);
        $file = $request->file('csv_file');

        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Unable to open uploaded file.');
        }

        // Parse header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'CSV file is empty.');
        }

        // Clean headers
        $header = array_map(function ($h) {
            return trim(strtolower(str_replace([' ', '_'], '', $h)));
        }, $header);

        // Find positions
        $regIdx = array_search('registrationnumber', $header);
        if ($regIdx === false)
            $regIdx = array_search('registrationno', $header);
        if ($regIdx === false)
            $regIdx = array_search('regno', $header);

        $htIdx = array_search('hallticketnumber', $header);
        if ($htIdx === false)
            $htIdx = array_search('hallticketno', $header);
        if ($htIdx === false)
            $htIdx = array_search('htnumber', $header);

        $obtainedIdx = array_search('marksobtained', $header);
        if ($obtainedIdx === false)
            $obtainedIdx = array_search('marks', $header);
        if ($obtainedIdx === false)
            $obtainedIdx = array_search('score', $header);

        $maxIdx = array_search('maxmarks', $header);
        if ($maxIdx === false)
            $maxIdx = array_search('max', $header);
        if ($maxIdx === false)
            $maxIdx = array_search('totalmarks', $header);

        $gradeIdx = array_search('grade', $header);
        $statusIdx = array_search('status', $header);
        $remarksIdx = array_search('remarks', $header);

        // Must match either Reg No or Hall Ticket, along with marks & max marks
        if (($regIdx === false && $htIdx === false) || $obtainedIdx === false || $maxIdx === false) {
            fclose($handle);
            return back()->with('error', 'CSV must contain columns: "Registration Number" (or "Hall Ticket Number"), "Marks Obtained" and "Max Marks".');
        }

        $rowNumber = 1;
        $createdCount = 0;
        $updatedCount = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (empty($row) || count($row) < 3)
                continue;

            $regVal = $regIdx !== false ? trim($row[$regIdx]) : '';
            $htVal = $htIdx !== false ? trim($row[$htIdx]) : '';
            $obtainedVal = trim($row[$obtainedIdx]);
            $maxVal = trim($row[$maxIdx]);

            if (empty($regVal) && empty($htVal)) {
                $errors[] = "Row {$rowNumber}: Missing student identification (both Registration and Hall Ticket Number are empty).";
                continue;
            }

            if (!is_numeric($obtainedVal) || !is_numeric($maxVal)) {
                $errors[] = "Row {$rowNumber}: Marks must be numeric values.";
                continue;
            }

            $obtained = (int) $obtainedVal;
            $max = (int) $maxVal;
            if ($max <= 0 || $obtained < 0 || $obtained > $max) {
                $errors[] = "Row {$rowNumber}: Invalid marks score range ({$obtained} out of {$max}).";
                continue;
            }

            // Find Student
            $student = Student::where('examination_id', $exam->id)
                ->where(function ($q) use ($regVal, $htVal) {
                    if (!empty($regVal))
                        $q->where('registration_number', $regVal);
                    if (!empty($htVal))
                        $q->orWhere('hall_ticket_number', $htVal);
                })->first();

            if (!$student) {
                $errors[] = "Row {$rowNumber}: Student not found registered in this exam session (Reg: '{$regVal}' / HT: '{$htVal}').";
                continue;
            }

            // Check if result already exists and handle overwrite option
            $existingResult = StudentResult::where('student_id', $student->id)->first();
            if ($existingResult && !$request->boolean('overwrite_existing')) {
                $errors[] = "Row {$rowNumber}: Student (Reg: '{$student->registration_number}') already has a result record. Select 'Overwrite existing student results' option to overwrite.";
                continue;
            }

            // Grade
            $grade = $gradeIdx !== false ? trim($row[$gradeIdx]) : '';
            $percentage = round(($obtained / $max) * 100, 2);
            if (empty($grade)) {
                if ($percentage >= 90)
                    $grade = 'A+';
                elseif ($percentage >= 80)
                    $grade = 'A';
                elseif ($percentage >= 70)
                    $grade = 'B';
                elseif ($percentage >= 60)
                    $grade = 'C';
                elseif ($percentage >= 50)
                    $grade = 'D';
                elseif ($percentage >= 40)
                    $grade = 'E';
                else
                    $grade = 'F';
            }

            // Status
            $status = $statusIdx !== false ? trim($row[$statusIdx]) : '';
            if (empty($status) || !in_array($status, ['Pass', 'Fail', 'Absent', 'Withheld'])) {
                $status = ($percentage >= 35) ? 'Pass' : 'Fail';
            }

            $remarks = $remarksIdx !== false ? trim($row[$remarksIdx]) : '';

            // Update or Create
            StudentResult::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'examination_id' => $exam->id,
                    'marks_obtained' => $obtained,
                    'max_marks' => $max,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'status' => $status,
                    'remarks' => $remarks
                ]
            );

            if ($existingResult) {
                $updatedCount++;
            } else {
                $createdCount++;
            }
        }

        fclose($handle);

        $successCount = $createdCount + $updatedCount;
        $msgDetails = "Processed {$successCount} results successfully ({$createdCount} created" . ($updatedCount > 0 ? ", {$updatedCount} updated" : "") . ").";

        activity()->log("Bulk imported {$successCount} exam results for examination: {$exam->name} via CSV (Created: {$createdCount}, Updated: {$updatedCount})");

        if (!empty($errors)) {
            $msg = "{$msgDetails} However, encountered issues on some rows:";
            return redirect()->route('admin.results.index')
                ->with('success', $msg)
                ->withErrors($errors);
        }

        return redirect()->route('admin.results.index')->with('success', "{$msgDetails}");
    }

    /**
     * Show public search form.
     */
    public function showPublicCheckForm()
    {
        $examinations = Examination::where('status', 'result published')->get();
        return view('public.results.check', compact('examinations'));
    }

    /**
     * Validate credentials and authorize result viewing.
     */
    public function checkPublicResult(Request $request)
    {
        $request->validate([
            'examination_id' => ['required', 'exists:examinations,id'],
            'search_number' => ['required', 'string'],
            'dob' => ['required', 'date'],
        ]);

        $exam = Examination::findOrFail($request->examination_id);
        if ($exam->status !== 'result published') {
            return back()->with('error', 'Results for this examination session have not been published yet.')->withInput();
        }

        $student = Student::where('examination_id', $request->examination_id)
            ->where(function ($q) use ($request) {
                $q->where('registration_number', $request->search_number)
                    ->orWhere('hall_ticket_number', $request->search_number);
            })->first();

        // 1. Basic matching validation
        if (!$student || $student->dob->format('Y-m-d') !== $request->dob) {
            return back()->with('error', 'No candidate records match the entered details. Please check the Registration/Hall Ticket Number and Date of Birth.')->withInput();
        }

        // 2. Check if results have been posted
        if (!$student->result) {
            return back()->with('error', 'Exam results for this candidate have not been declared yet or are withheld.')->withInput();
        }

        // 3. Bind authorization to the exact credentials supplied, not just the student ID.
        // Using HMAC-SHA256 keyed with APP_KEY means the token cannot be forged by anyone
        // who only knows the student ID — they must also supply the correct DOB, and cannot
        // construct the MAC without the application secret (CWE-807 remediation).
        $authToken = hash_hmac(
            'sha256',
            $student->registration_number . '|' . $student->dob->format('Y-m-d') . '|' . $student->id,
            config('app.key')
        );
        session(['result_auth_token' => $authToken]);

        return redirect()->route('results.marksheet', $student->id);
    }

    /**
     * Display the official marksheet (Public).
     */
    public function showPublicResult(Student $student)
    {
        // Recompute the expected HMAC for this student using the same inputs used during
        // the search submission. If the session token does not match, the visitor either
        // never searched for this student or tampered with the URL / session.
        // This prevents unauthorized access even if a session ID is leaked (CWE-807).
        $student->load(['result', 'school', 'class', 'category', 'examination']);

        $expectedToken = hash_hmac(
            'sha256',
            $student->registration_number . '|' . $student->dob->format('Y-m-d') . '|' . $student->id,
            config('app.key')
        );

        if (!hash_equals($expectedToken, (string) session('result_auth_token', ''))) {
            return redirect()->route('results.check-form')
                ->with('error', 'Unauthorized access. Please query candidate details from this search portal.');
        }

        if ($student->examination->status !== 'result published') {
            return redirect()->route('results.check-form')
                ->with('error', 'Results for this examination session have not been published yet.');
        }

        $result = $student->result;

        return view('public.results.show', compact('student', 'result'));
    }

    /**
     * Display a listing of results for students registered by the logged-in school.
     */
    public function schoolIndex(Request $request)
    {
        $school = auth()->user()->school;

        if (!$school) {
            return redirect()->route('school.dashboard')->with('error', 'School account profile not found.');
        }

        // Query students registered BY THIS SCHOOL (school_id = school->id), NOT by exam centre
        $query = Student::with(['class', 'category', 'examination', 'result'])
            ->where('school_id', $school->id);

        // Filter by Examination
        if ($request->filled('examination_id')) {
            $query->where('examination_id', $request->examination_id);
        }

        // Filter by Class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by Result Status
        if ($request->filled('result_status')) {
            if ($request->result_status === 'pending') {
                $query->doesntHave('result');
            } else {
                $query->whereHas('result', function ($q) use ($request) {
                    $q->where('status', $request->result_status);
                });
            }
        }

        // Search name / reg / hall ticket
        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('hall_ticket_number', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(20);
        $examinations = Examination::all();
        $classes = ClassMaster::all();

        // Stats summary for results declared for this school
        $totalRegistered = Student::where('school_id', $school->id)->count();
        $resultsDeclared = StudentResult::whereHas('student', fn($q) => $q->where('school_id', $school->id))->count();
        $passedCount = StudentResult::whereHas('student', fn($q) => $q->where('school_id', $school->id))->where('status', 'Pass')->count();
        $failedCount = StudentResult::whereHas('student', fn($q) => $q->where('school_id', $school->id))->where('status', 'Fail')->count();
        $passPercentage = $resultsDeclared > 0 ? round(($passedCount / $resultsDeclared) * 100, 1) : 0;

        return view('school-admin.results.index', compact(
            'students',
            'examinations',
            'classes',
            'totalRegistered',
            'resultsDeclared',
            'passedCount',
            'failedCount',
            'passPercentage'
        ));
    }

    /**
     * Show official marksheet for a student registered by the logged-in school.
     */
    public function schoolMarksheet(Student $student)
    {
        $school = auth()->user()->school;

        if (!$school || $student->school_id !== $school->id) {
            abort(403, 'Unauthorized access to candidate details from another institution.');
        }

        if (!$student->result) {
            return back()->with('error', 'Exam results for this student have not been declared yet.');
        }

        $student->load(['result', 'school', 'class', 'category', 'examination']);
        $result = $student->result;

        return view('public.results.show', compact('student', 'result'));
    }
}
