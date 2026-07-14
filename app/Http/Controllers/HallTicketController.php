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
            ->with(['school', 'class', 'category', 'examination', 'centre']);

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('examination_id')) {
            $query->where('examination_id', $request->examination_id);
        }

        if ($request->filled('centre_id')) {
            $query->where('centre_id', $request->centre_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('hall_ticket_number', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(20);
        $schools = School::where('status', true)->get();
        $centres = School::where('is_centre', true)->get();
        $examinations = Examination::all();
        $categories = \App\Models\CategoryMaster::where('status', true)->get();

        return view('super-admin.hall-tickets.index', compact('students', 'schools', 'centres', 'examinations', 'categories'));
    }

    /**
     * School Admin Index of Hall Tickets.
     */
    public function schoolIndex(Request $request)
    {
        $school = Auth::user()->school;

        $query = Student::where('school_id', $school->id)
            ->whereIn('status', ['Approved', 'Hall Ticket Issued'])
            ->with(['class', 'category', 'examination', 'centre']);

        if ($request->filled('centre_id')) {
            $query->where('centre_id', $request->centre_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('hall_ticket_number', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(20);
        $centres = School::where('is_centre', true)->get();
        $examinations = Examination::all();
        $categories = \App\Models\CategoryMaster::where('status', true)->get();

        return view('school-admin.hall-tickets.index', compact('students', 'centres', 'examinations', 'categories'));
    }

    /**
     * Generate Hall Ticket for a single approved student.
     */
    public function generateSingle(Student $student)
    {
        if ($student->status !== 'Approved' && $student->status !== 'Hall Ticket Issued') {
            return back()->with('error', 'Hall ticket can only be generated for Approved students.');
        }

        if (!$student->centre_id) {
            return back()->with('error', 'Please assign an Examination Centre for this candidate before generating a hall ticket.');
        }

        // Generate number if not already exists.
        // Uses cryptographically random bytes instead of sequential IDs to prevent
        // enumeration attacks on the public verification portal (CWE-330).
        if (!$student->hall_ticket_number) {
            do {
                $candidate = strtoupper(bin2hex(random_bytes(6)));
            } while (\App\Models\Student::where('hall_ticket_number', $candidate)->exists());
            $student->hall_ticket_number = $candidate;
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
                'qr_token' => $student->hallTicket?->qr_token ?? bin2hex(random_bytes(32)),
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
            'school_id' => ['required', 'exists:schools,id'],
            'examination_id' => ['required', 'exists:examinations,id'],
        ]);

        $query = Student::where('school_id', $request->school_id)
            ->where('examination_id', $request->examination_id)
            ->where('status', 'Approved');

        if ($request->filled('centre_id')) {
            $query->where('centre_id', $request->centre_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Eager-load relations needed for registration-number generation & HallTicket upsert
        $students = $query->with(['class', 'category', 'hallTicket'])->get();

        if ($students->isEmpty()) {
            return back()->with('info', 'No approved students found pending hall ticket generation matching the selected criteria.');
        }

        // Ensure every student has an exam centre before proceeding
        $unassignedCount = $students->whereNull('centre_id')->count();
        if ($unassignedCount > 0) {
            return back()->with('error', "{$unassignedCount} approved candidate(s) do not have an assigned Examination Centre. Please assign a centre first.");
        }

        // ── Step 1: Pre-fetch existing hall-ticket numbers to avoid per-student DB checks ──
        $existingHtNos = \App\Models\Student::whereNotNull('hall_ticket_number')
            ->pluck('hall_ticket_number')
            ->flip(); // O(1) lookup: ['AABBCC' => 0, ...]

        // ── Step 2: Generate all unique tokens in memory ────────────────────────────────
        $tokens = [];
        foreach ($students as $student) {
            do {
                $candidate = strtoupper(bin2hex(random_bytes(6)));
            } while (isset($existingHtNos[$candidate]) || in_array($candidate, $tokens, true));

            $tokens[$student->id] = $candidate;
        }

        // ── Step 3: Resolve registration numbers in one transaction per category range ──
        //    We call issueRegistrationNumber() only for students who need one, which
        //    itself opens a single locked transaction per category bucket.
        $regNumbers = [];
        foreach ($students->where('registration_number', null) as $student) {
            $regNumbers[$student->id] = $student->issueRegistrationNumber();
        }

        // ── Step 4: Batch-update students (one UPDATE per student, all in one TX) ───────
        $now = now();
        $count = 0;

        \DB::transaction(function () use ($students, $tokens, $regNumbers, $now, &$count) {
            foreach ($students as $student) {
                \DB::table('students')->where('id', $student->id)->update([
                    'hall_ticket_number' => $tokens[$student->id],
                    'registration_number' => $regNumbers[$student->id] ?? $student->registration_number,
                    'status' => 'Hall Ticket Issued',
                    'hall_ticket_issued_at' => $now,
                    'updated_at' => $now,
                ]);
                $count++;
            }
        });

        // ── Step 5: Bulk-upsert HallTicket records ──────────────────────────────────────
        $hallTicketRows = $students->map(function ($student) use ($tokens, $now) {
            return [
                'student_id' => $student->id,
                'hallticket_no' => $tokens[$student->id],
                // Re-use existing qr_token if already generated — prevents invalidating old QR codes
                'qr_token' => $student->hallTicket?->qr_token ?? bin2hex(random_bytes(32)),
                'issue_date' => $now,
                'status' => 'Issued',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        \App\Models\HallTicket::upsert(
            $hallTicketRows,
            ['student_id'],                                      // unique key
            ['hallticket_no', 'qr_token', 'issue_date', 'status', 'updated_at'] // columns to update
        );

        activity()->log("Bulk generated {$count} hall tickets for School ID: {$request->school_id}, Exam ID: {$request->examination_id}");

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

        $student->load(['school', 'class', 'category', 'examination', 'hallTicket', 'centre']);

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
        $pdf->setOption('isRemoteEnabled', false);

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

        $student->load(['school', 'class', 'category', 'examination', 'hallTicket', 'centre']);

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
        $pdf->setOption('isRemoteEnabled', false);

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

        $query = Student::where('school_id', $request->school_id)
            ->where('examination_id', $request->examination_id)
            ->where('status', 'Hall Ticket Issued');

        if ($request->filled('centre_id')) {
            $query->where('centre_id', $request->centre_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $students = $query->with(['school', 'class', 'category', 'examination', 'hallTicket', 'centre'])->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No hall tickets found issued matching the selected criteria.');
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
        $pdf->setOption('isRemoteEnabled', false);

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
            ->with(['class', 'category', 'examination', 'hallTicket', 'centre'])
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
        $pdf->setOption('isRemoteEnabled', false);

        return $pdf->download('bulk_hall_tickets_' . $school->code . '.pdf');
    }
}
