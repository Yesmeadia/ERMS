<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Examination;
use App\Models\HallTicket;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\School;
use App\Models\ClassMaster;
use App\Models\CategoryMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\UniqueConstraintViolationException;

class AttendanceController extends Controller
{
    /**
     * Show the QR Scanner.
     */
    public function scanner(Request $request)
    {
        // Restrict invigilators if no examination session is ongoing
        $ongoingExamExists = Examination::where('status', 'Examination Ongoing')->exists();
        if (!$ongoingExamExists && Auth::user()->hasRole('invigilator')) {
            return redirect()->route('attendance.history')->with('error', 'Attendance marking is only active when an examination is ongoing.');
        }

        $userAgent = strtolower($request->userAgent() ?? '');
        $cameraHelpMessage = null;

        // Check if device is mobile
        $isMobile = false;
        $mobileKeywords = ['mobi', 'android', 'iphone', 'ipad', 'ipod', 'windows phone', 'blackberry'];
        foreach ($mobileKeywords as $keyword) {
            if (str_contains($userAgent, $keyword)) {
                $isMobile = true;
                break;
            }
        }

        if ($isMobile) {
            // Determine browser-specific camera permission tips
            if (str_contains($userAgent, 'crios') || str_contains($userAgent, 'chrome')) {
                $cameraHelpMessage = "Tip for Google Chrome: If the camera doesn't start, tap the three dots (⋮) in the top-right > Settings > Site settings > Camera > Allow.";
            } elseif (str_contains($userAgent, 'fxios') || str_contains($userAgent, 'firefox')) {
                $cameraHelpMessage = "Tip for Mozilla Firefox: If the camera doesn't start, tap menu (⋮) > Settings > Site permissions > Camera > Allow.";
            } elseif (str_contains($userAgent, 'edgios') || str_contains($userAgent, 'edge')) {
                $cameraHelpMessage = "Tip for Microsoft Edge: If the camera doesn't start, tap menu (⋯) > Settings > Site permissions > Camera > Allow.";
            } elseif (str_contains($userAgent, 'safari') && (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad'))) {
                $cameraHelpMessage = "Tip for Safari: If the camera doesn't start, tap the 'aA' icon in the URL bar > Website Settings > Camera > Allow.";
            } else {
                $cameraHelpMessage = "Tip: If the camera doesn't start, please ensure you have granted camera permissions in your mobile browser settings.";
            }
        }

        return view('attendance.scanner', compact('cameraHelpMessage'));
    }

    /**
     * Show history of scans by current user.
     */
    public function history()
    {
        $user = Auth::user();

        // Get count of present and logs for today
        $today = now()->toDateString();
        
        $totalScans = Attendance::where('marked_by', $user->id)
            ->whereDate('attendance_date', $today)
            ->count();

        $logs = AttendanceLog::where('scanner_user_id', $user->id)
            ->with(['student.school'])
            ->latest()
            ->paginate(15);

        return view('attendance.history', compact('totalScans', 'logs'));
    }

    /**
     * Verify scanned QR code payload.
     */
    public function verifyScan(Request $request)
    {
        $payload = $request->input('payload');
        
        if (!$payload) {
            return response()->json(['error' => 'No QR data received.'], 400);
        }

        // F7: Reject oversized payloads before any processing (DoS / injection guard)
        if (strlen($payload) > 2000) {
            $this->logAction(null, 'scan_tampered', $request);
            return response()->json(['status' => 'error', 'message' => 'QR payload rejected: data exceeds maximum allowed size.'], 422);
        }

        // 1. Parse JSON
        $data = json_decode($payload, true);

        if (!$data || !isset($data['student_id'], $data['hallticket_no'], $data['exam_id'], $data['token'])) {
            // Log as invalid scan (if we can parse student_id we log it, otherwise null)
            $studentId = $data['student_id'] ?? null;
            $this->logAction($studentId, 'scan_invalid', $request);
            
            return response()->json(['status' => 'error', 'message' => 'Invalid Hall Ticket. QR payload is corrupted or invalid.'], 422);
        }

        $studentId = $data['student_id'];
        $hallticketNo = $data['hallticket_no'];
        $examId = $data['exam_id'];
        $token = $data['token'];

        // 2. Verify Student
        $student = Student::with(['school', 'class', 'category', 'centre'])->find($studentId);

        if (!$student) {
            $this->logAction(null, 'scan_invalid', $request);
            return response()->json(['status' => 'error', 'message' => 'Invalid Hall Ticket. Student record not found.'], 422);
        }

        // 2.1 Data-level authorization check (CWE-285)
        $user = Auth::user();
        if ($user->hasRole('invigilator')) {
            if (!$user->school_id || $student->school_id !== $user->school_id) {
                $this->logAction($studentId, 'scan_unauthorized', $request);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You are only authorized to mark attendance for students from your assigned school.'
                ], 403);
            }
        }

