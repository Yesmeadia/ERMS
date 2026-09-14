<?php

namespace App\Http\Controllers;

use App\Models\CategoryMaster;
use App\Models\ClassMaster;
use App\Models\Examination;
use App\Models\School;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamCentreController extends Controller
{
    /**
     * Display a listing of the exam centres and handles assignment.
     */
    public function index(Request $request)
    {
        // 1. List of designated centres with assigned student counts
        $query = School::where('is_centre', true)
            ->withCount(['assignedStudents' => function ($q) {
                $q->whereNull('deleted_at');
            }]);

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('zone', 'like', "%{$search}%");
            });
        }
        $centres = $query->latest()->paginate(10, ['*'], 'centres_page');

        // 2. All schools for designation list
        $schoolsQuery = School::query();
        if ($request->filled('school_search')) {
            $sSearch = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->school_search);
            $schoolsQuery->where(function ($q) use ($sSearch) {
                $q->where('name', 'like', "%{$sSearch}%")
                    ->orWhere('code', 'like', "%{$sSearch}%");
            });
        }
        $allSchools = $schoolsQuery->latest()->paginate(10, ['*'], 'schools_page');

        // 3. Dropdowns for assignment form
        $registeredSchools = School::where('status', true)->get();
        $designatedCentres = School::where('is_centre', true)->where('status', true)->get();
        $examinations = Examination::all();

        // 4. Statistics
        $totalCentres = School::where('is_centre', true)->count();
        $totalAssigned = Student::whereNotNull('centre_id')->count();
        $totalPending = Student::whereNull('centre_id')->whereIn('status', ['Approved', 'Hall Ticket Issued'])->count();

        return view('super-admin.exam-centres.index', compact(
            'centres',
            'allSchools',
            'registeredSchools',
            'designatedCentres',
            'examinations',
            'totalCentres',
            'totalAssigned',
            'totalPending'
        ));
    }

    /**
     * Display the details, student list, category/gender breakdown, and seat planner for a selected exam centre.
     */
    public function show(Request $request, School $school)
    {
        $this->authorizeCentreAccess($school);

        // Available examinations
        $examinations = Examination::all();
        $activeExam = Examination::whereIn('status', ['Registration Started', 'Registartion closed', 'Examination Ongoing', 'result published'])
            ->latest()
            ->first() ?? Examination::latest()->first();

        // Base student query for this centre
        $baseQuery = Student::where('centre_id', $school->id)->whereNull('deleted_at');

        // Get filter dropdown options from assigned students
        $assignedExamIds = (clone $baseQuery)->distinct()->pluck('examination_id');
        $assignedCategoryIds = (clone $baseQuery)->distinct()->pluck('category_id');
        $assignedClassIds = (clone $baseQuery)->distinct()->pluck('class_id');
        $assignedSchoolIds = (clone $baseQuery)->distinct()->pluck('school_id');

        $filterExaminations = Examination::whereIn('id', $assignedExamIds)->get();
        $filterCategories = CategoryMaster::whereIn('id', $assignedCategoryIds)->orderBy('name')->get();
        $filterClasses = ClassMaster::whereIn('id', $assignedClassIds)->orderBy('name')->get();
        $filterSchools = School::whereIn('id', $assignedSchoolIds)->orderBy('name')->get();

        // Build filtered query
        $query = Student::where('centre_id', $school->id)
            ->whereNull('deleted_at')
            ->with(['school', 'class', 'category', 'examination', 'hallTicket']);

        // Default to active exam if not explicitly requested and exam exists
        $selectedExamId = $request->get('examination_id');
        if ($request->has('examination_id')) {
            if ($request->filled('examination_id')) {
                $query->where('examination_id', $request->examination_id);
            }
        } elseif ($activeExam && $assignedExamIds->contains($activeExam->id)) {
            $selectedExamId = $activeExam->id;
            $query->where('examination_id', $activeExam->id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('gender')) {
            $reqGender = strtolower(trim($request->gender));
            if (in_array($reqGender, ['male', 'm'])) {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(TRIM(gender)) IN (?, ?)', ['male', 'm']);
                });
            } elseif (in_array($reqGender, ['female', 'f'])) {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(TRIM(gender)) IN (?, ?)', ['female', 'f']);
                });
            } else {
                $query->where('gender', $request->gender);
            }
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('hall_ticket_number', 'like', "%{$search}%");
            });
        }

        // Calculate statistics based on current filter or selected exam before applying order by
        $statsQuery = clone $query;
        $totalStudents = (clone $statsQuery)->count();

        $maleCount = (clone $statsQuery)->where(function ($q) {
            $q->whereRaw('LOWER(TRIM(gender)) IN (?, ?)', ['male', 'm']);
        })->count();
        $femaleCount = (clone $statsQuery)->where(function ($q) {
            $q->whereRaw('LOWER(TRIM(gender)) IN (?, ?)', ['female', 'f']);
        })->count();
        $otherGenderCount = $totalStudents - ($maleCount + $femaleCount);

        $htIssuedCount = (clone $statsQuery)->where('status', 'Hall Ticket Issued')->count();
        $approvedCount = (clone $statsQuery)->where('status', 'Approved')->count();

        // Category breakdown sorted by exact defined sequence:
        // RAINBOW 3 -> RAINBOW 4 -> RAINBOW 5 -> PLANET -> GALAXY HS -> GALAXY HSS (ARTS) -> GALAXY HSS (SCIENCE)
        $categoryBreakdown = (clone $statsQuery)
            ->reorder()
            ->select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($item) use ($totalStudents) {
                return [
                    'name' => $item->category?->name ?? 'Unassigned',
                    'code' => $item->category?->code ?? '-',
                    'count' => $item->total,
                    'percentage' => $totalStudents > 0 ? round(($item->total / $totalStudents) * 100, 1) : 0,
                ];
            })
            ->sortBy(fn ($item) => $this->getCategoryOrderWeight($item['name']))
            ->values();

        // Origin schools breakdown
        $schoolBreakdown = (clone $statsQuery)
            ->reorder()
            ->select('school_id', DB::raw('count(*) as total'))
            ->groupBy('school_id')
            ->with('school')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->school?->name ?? 'Unknown',
                    'code' => $item->school?->code ?? '-',
                    'count' => $item->total,
                ];
            });

        // Apply sorting to the list query
        $sortBy = $request->get('sort_by', 'reg_asc');
        switch ($sortBy) {
            case 'reg_desc':
                $query->orderByRaw('CAST(registration_number AS UNSIGNED) DESC')->orderBy('name');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'category_asc':
                $query->orderBy('category_id', 'asc')->orderByRaw('CAST(registration_number AS UNSIGNED) ASC');
                break;
            case 'school_asc':
                $query->orderBy('school_id', 'asc')->orderBy('name', 'asc');
                break;
            case 'reg_asc':
            default:
                $query->orderByRaw('CASE WHEN registration_number IS NULL THEN 1 ELSE 0 END, CAST(registration_number AS UNSIGNED) ASC')->orderBy('name');
                break;
        }

        // Paginated student list for the table
        $students = $query->paginate(25)->withQueryString();

        // Selected examination model
        $currentExam = $selectedExamId ? Examination::find($selectedExamId) : null;

        return view('super-admin.exam-centres.show', compact(
            'school',
            'students',
            'examinations',
            'activeExam',
            'filterExaminations',
            'filterCategories',
            'filterClasses',
            'filterSchools',
            'selectedExamId',
            'currentExam',
            'totalStudents',
            'maleCount',
            'femaleCount',
            'otherGenderCount',
            'htIssuedCount',
            'approvedCount',
            'categoryBreakdown',
            'schoolBreakdown',
            'sortBy'
        ));
    }

    /**
     * Download Category & Gender-wise Students List as a PDF for a selected Exam Centre.
     */
    public function downloadStudentsPdf(Request $request, School $school)
    {
        $this->authorizeCentreAccess($school);

        $query = Student::where('centre_id', $school->id)
            ->whereNull('deleted_at')
            ->with(['school', 'class', 'category', 'examination']);

        if ($request->filled('examination_id')) {
            $query->where('examination_id', $request->examination_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('gender')) {
            $reqGender = strtolower(trim($request->gender));
            if (in_array($reqGender, ['male', 'm'])) {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(TRIM(gender)) IN (?, ?)', ['male', 'm']);
                });
            } elseif (in_array($reqGender, ['female', 'f'])) {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(TRIM(gender)) IN (?, ?)', ['female', 'f']);
                });
            } else {
                $query->where('gender', $request->gender);
            }
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('hall_ticket_number', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'reg_asc');

        $students = $query->get();

        // Sort candidates primarily by the defined Category Sequence:
        // RAINBOW 3 -> RAINBOW 4 -> RAINBOW 5 -> PLANET -> GALAXY HS -> GALAXY HSS (ARTS) -> GALAXY HSS (SCIENCE)
        $students = $students->sort(function ($a, $b) use ($sortBy) {
            $weightA = $this->getCategoryOrderWeight($a->category?->name);
            $weightB = $this->getCategoryOrderWeight($b->category?->name);

            if ($weightA !== $weightB) {
                return $weightA <=> $weightB;
            }

            // Within the same category, apply secondary sort order
            switch ($sortBy) {
                case 'name_asc':
                    return strcasecmp($a->name, $b->name);
                case 'name_desc':
                    return strcasecmp($b->name, $a->name);
                case 'reg_desc':
                    return (int) $b->registration_number <=> (int) $a->registration_number;
                case 'school_asc':
                    $sch = strcasecmp($a->school?->name ?? '', $b->school?->name ?? '');

                    return $sch !== 0 ? $sch : ((int) $a->registration_number <=> (int) $b->registration_number);
                case 'reg_asc':
                case 'category_asc':
                default:
                    $regA = is_numeric($a->registration_number) ? (int) $a->registration_number : $a->registration_number;
                    $regB = is_numeric($b->registration_number) ? (int) $b->registration_number : $b->registration_number;

                    return $regA <=> $regB;
            }
        })->values();

        // Group students by category preserving the custom sequence
        $groupedStudents = $students->groupBy(fn ($s) => $s->category?->name ?? 'General')
            ->sortBy(fn ($group, $catName) => $this->getCategoryOrderWeight($catName));

        // Summary metrics for PDF header
        $totalCount = $students->count();
        $maleCount = $students->filter(fn ($s) => in_array(strtolower(trim($s->gender ?? '')), ['male', 'm']))->count();
        $femaleCount = $students->filter(fn ($s) => in_array(strtolower(trim($s->gender ?? '')), ['female', 'f']))->count();
        $otherCount = $totalCount - ($maleCount + $femaleCount);

        $categoryCounts = $groupedStudents->map(fn ($group) => $group->count());

        $examination = $request->filled('examination_id')
            ? Examination::find($request->examination_id)
            : $students->first()?->examination;

        $category = $request->filled('category_id') ? CategoryMaster::find($request->category_id) : null;

        $pdf = Pdf::loadView('pdf.exam-centre-students', [
            'school' => $school,
            'students' => $students,
            'groupedStudents' => $groupedStudents,
            'examination' => $examination,
            'category' => $category,
            'totalCount' => $totalCount,
            'maleCount' => $maleCount,
            'femaleCount' => $femaleCount,
            'otherCount' => $otherCount,
            'categoryCounts' => $categoryCounts,
            'filters' => $request->all(),
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isRemoteEnabled', false);

        $cleanCentreCode = ! empty($school->code) ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $school->code) : 'CENTRE_'.$school->id;
        $fileName = "exam_centre_students_{$cleanCentreCode}_".date('Ymd_His').'.pdf';

        activity()
            ->performedOn($school)
            ->log("Downloaded Students List PDF for Exam Centre '{$school->name}' ({$totalCount} candidates).");

        return $pdf->download($fileName);
    }

    /**
     * Download Seat Planner PDF with exactly 8 students' details per A4 page.
     */
    public function downloadSeatPlannerPdf(Request $request, School $school)
    {
        $this->authorizeCentreAccess($school);

        $query = Student::where('centre_id', $school->id)
            ->whereNull('deleted_at')
            ->with(['school', 'class', 'category', 'examination']);

        if ($request->filled('examination_id')) {
            $query->where('examination_id', $request->examination_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('gender')) {
            $reqGender = strtolower(trim($request->gender));
            if (in_array($reqGender, ['male', 'm'])) {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(TRIM(gender)) IN (?, ?)', ['male', 'm']);
                });
            } elseif (in_array($reqGender, ['female', 'f'])) {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(TRIM(gender)) IN (?, ?)', ['female', 'f']);
                });
            } else {
                $query->where('gender', $request->gender);
            }
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('hall_ticket_number', 'like', "%{$search}%");
            });
        }

        // Sorting for seat planning: primary category sequence, then sequential registration number
        $sortBy = $request->get('sort_by', 'reg_asc');

        $students = $query->get();

        // Sort candidates primarily by the defined Category Sequence:
        // RAINBOW 3 -> RAINBOW 4 -> RAINBOW 5 -> PLANET -> GALAXY HS -> GALAXY HSS (ARTS) -> GALAXY HSS (SCIENCE)
        $students = $students->sort(function ($a, $b) use ($sortBy) {
            $weightA = $this->getCategoryOrderWeight($a->category?->name);
            $weightB = $this->getCategoryOrderWeight($b->category?->name);

            if ($weightA !== $weightB) {
                return $weightA <=> $weightB;
            }

            switch ($sortBy) {
                case 'name_asc':
                    return strcasecmp($a->name, $b->name);
                case 'name_desc':
                    return strcasecmp($b->name, $a->name);
                case 'reg_desc':
                    return (int) $b->registration_number <=> (int) $a->registration_number;
                case 'school_asc':
                    $sch = strcasecmp($a->school?->name ?? '', $b->school?->name ?? '');

                    return $sch !== 0 ? $sch : ((int) $a->registration_number <=> (int) $b->registration_number);
                case 'reg_asc':
                case 'category_asc':
                default:
                    $regA = is_numeric($a->registration_number) ? (int) $a->registration_number : $a->registration_number;
                    $regB = is_numeric($b->registration_number) ? (int) $b->registration_number : $b->registration_number;

                    return $regA <=> $regB;
            }
        })->values();

        // Chunk students into pages of EXACTLY 33 students per A4 page (3 cols x 11 rows)
        $studentChunks = $students->chunk(33);

        $examination = $request->filled('examination_id')
            ? Examination::find($request->examination_id)
            : $students->first()?->examination;

        $category = $request->filled('category_id') ? CategoryMaster::find($request->category_id) : null;

        $pdf = Pdf::loadView('pdf.exam-centre-seat-planner', [
            'school' => $school,
            'studentChunks' => $studentChunks,
            'totalStudents' => $students->count(),
            'examination' => $examination,
            'category' => $category,
            'filters' => $request->all(),
        ]);

        // Standard A4 portrait: 210mm x 297mm (3 columns x 11 rows = 33 slips per page)
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', false);

        $cleanCentreCode = ! empty($school->code) ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $school->code) : 'CENTRE_'.$school->id;
        $fileName = "seat_planner_33perpage_{$cleanCentreCode}_".date('Ymd_His').'.pdf';

        activity()
            ->performedOn($school)
            ->log("Downloaded Seat Planner PDF (33/page) for Exam Centre '{$school->name}' ({$students->count()} candidates).");

        return $pdf->download($fileName);
    }

    /**
     * Toggle the designated Exam Centre status of a school.
     */
    public function toggle(School $school)
    {
        $school->is_centre = ! $school->is_centre;
        $school->save();

        $statusStr = $school->is_centre ? 'designated as an Exam Centre' : 'removed from Exam Centres';

        activity()
            ->performedOn($school)
            ->log("School '{$school->name}' was {$statusStr}.");

        return back()->with('success', "School '{$school->name}' is now {$statusStr}.");
    }

    /**
     * Bulk assign a Centre of Examination to students of a particular School and Examination.
     */
    public function assignCentres(Request $request)
    {
        $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'examination_id' => ['required', 'exists:examinations,id'],
            'centre_id' => ['required', 'exists:schools,id'],
        ]);

        $centre = School::where('id', $request->centre_id)->where('is_centre', true)->first();
        if (! $centre) {
            return back()->with('error', 'The selected school is not designated as an Exam Centre.');
        }

        // Get student count that will be updated
        $query = Student::where('school_id', $request->school_id)
            ->where('examination_id', $request->examination_id);

        $count = $query->count();

        if ($count === 0) {
            return back()->with('info', 'No registered students found for the selected School and Examination.');
        }

        // Assign the centre
        $query->update(['centre_id' => $centre->id]);

        $school = School::find($request->school_id);
        $exam = Examination::find($request->examination_id);

        activity()
            ->log("Assigned Exam Centre '{$centre->name}' to {$count} students of school '{$school->name}' for exam '{$exam->name}'.");

        return back()->with('success', "Successfully assigned Exam Centre '{$centre->name}' to {$count} students.");
    }

    /**
     * Clear the assigned exam centre for a student.
     */
    public function unassignCentre(Student $student)
    {
        $student->centre_id = null;
        $student->save();

        activity()
            ->performedOn($student)
            ->log("Cleared exam centre assignment for candidate: {$student->name}.");

        return back()->with('success', "Cleared centre assignment for student {$student->name}.");
    }

    /**
     * Assign Exam Centre to a single student.
     */
    public function assignSingle(Request $request, Student $student)
    {
        $request->validate([
            'centre_id' => ['required', 'exists:schools,id'],
        ]);

        $centre = School::where('id', $request->centre_id)->where('is_centre', true)->first();
        if (! $centre) {
            return back()->with('error', 'The selected school is not designated as an Exam Centre.');
        }

        $student->centre_id = $centre->id;
        $student->save();

        activity()
            ->performedOn($student)
            ->log("Assigned Exam Centre '{$centre->name}' to candidate: {$student->name}.");

        return back()->with('success', "Successfully assigned Exam Centre '{$centre->name}' to candidate {$student->name}.");
    }

    /**
     * School Admin View: Centre Details, Assigned Students & Seat Planner (if school is a centre).
     */
    public function schoolShow(Request $request)
    {
        $school = Auth::user()->school;
        if (! $school || ! $school->is_centre) {
            abort(403, 'Your institution is not designated as an Exam Centre venue.');
        }

        return $this->show($request, $school);
    }

    /**
     * School Admin Download: Category & Gender wise Students List PDF.
     */
    public function schoolStudentsPdf(Request $request)
    {
        $school = Auth::user()->school;
        if (! $school || ! $school->is_centre) {
            abort(403, 'Your institution is not designated as an Exam Centre venue.');
        }

        return $this->downloadStudentsPdf($request, $school);
    }

    /**
     * School Admin Download: 8-per-page Seat Planner PDF.
     */
    public function schoolSeatPlannerPdf(Request $request)
    {
        $school = Auth::user()->school;
        if (! $school || ! $school->is_centre) {
            abort(403, 'Your institution is not designated as an Exam Centre venue.');
        }

        return $this->downloadSeatPlannerPdf($request, $school);
    }

    /**
     * Authorize access to Exam Centre for Super Admin or School Admin of that venue.
     */
    protected function authorizeCentreAccess(School $school): void
    {
        if (! $school->is_centre) {
            abort(404, 'The selected school is not designated as an Exam Centre.');
        }

        $user = Auth::user();
        if ($user && $user->hasRole('school-admin')) {
            if (! $user->school_id || (int) $user->school_id !== (int) $school->id) {
                abort(403, 'Unauthorized access to another examination centre.');
            }
        }
    }

    /**
     * Get the standardized sort weight for examination categories.
     * Defined Order: RAINBOW 3 -> RAINBOW 4 -> RAINBOW 5 -> PLANET -> GALAXY HS -> GALAXY HSS (ARTS) -> GALAXY HSS (SCIENCE)
     */
    protected function getCategoryOrderWeight(?string $categoryName): int
    {
        if (! $categoryName) {
            return 999;
        }

        $norm = strtoupper(trim($categoryName));

        // 1. RAINBOW 3
        if (str_contains($norm, 'RAINBOW 3') || str_contains($norm, 'RAINBOW3')) {
            return 1;
        }
        // 2. RAINBOW 4
        if (str_contains($norm, 'RAINBOW 4') || str_contains($norm, 'RAINBOW4')) {
            return 2;
        }
        // 3. RAINBOW 5
        if (str_contains($norm, 'RAINBOW 5') || str_contains($norm, 'RAINBOW5')) {
            return 3;
        }
        // 4. PLANET
        if (str_contains($norm, 'PLANET')) {
            return 4;
        }
        // 5. GALAXY HS (Secondary, strictly not Higher Secondary)
        if (str_contains($norm, 'GALAXY HS') || (str_contains($norm, 'GALAXY') && str_contains($norm, 'HS') && ! str_contains($norm, 'HSS'))) {
            return 5;
        }
        // 6. GALAXY HSS (ARTS)
        if ((str_contains($norm, 'GALAXY') && str_contains($norm, 'HSS') && str_contains($norm, 'ART')) || str_contains($norm, '(ARTS)') || str_contains($norm, 'ARTS')) {
            return 6;
        }
        // 7. GALAXY HSS (SCIENCE)
        if ((str_contains($norm, 'GALAXY') && str_contains($norm, 'HSS') && str_contains($norm, 'SCI')) || str_contains($norm, '(SCIENCE)') || str_contains($norm, 'SCIENCE')) {
            return 7;
        }

        return 50;
    }
}
