<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\School;
use App\Models\ClassMaster;
use App\Models\CategoryMaster;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Examination;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Super Admin Dashboard.
     */
    public function superAdmin()
    {
        $totalIssued = Student::where('status', 'Hall Ticket Issued')->count();
        $totalPresent = Attendance::where('status', 'Present')->count();
        $totalAbsent = $totalIssued - $totalPresent;
        $attendancePercentage = $totalIssued > 0 ? round(($totalPresent / $totalIssued) * 100, 1) : 0;

        $stats = [
            'total_schools' => School::count(),
            'total_classes' => ClassMaster::count(),
            'total_categories' => CategoryMaster::count(),
            'total_students' => Student::count(),
            'pending_verification' => Student::where('status', 'Submitted')->count(),
            'approved_registrations' => Student::where('status', 'Approved')->count(),
            'rejected_registrations' => Student::where('status', 'Rejected')->count(),
            'hall_tickets_issued' => $totalIssued,
            'total_present' => $totalPresent,
            'total_absent' => $totalAbsent,
            'attendance_percentage' => $attendancePercentage,
        ];

        // Charts Data
        // 1. School-wise registration & attendance
        $schoolWise = Student::join('schools', 'students.school_id', '=', 'schools.id')
            ->select('schools.name as school_name', DB::raw('count(students.id) as count'))
            ->groupBy('schools.name')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $schoolAttendance = School::leftJoin('students', 'schools.id', '=', 'students.school_id')
            ->leftJoin('attendance', 'students.id', '=', 'attendance.student_id')
            ->select(
                'schools.name as school_name',
                DB::raw('count(distinct case when students.status = "Hall Ticket Issued" then students.id end) as total_issued'),
                DB::raw('count(distinct case when attendance.status = "Present" then attendance.id end) as present_count')
            )
            ->groupBy('schools.name')
            ->orderByDesc('total_issued')
            ->take(10)
            ->get();

        // 2. Class-wise registration
        $classWise = Student::join('classes', 'students.class_id', '=', 'classes.id')
            ->select('classes.name as class_name', DB::raw('count(students.id) as count'))
            ->groupBy('classes.name')
            ->get();

        // 3. Category-wise registration & attendance
        $categoryWise = Student::join('categories', 'students.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', DB::raw('count(students.id) as count'))
            ->groupBy('categories.name')
            ->get();

        $categoryAttendance = CategoryMaster::leftJoin('students', 'categories.id', '=', 'students.category_id')
            ->leftJoin('attendance', 'students.id', '=', 'attendance.student_id')
            ->select(
                'categories.name as category_name',
                DB::raw('count(distinct case when students.status = "Hall Ticket Issued" then students.id end) as total_issued'),
                DB::raw('count(distinct case when attendance.status = "Present" then attendance.id end) as present_count')
            )
            ->groupBy('categories.name')
            ->get();

        // 4. Gender-wise Attendance
        $genderAttendance = Student::leftJoin('attendance', 'students.id', '=', 'attendance.student_id')
            ->where('students.status', 'Hall Ticket Issued')
            ->select(
                'students.gender',
                DB::raw('count(distinct students.id) as total_issued'),
                DB::raw('count(distinct case when attendance.status = "Present" then attendance.id end) as present_count')
            )
            ->groupBy('students.gender')
            ->get();

        // 5. Registration trend (last 10 days)
        $registrationTrend = Student::select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->take(10)
            ->get();

        // Recent 5 activities
        $recentActivities = Activity::with('causer')->latest()->take(5)->get();

        return view('super-admin.dashboard', compact(
            'stats',
            'schoolWise',
            'schoolAttendance',
            'classWise',
            'categoryWise',
            'categoryAttendance',
            'genderAttendance',
            'registrationTrend',
            'recentActivities'
        ));
    }

    /**
     * School Admin Dashboard.
     */
    public function schoolAdmin()
    {
        $school = Auth::user()->school;

        if (!$school) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'User not associated with any school.']);
        }

        $schoolIssued = Student::where('school_id', $school->id)->where('status', 'Hall Ticket Issued')->count();
        $schoolPresent = Attendance::whereIn('student_id', function($q) use ($school) {
            $q->select('id')->from('students')->where('school_id', $school->id);
        })->where('status', 'Present')->count();
        $schoolAbsent = $schoolIssued - $schoolPresent;
        $attendancePercentage = $schoolIssued > 0 ? round(($schoolPresent / $schoolIssued) * 100, 1) : 0;

        $stats = [
            'total_registered' => Student::where('school_id', $school->id)->count(),
            'submitted' => Student::where('school_id', $school->id)->where('status', 'Submitted')->count(),
            'approved' => Student::where('school_id', $school->id)->where('status', 'Approved')->count(),
            'rejected' => Student::where('school_id', $school->id)->where('status', 'Rejected')->count(),
            'hall_tickets_available' => $schoolIssued,
            'present_students' => $schoolPresent,
            'absent_students' => $schoolAbsent,
            'attendance_percentage' => $attendancePercentage,
        ];

        // Status counts for progress bar / chart
        $statusCounts = Student::where('school_id', $school->id)
            ->select('status', DB::raw('count(id) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses have keys
        $allStatuses = ['Draft', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Hall Ticket Issued'];
        $statusDistribution = [];
        foreach ($allStatuses as $status) {
            $statusDistribution[$status] = $statusCounts[$status] ?? 0;
        }

        // Registration trends for current school
        $registrationTrend = Student::where('school_id', $school->id)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->take(10)
            ->get();

        return view('school-admin.dashboard', compact('school', 'stats', 'statusDistribution', 'registrationTrend'));
    }

    /**
     * View Audit logs for Super Admin.
     */
    public function activityLogs()
    {
        $activities = Activity::with('causer')->latest()->paginate(25);
        return view('super-admin.activity-logs', compact('activities'));
    }
}
