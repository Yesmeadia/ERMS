<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentResult extends Model
{
    use HasFactory;

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
}
