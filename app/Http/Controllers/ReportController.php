<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\School;
use App\Models\ClassMaster;
use App\Models\CategoryMaster;
use App\Models\Examination;
use App\Exports\ReportsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Super Admin Reports Dashboard.
     */
    public function adminIndex(Request $request)
    {
        $reportType    = $request->get('type', 'school_wise');
        $examinationId = $request->get('examination_id');
        $classId       = $request->get('class_id');
        $categoryId    = $request->get('category_id');
        $zone          = $request->get('zone');

        $examinations = Examination::all();

        // Only show classes/categories that have student registrations
        $studentQuery = Student::whereNull('deleted_at');
        if ($examinationId) {
            $studentQuery->where('examination_id', $examinationId);
        }
        $registeredClassIds    = (clone $studentQuery)->distinct()->pluck('class_id');
        $registeredCategoryIds = (clone $studentQuery)->distinct()->pluck('category_id');

        $classes    = ClassMaster::whereIn('id', $registeredClassIds)->orderBy('name')->get();
        $categories = CategoryMaster::whereIn('id', $registeredCategoryIds)->orderBy('name')->get();
        $zones      = School::whereNotNull('zone')->where('zone', '!=', '')->distinct()->orderBy('zone')->pluck('zone');

        $reportData   = $this->getAdminReportData($reportType, $examinationId, $classId, $categoryId, $zone);
        $reportCharts = $this->getAdminReportCharts($examinationId);

        return view('super-admin.reports.index', compact(
            'reportType',
            'examinationId',
            'classId',
            'categoryId',
            'zone',
            'examinations',
            'classes',
            'categories',
            'zones',
            'reportData',
            'reportCharts'
        ));
    }

    /**
     * School Admin Reports Dashboard.
     */
    public function schoolIndex(Request $request)
    {
        $reportType    = $request->get('type', 'registered');
        $examinationId = $request->get('examination_id');
        $classId       = $request->get('class_id');
        $categoryId    = $request->get('category_id');

        $school       = Auth::user()->school;
        $examinations = Examination::all();

        // Only show classes/categories that have student registrations for this school
        $studentQuery = Student::whereNull('deleted_at')->where('school_id', $school->id);
        if ($examinationId) {
            $studentQuery->where('examination_id', $examinationId);
        }
        $registeredClassIds    = (clone $studentQuery)->distinct()->pluck('class_id');
        $registeredCategoryIds = (clone $studentQuery)->distinct()->pluck('category_id');

        $classes    = ClassMaster::whereIn('id', $registeredClassIds)->orderBy('name')->get();
        $categories = CategoryMaster::whereIn('id', $registeredCategoryIds)->orderBy('name')->get();

        $reportData = $this->getSchoolReportData($reportType, $school->id, $examinationId, $classId, $categoryId);

        return view('school-admin.reports.index', compact(
            'reportType',
            'examinationId',
            'classId',
            'categoryId',
            'examinations',
            'classes',
            'categories',
            'reportData'
        ));
    }

    /**
     * Export Super Admin Reports.
     */
    public function adminExport(Request $request)
    {
        $reportType    = $request->get('type', 'school_wise');
        $examinationId = $request->get('examination_id');
        $classId       = $request->get('class_id');
        $categoryId    = $request->get('category_id');
        $zone          = $request->get('zone');
        $format        = $request->get('format', 'excel'); // excel, csv, pdf

        $reportData = $this->getAdminReportData($reportType, $examinationId, $classId, $categoryId, $zone);
        $title = ucwords(str_replace('_', ' ', $reportType)) . ' Report';

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pdf.report', compact('reportData', 'title'));
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download(strtolower(str_replace(' ', '_', $title)) . '_' . time() . '.pdf');
        }

        $fileName = strtolower(str_replace(' ', '_', $title)) . '_' . time() . ($format === 'csv' ? '.csv' : '.xlsx');
        $excelFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(
            new ReportsExport($reportData['headings'], $reportData['export_rows']),
            $fileName,
            $excelFormat
        );
    }

    /**
     * Export School Admin Reports.
     */
    public function schoolExport(Request $request)
    {
        $reportType    = $request->get('type', 'registered');
        $examinationId = $request->get('examination_id');
        $classId       = $request->get('class_id');
        $categoryId    = $request->get('category_id');
        $format        = $request->get('format', 'excel');

        $school     = Auth::user()->school;
        $reportData = $this->getSchoolReportData($reportType, $school->id, $examinationId, $classId, $categoryId);
        $title      = $school->code . ' - ' . ucwords(str_replace('_', ' ', $reportType)) . ' Report';

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pdf.report', compact('reportData', 'title'));
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download(strtolower(str_replace(' ', '_', $title)) . '_' . time() . '.pdf');
        }

        $fileName = strtolower(str_replace(' ', '_', $title)) . '_' . time() . ($format === 'csv' ? '.csv' : '.xlsx');
        $excelFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(
            new ReportsExport($reportData['headings'], $reportData['export_rows']),
            $fileName,
            $excelFormat
        );
    }

    /**
     * Helper to get Super Admin Report Queries and structure.
     */
    protected function getAdminReportData($type, $examinationId = null, $classId = null, $categoryId = null, $zone = null)
    {
        $headings = [];
        $rows = [];
        $exportRows = [];
        $chart = null;

        switch ($type) {
            case 'zone_wise':
                $headings = ['Zone', 'Total Schools', 'Total Students', 'Drafts', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Hall Ticket Issued'];
                $query = School::leftJoin('students', function ($join) {
                    $join->on('schools.id', '=', 'students.school_id')
                        ->whereNull('students.deleted_at');
                });

                if ($examinationId) {
                    $query->where(function ($q) use ($examinationId) {
                        $q->where('students.examination_id', $examinationId)->orWhereNull('students.id');
                    });
                }
                if ($classId) {
                    $query->where(function ($q) use ($classId) {
                        $q->where('students.class_id', $classId)->orWhereNull('students.id');
                    });
                }
                if ($categoryId) {
                    $query->where(function ($q) use ($categoryId) {
                        $q->where('students.category_id', $categoryId)->orWhereNull('students.id');
                    });
                }

                $data = $query->select(
                    DB::raw('COALESCE(schools.zone, "Unassigned") as zone_name'),
                    DB::raw('count(distinct schools.id) as total_schools'),
                    DB::raw('count(students.id) as total'),
                    DB::raw('sum(case when students.status = "Draft" then 1 else 0 end) as draft'),
                    DB::raw('sum(case when students.status = "Submitted" then 1 else 0 end) as submitted'),
                    DB::raw('sum(case when students.status = "Under Review" then 1 else 0 end) as under_review'),
                    DB::raw('sum(case when students.status = "Approved" then 1 else 0 end) as approved'),
                    DB::raw('sum(case when students.status = "Rejected" then 1 else 0 end) as rejected'),
                    DB::raw('sum(case when students.status = "Hall Ticket Issued" then 1 else 0 end) as ht_issued')
                )
                    ->groupBy('zone_name')
                    ->get();

                foreach ($data as $item) {
                    $row = [
                        $item->zone_name,
                        $item->total_schools,
                        $item->total,
                        $item->draft ?? 0,
                        $item->submitted ?? 0,
                        $item->under_review ?? 0,
                        $item->approved ?? 0,
                        $item->rejected ?? 0,
                        $item->ht_issued ?? 0,
                    ];
                    $rows[]       = $row;
                    $exportRows[] = $row;
                }

                $ordered = $data->sortByDesc('total')->values();

                $chart = [
                    'title'      => 'Students by Zone',
                    'chartType'  => 'bar',
                    'stacked'    => true,
                    'categories' => $ordered->pluck('zone_name')->all(),
                    'series'     => [
                        ['name' => 'Draft', 'data' => $ordered->pluck('draft')->map(fn($v) => (int)($v ?? 0))->all()],
                        ['name' => 'Submitted', 'data' => $ordered->pluck('submitted')->map(fn($v) => (int)($v ?? 0))->all()],
                        ['name' => 'Under Review', 'data' => $ordered->pluck('under_review')->map(fn($v) => (int)($v ?? 0))->all()],
                        ['name' => 'Approved', 'data' => $ordered->pluck('approved')->map(fn($v) => (int)($v ?? 0))->all()],
                        ['name' => 'Rejected', 'data' => $ordered->pluck('rejected')->map(fn($v) => (int)($v ?? 0))->all()],
                        ['name' => 'Hall Ticket Issued', 'data' => $ordered->pluck('ht_issued')->map(fn($v) => (int)($v ?? 0))->all()],
                    ],
                ];
                break;

            case 'school_wise':
                $headings = ['School Code', 'School Name', 'Zone', 'Total Students', 'Drafts', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Hall Ticket Issued'];
                $query = School::leftJoin('students', function ($join) {
                    $join->on('schools.id', '=', 'students.school_id')
                        ->whereNull('students.deleted_at');
                });

                if ($examinationId) {
                    $query->where(function ($q) use ($examinationId) {
                        $q->where('students.examination_id', $examinationId)->orWhereNull('students.id');
                    });
                }
                if ($zone) {
                    $query->where('schools.zone', $zone);
                }

                $data = $query->select(
                    'schools.code',
                    'schools.name',
                    DB::raw('COALESCE(schools.zone, "Unassigned") as zone_name'),
                    DB::raw('count(students.id) as total'),
                    DB::raw('sum(case when students.status = "Draft" then 1 else 0 end) as draft'),
                    DB::raw('sum(case when students.status = "Submitted" then 1 else 0 end) as submitted'),
                    DB::raw('sum(case when students.status = "Under Review" then 1 else 0 end) as under_review'),
                    DB::raw('sum(case when students.status = "Approved" then 1 else 0 end) as approved'),
                    DB::raw('sum(case when students.status = "Rejected" then 1 else 0 end) as rejected'),
                    DB::raw('sum(case when students.status = "Hall Ticket Issued" then 1 else 0 end) as ht_issued')
                )
                    ->groupBy('schools.code', 'schools.name', 'zone_name')
                    ->get();

                foreach ($data as $item) {
                    $row = [
                        $item->code,
                        $item->name,
                        $item->zone_name,
                        $item->total,
                        $item->draft ?? 0,
                        $item->submitted ?? 0,
                        $item->under_review ?? 0,
                        $item->approved ?? 0,
                        $item->rejected ?? 0,
                        $item->ht_issued ?? 0
                    ];
                    $rows[] = $row;
                    $exportRows[] = $row;
                }

                $ordered = $data->sortByDesc('total')->values();

                $chart = [
                    'title' => 'Students by School',
                    'chartType' => 'bar',
                    'stacked' => true,
                    'categories' => $ordered->pluck('name')->all(),
                    'series' => [
                        ['name' => 'Draft', 'data' => $ordered->pluck('draft')->map(fn($value) => (int) ($value ?? 0))->all()],
                        ['name' => 'Submitted', 'data' => $ordered->pluck('submitted')->map(fn($value) => (int) ($value ?? 0))->all()],
                        ['name' => 'Under Review', 'data' => $ordered->pluck('under_review')->map(fn($value) => (int) ($value ?? 0))->all()],
                        ['name' => 'Approved', 'data' => $ordered->pluck('approved')->map(fn($value) => (int) ($value ?? 0))->all()],
                        ['name' => 'Rejected', 'data' => $ordered->pluck('rejected')->map(fn($value) => (int) ($value ?? 0))->all()],
                        ['name' => 'Hall Ticket Issued', 'data' => $ordered->pluck('ht_issued')->map(fn($value) => (int) ($value ?? 0))->all()],
                    ],
                ];
                break;

            case 'attendance':
                $headings = ['Reg. Number', 'HT Number', 'Student Name', 'School', 'Category', 'Exam Session', 'Date', 'Time', 'Marked By', 'Status'];

                $query = Student::join('schools', 'students.school_id', '=', 'schools.id')
                    ->join('categories', 'students.category_id', '=', 'categories.id')
                    ->join('examinations', 'students.examination_id', '=', 'examinations.id')
                    ->leftJoin('attendance', 'students.id', '=', 'attendance.student_id')
                    ->leftJoin('users', 'attendance.marked_by', '=', 'users.id')
                    ->where('students.status', 'Hall Ticket Issued');

                if ($examinationId) {
                    $query->where('students.examination_id', $examinationId);
                }

                if ($request = request()) {
                    if ($request->filled('school_id')) {
                        $query->where('students.school_id', $request->school_id);
                    }
                    if ($request->filled('category_id')) {
                        $query->where('students.category_id', $request->category_id);
                    }
                    if ($request->filled('date')) {
                        $query->whereDate('attendance.attendance_date', $request->date);
                    }
                }

                $data = $query->select(
                    'students.registration_number',
                    'students.hall_ticket_number',
                    'students.name as student_name',
                    'schools.name as school_name',
                    'categories.name as category_name',
                    'examinations.name as exam_name',
                    'attendance.attendance_date',
                    'attendance.attendance_time',
                    'users.name as marker_name',
                    DB::raw('COALESCE(attendance.status, "Absent") as status')
                )->get();

                foreach ($data as $item) {
                    $row = [
                        $item->registration_number ?? 'N/A',
                        $item->hall_ticket_number ?? 'N/A',
                        $item->student_name,
                        $item->school_name,
                        $item->category_name,
                        $item->exam_name,
                        $item->attendance_date ? date('d M Y', strtotime($item->attendance_date)) : 'N/A',
                        $item->attendance_time ? date('h:i A', strtotime($item->attendance_time)) : 'N/A',
                        $item->marker_name ?? 'N/A',
                        $item->status
                    ];
                    $rows[] = $row;
                    $exportRows[] = $row;
                }

                $grouped = $data->groupBy('status')->map(fn($items) => $items->count());
                $chart = [
                    'title' => 'Attendance Summary',
                    'chartType' => 'donut',
                    'categories' => $grouped->keys()->all(),
                    'labels' => $grouped->keys()->all(),
                    'series' => $grouped->values()->map(fn($value) => (int) $value)->all(),
                ];
                break;

            case 'attendance_category':
                $headings = ['Category Name', 'Total Tickets Issued', 'Present Today', 'Absent Today', 'Attendance Rate'];

                $query = CategoryMaster::leftJoin('students', function ($join) use ($examinationId) {
                    $join->on('categories.id', '=', 'students.category_id')
                        ->where('students.status', '=', 'Hall Ticket Issued')
                        ->whereNull('students.deleted_at');
                    if ($examinationId) {
                        $join->where('students.examination_id', '=', $examinationId);
                    }
                })
                    ->leftJoin('attendance', 'students.id', '=', 'attendance.student_id')
                    ->select(
                        'categories.name as category_name',
                        DB::raw('count(distinct students.id) as total_issued'),
                        DB::raw('count(distinct case when attendance.status = "Present" then attendance.id end) as present_count')
                    )
                    ->groupBy('categories.name');

                $data = $query->get();

                foreach ($data as $item) {
                    $total = $item->total_issued ?? 0;
                    $present = $item->present_count ?? 0;
                    $absent = $total - $present;
                    $rate = $total > 0 ? round(($present / $total) * 100, 1) . '%' : '0%';

                    $row = [
                        $item->category_name,
                        $total,
                        $present,
                        $absent,
                        $rate
                    ];
                    $rows[] = $row;
                    $exportRows[] = $row;
                }

                $ordered = $data->sortByDesc('total_issued')->values();

                $chart = [
                    'title' => 'Attendance by Category',
                    'chartType' => 'bar',
                    'categories' => $ordered->pluck('category_name')->all(),
                    'series' => [
                        ['name' => 'Tickets Issued', 'data' => $ordered->pluck('total_issued')->map(fn($value) => (int) $value)->all()],
                        ['name' => 'Present Today', 'data' => $ordered->pluck('present_count')->map(fn($value) => (int) $value)->all()],
                    ],
                ];
                break;

            case 'attendance_gender':
                $headings = ['Gender', 'Total Tickets Issued', 'Present Today', 'Absent Today', 'Attendance Rate'];

                $query = Student::where('students.status', 'Hall Ticket Issued')
                    ->leftJoin('attendance', 'students.id', '=', 'attendance.student_id')
                    ->select(
                        'students.gender',
                        DB::raw('count(distinct students.id) as total_issued'),
                        DB::raw('count(distinct case when attendance.status = "Present" then attendance.id end) as present_count')
                    )
                    ->groupBy('students.gender');

                if ($examinationId) {
                    $query->where('students.examination_id', $examinationId);
                }

                $data = $query->get();

                foreach ($data as $item) {
                    $genderLabel = ucfirst($item->gender ?? 'Unknown');
                    $total = $item->total_issued ?? 0;
                    $present = $item->present_count ?? 0;
                    $absent = $total - $present;
                    $rate = $total > 0 ? round(($present / $total) * 100, 1) . '%' : '0%';

                    $row = [
                        $genderLabel,
                        $total,
                        $present,
                        $absent,
                        $rate
                    ];
                    $rows[] = $row;
                    $exportRows[] = $row;
                }

                $ordered = $data->sortByDesc('total_issued')->values();

                $chart = [
                    'title' => 'Attendance by Gender',
                    'chartType' => 'bar',
                    'categories' => $ordered->pluck('gender')->map(fn($g) => ucfirst($g ?? 'Unknown'))->all(),
                    'series' => [
                        ['name' => 'Tickets Issued', 'data' => $ordered->pluck('total_issued')->map(fn($value) => (int) $value)->all()],
                        ['name' => 'Present Today', 'data' => $ordered->pluck('present_count')->map(fn($value) => (int) $value)->all()],
                    ],
                ];
                break;

            case 'class_wise':
                $headings = ['Class Name', 'Total Students Registered'];
                $query = ClassMaster::leftJoin('students', function ($join) {
                    $join->on('classes.id', '=', 'students.class_id')
                        ->whereNull('students.deleted_at');
                });

                if ($examinationId) {
                    $query->where(function ($q) use ($examinationId) {
                        $q->where('students.examination_id', $examinationId)->orWhereNull('students.id');
                    });
                }
                if ($categoryId) {
                    $query->where(function ($q) use ($categoryId) {
                        $q->where('students.category_id', $categoryId)->orWhereNull('students.id');
                    });
                }
                if ($zone) {
                    $query->leftJoin('schools as s_zone', 's_zone.id', '=', 'students.school_id')
                          ->where('s_zone.zone', $zone);
                }

                $data = $query->select('classes.name', DB::raw('count(students.id) as total'))
                    ->groupBy('classes.name')
                    ->get();

                foreach ($data as $item) {
                    $row = [$item->name, $item->total];
                    $rows[] = $row;
                    $exportRows[] = $row;
                }

                $ordered = $data->sortByDesc('total')->values();

                $chart = [
                    'title' => 'Students by Class',
                    'chartType' => 'donut',
                    'categories' => $ordered->pluck('name')->all(),
                    'labels' => $ordered->pluck('name')->all(),
                    'series' => $ordered->pluck('total')->map(fn($value) => (int) $value)->all(),
                ];
                break;

            case 'category_wise':
                $headings = ['Category Code', 'Category Name', 'Total Students Registered'];
                $query = CategoryMaster::leftJoin('students', function ($join) {
                    $join->on('categories.id', '=', 'students.category_id')
                        ->whereNull('students.deleted_at');
                });

                if ($examinationId) {
                    $query->where(function ($q) use ($examinationId) {
                        $q->where('students.examination_id', $examinationId)->orWhereNull('students.id');
                    });
                }
                if ($classId) {
                    $query->where(function ($q) use ($classId) {
                        $q->where('students.class_id', $classId)->orWhereNull('students.id');
                    });
                }
                if ($zone) {
                    $query->leftJoin('schools as s_zone2', 's_zone2.id', '=', 'students.school_id')
                          ->where('s_zone2.zone', $zone);
                }

                $data = $query->select('categories.code', 'categories.name', DB::raw('count(students.id) as total'))
                    ->groupBy('categories.code', 'categories.name')
                    ->get();

                foreach ($data as $item) {
                    $row = [$item->code, $item->name, $item->total];
                    $rows[] = $row;
                    $exportRows[] = $row;
                }

                $ordered = $data->sortByDesc('total')->values();

                $chart = [
                    'title' => 'Students by Category',
                    'chartType' => 'pie',
                    'categories' => $ordered->pluck('name')->all(),
                    'labels' => $ordered->pluck('name')->all(),
                    'series' => $ordered->pluck('total')->map(fn($value) => (int) $value)->all(),
                ];
                break;

            case 'examination_wise':
                $headings = ['Examination Name', 'Academic Year', 'Total Registrations'];
                $data = Examination::leftJoin('students', function ($join) {
                    $join->on('examinations.id', '=', 'students.examination_id')
                        ->whereNull('students.deleted_at');
                })
                    ->select('examinations.name', 'examinations.academic_year', DB::raw('count(students.id) as total'))
                    ->groupBy('examinations.name', 'examinations.academic_year')
                    ->get();

                foreach ($data as $item) {
                    $row = [$item->name, $item->academic_year, $item->total];
                    $rows[] = $row;
                    $exportRows[] = $row;
                }

                $ordered = $data->sortByDesc('total')->values();

                $chart = [
                    'title' => 'Registrations by Examination',
                    'chartType' => 'area',
                    'stacked' => false,
                    'categories' => $ordered->pluck('name')->all(),
                    'series' => [
                        ['name' => 'Registrations', 'data' => $ordered->pluck('total')->map(fn($value) => (int) $value)->all()],
                    ],
                ];
                break;

            case 'approved':
            case 'rejected':
            case 'hall_ticket':
                $headings = ['Reg. Number', 'HT Number', 'Student Name', 'School', 'Class', 'Category', 'Exam Session', 'Status'];
                if ($type === 'rejected') {
                    $headings[] = 'Rejection Remarks';
                }

                $statusFilter = $type === 'approved' ? 'Approved' : ($type === 'rejected' ? 'Rejected' : 'Hall Ticket Issued');
                $query = Student::where('students.status', $statusFilter)
                    ->join('schools', 'students.school_id', '=', 'schools.id')
                    ->join('classes', 'students.class_id', '=', 'classes.id')
                    ->join('categories', 'students.category_id', '=', 'categories.id')
                    ->join('examinations', 'students.examination_id', '=', 'examinations.id');

                if ($examinationId) {
                    $query->where('students.examination_id', $examinationId);
                }

                $data = $query->select(
                    'students.registration_number',
                    'students.hall_ticket_number',
                    'students.name as student_name',
                    'schools.name as school_name',
                    'classes.name as class_name',
                    'categories.name as category_name',
                    'examinations.name as exam_name',
                    'students.status',
                    'students.remarks'
                )->get();

                foreach ($data as $item) {
                    $row = [
                        $item->registration_number ?? 'N/A',
                        $item->hall_ticket_number ?? 'N/A',
                        $item->student_name,
                        $item->school_name,
                        $item->class_name,
                        $item->category_name,
                        $item->exam_name,
                        $item->status
                    ];
                    if ($type === 'rejected') {
                        $row[] = $item->remarks ?? 'N/A';
                    }
                    $rows[] = $row;
                    $exportRows[] = $row;
                }

                $groupedBySchool = $data->groupBy('school_name')->map(fn($items) => $items->count())->sortDesc();
                $chartType = match ($type) {
                    'approved' => 'bar',
                    'rejected' => 'line',
                    'hall_ticket' => 'pie',
                    default => 'bar',
                };
                if ($chartType === 'pie') {
                    $chart = [
                        'title' => ucfirst(str_replace('_', ' ', $type)) . ' Records by School',
                        'chartType' => $chartType,
                        'categories' => $groupedBySchool->keys()->values()->all(),
                        'labels' => $groupedBySchool->keys()->values()->all(),
                        'series' => $groupedBySchool->values()->map(fn($value) => (int) $value)->all(),
                    ];
                } else {
                    $chart = [
                        'title' => ucfirst(str_replace('_', ' ', $type)) . ' Records by School',
                        'chartType' => $chartType,
                        'stacked' => false,
                        'categories' => $groupedBySchool->keys()->values()->all(),
                        'series' => [
                            ['name' => 'Records', 'data' => $groupedBySchool->values()->map(fn($value) => (int) $value)->all()],
                        ],
                    ];
                }
                break;
        }

        return [
            'headings' => $headings,
            'rows' => $rows,
            'export_rows' => $exportRows,
            'chart' => $chart,
        ];
    }

    /**
     * Build chart data for all report types.
     */
    protected function getAdminReportCharts($examinationId = null): array
    {
        $types = [
            'zone_wise',
            'school_wise',
            'class_wise',
            'category_wise',
            'examination_wise',
            'approved',
            'rejected',
            'hall_ticket',
            'attendance',
            'attendance_category',
            'attendance_gender',
        ];

        $charts = [];

        foreach ($types as $type) {
            $charts[$type] = $this->getAdminReportData($type, $examinationId)['chart'];
        }

        return $charts;
    }

    /**
     * Helper to get School Admin Report Queries and structure.
     */
    protected function getSchoolReportData($type, $schoolId, $examinationId = null, $classId = null, $categoryId = null)
    {
        $headings = [];
        $rows = [];
        $exportRows = [];

        $query = Student::where('students.school_id', $schoolId)
            ->join('categories', 'students.category_id', '=', 'categories.id')
            ->join('examinations', 'students.examination_id', '=', 'examinations.id');

        if ($examinationId) {
            $query->where('students.examination_id', $examinationId);
        }

        switch ($type) {
            case 'attendance':
                $headings = ['Reg. Number', 'HT Number', 'Student Name', 'Category', 'Exam Session', 'Date', 'Time', 'Marked By', 'Status'];
                $query->leftJoin('attendance', 'students.id', '=', 'attendance.student_id')
                    ->leftJoin('users', 'attendance.marked_by', '=', 'users.id')
                    ->where('students.status', 'Hall Ticket Issued');

                if ($request = request()) {
                    if ($request->filled('category_id')) {
                        $query->where('students.category_id', $request->category_id);
                    }
                    if ($request->filled('date')) {
                        $query->whereDate('attendance.attendance_date', $request->date);
                    }
                }

                $data = $query->select(
                    'students.registration_number',
                    'students.hall_ticket_number',
                    'students.name as student_name',
                    'categories.name as category_name',
                    'examinations.name as exam_name',
                    'attendance.attendance_date',
                    'attendance.attendance_time',
                    'users.name as marker_name',
                    DB::raw('COALESCE(attendance.status, "Absent") as status')
                )->get();

                foreach ($data as $item) {
                    $row = [
                        $item->registration_number ?? 'N/A',
                        $item->hall_ticket_number ?? 'N/A',
                        $item->student_name,
                        $item->category_name,
                        $item->exam_name,
                        $item->attendance_date ? date('d M Y', strtotime($item->attendance_date)) : 'N/A',
                        $item->attendance_time ? date('h:i A', strtotime($item->attendance_time)) : 'N/A',
                        $item->marker_name ?? 'N/A',
                        $item->status
                    ];
                    $rows[] = $row;
                    $exportRows[] = $row;
                }
                break;

            default:
                $headings = ['Reg. Number', 'Student Name', 'Class', 'Category', 'Exam Session', 'Status', 'HT Number'];
                $query->join('classes', 'students.class_id', '=', 'classes.id');

                switch ($type) {
                    case 'submitted':
                        $query->where('students.status', 'Submitted');
                        break;
                    case 'approved':
                        $query->where('students.status', 'Approved');
                        break;
                    case 'rejected':
                        $query->where('students.status', 'Rejected');
                        $headings[] = 'Remarks';
                        break;
                    case 'hall_ticket':
                        $query->where('students.status', 'Hall Ticket Issued');
                        break;
                }

                // Apply optional class and category filters
                if ($classId) {
                    $query->where('students.class_id', $classId);
                }
                if ($categoryId) {
                    $query->where('students.category_id', $categoryId);
                }

                $data = $query->select(
                    'students.registration_number',
                    'students.name as student_name',
                    'classes.name as class_name',
                    'categories.name as category_name',
                    'examinations.name as exam_name',
                    'students.status',
                    'students.hall_ticket_number',
                    'students.remarks'
                )->get();

                foreach ($data as $item) {
                    $row = [
                        $item->registration_number ?? 'N/A',
                        $item->student_name,
                        $item->class_name,
                        $item->category_name,
                        $item->exam_name,
                        $item->status,
                        $item->hall_ticket_number ?? 'N/A'
                    ];
                    if ($type === 'rejected') {
                        $row[] = $item->remarks ?? 'N/A';
                    }
                    $rows[] = $row;
                    $exportRows[] = $row;
                }
                break;
        }

        return [
            'headings' => $headings,
            'rows' => $rows,
            'export_rows' => $exportRows
        ];
    }
}
