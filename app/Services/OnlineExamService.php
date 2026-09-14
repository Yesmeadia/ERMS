<?php

namespace App\Services;

use App\Models\OnlineExam;
use App\Models\OnlineExamStudent;
use App\Models\OnlineExamAuditLog;
use App\Models\Student;
use App\Models\User;
use App\Enums\ExamStatus;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OnlineExamService
{
    /**
     * Publish an online examination with atomic transaction and capacity validation.
     * Enforces the critical rule: enrolled eligible students < 200 (max 199).
     */
    public function publishExam(OnlineExam $exam, User $admin): array
    {
        return DB::transaction(function () use ($exam, $admin) {
            // Acquire row lock to prevent race conditions during concurrent admin operations
            $lockedExam = OnlineExam::where('id', $exam->id)->lockForUpdate()->firstOrFail();

            // 1. Validate questions assigned
            $questionCount = $lockedExam->examQuestions()->count();
            if ($questionCount === 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot publish exam without questions. Please assign at least one question from the Question Bank.',
                ];
            }

            // 2. Count enrolled eligible students
            $enrolledCount = $lockedExam->examStudents()
                ->where('is_eligible', true)
                ->count();

            if ($enrolledCount === 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot publish exam without enrolled students. Please enroll eligible students first.',
                ];
            }

            // 3. Mandatory Capacity Constraint: Strictly < 200 (max 199)
            if ($enrolledCount >= 200) {
                return [
                    'success' => false,
                    'message' => 'This examination cannot be published because the maximum allowed number of eligible students is 199.',
                ];
            }

            // 4. Update status and record publish timestamp
            $oldValues = ['status' => $lockedExam->status->value];
            $lockedExam->update([
                'status' => ExamStatus::PUBLISHED,
                'published_at' => now(),
            ]);

            // 5. Audit Log
            OnlineExamAuditLog::create([
                'user_id' => $admin->id,
                'action' => 'EXAM_PUBLISHED',
                'entity_type' => OnlineExam::class,
                'entity_id' => $lockedExam->id,
                'old_values' => $oldValues,
                'new_values' => ['status' => ExamStatus::PUBLISHED->value, 'published_at' => now()->toDateTimeString()],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return [
                'success' => true,
                'message' => "Examination '{$lockedExam->name}' has been successfully published with {$enrolledCount} eligible students.",
            ];
        });
    }

    /**
     * Unpublish an exam back to DRAFT (only if no sessions are active).
     */
    public function unpublishExam(OnlineExam $exam, User $admin): array
    {
        return DB::transaction(function () use ($exam, $admin) {
            $lockedExam = OnlineExam::where('id', $exam->id)->lockForUpdate()->firstOrFail();

            // Check if students have already started
            $activeSessionsCount = $lockedExam->sessions()
                ->whereNotIn('status', ['NOT_STARTED'])
                ->count();

            if ($activeSessionsCount > 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot unpublish exam because students have already started examination sessions.',
                ];
            }

            $oldValues = ['status' => $lockedExam->status->value];
            $lockedExam->update([
                'status' => ExamStatus::DRAFT,
                'published_at' => null,
            ]);

            OnlineExamAuditLog::create([
                'user_id' => $admin->id,
                'action' => 'EXAM_UNPUBLISHED',
                'entity_type' => OnlineExam::class,
                'entity_id' => $lockedExam->id,
                'old_values' => $oldValues,
                'new_values' => ['status' => ExamStatus::DRAFT->value],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return [
                'success' => true,
                'message' => "Examination '{$lockedExam->name}' has been unpublished and returned to Draft.",
            ];
        });
    }

    /**
     * Enroll students into an exam while strictly guarding the 199 student limit.
     */
    public function enrollStudents(OnlineExam $exam, array $studentIds, ?User $admin = null): array
    {
        return DB::transaction(function () use ($exam, $studentIds, $admin) {
            $lockedExam = OnlineExam::where('id', $exam->id)->lockForUpdate()->firstOrFail();

            $currentCount = $lockedExam->examStudents()->count();
            $studentsToEnroll = Student::whereIn('id', $studentIds)
                ->whereNotNull('registration_number')
                ->get();

            $newCount = $currentCount + $studentsToEnroll->count();

            if ($newCount > 199) {
                return [
                    'success' => false,
                    'message' => "Cannot enroll students. An examination can have a maximum of 199 students (currently {$currentCount}, attempted to add {$studentsToEnroll->count()}).",
                ];
            }

            $enrolled = 0;
            foreach ($studentsToEnroll as $student) {
                $record = OnlineExamStudent::firstOrCreate(
                    [
                        'online_exam_id' => $lockedExam->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'registration_number' => $student->registration_number,
                        'is_eligible' => true,
                        'enrolled_at' => now(),
                    ]
                );

                if ($record->wasRecentlyCreated) {
                    $enrolled++;
                }
            }

            if ($admin) {
                OnlineExamAuditLog::create([
                    'user_id' => $admin->id,
                    'action' => 'STUDENTS_ENROLLED',
                    'entity_type' => OnlineExam::class,
                    'entity_id' => $lockedExam->id,
                    'new_values' => ['enrolled_count' => $enrolled, 'total_enrolled' => $lockedExam->examStudents()->count()],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return [
                'success' => true,
                'message' => "Successfully enrolled {$enrolled} students. Total enrolled: {$lockedExam->examStudents()->count()}/199.",
            ];
        });
    }
}
