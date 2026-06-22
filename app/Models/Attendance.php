<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'student_id',
        'exam_id',
        'attendance_date',
        'attendance_time',
        'marked_by',
        'status',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the examination.
     */
    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class, 'exam_id');
    }

    /**
     * Get the user who marked the attendance.
     */
    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
