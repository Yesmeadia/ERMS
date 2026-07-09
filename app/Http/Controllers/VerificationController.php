<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\School;
use App\Models\ClassMaster;
use App\Models\CategoryMaster;
use App\Models\Examination;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Display listing of registrations pending review/verification.
     */
    public function index(Request $request)
    {
        $query = Student::with(['school', 'class', 'category', 'examination']);

        // Default to showing Submitted & Under Review first
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['Submitted', 'Under Review', 'Approved', 'Rejected', 'Hall Ticket Issued']);
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(15);
        $schools = School::where('status', true)->get();
        $classes = ClassMaster::where('status', true)->get();
        $categories = CategoryMaster::where('status', true)->get();

        return view('super-admin.verification.index', compact('students', 'schools', 'classes', 'categories'));
    }

    /**
     * Display student details for verification.
     */
    public function show(Student $student)
    {
        $student->load(['school', 'class', 'category', 'examination', 'payments']);
        return view('super-admin.verification.show', compact('student'));
    }

    /**
     * Update verification status (Approve, Reject, Under Review).
     */
    public function verify(Request $request, Student $student)
    {
        $request->validate([
            'action' => ['required', 'in:review,approve,reject'],
            'remarks' => ['required_if:action,reject', 'nullable', 'string', 'max:500'],
        ]);

        $statusMap = [
            'review' => 'Under Review',
            'approve' => 'Approved',
            'reject' => 'Rejected',
        ];

        $oldStatus = $student->status;
        $student->status = $statusMap[$request->action];
        
        if ($request->action === 'reject') {
            $student->remarks = $request->remarks;
        } else {
            $student->remarks = null; // Clear old remarks if approved/under review
        }

        $student->save();

        activity()
            ->performedOn($student)
            ->log("Updated verification status from '{$oldStatus}' to '{$student->status}'" . ($request->action === 'reject' ? " with remarks: {$request->remarks}" : ''));

        return redirect()->route('admin.verification.index')->with('success', "Student registration is now {$student->status}.");
    }

    /**
     * Public QR Code Verification Portal.
     * Anyone can scan the QR code to load this public page.
     *
     * Security (CWE-200): Only the minimum fields needed to confirm ticket validity
     * are passed to the view. Sensitive PII (father/mother names, DOB, gender,
     * registration number, mobile) is intentionally excluded.
     * All lookups are audit-logged for GDPR accountability.
     */
    public function verifyPublic(Request $request, $number)
    {
        // Sanitise the input — only allow characters valid in a hall ticket / reg number
        $number = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', $number));

        // Lookup by hall ticket number (primary), then registration number (fallback)
        // Only select columns required for validity display — exclude PII columns.
        $safeColumns = ['id', 'name', 'photograph', 'school_id', 'class_id', 'category_id',
                        'examination_id', 'hall_ticket_number', 'hall_ticket_issued_at', 'status'];

        $student = Student::select($safeColumns)
            ->where('hall_ticket_number', $number)
            ->with(['school:id,name', 'class:id,name', 'category:id,name', 'examination:id,name'])
            ->first();

        if (!$student) {
            $student = Student::select($safeColumns)
                ->where('registration_number', $number)
                ->with(['school:id,name', 'class:id,name', 'category:id,name', 'examination:id,name'])
                ->first();
        }

        $verified = $student && in_array($student->status, ['Approved', 'Hall Ticket Issued']);

        // Audit-log every public lookup for GDPR accountability and intrusion detection.
        // Logged regardless of outcome so failed probing attempts are also captured.
        activity()
            ->withProperties(['ip' => $request->ip(), 'lookup' => $number, 'found' => (bool) $student, 'verified' => $verified])
            ->log('Public hall-ticket verification lookup');

        return view('public.verify-hall-ticket', compact('student', 'verified', 'number'));
    }
}
