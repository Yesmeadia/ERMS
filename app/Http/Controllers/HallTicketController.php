<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\School;
use App\Models\Examination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HallTicketController extends Controller
{
    /**
     * Super Admin Index of Hall Tickets.
     */
    public function adminIndex(Request $request)
    {
        $query = Student::whereIn('status', ['Approved', 'Hall Ticket Issued'])
            ->with(['school', 'class', 'category', 'examination']);

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('examination_id')) {
            $query->where('examination_id', $request->examination_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('hall_ticket_number', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(15);
        $schools = School::where('status', true)->get();
        $examinations = Examination::all();
        $categories = \App\Models\CategoryMaster::where('status', true)->get();

        return view('super-admin.hall-tickets.index', compact('students', 'schools', 'examinations', 'categories'));
    }

    /**
     * School Admin Index of Hall Tickets.
     */
    public function schoolIndex(Request $request)
    {
        $school = Auth::user()->school;
        
        $query = Student::where('school_id', $school->id)
            ->whereIn('status', ['Approved', 'Hall Ticket Issued'])
            ->with(['class', 'category', 'examination']);

        if ($request->filled('examination_id')) {
            $query->where('examination_id', $request->examination_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('hall_ticket_number', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(15);
        $examinations = Examination::all();
        $categories = \App\Models\CategoryMaster::where('status', true)->get();

        return view('school-admin.hall-tickets.index', compact('students', 'examinations', 'categories'));
    }

    /**
     * Generate Hall Ticket for a single approved student.
     */
    public function generateSingle(Student $student)
    {
        if ($student->status !== 'Approved' && $student->status !== 'Hall Ticket Issued') {
            return back()->with('error', 'Hall ticket can only be generated for Approved students.');
        }

        // Generate number if not already exists
        if (!$student->hall_ticket_number) {
            $year = date('Y', strtotime($student->examination->registration_start_date));
            $student->hall_ticket_number = 'HT-' . $year . '-' . str_pad($student->id, 6, '0', STR_PAD_LEFT);
        }

        // Generate registration number if not already exists
        if (!$student->registration_number) {
            $student->registration_number = $student->issueRegistrationNumber();
        }

        $student->status = 'Hall Ticket Issued';
        $student->hall_ticket_issued_at = now();
        $student->save();

        // Create/Update HallTicket entry with secure qr_token
        \App\Models\HallTicket::updateOrCreate(
            ['student_id' => $student->id],
            [
                'hallticket_no' => $student->hall_ticket_number,
                'qr_token' => $student->hallTicket->qr_token ?? bin2hex(random_bytes(32)),
                'issue_date' => now(),
                'status' => 'Issued',
            ]
        );

        activity()
            ->performedOn($student)
            ->log("Generated hall ticket ({$student->hall_ticket_number}) for student: {$student->name}");

        return back()->with('success', "Hall Ticket generated successfully. Number: {$student->hall_ticket_number}");
    }

    /**
     * Bulk Generate Hall Tickets for all Approved students in a School / Examination.
     */
    public function generateBulk(Request $request)
    {
        $request->validate([
            'bulk_school_id' => ['required', 'exists:schools,id'],
            'bulk_examination_id' => ['required', 'exists:examinations,id'],
        ]);

        $students = Student::where('school_id', $request->bulk_school_id)
            ->where('examination_id', $request->bulk_examination_id)
            ->where('status', 'Approved')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('info', 'No approved students found pending hall ticket generation for this school and examination.');
        }

        $count = 0;
        foreach ($students as $student) {
            $year = date('Y', strtotime($student->examination->registration_start_date));
            $student->hall_ticket_number = 'HT-' . $year . '-' . str_pad($student->id, 6, '0', STR_PAD_LEFT);
            
            if (!$student->registration_number) {
                $student->registration_number = $student->issueRegistrationNumber();
            }

            $student->status = 'Hall Ticket Issued';
            $student->hall_ticket_issued_at = now();
            $student->save();

            // Create/Update HallTicket entry with secure qr_token
            \App\Models\HallTicket::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'hallticket_no' => $student->hall_ticket_number,
                    'qr_token' => $student->hallTicket->qr_token ?? bin2hex(random_bytes(32)),
                    'issue_date' => now(),
                    'status' => 'Issued',
                ]
            );

            $count++;
        }

        activity()
            ->log("Bulk generated {$count} hall tickets for School ID: {$request->bulk_school_id}, Exam ID: {$request->bulk_examination_id}");

        return back()->with('success', "Successfully generated {$count} hall tickets.");
    }

    /**
     * Print/Render single Hall Ticket as PDF.
     */
    public function printSingle(Student $student)
    {
        if ($student->status !== 'Hall Ticket Issued') {
            return back()->with('error', 'Hall ticket has not been issued yet for this student.');
        }

        $student->load(['school', 'class', 'category', 'examination', 'hallTicket']);
        
        $hallTicket = $student->hallTicket;
        if (!$hallTicket) {
            $hallTicket = \App\Models\HallTicket::create([
                'student_id' => $student->id,
                'hallticket_no' => $student->hall_ticket_number,
                'qr_token' => bin2hex(random_bytes(32)),
                'issue_date' => now(),
                'status' => 'Issued',
            ]);
        }

        // Format QR Payload as JSON
        $qrPayload = json_encode([
            'student_id' => $student->id,
            'hallticket_no' => $student->hall_ticket_number,
            'exam_id' => $student->examination_id,
            'token' => $hallTicket->qr_token,
        ]);

        $verifyUrl = route('verification.hall-ticket', $student->hall_ticket_number);

        // SVG with quiet-zone margin — margin(2) is critical for camera-based scanning
        $qrSvg = QrCode::size(220)->margin(2)->generate($qrPayload);
        $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $pdf = Pdf::loadView('pdf.hall-ticket', compact('student', 'qrDataUri', 'verifyUrl'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        
        return $pdf->stream('hall_ticket_' . $student->hall_ticket_number . '.pdf');
    }

    /**
     * Download single Hall Ticket PDF (for School Admin).
     */
    public function downloadSingle(Student $student)
    {
        if ($student->school_id !== Auth::user()->school_id) {
            abort(403);
        }

        if ($student->status !== 'Hall Ticket Issued') {
            return back()->with('error', 'Hall ticket has not been issued yet for this student.');
        }

        $student->load(['school', 'class', 'category', 'examination', 'hallTicket']);

        $hallTicket = $student->hallTicket;
        if (!$hallTicket) {
            $hallTicket = \App\Models\HallTicket::create([
                'student_id' => $student->id,
                'hallticket_no' => $student->hall_ticket_number,
                'qr_token' => bin2hex(random_bytes(32)),
                'issue_date' => now(),
                'status' => 'Issued',
            ]);
        }

        // Format QR Payload as JSON
        $qrPayload = json_encode([
            'student_id' => $student->id,
            'hallticket_no' => $student->hall_ticket_number,
            'exam_id' => $student->examination_id,
            'token' => $hallTicket->qr_token,
        ]);

        $verifyUrl = route('verification.hall-ticket', $student->hall_ticket_number);

        // SVG with quiet-zone margin — margin(2) is critical for camera-based scanning
        $qrSvg = QrCode::size(220)->margin(2)->generate($qrPayload);
        $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        // Increment downloaded count or log activity
        activity()
            ->performedOn($student)
            ->log("School Admin downloaded hall ticket ({$student->hall_ticket_number}) for student: {$student->name}");

        $pdf = Pdf::loadView('pdf.hall-ticket', compact('student', 'qrDataUri', 'verifyUrl'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('hall_ticket_' . $student->hall_ticket_number . '.pdf');
    }

    /**
     * Print Bulk Hall Tickets (for Super Admin).
     */
    public function printBulk(Request $request)
    {
        $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'examination_id' => ['required', 'exists:examinations,id'],
        ]);

        $students = Student::where('school_id', $request->school_id)
            ->where('examination_id', $request->examination_id)
            ->where('status', 'Hall Ticket Issued')
            ->with(['school', 'class', 'category', 'examination', 'hallTicket'])
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No hall tickets found issued for this school and examination session.');
        }

        // Generate QR codes for all
        $studentsData = [];
        foreach ($students as $student) {
            $hallTicket = $student->hallTicket;
            if (!$hallTicket) {
                $hallTicket = \App\Models\HallTicket::create([
                    'student_id' => $student->id,
                    'hallticket_no' => $student->hall_ticket_number,
                    'qr_token' => bin2hex(random_bytes(32)),
                    'issue_date' => now(),
                    'status' => 'Issued',
                ]);
            }

            // Format QR Payload as JSON
            $qrPayload = json_encode([
                'student_id' => $student->id,
                'hallticket_no' => $student->hall_ticket_number,
                'exam_id' => $student->examination_id,
                'token' => $hallTicket->qr_token,
            ]);

            $verifyUrl = route('verification.hall-ticket', $student->hall_ticket_number);
            // SVG with quiet-zone margin — margin(2) is critical for camera-based scanning
            $qrSvg = QrCode::size(220)->margin(2)->generate($qrPayload);
            $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

            $studentsData[] = [
                'student' => $student,
                'qrDataUri' => $qrDataUri,
                'verifyUrl' => $verifyUrl
            ];
        }

        $pdf = Pdf::loadView('pdf.hall-tickets-bulk', compact('studentsData'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->stream('bulk_hall_tickets_' . time() . '.pdf');
    }

    /**
     * Download Bulk Hall Tickets (for School Admin).
     */
    public function downloadBulk(Request $request)
    {
        $request->validate([
            'examination_id' => ['required', 'exists:examinations,id'],
        ]);

        $school = Auth::user()->school;

        $students = Student::where('school_id', $school->id)
            ->where('examination_id', $request->examination_id)
            ->where('status', 'Hall Ticket Issued')
            ->with(['class', 'category', 'examination', 'hallTicket'])
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No hall tickets available to download for this examination session.');
        }

        $studentsData = [];
        foreach ($students as $student) {
            $hallTicket = $student->hallTicket;
            if (!$hallTicket) {
                $hallTicket = \App\Models\HallTicket::create([
                    'student_id' => $student->id,
                    'hallticket_no' => $student->hall_ticket_number,
                    'qr_token' => bin2hex(random_bytes(32)),
                    'issue_date' => now(),
                    'status' => 'Issued',
                ]);
            }

            // Format QR Payload as JSON
            $qrPayload = json_encode([
                'student_id' => $student->id,
                'hallticket_no' => $student->hall_ticket_number,
                'exam_id' => $student->examination_id,
                'token' => $hallTicket->qr_token,
            ]);

            $verifyUrl = route('verification.hall-ticket', $student->hall_ticket_number);
            // SVG with quiet-zone margin — margin(2) is critical for camera-based scanning
            $qrSvg = QrCode::size(220)->margin(2)->generate($qrPayload);
            $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

            $studentsData[] = [
                'student' => $student,
                'qrDataUri' => $qrDataUri,
                'verifyUrl' => $verifyUrl
            ];
        }

        activity()
            ->log("School Admin downloaded bulk hall tickets for School Code: {$school->code}, Exam ID: {$request->examination_id}");

        $pdf = Pdf::loadView('pdf.hall-tickets-bulk', compact('studentsData'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('bulk_hall_tickets_' . $school->code . '.pdf');
    }
}
