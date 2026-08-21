<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HallTicketPdfPart extends Model
{
    use HasFactory;

    protected $table = 'hall_ticket_pdf_parts';

    protected $fillable = [
        'batch_id',
        'part_number',
        'student_ids',
        'total_students',
        'completed_students',
        'status',
        'processing_token',
        'pdf_path',
        'file_size',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'part_number' => 'integer',
        'student_ids' => 'array',
        'total_students' => 'integer',
        'completed_students' => 'integer',
        'file_size' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(HallTicketBatch::class, 'batch_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
