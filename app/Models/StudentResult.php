<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'examination_id',
        'marks_obtained',
        'max_marks',
        'percentage',
        'grade',
        'status',
        'subject_marks',
        'remarks',
    ];

    protected $casts = [
        'subject_marks' => 'array',
        'percentage' => 'float',
    ];

    /**
     * Get the student associated with the result.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the examination associated with the result.
     */
    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class, 'examination_id');
    }

    /**
     * Get default maximum marks based on student category.
     * Rainbow (Rainbow 3, 4, 5) => 40
     * Planets (Planet) => 50
     * Galaxy (Galaxy HS, Galaxy HSS Arts/Science) => 60
     */
    public static function getDefaultMaxMarks(?string $categoryName = null): int
    {
        $normalized = strtoupper(trim((string) $categoryName));
        if (str_contains($normalized, 'RAINBOW') || str_contains($normalized, 'RAIN OW')) {
            return 40;
        }
        if (str_contains($normalized, 'PLANET')) {
            return 50;
        }
        if (str_contains($normalized, 'GALAXY')) {
            return 60;
        }

        return 50;
    }

    /**
     * Calculate grade according to category specific thresholds:
     * - Rainbow & Planets:
     *     >= 90% => A+
     *     >= 80% => A
     *     >= 70% => B+
     *     >= 60% => B
     * - Galaxy:
     *     >= 85% => A+
     *     >= 70% => A
     *     >= 55% => B+
     *     >= 40% => B
     */
    public static function calculateGrade(float $percentage, ?string $categoryName = null): ?string
    {
        $normalized = strtoupper(trim((string) $categoryName));

        if (str_contains($normalized, 'GALAXY')) {
            if ($percentage >= 85) {
                return 'A+';
            }
            if ($percentage >= 70) {
                return 'A';
            }
            if ($percentage >= 55) {
                return 'B+';
            }
            if ($percentage >= 40) {
                return 'B';
            }

            return null;
        }

        // Default for Rainbow, Planets, and general
        if ($percentage >= 90) {
            return 'A+';
        }
        if ($percentage >= 80) {
            return 'A';
        }
        if ($percentage >= 70) {
            return 'B+';
        }
        if ($percentage >= 60) {
            return 'B';
        }

        return null;
    }
}
