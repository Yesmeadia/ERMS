<?php

namespace App\Services;

use App\Jobs\GenerateResultPdfPart;
use App\Models\CategoryMaster;
use App\Models\ClassMaster;
use App\Models\Examination;
use App\Models\ResultBatch;
use App\Models\ResultPdfPart;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DompdfWrapper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ResultReportService
{
    /**
     * Build student query matching filter parameters.
     */
    public function buildQuery(string $scope, ?int $schoolId, array $filters = [])
    {
        $query = Student::select([
            'id', 'name', 'registration_number', 'hall_ticket_number',
            'gender', 'class_id', 'category_id', 'school_id', 'examination_id', 'status'
        ]);

        if ($scope === 'school' && $schoolId) {
            $query->where('school_id', $schoolId);
        } elseif (!empty($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        // Examination filter
        if (!empty($filters['examination_id'])) {
            $query->where('examination_id', $filters['examination_id']);
        } else {
            if ($scope === 'school') {
                $query->whereHas('examination', function ($eq) {
                    $eq->whereRaw('LOWER(status) = ?', ['result published']);
                });
            } else {
                $liveExam = Examination::getActiveExam();
                if ($liveExam) {
                    $query->where('examination_id', $liveExam->id);
                }
            }
        }

        // Zone filter
        if (!empty($filters['zone'])) {
            $query->whereHas('school', function ($q) use ($filters) {
                $q->where('zone', $filters['zone']);
            });
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Class filter
        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        // Gender filter
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        // Result Status filter
        if (!empty($filters['result_status'])) {
            $status = $filters['result_status'];
            if ($status === 'entered') {
                $query->has('result');
            } elseif ($status === 'pending') {
                $query->doesntHave('result');
            } elseif (in_array($status, ['Pass', 'Fail', 'Withheld'])) {
                $query->whereHas('result', fn($q) => $q->where('status', $status));
            } elseif ($status === 'Absent') {
                $query->where(function ($q) {
                    $q->whereHas('result', fn($rq) => $rq->where('status', 'Absent'))
                        ->orWhere(function ($sq) {
                            $sq->doesntHave('result')
                                ->whereDoesntHave('attendances', fn($aq) => $aq->where('status', 'Present'));
                        });
                });
            }
        }

        // Search name/reg/hall ticket
        if (!empty($filters['search'])) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('hall_ticket_number', 'like', "%{$search}%");
            });
        }

        if ($scope === 'admin') {
            $query->whereIn('status', ['Approved', 'Hall Ticket Issued']);
        }

        return $query;
    }

    /**
     * Compute statistics for the given student IDs collection.
     */
    public function computeStats(array $studentIds): array
    {
        $total = count($studentIds);
        if ($total === 0) {
            return [
                'total' => 0,
                'appeared' => 0,
                'passed' => 0,
                'failed' => 0,
                'absent' => 0,
                'withheld' => 0,
                'pass_rate' => 0,
            ];
        }

        $results = StudentResult::whereIn('student_id', $studentIds)
            ->select(['student_id', 'status', 'marks_obtained', 'max_marks'])
            ->get();

        $passed = $results->where('status', 'Pass')->count();
        $failed = $results->where('status', 'Fail')->count();
        $withheld = $results->where('status', 'Withheld')->count();
        $explicitAbsent = $results->where('status', 'Absent')->count();

        // Check students without result who also have no 'Present' attendance record
        $resultStudentIds = $results->pluck('student_id')->all();
        $noResultStudentIds = array_diff($studentIds, $resultStudentIds);

        $presentAttendanceStudentIds = [];
        if (!empty($noResultStudentIds)) {
            $presentAttendanceStudentIds = DB::table('attendance')
                ->whereIn('student_id', $noResultStudentIds)
                ->where('status', 'Present')
                ->pluck('student_id')
                ->all();
        }

        $implicitAbsentCount = count($noResultStudentIds) - count($presentAttendanceStudentIds);
        $absent = $explicitAbsent + max(0, $implicitAbsentCount);

        $appeared = max(0, $total - $absent);
        $passRate = $appeared > 0 ? round(($passed / $appeared) * 100, 1) : 0;

        return [
            'total' => $total,
            'appeared' => $appeared,
            'passed' => $passed,
            'failed' => $failed,
            'absent' => $absent,
            'withheld' => $withheld,
            'pass_rate' => $passRate,
        ];
    }

    /**
     * Build user-friendly filter labels for report header.
     */
    public function buildFilterLabels(string $scope, ?int $schoolId, array $filters = []): array
    {
        $labels = [];

        // Exam label
        if (!empty($filters['examination_id'])) {
            $exam = Examination::find($filters['examination_id']);
            $labels['examination'] = $exam ? $exam->name : 'Exam #' . $filters['examination_id'];
        } else {
            if ($scope === 'school') {
                $liveExam = Examination::whereRaw('LOWER(status) = ?', ['result published'])->latest()->first() ?? Examination::getActiveExam();
                $labels['examination'] = $liveExam ? $liveExam->name : 'YES GENIUS TALENT SEARCH SEASON-4';
            } else {
                $liveExam = Examination::getActiveExam();
                $labels['examination'] = $liveExam ? $liveExam->name : 'YES GENIUS TALENT SEARCH SEASON-4';
            }
        }

        // Zone label
        $labels['zone'] = !empty($filters['zone']) ? $filters['zone'] : 'All Zones';

        // School label
        if ($scope === 'school' && $schoolId) {
            $school = School::find($schoolId);
            $labels['school'] = $school ? $school->name : 'School #' . $schoolId;
            $labels['zone'] = $school->zone ?? 'N/A';
        } elseif (!empty($filters['school_id'])) {
            $school = School::find($filters['school_id']);
            $labels['school'] = $school ? $school->name : 'School #' . $filters['school_id'];
        } else {
            $labels['school'] = 'All Schools';
        }

        // Category label
        if (!empty($filters['category_id'])) {
            $cat = CategoryMaster::find($filters['category_id']);
            $labels['category'] = $cat ? $cat->name : 'Category #' . $filters['category_id'];
        } else {
            $labels['category'] = 'All Categories';
        }

        // Class label
        if (!empty($filters['class_id'])) {
            $cls = ClassMaster::find($filters['class_id']);
            $labels['class'] = $cls ? $cls->name : 'Class #' . $filters['class_id'];
        } else {
            $labels['class'] = 'All Classes';
        }

        // Gender label
        $labels['gender'] = !empty($filters['gender']) ? ucfirst($filters['gender']) : 'All Genders';

        // Result Status label
        if (!empty($filters['result_status'])) {
            $st = $filters['result_status'];
            if ($st === 'entered') {
                $labels['result_status'] = 'Results Entered';
            } elseif ($st === 'pending') {
                $labels['result_status'] = 'Results Pending';
            } elseif ($st === 'Absent') {
                $labels['result_status'] = 'Absent Candidates';
            } else {
                $labels['result_status'] = ucfirst($st);
            }
        } else {
            $labels['result_status'] = 'All Statuses';
        }

        return $labels;
    }

    /**
     * Create or retrieve an active ResultBatch and dispatch generation jobs.
     */
    public function createAndDispatchBatch(
        string $scope,
        ?int $schoolId,
        ?int $examinationId,
        int $requestedByUserId,
        array $filters = []
    ): ResultBatch {
        $lockKey = "create_result_batch_{$scope}_" . ($schoolId ?? 'all') . "_" . ($examinationId ?? 'all');

        return Cache::lock($lockKey, 10)->block(5, function () use (
            $scope,
            $schoolId,
            $examinationId,
            $requestedByUserId,
            $filters
        ) {
            // Query student IDs with deterministic ordering
            $query = $this->buildQuery($scope, $schoolId, $filters);
            $studentIds = $query->orderBy('registration_number', 'asc')
                ->orderBy('name', 'asc')
                ->orderBy('id', 'asc')
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->all();

            if (empty($studentIds)) {
                throw ValidationException::withMessages([
                    'examination_id' => ['No candidate records found matching the selected filter criteria.'],
                ]);
            }

            $filterLabels = $this->buildFilterLabels($scope, $schoolId, $filters);
            $stats = $this->computeStats($studentIds);

            // Chunk students into parts (e.g. 250 students per part for safe PDF rendering)
            $maxPerPdf = min(500, max(1, (int) config('results.max_per_pdf', 250)));
            $chunks = array_chunk($studentIds, $maxPerPdf);
            $totalStudents = count($studentIds);
            $totalParts = count($chunks);
            $expirationHours = (int) config('results.expiration_hours', 24);

            $title = $scope === 'admin' 
                ? 'STUDENT EXAMINATION RESULT STATEMENT' 
                : 'INSTITUTION EXAMINATION RESULT STATEMENT';
            $subtitle = $scope === 'admin' 
                ? 'Super Administrator Consolidated Tabulation Register' 
                : ($filterLabels['school'] ?? 'Institutional Statement');

            return DB::transaction(function () use (
                $scope,
                $schoolId,
                $examinationId,
                $requestedByUserId,
                $filters,
                $filterLabels,
                $stats,
                $title,
                $subtitle,
                $totalStudents,
                $totalParts,
                $chunks,
                $expirationHours
            ) {
                $batch = ResultBatch::create([
                    'scope' => $scope,
                    'school_id' => $schoolId,
                    'examination_id' => $examinationId,
                    'requested_by' => $requestedByUserId,
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'total_students' => $totalStudents,
                    'total_parts' => $totalParts,
                    'completed_parts' => 0,
                    'failed_parts' => 0,
                    'completed_students' => 0,
                    'failed_students' => 0,
                    'status' => 'pending',
                    'filter_criteria' => array_merge($filters, ['labels' => $filterLabels]),
                    'stats' => $stats,
                    'expires_at' => now()->addHours($expirationHours),
                ]);

                $partsToDispatch = [];
                foreach ($chunks as $idx => $chunk) {
                    $partNumber = $idx + 1;
                    $part = ResultPdfPart::create([
                        'batch_id' => $batch->id,
                        'part_number' => $partNumber,
                        'student_ids' => $chunk,
                        'total_students' => count($chunk),
                        'completed_students' => 0,
                        'status' => 'pending',
                    ]);
                    $partsToDispatch[] = $part;
                }

                DB::afterCommit(function () use ($partsToDispatch) {
                    foreach ($partsToDispatch as $part) {
                        GenerateResultPdfPart::dispatch($part->id)
                            ->onQueue(config('results.queue', 'pdf'));
                    }
                });

                return $batch;
            });
        });
    }

    /**
     * Retry failed parts in a result batch.
     */
    public function retryFailedParts(ResultBatch $batch): void
    {
        $failedParts = $batch->parts()->where('status', 'failed')->get();

        if ($failedParts->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($batch, $failedParts) {
            foreach ($failedParts as $part) {
                $part->update([
                    'status' => 'pending',
                    'error_message' => null,
                    'processing_token' => null,
                    'started_at' => null,
                    'completed_at' => null,
                ]);

                GenerateResultPdfPart::dispatch($part->id)
                    ->onQueue(config('results.queue', 'pdf'));
            }

            $batch->status = 'processing';
            $batch->save();
            $batch->recalculateProgress();
        });
    }

    /**
     * Generate PDF file for an individual ResultPdfPart.
     */
    public function generatePartPdf(ResultPdfPart $part): array
    {
        $batch = $part->batch;
        if (!$batch) {
            throw new RuntimeException("Parent ResultBatch not found for Part #{$part->id}");
        }

        $studentIds = $part->student_ids ?? [];
        if (empty($studentIds)) {
            throw new RuntimeException("Part #{$part->id} has no student IDs assigned.");
        }

        // Fetch students with minimal lightweight hydration
        $students = Student::select(['id', 'name', 'registration_number', 'hall_ticket_number', 'gender', 'class_id', 'category_id', 'school_id', 'examination_id'])
            ->whereIn('id', $studentIds)
            ->with([
                'class:id,name',
                'category:id,name',
                'school:id,name,code,state,zone',
                'result:id,student_id,marks_obtained,max_marks,grade,status',
                'attendances:id,student_id,exam_id,status'
            ])
            ->orderBy('registration_number', 'asc')
            ->orderBy('name', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Calculate continuous serial number across parts
        $previousStudentsCount = ResultPdfPart::where('batch_id', $batch->id)
            ->where('part_number', '<', $part->part_number)
            ->sum('total_students');
        $sno = $previousStudentsCount + 1;

        $rows = [];
        foreach ($students as $student) {
            $res = $student->result;
            $isPresent = $student->attendances ? $student->attendances->where('status', 'Present')->isNotEmpty() : false;
            $isAbsent = false;
            if ($res && $res->status === 'Absent') {
                $isAbsent = true;
            } elseif (!$res) {
                $isAbsent = !$isPresent;
            }

            $marksText = '-';
            $pctText = '-';
            $gradeText = '-';
            $statusText = 'Pending';
            $statusClass = 'badge-withheld';

            if ($res) {
                if ($res->status === 'Absent') {
                    $statusText = 'Absent';
                    $statusClass = 'badge-absent';
                } elseif ($res->status === 'Withheld') {
                    $statusText = 'Withheld';
                    $statusClass = 'badge-withheld';
                } else {
                    $marksText = $res->marks_obtained . ' / ' . $res->max_marks;
                    $pct = $res->max_marks > 0 ? round(($res->marks_obtained / $res->max_marks) * 100, 1) : 0;
                    $pctText = $pct . '%';
                    $gradeText = $res->grade ?? '-';
                    $statusText = $res->status;
                    $statusClass = ($res->status === 'Pass') ? 'badge-pass' : 'badge-fail';
                }
            } elseif ($isAbsent) {
                $statusText = 'Absent';
                $statusClass = 'badge-absent';
            }

            $rows[] = [
                'sno' => $sno++,
                'reg_no' => $student->registration_number ?? '-',
                'ht_no' => $student->hall_ticket_number ?? '-',
                'name' => $student->name ?? '-',
                'school' => $student->school->name ?? '-',
                'state' => $student->school->state ?? '-',
                'zone' => $student->school->zone ?? '-',
                'class' => $student->class->name ?? '-',
                'category' => $student->category->name ?? '-',
                'gender' => ucfirst($student->gender ?? '-'),
                'marks' => $marksText,
                'pct' => $pctText,
                'grade' => $gradeText,
                'status' => $statusText,
                'status_class' => $statusClass,
            ];
        }

        // Free heavy Eloquent collections immediately
        unset($students);
        gc_collect_cycles();

        // Chunk rows into pages: exactly 20 rows on first page (with headers/KPIs), 27 rows on subsequent pages
        $pages = [];
        if (empty($rows)) {
            $pages[] = [];
        } elseif (count($rows) <= 20) {
            $pages[] = $rows;
        } else {
            $pages[] = array_slice($rows, 0, 20);
            $remaining = array_slice($rows, 20);
            $subsequent = array_chunk($remaining, 27);
            foreach ($subsequent as $chunk) {
                $pages[] = $chunk;
            }
        }
        unset($rows);
        gc_collect_cycles();

        // Prepare Base64 assets
        $leftLogoPath = file_exists(public_path('logob_pdf.png')) 
            ? public_path('logob_pdf.png') 
            : (file_exists(public_path('logob.png')) ? public_path('logob.png') : (file_exists(public_path('logo.png')) ? public_path('logo.png') : null));

        $rightLogoPath = file_exists(public_path('logo_pdf.png')) 
            ? public_path('logo_pdf.png') 
            : (file_exists(public_path('logo.png')) ? public_path('logo.png') : null);

        $signPath = file_exists(public_path('sign_pdf.png')) 
            ? public_path('sign_pdf.png') 
            : (file_exists(public_path('Sign.png')) ? public_path('Sign.png') : (file_exists(public_path('sign.png')) ? public_path('sign.png') : null));

        $leftLogoBase64 = $this->getPdfImageBase64($leftLogoPath);
        $rightLogoBase64 = $this->getPdfImageBase64($rightLogoPath);
        $signBase64 = $this->getPdfImageBase64($signPath);

        $filterCriteria = $batch->filter_criteria ?? [];
        $filters = $filterCriteria['labels'] ?? [];
        $stats = $batch->stats ?? [];
        $title = $batch->title;
        $subtitle = $batch->subtitle;
        $isSuperAdmin = ($batch->scope === 'admin');
        $generatedAt = date('d M Y, h:i A');
        $examTitle = $filters['examination'] ?? 'YES GENIUS TALENT SEARCH';
        $examName = $examTitle;

        $partInfo = ($batch->total_parts > 1) 
            ? "Part {$part->part_number} of {$batch->total_parts} (Candidates {$part->total_students})" 
            : null;

        $fontDir = storage_path('fonts');
        if (!is_dir($fontDir)) {
            @mkdir($fontDir, 0775, true);
        }

        @ini_set('memory_limit', '1024M');
        @ini_set('max_execution_time', '600');
        @set_time_limit(600);

        $pdf = Pdf::loadView('pdf.results-report', compact(
            'pages',
            'title',
            'subtitle',
            'examName',
            'examTitle',
            'filters',
            'stats',
            'isSuperAdmin',
            'generatedAt',
            'partInfo',
            'leftLogoBase64',
            'rightLogoBase64',
            'signBase64'
        ));

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('isFontSubsettingEnabled', false);
        $pdf->setOption('defaultFont', 'Helvetica');

        $pdfContent = $pdf->output();
        $fileSize = strlen($pdfContent);

        $diskName = config('results.disk', 'local');
        $disk = Storage::disk($diskName);
        $directory = "results_reports/{$batch->batch_uuid}";
        $filename = "part_{$part->part_number}.pdf";
        $storagePath = "{$directory}/{$filename}";

        $disk->put($storagePath, $pdfContent);

        return [
            'pdf_path' => $storagePath,
            'file_size' => $fileSize,
            'completed_students' => count($studentIds),
        ];
    }

    /**
     * Convert local image to base64 Data URI.
     */
    protected function getPdfImageBase64(?string $path): ?string
    {
        if (!$path || !file_exists($path)) {
            return null;
        }

        try {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/png';
            $data = @file_get_contents($path);
            if ($data !== false && strlen($data) > 0) {
                return 'data:' . $mime . ';base64,' . base64_encode($data);
            }
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
