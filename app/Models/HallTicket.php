<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HallTicket extends Model
{
    use HasFactory;

    protected $table = 'hall_tickets';

    protected $fillable = [
        'student_id',
        'hallticket_no',
        'qr_token',
        'issue_date',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    /**
     * Get the student associated with the hall ticket.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
