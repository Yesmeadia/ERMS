<?php

namespace App\Http\Controllers;

use App\Exports\ResultTemplateExport;
use App\Models\CategoryMaster;
use App\Models\ClassMaster;
use App\Models\Examination;
use App\Models\ResultBatch;
use App\Models\ResultPdfPart;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\AppSetting;
use App\Services\ResultReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ResultController extends Controller
{
    protected ResultReportService $reportService;

    public function __construct(ResultReportService $reportService)
    {
        $this->reportService = $reportService;
    }

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

        // Filter by Zone
        if ($request->filled('zone')) {
            $query->whereHas('school', function ($q) use ($request) {
                $q->where('zone', $request->zone);
            });
        }

        // Filter by School
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by Class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by Gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filter by Result Status
        if ($request->filled('result_status')) {
            if ($request->result_status === 'entered') {
                $query->has('result');
            } elseif ($request->result_status === 'pending') {
                $query->doesntHave('result');
            } elseif (in_array($request->result_status, ['Pass', 'Fail', 'Withheld'])) {
                $query->whereHas('result', fn ($q) => $q->where('status', $request->result_status));
            } elseif ($request->result_status === 'Absent') {
                $query->where(function ($q) {
                    $q->whereHas('result', fn ($rq) => $rq->where('status', 'Absent'))
                        ->orWhere(function ($sq) {
                            $sq->doesntHave('result')
                                ->whereDoesntHave('attendances', fn ($aq) => $aq->where('attendance_date', '2026-08-30')->where('status', 'Present'));
                        });
                });
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
        $schools = School::orderBy('name')->get();
        $classes = ClassMaster::all();
        $categories = CategoryMaster::all();
        $zones = School::whereNotNull('zone')->where('zone', '!=', '')->distinct()->orderBy('zone')->pluck('zone');

        $recentBatches = ResultBatch::where('scope', 'admin')
            ->with(['examination', 'parts'])
            ->latest()
            ->take(10)
            ->get();

        return view('super-admin.results.index', compact('students', 'examinations', 'schools', 'classes', 'categories', 'zones', 'recentBatches'));
    }

    /**
     * Show form to enter result for a specific student.
     */
    public function create(Student $student)
    {
        if ($student->result) {
            return redirect()->route('admin.results.index')->with('error', 'Result already exists for this student. Use edit instead.');
        }

        $defaultMaxMarks = StudentResult::getDefaultMaxMarks($student->category ? $student->category->name : null);

        return view('super-admin.results.create', compact('student', 'defaultMaxMarks'));
    }

    /**
     * Store result in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'marks_obtained' => ['required', 'integer', 'min:0'],
            'max_marks' => ['required', 'integer', 'min:1', 'gte:marks_obtained'],
            'grade' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'subject_names' => ['nullable', 'array'],
            'subject_marks' => ['nullable', 'array'],
            'subject_max' => ['nullable', 'array'],
        ]);

        $student = Student::findOrFail($request->student_id);

        // Process Subject Marks
        $subjectDetails = [];
        if ($request->filled('subject_names')) {
            foreach ($request->subject_names as $index => $name) {
                if (empty($name)) {
                    continue;
                }

                $marks = isset($request->subject_marks[$index]) ? (int) $request->subject_marks[$index] : 0;
                $max = isset($request->subject_max[$index]) ? (int) $request->subject_max[$index] : 100;

                $subjectDetails[$name] = [
                    'marks' => $marks,
                    'max' => $max,
                ];
            }
        }

        $percentage = round(($request->marks_obtained / $request->max_marks) * 100, 2);

        // Determine grade according to category thresholds if not provided
        $grade = $request->grade;
        if (empty($grade)) {
            $grade = StudentResult::calculateGrade($percentage, $student->category ? $student->category->name : null);
        }

        $status = $request->filled('status') ? $request->status : 'Pass';
        $remarks = $request->filled('remarks') ? trim($request->remarks) : null;

        $existingResult = StudentResult::withTrashed()->where('student_id', $student->id)->first();

        if ($existingResult) {
            $existingResult->restore();
            $existingResult->update([
                'examination_id' => $student->examination_id,
                'marks_obtained' => $request->marks_obtained,
                'max_marks' => $request->max_marks,
                'percentage' => $percentage,
                'grade' => $grade,
                'status' => $status,
                'subject_marks' => $subjectDetails,
                'remarks' => $remarks,
            ]);
            $result = $existingResult;
        } else {
            $result = StudentResult::create([
                'student_id' => $student->id,
                'examination_id' => $student->examination_id,
                'marks_obtained' => $request->marks_obtained,
                'max_marks' => $request->max_marks,
                'percentage' => $percentage,
                'grade' => $grade,
                'status' => $status,
                'subject_marks' => $subjectDetails,
                'remarks' => $remarks,
            ]);
        }

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
            'max_marks' => ['required', 'integer', 'min:1', 'gte:marks_obtained'],
            'grade' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'subject_names' => ['nullable', 'array'],
            'subject_marks' => ['nullable', 'array'],
            'subject_max' => ['nullable', 'array'],
        ]);

        // Process Subject Marks
        $subjectDetails = [];
        if ($request->filled('subject_names')) {
            foreach ($request->subject_names as $index => $name) {
                if (empty($name)) {
                    continue;
                }

                $marks = isset($request->subject_marks[$index]) ? (int) $request->subject_marks[$index] : 0;
                $max = isset($request->subject_max[$index]) ? (int) $request->subject_max[$index] : 100;

                $subjectDetails[$name] = [
                    'marks' => $marks,
                    'max' => $max,
                ];
            }
        }

        $percentage = round(($request->marks_obtained / $request->max_marks) * 100, 2);

        // Determine grade according to category thresholds if not provided
        $grade = $request->grade;
        if (empty($grade)) {
            $student = $result->student;
            $grade = StudentResult::calculateGrade($percentage, $student && $student->category ? $student->category->name : null);
        }

        $status = $request->filled('status') ? $request->status : ($result->status ?? 'Pass');
        $remarks = $request->filled('remarks') ? trim($request->remarks) : null;

        $result->update([
            'marks_obtained' => $request->marks_obtained,
            'max_marks' => $request->max_marks,
            'percentage' => $percentage,
            'grade' => $grade,
            'status' => $status,
            'subject_marks' => $subjectDetails,
            'remarks' => $remarks,
        ]);

        $sName = $result->student?->name ?? 'Student';
        activity()
            ->performedOn($result)
            ->log("Updated exam result for student: {$sName}");

        return redirect()->route('admin.results.index')->with('success', 'Exam result updated successfully.');
    }

    /**
     * Delete result.
     */
    public function destroy(StudentResult $result)
    {
        $studentName = $result->student->name ?? 'Student';

        activity()
            ->performedOn($result)
            ->log("Deleted exam result for student: {$studentName}");

        $result->forceDelete();

        return redirect()->route('admin.results.index')->with('success', "Exam result for {$studentName} deleted successfully.");
    }

    /**
     * Show Excel/CSV import form.
     */
    public function showImportForm(Request $request)
    {
        $examinations = Examination::all();
        $selectedExamId = $request->input('examination_id');
        if (! $selectedExamId && $examinations->count() > 0) {
            $activeExam = Examination::getActiveExam();
            $selectedExamId = $activeExam ? $activeExam->id : $examinations->first()->id;
        }

        // Fetch sample candidates for dynamic preview
        $previewCandidates = Student::where('examination_id', $selectedExamId)
            ->whereIn('status', ['Hall Ticket Issued', 'Approved'])
            ->with(['class', 'category', 'school'])
            ->take(5)
            ->get();

        return view('super-admin.results.import', compact('examinations', 'selectedExamId', 'previewCandidates'));
    }

    /**
     * Download sample Excel or CSV template for exam results bulk import.
     */
    public function downloadTemplate(Request $request)
    {
        $examinationId = $request->input('examination_id');
        $format = strtolower((string) $request->input('format', 'excel'));

        if ($format === 'csv') {
            return Excel::download(new ResultTemplateExport($examinationId), 'erms_exam_results_sample_template.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new ResultTemplateExport($examinationId), 'erms_exam_results_sample_template.xlsx');
    }

    /**
     * Import results from Excel (.xlsx, .xls) or CSV (.csv).
     */
    public function import(Request $request)
    {
        $request->validate([
            'examination_id' => ['required', 'exists:examinations,id'],
            'result_file' => ['nullable', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
            'csv_file' => ['nullable', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ]);

        $file = $request->file('result_file') ?? $request->file('csv_file');
        if (! $file) {
            return back()->with('error', 'Please select a valid Excel (.xlsx, .xls) or CSV (.csv) file to upload.');
        }

        $exam = Examination::findOrFail($request->examination_id);

        try {
            $data = Excel::toArray(new \stdClass, $file);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to parse uploaded spreadsheet: '.$e->getMessage());
        }

        if (empty($data) || empty($data[0])) {
            return back()->with('error', 'The uploaded file is empty or has no readable data sheets.');
        }

        $sheet = $data[0];
        $rawHeader = array_shift($sheet);

        if (empty($rawHeader)) {
            return back()->with('error', 'Uploaded spreadsheet does not contain a header row.');
        }

        // Clean headers: remove spaces, underscores, dashes and lowercase
        $header = array_map(function ($h) {
            return trim(strtolower(str_replace([' ', '_', '-'], '', (string) $h)));
        }, $rawHeader);

        // Find column indices
        $regIdx = false;
        foreach (['registrationnumber', 'registrationno', 'regno', 'regnumber', 'reg'] as $col) {
            $idx = array_search($col, $header);
            if ($idx !== false) {
                $regIdx = $idx;
                break;
            }
        }

        $htIdx = false;
        foreach (['hallticketnumber', 'hallticketno', 'htnumber', 'htno', 'hallticket', 'ht'] as $col) {
            $idx = array_search($col, $header);
            if ($idx !== false) {
                $htIdx = $idx;
                break;
            }
        }

        $obtainedIdx = false;
        foreach (['marksobtained', 'marks', 'score', 'obtainedmarks', 'securedmarks', 'obtained'] as $col) {
            $idx = array_search($col, $header);
            if ($idx !== false) {
                $obtainedIdx = $idx;
                break;
            }
        }

        $maxIdx = false;
        foreach (['maxmarks', 'max', 'totalmarks', 'maximummarks', 'total'] as $col) {
            $idx = array_search($col, $header);
            if ($idx !== false) {
                $maxIdx = $idx;
                break;
            }
        }

        $gradeIdx = array_search('grade', $header);
        $statusIdx = false;
        foreach (['status', 'resultstatus', 'result'] as $col) {
            $idx = array_search($col, $header);
            if ($idx !== false) {
                $statusIdx = $idx;
                break;
            }
        }

        $remarksIdx = false;
        foreach (['remarks', 'remark', 'comments', 'comment'] as $col) {
            $idx = array_search($col, $header);
            if ($idx !== false) {
                $remarksIdx = $idx;
                break;
            }
        }

        // Must match either Reg No or Hall Ticket, along with marks & max marks
        if (($regIdx === false && $htIdx === false) || $obtainedIdx === false || $maxIdx === false) {
            return back()->with('error', 'Spreadsheet must contain columns: "Registration Number" (or "Hall Ticket Number"), "Marks Obtained" and "Max Marks". Please use the downloadable sample template.');
        }

        $rowNumber = 1;
        $createdCount = 0;
        $updatedCount = 0;
        $errors = [];

        foreach ($sheet as $row) {
            $rowNumber++;

            // Skip empty rows
            $hasContent = false;
            foreach ($row as $cell) {
                if ($cell !== null && trim((string) $cell) !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if (! $hasContent) {
                continue;
            }

            $regVal = ($regIdx !== false && isset($row[$regIdx])) ? trim((string) $row[$regIdx]) : '';
            $htVal = ($htIdx !== false && isset($row[$htIdx])) ? trim((string) $row[$htIdx]) : '';
            $obtainedVal = ($obtainedIdx !== false && isset($row[$obtainedIdx])) ? trim((string) $row[$obtainedIdx]) : '';
            $maxVal = ($maxIdx !== false && isset($row[$maxIdx])) ? trim((string) $row[$maxIdx]) : '';

            if (empty($regVal) && empty($htVal)) {
                $errors[] = "Row {$rowNumber}: Missing student identification (both Registration and Hall Ticket Number are empty).";

                continue;
            }

            // Find Student first to validate marks against candidate's category
            $student = Student::where('examination_id', $exam->id)
                ->where(function ($q) use ($regVal, $htVal) {
                    if (! empty($regVal)) {
                        $q->where('registration_number', $regVal);
                    }
                    if (! empty($htVal)) {
                        $q->orWhere('hall_ticket_number', $htVal);
                    }
                })
                ->with(['category', 'class', 'school'])
                ->first();

            if (! $student) {
                $identifier = ! empty($regVal) ? "Reg: '{$regVal}'" : "HT: '{$htVal}'";
                $errors[] = "Row {$rowNumber}: Candidate not found in exam session '{$exam->name}' ({$identifier}).";

                continue;
            }

            $categoryName = $student->category ? $student->category->name : 'General';
            $expectedMax = StudentResult::getDefaultMaxMarks($categoryName);

            // Validate / determine Max Marks according to category rules
            if ($maxVal === '' || $maxVal === null) {
                $max = $expectedMax;
            } else {
                if (! is_numeric($maxVal) || (int) $maxVal <= 0) {
                    $errors[] = "Row {$rowNumber}: Max marks for student '{$student->name}' must be a valid positive number ('{$maxVal}' provided).";

                    continue;
                }
                $max = (int) $maxVal;
                if ($max !== $expectedMax) {
                    $errors[] = "Row {$rowNumber}: Invalid Max Marks '{$max}' for student '{$student->name}'. Category '{$categoryName}' requires Maximum Marks = {$expectedMax}.";

                    continue;
                }
            }

            // Validate Marks Obtained
            if ($obtainedVal === '' || ! is_numeric($obtainedVal)) {
                $errors[] = "Row {$rowNumber}: Marks obtained for student '{$student->name}' must be a valid numeric score ('{$obtainedVal}' provided).";

                continue;
            }

            $obtained = (int) $obtainedVal;
            if ($obtained < 0) {
                $errors[] = "Row {$rowNumber}: Marks obtained cannot be negative for student '{$student->name}' ({$obtained} provided).";

                continue;
            }

            if ($obtained > $max) {
                $errors[] = "Row {$rowNumber}: Marks obtained ({$obtained}) exceeds Maximum Marks ({$max}) for student '{$student->name}' in category '{$categoryName}'.";

                continue;
            }

            // Check if result already exists (including soft-deleted)
            $existingResult = StudentResult::withTrashed()->where('student_id', $student->id)->first();
            if ($existingResult && ! $existingResult->trashed() && ! $request->boolean('overwrite_existing')) {
                $errors[] = "Row {$rowNumber}: Student '{$student->name}' (Reg: '{$student->registration_number}') already has a result record. Select 'Overwrite existing results' to update.";

                continue;
            }

            // Grade auto calculation based on category thresholds
            $grade = ($gradeIdx !== false && isset($row[$gradeIdx])) ? trim((string) $row[$gradeIdx]) : '';
            $percentage = round(($obtained / $max) * 100, 2);
            if (empty($grade)) {
                $grade = StudentResult::calculateGrade($percentage, $categoryName);
            } else {
                $grade = strtoupper($grade);
            }

            // Status
            $status = ($statusIdx !== false && isset($row[$statusIdx])) ? trim((string) $row[$statusIdx]) : '';
            if (empty($status) || ! in_array($status, ['Pass', 'Fail', 'Absent', 'Withheld'])) {
                $status = 'Pass';
            }

            $remarks = ($remarksIdx !== false && isset($row[$remarksIdx])) ? trim((string) $row[$remarksIdx]) : null;
            if ($remarks === '') {
                $remarks = null;
            }

            if ($existingResult) {
                $existingResult->restore();
                $existingResult->update([
                    'examination_id' => $exam->id,
                    'marks_obtained' => $obtained,
                    'max_marks' => $max,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'status' => $status,
                    'remarks' => $remarks,
                ]);
                $updatedCount++;
            } else {
                StudentResult::create([
                    'student_id' => $student->id,
                    'examination_id' => $exam->id,
                    'marks_obtained' => $obtained,
                    'max_marks' => $max,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'status' => $status,
                    'remarks' => $remarks,
                ]);
                $createdCount++;
            }
        }

        $successCount = $createdCount + $updatedCount;
        $msgDetails = "Processed {$successCount} results successfully ({$createdCount} created".($updatedCount > 0 ? ", {$updatedCount} updated" : '').').';

        activity()->log("Bulk imported {$successCount} exam results for examination: {$exam->name} (Created: {$createdCount}, Updated: {$updatedCount})");

        if (! empty($errors)) {
            $msg = "{$msgDetails} However, encountered issues on some rows:";

            return redirect()->route('admin.results.index')
                ->with('success', $msg)
                ->withErrors($errors);
        }

        return redirect()->route('admin.results.index')->with('success', $msgDetails);
    }

    /**
     * Show public search form.
     */
    public function showPublicCheckForm()
    {
        $examinations = Examination::where('status', 'result published')->get();

        $releaseUtc = AppSetting::resultReleaseDatetime();
        $released   = AppSetting::resultsReleased();
        $releaseIso = $releaseUtc->toIso8601String();
        $releaseIst = $releaseUtc->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A');

        return view('public.results.check', compact('examinations', 'released', 'releaseIso', 'releaseIst'));
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

        if (! AppSetting::resultsReleased()) {
            $releaseIst = AppSetting::resultReleaseDatetime()->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A');
            return back()->with('error', "Results have not been released yet. Official results will be published on {$releaseIst} IST.")->withInput();
        }

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
        if (! $student || Carbon::parse($student->dob)->format('Y-m-d') !== $request->dob) {
            return back()->with('error', 'No candidate records match the entered details. Please check the Registration/Hall Ticket Number and Date of Birth.')->withInput();
        }

        // 2. Check if results have been posted or candidate was absent
        if (! $student->result) {
            // Check if student was absent on exam day (30/08/2026)
            $isAbsent = $student->attendances()
                ->where('attendance_date', '2026-08-30')
                ->where('status', 'Present')
                ->count() === 0;

            if ($isAbsent) {
                return back()->with('error', 'Candidate was Absent for the examination. Result status: Absent.')->withInput();
            }

            return back()->with('error', 'Exam results for this candidate have not been declared yet or are withheld.')->withInput();
        }

        // 3. Bind authorization to the exact credentials supplied, not just the student ID.
        // Using HMAC-SHA256 keyed with APP_KEY means the token cannot be forged by anyone
        // who only knows the student ID — they must also supply the correct DOB, and cannot
        // construct the MAC without the application secret (CWE-807 remediation).
        $authToken = hash_hmac(
            'sha256',
            $student->registration_number.'|'.$student->dob->format('Y-m-d').'|'.$student->id,
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
            $student->registration_number.'|'.$student->dob->format('Y-m-d').'|'.$student->id,
            config('app.key')
        );

        if (! hash_equals($expectedToken, (string) session('result_auth_token', ''))) {
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
     * Helper to safely get base64 encoded image for PDF exports.
     */
    /**
     * Dispatch background Result PDF generation for Super Admin.
     */
    public function adminExportPdf(Request $request)
    {
        try {
            $examinationId = $request->filled('examination_id') ? (int) $request->examination_id : null;
            $batch = $this->reportService->createAndDispatchBatch(
                'admin',
                null,
                $examinationId,
                auth()->id() ?? 1,
                $request->all()
            );

            return redirect()->route('admin.results.batches.show', $batch)
                ->with('success', 'Result statement generation started in the background.');
        } catch (ValidationException $e) {
            return redirect()->route('admin.results.index')
                ->withErrors($e->errors());
        } catch (\Throwable $e) {
            Log::error('Super Admin PDF Export Dispatch Failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()->route('admin.results.index')->with('error', 'Unable to initiate results export: '.$e->getMessage());
        }
    }

    /**
     * Super Admin Batch Progress View.
     */
    public function showAdminBatch(ResultBatch $batch)
    {
        Gate::authorize('view', $batch);

        $batch->load(['school', 'examination', 'parts']);

        $initialParts = $batch->parts->map(fn ($p) => [
            'id' => $p->id,
            'part_number' => $p->part_number,
            'total_students' => $p->total_students,
            'completed_students' => $p->completed_students,
            'status' => $p->status,
            'file_size_formatted' => $p->file_size ? number_format($p->file_size / 1024, 1).' KB' : null,
            'error_message' => $p->error_message,
            'download_url' => ($p->status === 'completed' && ! empty($p->pdf_path))
                ? route('admin.results.parts.download', $p)
                : null,
        ]);

        return view('super-admin.results.batch-progress', compact('batch', 'initialParts'));
    }

    /**
     * School Admin Batch Progress View.
     */
    public function showSchoolBatch(ResultBatch $batch)
    {
        Gate::authorize('view', $batch);

        $batch->load(['school', 'examination', 'parts']);

        $initialParts = $batch->parts->map(fn ($p) => [
            'id' => $p->id,
            'part_number' => $p->part_number,
            'total_students' => $p->total_students,
            'completed_students' => $p->completed_students,
            'status' => $p->status,
            'file_size_formatted' => $p->file_size ? number_format($p->file_size / 1024, 1).' KB' : null,
            'error_message' => $p->error_message,
            'download_url' => ($p->status === 'completed' && ! empty($p->pdf_path))
                ? route('school.results.parts.download', $p)
                : null,
        ]);

        return view('school-admin.results.batch-progress', compact('batch', 'initialParts'));
    }

    /**
     * Batch Status JSON Polling Endpoint.
     */
    public function batchStatus(ResultBatch $batch)
    {
        Gate::authorize('view', $batch);

        $parts = $batch->parts()->get(['id', 'batch_id', 'part_number', 'total_students', 'completed_students', 'status', 'pdf_path', 'file_size', 'error_message']);
        $isSuperAdmin = auth()->user()->hasRole('super-admin');

        $partsData = $parts->map(function ($part) use ($isSuperAdmin) {
            $downloadUrl = null;
            if ($part->status === 'completed' && ! empty($part->pdf_path)) {
                $downloadUrl = $isSuperAdmin
                    ? route('admin.results.parts.download', $part)
                    : route('school.results.parts.download', $part);
            }

            return [
                'id' => $part->id,
                'part_number' => $part->part_number,
                'total_students' => $part->total_students,
                'completed_students' => $part->completed_students,
                'status' => $part->status,
                'file_size_formatted' => $part->file_size ? number_format($part->file_size / 1024, 1).' KB' : null,
                'error_message' => $part->error_message,
                'download_url' => $downloadUrl,
            ];
        });

        return response()->json([
            'batch_id' => $batch->id,
            'batch_uuid' => $batch->batch_uuid,
            'status' => $batch->status,
            'total_students' => $batch->total_students,
            'completed_students' => $batch->completed_students,
            'failed_students' => $batch->failed_students,
            'total_parts' => $batch->total_parts,
            'completed_parts' => $batch->completed_parts,
            'failed_parts' => $batch->failed_parts,
            'progress' => $batch->progress_percentage,
            'download_available' => $batch->completed_parts > 0,
            'has_downloadable_parts' => $batch->completed_parts > 0,
            'is_completed' => $batch->isCompleted(),
            'parts' => $partsData,
        ]);
    }

    /**
     * Secure Download for an individual completed PDF Part.
     */
    public function downloadPart(ResultPdfPart $part)
    {
        Gate::authorize('download', $part);

        $batch = $part->batch;
        if (! $batch) {
            abort(404, 'Associated result batch not found.');
        }

        if ($part->status !== 'completed' || empty($part->pdf_path)) {
            return back()->with('error', 'This PDF part is not yet ready for download.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('results.disk', 'local'));
        if (! $disk->exists($part->pdf_path)) {
            return back()->with('error', 'The requested PDF part file was not found or has expired.');
        }

        $prefix = ($batch->scope === 'admin') ? 'Consolidated_Results' : ($batch->school?->code ?? 'School_Results');
        $downloadFilename = "{$prefix}_Part_{$part->part_number}_".date('Ymd_His').'.pdf';

        return $disk->download($part->pdf_path, $downloadFilename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$downloadFilename.'"',
        ]);
    }

    /**
     * Retry failed parts in a ResultBatch.
     */
    public function retryBatch(ResultBatch $batch)
    {
        Gate::authorize('retry', $batch);

        try {
            $this->reportService->retryFailedParts($batch);

            return back()->with('success', 'Failed PDF parts have been queued for retry.');
        } catch (\Throwable $e) {
            Log::error("Failed to retry ResultBatch #{$batch->id}: ".$e->getMessage());

            return back()->with('error', 'Could not retry failed parts: '.$e->getMessage());
        }
    }

    /**
     * Display a listing of results for students registered by the logged-in school.
     * Only reveals result data and statistics when examination status is "result published".
     */
    public function schoolIndex(Request $request)
    {
        $school = auth()->user()->school;

        if (! $school) {
            return redirect()->route('school.dashboard')->with('error', 'School account profile not found.');
        }

        // Query students registered BY THIS SCHOOL (school_id = school->id)
        $query = Student::with(['class', 'category', 'examination', 'result', 'attendances'])
            ->where('school_id', $school->id);

        // Filter by Examination
        if ($request->filled('examination_id')) {
            $query->where('examination_id', $request->examination_id);
        }

        // Filter by Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by Class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by Gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filter by Result Status
        if ($request->filled('result_status')) {
            if ($request->result_status === 'pending') {
                $query->where(function ($sq) {
                    $sq->doesntHave('result')
                        ->orWhereHas('examination', function ($eq) {
                            $eq->whereRaw('LOWER(status) != ?', ['result published']);
                        });
                });
            } elseif (in_array($request->result_status, ['Pass', 'Fail', 'Withheld'])) {
                $query->whereHas('examination', function ($eq) {
                    $eq->whereRaw('LOWER(status) = ?', ['result published']);
                })->whereHas('result', function ($q) use ($request) {
                    $q->where('status', $request->result_status);
                });
            } elseif ($request->result_status === 'Absent') {
                $query->whereHas('examination', function ($eq) {
                    $eq->whereRaw('LOWER(status) = ?', ['result published']);
                })->where(function ($q) {
                    $q->whereHas('result', fn ($rq) => $rq->where('status', 'Absent'))
                        ->orWhere(function ($sq) {
                            $sq->doesntHave('result')
                                ->whereDoesntHave('attendances', fn ($aq) => $aq->where('attendance_date', '2026-08-30')->where('status', 'Present'));
                        });
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
        $categories = CategoryMaster::all();

        // Selected exam check
        $selectedExam = $request->filled('examination_id') ? Examination::find($request->examination_id) : null;
        $isExamResultPublished = $selectedExam ? (strtolower(trim((string) $selectedExam->status)) === 'result published') : null;

        // Stats summary for results declared ONLY for published examinations for this school
        $totalRegistered = Student::where('school_id', $school->id)
            ->when($request->filled('examination_id'), fn ($q) => $q->where('examination_id', $request->examination_id))
            ->count();

        $resultsDeclaredQuery = StudentResult::whereHas('student', function ($q) use ($school, $request) {
            $q->where('school_id', $school->id);
            if ($request->filled('examination_id')) {
                $q->where('examination_id', $request->examination_id);
            }
        })->whereHas('examination', function ($q) {
            $q->whereRaw('LOWER(status) = ?', ['result published']);
        });

        $resultsDeclared = (clone $resultsDeclaredQuery)->count();
        $passedCount = (clone $resultsDeclaredQuery)->where('status', 'Pass')->count();
        $failedCount = (clone $resultsDeclaredQuery)->where('status', 'Fail')->count();
        $passPercentage = $resultsDeclared > 0 ? round(($passedCount / $resultsDeclared) * 100, 1) : 0;

        $recentBatches = ResultBatch::where('scope', 'school')
            ->where('school_id', $school->id)
            ->with(['examination', 'parts'])
            ->latest()
            ->take(10)
            ->get();

        return view('school-admin.results.index', compact(
            'students',
            'examinations',
            'classes',
            'categories',
            'totalRegistered',
            'resultsDeclared',
            'passedCount',
            'failedCount',
            'passPercentage',
            'selectedExam',
            'isExamResultPublished',
            'recentBatches'
        ));
    }

    /**
     * Dispatch background Result PDF generation for School Admin.
     * Strictly scoped to own school and only for RESULT PUBLISHED examinations.
     */
    public function schoolExportPdf(Request $request)
    {
        try {
            $school = auth()->user()->school;
            if (! $school) {
                return redirect()->route('school.dashboard')->with('error', 'School account profile not found.');
            }

            // If a specific exam was requested, check if it is result published
            if ($request->filled('examination_id')) {
                $exam = Examination::find($request->examination_id);
                if (! $exam || ! $exam->isResultPublished()) {
                    return redirect()->route('school.results.index')
                        ->with('error', 'Results for this examination session have not been published yet.');
                }
            }

            $examinationId = $request->filled('examination_id') ? (int) $request->examination_id : null;
            $batch = $this->reportService->createAndDispatchBatch(
                'school',
                $school->id,
                $examinationId,
                auth()->id(),
                $request->all()
            );

            return redirect()->route('school.results.batches.show', $batch)
                ->with('success', 'Institutional result statement generation started in the background.');
        } catch (ValidationException $e) {
            return redirect()->route('school.results.index')
                ->withErrors($e->errors());
        } catch (\Throwable $e) {
            Log::error('School PDF Export Dispatch Failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()->route('school.results.index')->with('error', 'Unable to initiate results export: '.$e->getMessage());
        }
    }

    /**
     * Show official marksheet for a student registered by the logged-in school.
     */
    public function schoolMarksheet(Student $student)
    {
        $school = auth()->user()->school;

        if (! $school || $student->school_id !== $school->id) {
            abort(403, 'Unauthorized access to candidate details from another institution.');
        }

        $student->load(['result', 'school', 'class', 'category', 'examination']);

        if (! $student->examination || strtolower(trim((string) $student->examination->status)) !== 'result published') {
            return redirect()->route('school.results.index')
                ->with('error', 'Results for this examination session have not been published yet.');
        }

        if (! $student->result) {
            return redirect()->route('school.results.index')
                ->with('error', 'Exam results for this student have not been declared yet.');
        }

        $result = $student->result;

        return view('public.results.show', compact('student', 'result'));
    }
}
