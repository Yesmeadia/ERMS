<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $table = 'attendance_logs';

    protected $fillable = [
        'student_id',
        'scanner_user_id',
        'scan_time',
        'device_info',
        'ip_address',
        'action',
    ];

    protected $casts = [
        'scan_time' => 'datetime',
    ];

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the user who scanned the hall ticket.
     */
    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanner_user_id');
    }
}