        // 3. Verify Hall Ticket Table Record
        $hallTicket = HallTicket::where('student_id', $studentId)
            ->where('hallticket_no', $hallticketNo)
            ->where('qr_token', $token)
            ->first();

        if (!$hallTicket || $hallTicket->status !== 'Issued') {
            $this->logAction($studentId, 'scan_invalid', $request);
            return response()->json(['status' => 'error', 'message' => 'Invalid Hall Ticket. Signature token verification failed.'], 422);
        }

        // 4. Verify Exam Session
        $exam = Examination::find($examId);
        if (!$exam || $exam->status !== 'Examination Ongoing') {
            $this->logAction($studentId, 'scan_invalid', $request);
            return response()->json(['status' => 'error', 'message' => 'Attendance marking is only allowed when the examination is ongoing.'], 422);
        }

        // 5. Verify Duplicate Attendance
        $today = now()->toDateString();
        $alreadyMarked = Attendance::where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->where('attendance_date', $today)
            ->exists();

        if ($alreadyMarked) {
            $this->logAction($studentId, 'scan_duplicate', $request);
            return response()->json([
                'status' => 'duplicate',
                'message' => 'Attendance Already Marked',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'registration_number' => $student->registration_number,
                    'hall_ticket_number' => $student->hall_ticket_number,
                    'school' => $student->school->name,
                    'class' => $student->class->name,
                    'centre' => optional($student->centre)->name ?? 'Not Assigned',
                    'photo_url' => $student->photo_url,
                ]
            ]);
        }

        // If everything checks out, log scan_success and return student data for confirmation screen
        $this->logAction($studentId, 'scan_success', $request);

        return response()->json([
            'status' => 'success',
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'registration_number' => $student->registration_number,
                'hall_ticket_number' => $student->hall_ticket_number,
                'school' => $student->school->name,
                'class' => $student->class->name,
                'category' => $student->category->name,
                'centre' => optional($student->centre)->name ?? 'Not Assigned',
                'exam_name' => $exam->name,
                'photo_url' => $student->photo_url,
            ],
            'exam_id' => $examId
        ]);
    }

    /**
     * Confirms and marks student as present.
     */
    public function markPresent(Request $request)
    {
        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'exam_id' => ['required', 'exists:examinations,id'],
        ]);

        $studentId = $request->student_id;
        $examId = $request->exam_id;
        $user = Auth::user();
        $today = now()->toDateString();

        $student = Student::findOrFail($studentId);
        $exam = Examination::findOrFail($examId);

        // Verify Exam Session is active
        if ($exam->status !== 'Examination Ongoing') {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance marking is only allowed when the examination is ongoing.'
            ], 422);
        }

        // Data-level authorization check (CWE-285)
        if ($user->hasRole('invigilator')) {
            if (!$user->school_id || $student->school_id !== $user->school_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You are only authorized to mark attendance for students from your assigned school.'
                ], 403);
            }
        }

        try {
            // F5: Wrap in a transaction so the duplicate check + insert are atomic.
            // The DB unique constraint (student_id, exam_id, attendance_date) is the
            // final safety net if two requests race past the pre-check simultaneously.
            $student = DB::transaction(function () use ($studentId, $examId, $today, $user, $request) {
                // Re-verify inside the transaction to close the race window
                $alreadyMarked = Attendance::where('student_id', $studentId)
                    ->where('exam_id', $examId)
                    ->where('attendance_date', $today)
                    ->exists();

                if ($alreadyMarked) {
                    // Throw a specific exception to break out of the transaction cleanly
                    throw new \RuntimeException('duplicate');
                }

                Attendance::create([
                    'student_id' => $studentId,
                    'exam_id' => $examId,
                    'attendance_date' => $today,
                    'attendance_time' => now()->toTimeString(),
                    'marked_by' => $user->id,
                    'status' => 'Present',
                ]);

                $this->logAction($studentId, 'mark_present', $request);

                return Student::find($studentId);
            });
        } catch (UniqueConstraintViolationException $e) {
            // Caught when two concurrent requests both passed the pre-check and
            // the DB unique constraint blocked the second insert.
            return response()->json([
                'status' => 'duplicate',
                'message' => 'Attendance was already marked for this student today.'
            ]);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'duplicate') {
                return response()->json([
                    'status' => 'duplicate',
                    'message' => 'Attendance was already marked for this student today.'
                ]);
            }
            throw $e;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance Marked Successfully',
            'data' => [
                'student_name' => $student->name,
                'hallticket_no' => $student->hall_ticket_number,
                'scan_time' => now()->format('d M Y, h:i A')
            ]
        ]);
    }

    /**
     * F1: Lightweight attendance count API for the scanner counter panel.
     * Returns only the today's present-mark count for the authenticated user.
     */
    public function scanCount()
    {
        $count = Attendance::where('marked_by', Auth::id())
            ->whereDate('attendance_date', now()->toDateString())
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Helper to write to attendance_logs.
     */
    private function logAction($studentId, $action, Request $request)
    {
        AttendanceLog::create([
            'student_id' => $studentId,
            'scanner_user_id' => Auth::id() ?? 1, // Fallback if admin guest scanned
            'scan_time' => now(),
            'device_info' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'action' => $action,
        ]);
    }

    /**
     * Super Admin Manual Attendance Management.
     */
    public function adminAttendanceIndex(Request $request)
    {
        $examinations = Examination::all();
        $schools = School::where('status', true)->get();
        $classes = ClassMaster::where('status', true)->get();

        $examinationId = $request->get('examination_id');
        if (!$examinationId && $examinations->count() > 0) {
            $activeExam = $examinations->where('status', 'Active')->first();
            $examinationId = $activeExam ? $activeExam->id : $examinations->first()->id;
        }

        $date = $request->get('date', now()->toDateString());
        $schoolId = $request->get('school_id');
        $classId = $request->get('class_id');
        $search = $request->get('search');

        $baseQuery = function() use ($date, $examinationId, $schoolId, $classId, $search) {
            $q = Student::where('students.status', 'Hall Ticket Issued')
                ->leftJoin('attendance', function($join) use ($date, $examinationId) {
                    $join->on('students.id', '=', 'attendance.student_id')
                         ->whereDate('attendance.attendance_date', '=', $date);
                    if ($examinationId) {
                        $join->where('attendance.exam_id', '=', $examinationId);
                    }
                })
                ->select(
                    'students.*',
                    'attendance.status as attendance_status',
                    'attendance.attendance_time'
                );
            if ($examinationId) $q->where('students.examination_id', $examinationId);
            if ($schoolId)      $q->where('students.school_id', $schoolId);
            if ($classId)       $q->where('students.class_id', $classId);
            if ($search) {
                $q->where(function($s) use ($search) {
                    $s->where('students.name', 'like', "%{$search}%")
                      ->orWhere('students.registration_number', 'like', "%{$search}%")
                      ->orWhere('students.hall_ticket_number', 'like', "%{$search}%");
                });
            }
            return $q;
        };

        // Aggregate summary
        $allRows = $baseQuery()->get();
        $total   = $allRows->count();
        $present = $allRows->where('attendance_status', 'Present')->count();
        $absent  = $total - $present;
        $percent = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        $summary = compact('total', 'present', 'absent', 'percent');

        // Class-wise breakdown
        $classBreakdown = $allRows->groupBy(fn($s) => optional($s->class)->name ?? 'N/A')
            ->map(fn($grp) => [
                'total'   => $grp->count(),
                'present' => $grp->where('attendance_status', 'Present')->count(),
                'absent'  => $grp->where('attendance_status', 'Absent')->count()
                              + $grp->whereNull('attendance_status')->count(),
            ]);

        $students = $baseQuery()->with(['school', 'class', 'category'])->paginate(20)->withQueryString();

        return view('attendance.index', compact(
            'students',
            'examinations',
            'schools',
            'classes',
            'examinationId',
            'date',
            'schoolId',
            'classId',
            'search',
            'summary',
            'classBreakdown'
        ));
    }

    /**
     * School Admin View Attendance Report.
     */
    public function schoolAttendanceIndex(Request $request)
    {
        $school = Auth::user()->school;
        if (!$school) {
            return redirect()->route('school.dashboard')->with('error', 'No school associated with this account.');
        }

        $examinations = Examination::all();
        $classes = ClassMaster::where('status', true)->get();

        $examinationId = $request->get('examination_id');
        if (!$examinationId && $examinations->count() > 0) {
            $activeExam = $examinations->where('status', 'Active')->first();
            $examinationId = $activeExam ? $activeExam->id : $examinations->first()->id;
        }

        $date = $request->get('date', now()->toDateString());
        $classId = $request->get('class_id');
        $search = $request->get('search');

        $baseQuery = function() use ($school, $date, $examinationId, $classId, $search) {
            $q = Student::where('students.school_id', $school->id)
                ->where('students.status', 'Hall Ticket Issued')
                ->leftJoin('attendance', function($join) use ($date, $examinationId) {
                    $join->on('students.id', '=', 'attendance.student_id')
                         ->whereDate('attendance.attendance_date', '=', $date);
                    if ($examinationId) {
                        $join->where('attendance.exam_id', '=', $examinationId);
                    }
                })
                ->select(
                    'students.*',
                    'attendance.status as attendance_status',
                    'attendance.attendance_time'
                );
            if ($examinationId) $q->where('students.examination_id', $examinationId);
            if ($classId)       $q->where('students.class_id', $classId);
            if ($search) {
                $q->where(function($s) use ($search) {
                    $s->where('students.name', 'like', "%{$search}%")
                      ->orWhere('students.registration_number', 'like', "%{$search}%")
                      ->orWhere('students.hall_ticket_number', 'like', "%{$search}%");
                });
            }
            return $q;
        };

        // Aggregate summary
        $allRows = $baseQuery()->get();
        $total   = $allRows->count();
        $present = $allRows->where('attendance_status', 'Present')->count();
        $absent  = $total - $present;
        $percent = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        $summary = compact('total', 'present', 'absent', 'percent');

        // Class-wise breakdown
        $classBreakdown = $allRows->groupBy(fn($s) => optional($s->class)->name ?? 'N/A')
            ->map(fn($grp) => [
                'total'   => $grp->count(),
                'present' => $grp->where('attendance_status', 'Present')->count(),
                'absent'  => $grp->where('attendance_status', 'Absent')->count()
                              + $grp->whereNull('attendance_status')->count(),
            ]);

        $students = $baseQuery()->with(['class', 'category'])->paginate(20)->withQueryString();

        return view('attendance.index', compact(
            'students',
            'examinations',
            'classes',
            'examinationId',
            'date',
            'classId',
            'search',
            'summary',
            'classBreakdown'
        ));
    }

    /**
     * Mark manual attendance (Super Admin only).
     */
    public function adminAttendanceMark(Request $request)
    {
        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'exam_id' => ['required', 'exists:examinations,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:Present,Absent'],
        ]);

        $studentId = $request->student_id;
        $examId = $request->exam_id;
        $date = $request->date;
        $status = $request->status;
        $adminId = Auth::id();

        $exam = Examination::findOrFail($examId);
        if ($exam->status === 'result published') {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance cannot be marked or changed after results are published.'
            ], 422);
        }

        // Update or insert attendance record
        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $studentId,
                'exam_id' => $examId,
                'attendance_date' => $date,
            ],
            [
                'attendance_time' => now()->toTimeString(),
                'marked_by' => $adminId,
                'status' => $status,
            ]
        );

        // Also write log
        AttendanceLog::create([
            'student_id' => $studentId,
            'scanner_user_id' => $adminId,
            'scan_time' => now(),
            'device_info' => $request->userAgent() . ' (Manual Mark)',
            'ip_address' => $request->ip(),
            'action' => $status === 'Present' ? 'mark_present' : 'mark_absent',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance marked successfully.'
        ]);
    }
}
