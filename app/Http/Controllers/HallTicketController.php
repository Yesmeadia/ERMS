<?php

namespace App\Http\Controllers;

use App\Models\CategoryMaster;
use App\Models\Examination;
use App\Models\HallTicket;
use App\Models\HallTicketBatch;
use App\Models\HallTicketPdfPart;
use App\Models\School;
use App\Models\Student;
use App\Services\HallTicketBatchService;
use App\Services\HallTicketPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class HallTicketController extends Controller
{
    protected HallTicketBatchService $batchService;

    protected HallTicketPdfService $pdfService;

    public function __construct(HallTicketBatchService $batchService, HallTicketPdfService $pdfService)
    {
        $this->batchService = $batchService;
        $this->pdfService = $pdfService;
    }

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

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
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
        $categories = CategoryMaster::where('status', true)->get();

        // Recent batches for super admin quick access & monitoring
        $recentBatches = HallTicketBatch::with(['school', 'examination', 'requester', 'parts'])
            ->latest()
            ->take(50)
            ->get();

        return view('super-admin.hall-tickets.index', compact('students', 'schools', 'centres', 'examinations', 'categories', 'recentBatches'));
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

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

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
        $categories = CategoryMaster::where('status', true)->get();

        // Active & Recent batches for this school
        $recentBatches = HallTicketBatch::where('school_id', $school->id)
            ->with(['examination', 'parts'])
            ->latest()
            ->take(15)
            ->get();

        $recentBatch = $recentBatches->first();

        return view('school-admin.hall-tickets.index', compact('students', 'centres', 'examinations', 'categories', 'recentBatch', 'recentBatches'));
    }

    /**
     * Generate Hall Ticket for a single approved student (Assigns HT & Reg Numbers).
     */
    public function generateSingle(Student $student)
    {
        Gate::authorize('generateHallTicket', $student);

        if ($student->status !== 'Approved' && $student->status !== 'Hall Ticket Issued') {
            return back()->with('error', 'Hall ticket can only be generated for Approved students.');
        }

        if (! $student->centre_id) {
            return back()->with('error', 'Please assign an Examination Centre for this candidate before generating a hall ticket.');
        }

        try {
            DB::transaction(function () use ($student) {
                $locked = Student::lockForUpdate()->findOrFail($student->id);

                if (! $locked->hall_ticket_number) {
                    do {
                        $candidate = strtoupper(bin2hex(random_bytes(6)));
                    } while (Student::withTrashed()->where('hall_ticket_number', $candidate)->exists());
                    $locked->hall_ticket_number = $candidate;
                }

                if (! $locked->registration_number) {
                    $locked->registration_number = $locked->issueRegistrationNumber();
                }

                $locked->status = 'Hall Ticket Issued';
                $locked->hall_ticket_issued_at = now();
                $locked->save();

                HallTicket::updateOrCreate(
                    ['student_id' => $locked->id],
                    [
                        'hallticket_no' => $locked->hall_ticket_number,
                        'qr_token' => $locked->hallTicket?->qr_token ?? bin2hex(random_bytes(32)),
                        'issue_date' => now(),
                        'status' => 'Issued',
                    ]
                );

                $student->hall_ticket_number = $locked->hall_ticket_number;
                $student->registration_number = $locked->registration_number;
                $student->status = $locked->status;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        activity()
            ->performedOn($student)
            ->log("Generated hall ticket ({$student->hall_ticket_number}) for student: {$student->name}");

        return back()->with('success', "Hall Ticket generated successfully. Number: {$student->hall_ticket_number}");
    }

    /**
     * Bulk Generate Hall Tickets for all Approved students in a School / Examination (Assigns HT & Reg Numbers).
     */
    public function generateBulk(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('school-admin')) {
            $schoolId = (int) $user->school_id;
            if ($request->filled('school_id') && (int) $request->school_id !== $schoolId) {
                abort(403, 'Unauthorized access to generate hall tickets for another school.');
            }
        } else {
            $request->validate(['school_id' => ['required', 'exists:schools,id']]);
            $schoolId = (int) $request->school_id;
        }

        $request->validate([
            'examination_id' => ['required', 'exists:examinations,id'],
        ]);

        $query = Student::where('school_id', $schoolId)
            ->where('examination_id', $request->examination_id)
            ->where('status', 'Approved');

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('centre_id')) {
            $query->where('centre_id', $request->centre_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $students = $query->with(['class', 'category', 'hallTicket'])->get();

        if ($students->isEmpty()) {
            return back()->with('info', 'No approved students found pending hall ticket generation matching the selected criteria.');
        }

        $unassignedCount = $students->whereNull('centre_id')->count();
        if ($unassignedCount > 0) {
            return back()->with('error', "{$unassignedCount} approved candidate(s) do not have an assigned Examination Centre. Please assign a centre first.");
        }

        $existingHtNos = Student::withTrashed()
            ->whereNotNull('hall_ticket_number')
            ->pluck('hall_ticket_number')
            ->flip();

        $tokens = [];
        foreach ($students as $student) {
            if ($student->hall_ticket_number) {
                $tokens[$student->id] = $student->hall_ticket_number;

                continue;
            }

            do {
                $candidate = strtoupper(bin2hex(random_bytes(6)));
            } while (isset($existingHtNos[$candidate]) || in_array($candidate, $tokens, true));

            $tokens[$student->id] = $candidate;
        }

        $now = now();
        $count = 0;

        try {
            DB::transaction(function () use ($students, $tokens, $now, &$count) {
                $studentsNeedingReg = $students->filter(fn ($s) => empty($s->registration_number));

                $groupedByRange = [];
                foreach ($studentsNeedingReg as $student) {
                    [$start, $end] = $student->getRegistrationNumberRange();
                    $key = "{$start}_{$end}";
                    $groupedByRange[$key]['start'] = $start;
                    $groupedByRange[$key]['end'] = $end;
                    $groupedByRange[$key]['students'][] = $student;
                }

                $regNumbers = [];
                foreach ($groupedByRange as $group) {
                    $start = $group['start'];
                    $end = $group['end'];
                    $neededCount = count($group['students']);

                    $allocatedList = Student::allocateRegistrationNumbers($start, $end, $neededCount);

                    foreach ($group['students'] as $idx => $student) {
                        $regNumbers[$student->id] = $allocatedList[$idx];
                    }
                }

                foreach ($students as $student) {
                    $regNo = $regNumbers[$student->id] ?? $student->registration_number;
                    $htNo = $tokens[$student->id];

                    DB::table('students')->where('id', $student->id)->update([
                        'hall_ticket_number' => $htNo,
                        'registration_number' => $regNo,
                        'status' => 'Hall Ticket Issued',
                        'hall_ticket_issued_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $student->hall_ticket_number = $htNo;
                    $student->registration_number = $regNo;
                    $student->status = 'Hall Ticket Issued';
                    $student->hall_ticket_issued_at = $now;

                    HallTicket::updateOrCreate(
                        ['student_id' => $student->id],
                        [
                            'hallticket_no' => $htNo,
                            'qr_token' => $student->hallTicket?->qr_token ?? bin2hex(random_bytes(32)),
                            'issue_date' => $now,
                            'status' => 'Issued',
                        ]
                    );

                    $count++;
                }
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        activity()->log("Bulk generated {$count} hall tickets for School ID: {$schoolId}, Exam ID: {$request->examination_id}");

        return back()->with('success', "Successfully generated {$count} hall tickets.");
    }

    /**
     * Print/Render single Hall Ticket as PDF.
     */
    public function printSingle(Student $student)
    {
        Gate::authorize('printHallTicket', $student);

        if ($student->status !== 'Hall Ticket Issued') {
            return back()->with('error', 'Hall ticket has not been issued yet for this student.');
        }

        $student->loadMissing(['school', 'class', 'category', 'examination', 'hallTicket', 'centre']);
        $pdf = $this->pdfService->generateSinglePdf($student);

        return $pdf->stream('hall_ticket_'.$student->hall_ticket_number.'.pdf');
    }

    /**
     * Download single Hall Ticket PDF (for School Admin).
     */
    public function downloadSingle(Student $student)
    {
        Gate::authorize('downloadHallTicket', $student);

        if ($student->status !== 'Hall Ticket Issued') {
            return back()->with('error', 'Hall ticket has not been issued yet for this student.');
        }

        $student->loadMissing(['school', 'class', 'category', 'examination', 'hallTicket', 'centre']);

        activity()
            ->performedOn($student)
            ->log("School Admin downloaded hall ticket ({$student->hall_ticket_number}) for student: {$student->name}");

        $pdf = $this->pdfService->generateSinglePdf($student);

        return $pdf->download('hall_ticket_'.$student->hall_ticket_number.'.pdf');
    }

    /**
     * Asynchronous Bulk Download (for School Admin).
     */
    public function downloadBulk(Request $request)
    {
        $request->validate([
            'examination_id' => ['required', 'exists:examinations,id'],
        ]);

        $school = Auth::user()->school;
        if (! $school) {
            abort(403, 'User is not assigned to a school.');
        }

        $filters = $request->only(['gender', 'category_id', 'centre_id', 'search']);

        try {
            $batch = $this->batchService->createAndDispatchBatch(
                $school->id,
                (int) $request->examination_id,
                Auth::id(),
                $filters
            );
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Bulk download batch creation failed: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Unable to prepare the Hall Tickets. Please try again or contact the administrator.');
        }

        activity()
            ->log("School Admin requested bulk hall ticket batch #{$batch->id} for Exam ID: {$request->examination_id}");

        return redirect()->route('school.hall-tickets.batches.show', $batch)
            ->with('success', 'Your Hall Tickets are being prepared in the background.');
    }

    /**
     * Asynchronous Bulk Print/Download (for Super Admin).
     */
    public function printBulk(Request $request)
    {
        if (! Auth::user()->hasRole('super-admin')) {
            abort(403, 'Only Super Administrators can perform bulk print across institutions.');
        }

        $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'examination_id' => ['required', 'exists:examinations,id'],
        ]);

        $filters = $request->only(['gender', 'category_id', 'centre_id', 'search']);

        try {
            $batch = $this->batchService->createAndDispatchBatch(
                (int) $request->school_id,
                (int) $request->examination_id,
                Auth::id(),
                $filters
            );
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Super Admin bulk download batch creation failed: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Unable to prepare the Hall Tickets. Please try again or contact the administrator.');
        }

        activity()
            ->log("Super Admin requested bulk hall ticket batch #{$batch->id} for School ID: {$request->school_id}, Exam ID: {$request->examination_id}");

        return redirect()->route('admin.hall-tickets.batches.show', $batch)
            ->with('success', 'Hall Ticket batch prepared. PDF generation is processing in the background.');
    }

    /**
     * School Admin Batch Progress View.
     */
    public function showSchoolBatch(HallTicketBatch $batch)
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
                ? route('school.hall-tickets.parts.download', $p)
                : null,
        ]);

        return view('school-admin.hall-tickets.batch-progress', compact('batch', 'initialParts'));
    }

    /**
     * Super Admin Batch Progress View.
     */
    public function showAdminBatch(HallTicketBatch $batch)
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
                ? route('admin.hall-tickets.parts.download', $p)
                : null,
        ]);

        return view('super-admin.hall-tickets.batch-progress', compact('batch', 'initialParts'));
    }

    /**
     * Batch Status JSON Polling Endpoint.
     */
    public function batchStatus(HallTicketBatch $batch)
    {
        Gate::authorize('view', $batch);

        $parts = $batch->parts()->get(['id', 'batch_id', 'part_number', 'total_students', 'completed_students', 'status', 'pdf_path', 'file_size', 'error_message']);
        $isSuperAdmin = Auth::user()->hasRole('super-admin');

        $partsData = $parts->map(function ($part) use ($isSuperAdmin) {
            $downloadUrl = null;
            if ($part->status === 'completed' && ! empty($part->pdf_path)) {
                $downloadUrl = $isSuperAdmin
                    ? route('admin.hall-tickets.parts.download', $part)
                    : route('school.hall-tickets.parts.download', $part);
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
    public function downloadPart(HallTicketPdfPart $part)
    {
        Gate::authorize('download', $part);

        $batch = $part->batch;
        if (! $batch) {
            abort(404, 'Associated batch not found.');
        }

        if ($part->status !== 'completed' || empty($part->pdf_path)) {
            return back()->with('error', 'This PDF part is not yet ready for download.');
        }

        $disk = Storage::disk(config('hallticket.disk', 'local'));
        if (! $disk->exists($part->pdf_path)) {
            return back()->with('error', 'The requested PDF part file was not found or has expired.');
        }

        $schoolCode = $batch->school?->code ?? 'School';
        $examYear = $batch->examination?->academic_year ? str_replace('/', '_', $batch->examination->academic_year) : date('Y');
        $filename = "Hall_Tickets_{$schoolCode}_{$examYear}_Part_{$part->part_number}.pdf";

        activity()
            ->performedOn($batch)
            ->log('User '.Auth::user()->name." downloaded Hall Ticket PDF Part #{$part->part_number} for Batch #{$batch->id}");

        return $disk->download($part->pdf_path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Retry failed parts in a batch.
     */
    public function retryBatch(HallTicketBatch $batch)
    {
        Gate::authorize('retry', $batch);

        $this->batchService->retryFailedParts($batch);

        return back()->with('success', 'Failed Hall Ticket PDF parts have been requeued for processing.');
    }
}
