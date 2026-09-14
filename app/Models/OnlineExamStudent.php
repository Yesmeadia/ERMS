<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineExamStudent extends Model
{
    protected $table = 'online_exam_students';

    protected $fillable = [
        'online_exam_id',
        'student_id',
        'registration_number',
        'is_eligible',
        'eligibility_notes',
        'enrolled_at',
    ];

    protected $casts = [
        'is_eligible' => 'boolean',
        'enrolled_at' => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class, 'online_exam_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
