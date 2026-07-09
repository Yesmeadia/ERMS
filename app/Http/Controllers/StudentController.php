<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\School;
use App\Models\ClassMaster;
use App\Models\CategoryMaster;
use App\Models\Examination;
use App\Imports\StudentsImport;
use App\Exports\StudentTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    /**
     * Super Admin: List ALL students across all schools with full filters.
     */
    public function adminIndex(Request $request)
    {
        $query = Student::with(['class', 'category', 'school', 'examination', 'attendances', 'centre']);

        if ($request->filled('examination_id')) {
            $query->where('examination_id', $request->examination_id);
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('status')) {
            $filterStatus = $request->status;
            // 'Present' and 'Absent' are virtual — filter via attendance
            if ($filterStatus === 'Present') {
                $query->whereHas('attendances', function ($q) {
                    $q->where('status', 'Present');
                });
            } elseif ($filterStatus === 'Absent') {
                $query->whereIn('status', ['Approved', 'Hall Ticket Issued'])
                      ->whereDoesntHave('attendances', function ($q) {
                          $q->where('status', 'Present');
                      });
            } else {
                $query->where('status', $filterStatus);
            }
        }

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('hall_ticket_number', 'like', "%{$search}%");
            });
        }

        $students     = $query->latest()->paginate(20)->withQueryString();
        $examinations = Examination::all();
        $schools      = School::where('status', true)->get();
        $categories   = CategoryMaster::where('status', true)->get();
        $genders      = ['Male', 'Female', 'Other'];
        $statuses     = ['Draft', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Hall Ticket Issued', 'Present', 'Absent'];
        $designatedCentres = School::where('is_centre', true)->where('status', true)->get();

        // Stats
        $totalCount    = Student::count();
        $draftCount    = Student::where('status', 'Draft')->count();
        $submittedCount = Student::where('status', 'Submitted')->count();
        $approvedCount = Student::whereIn('status', ['Approved', 'Hall Ticket Issued'])->count();

        return view('super-admin.students.index', compact(
            'students', 'examinations', 'schools', 'categories',
            'genders', 'statuses', 'totalCount', 'draftCount',
            'submittedCount', 'approvedCount', 'designatedCentres'
        ));
    }

    /**
     * Super Admin: Issue a Registration Number for a student.
     */
    public function adminIssueRegistration(Student $student)
    {
        if ($student->registration_number) {
            return back()->with('info', 'Registration number already issued: ' . $student->registration_number);
        }

        if (!in_array($student->status, ['Submitted', 'Under Review', 'Approved', 'Rejected', 'Hall Ticket Issued'])) {
            return back()->with('error', 'Registration number can only be issued for submitted or approved students.');
        }

        DB::transaction(function () use ($student) {
            // Re-fetch student inside the transaction with a row lock to guard
            // against concurrent issuance requests for the same student.
            $locked = Student::lockForUpdate()->findOrFail($student->id);

            // Double-check: another request may have already assigned a number.
            if ($locked->registration_number) {
                return;
            }

            $locked->registration_number = $locked->issueRegistrationNumber();
            $locked->save();

            activity()
                ->performedOn($locked)
                ->log("Issued registration number ({$locked->registration_number}) for student: {$locked->name}");

            // Refresh the original model so the response message is correct.
            $student->registration_number = $locked->registration_number;
        });

        // Reload to reflect any changes made inside the transaction.
        $student->refresh();

        return back()->with('success', "Registration number {$student->registration_number} issued for {$student->name}.");
    }

    /**
     * Super Admin: View a student's full profile.
     */
    public function adminShow(Student $student)
    {
        $student->load(['class', 'category', 'school', 'examination', 'hallTicket', 'result', 'attendances', 'payments', 'centre']);
        $designatedCentres = School::where('is_centre', true)->where('status', true)->get();
        return view('super-admin.students.show', compact('student', 'designatedCentres'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $school = Auth::user()->school;
        $query = Student::where('school_id', $school->id)
            ->with(['class', 'category', 'examination']);

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('examination_id')) {
            $query->where('examination_id', $request->examination_id);
        }

        $students = $query->latest()->paginate(15);
        $examinations = Examination::all();

        return view('school-admin.students.index', compact('students', 'examinations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = ClassMaster::where('status', true)->get();
        $categories = CategoryMaster::where('status', true)->get();

        // Only allow registering for examinations that are currently OPEN
        $examinations = Examination::where('status', 'Open')->get();

        if ($examinations->isEmpty()) {
            return redirect()->route('school.students.index')->with('error', 'There are no active examination sessions open for registration at this time.');
        }

        return view('school-admin.students.create', compact('classes', 'categories', 'examinations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'examination_id' => ['required', 'exists:examinations,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'dob' => ['required', 'date', 'before:today'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:15'],
            'photograph' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072', new \App\Rules\VirusFree], // Max 3MB + Virus scan
        ]);

        // Validate examination is open
        $exam = Examination::findOrFail($request->examination_id);
        if ($exam->status !== 'Open') {
            return back()->withErrors(['examination_id' => 'Registration is closed for this examination session.'])->withInput();
        }

        $school = Auth::user()->school;

        $studentData = array_merge($validated, [
            'school_id' => $school->id,
            'status' => 'Draft',
        ]);

        if ($request->hasFile('photograph')) {
            $studentData['photograph'] = $request->file('photograph')->store('students/photos', 'public');
        }

        $student = Student::create($studentData);

        activity()
            ->performedOn($student)
            ->log("Added student registration draft: {$student->name}");

        return redirect()->route('school.students.index')->with('success', 'Student registration draft saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        $this->authorizeAccess($student);
        $student->load(['class', 'category', 'school', 'examination', 'payments']);
        return view('school-admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        $this->authorizeAccess($student);

        // Allow editing only if status is Draft or Rejected
        if (!in_array($student->status, ['Draft', 'Rejected'])) {
            return redirect()->route('school.students.index')->with('error', 'Cannot edit student registration once submitted or approved.');
        }

        $classes = ClassMaster::where('status', true)->get();
        $categories = CategoryMaster::where('status', true)->get();
        $examinations = Examination::where('status', 'Open')->get();

        return view('school-admin.students.edit', compact('student', 'classes', 'categories', 'examinations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $this->authorizeAccess($student);

        if (!in_array($student->status, ['Draft', 'Rejected'])) {
            return redirect()->route('school.students.index')->with('error', 'Cannot update student registration once submitted or approved.');
        }

        $validated = $request->validate([
            'examination_id' => ['required', 'exists:examinations,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'dob' => ['required', 'date', 'before:today'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:15'],
            'photograph' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072', new \App\Rules\VirusFree],
        ]);

        $exam = Examination::findOrFail($request->examination_id);
        if ($exam->status !== 'Open') {
            return back()->withErrors(['examination_id' => 'Registration is closed for this examination session.'])->withInput();
        }

        $school = Auth::user()->school;

        $studentData = $validated;

        if ($request->hasFile('photograph')) {
            // Delete old photo if it exists
            if ($student->photograph) {
                Storage::disk('public')->delete($student->photograph);
            }
            $studentData['photograph'] = $request->file('photograph')->store('students/photos', 'public');
        }

        // Keep it draft (if it was rejected, we reset to Draft or keep Draft status)
        $studentData['status'] = 'Draft';

        $student->update($studentData);

        activity()
            ->performedOn($student)
            ->log("Updated student registration details: {$student->name}");

        return redirect()->route('school.students.index')->with('success', 'Student registration details updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $this->authorizeAccess($student);

        if (!in_array($student->status, ['Draft', 'Rejected'])) {
            return redirect()->route('school.students.index')->with('error', 'Cannot delete student registration once submitted or approved.');
        }

        if ($student->photograph) {
            Storage::disk('public')->delete($student->photograph);
        }

        activity()
            ->performedOn($student)
            ->log("Deleted student registration: {$student->name}");

        $student->delete();

        return redirect()->route('school.students.index')->with('success', 'Student registration deleted successfully.');
    }

    /**
     * Submit Student Registration to Super Admin for verification.
     */
    public function submitStudent(Student $student)
    {
        $this->authorizeAccess($student);

        if (!in_array($student->status, ['Draft', 'Rejected'])) {
            return back()->with('error', 'Student registration has already been submitted.');
        }

        $student->status = 'Submitted';
        $student->save();

        activity()
            ->performedOn($student)
            ->log("Submitted student registration for verification");

        return back()->with('success', "Student registration submitted successfully.");
    }

    /**
     * Download Excel template for import.
     */
    public function downloadTemplate()
    {
        return Excel::download(new StudentTemplateExport, 'erms_student_import_template.xlsx');
    }

    /**
     * Import students from Excel.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'import_examination_id' => ['required', 'exists:examinations,id'],
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ]);

        $exam = Examination::findOrFail($request->import_examination_id);
        if ($exam->status !== 'Open') {
            return back()->with('error', 'Cannot import. Registration is closed for the selected examination session.');
        }

        $school = Auth::user()->school;

        try {
            Excel::import(
                new StudentsImport($exam->id, $school->id),
                $request->file('excel_file')
            );

            activity()
                ->log("Bulk imported student registrations for examination: {$exam->name} from Excel");

            return redirect()->route('school.students.index')->with('success', 'Students imported successfully as drafts. Please review and submit them.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMsgs = [];
            foreach ($failures as $failure) {
                $errorMsgs[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            return back()->withErrors($errorMsgs)->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Error importing file: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Ensure user has access to student.
     */
    protected function authorizeAccess(Student $student)
    {
        if ($student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }
    }
}
