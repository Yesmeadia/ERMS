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
            $search = $request->search;
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
        $student->load(['school', 'class', 'category', 'examination']);
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
     */
    public function verifyPublic($number)
    {
        // Find by hall ticket number
        $student = Student::where('hall_ticket_number', $number)
            ->with(['school', 'class', 'category', 'examination'])
            ->first();

        // Fallback check by registration number just in case
        if (!$student) {
            $student = Student::where('registration_number', $number)
                ->with(['school', 'class', 'category', 'examination'])
                ->first();
        }

        $verified = $student && in_array($student->status, ['Approved', 'Hall Ticket Issued']);

        return view('public.verify-hall-ticket', compact('student', 'verified', 'number'));
    }
}
