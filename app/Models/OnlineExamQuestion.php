<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineExamQuestion extends Model
{
    protected $table = 'online_exam_questions';

    protected $fillable = [
        'online_exam_id',
        'question_id',
        'sort_order',
        'marks',
        'negative_marks',
        'time_limit_seconds',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'marks' => 'decimal:2',
        'negative_marks' => 'decimal:2',
        'time_limit_seconds' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class, 'online_exam_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(OnlineQuestion::class, 'question_id');
    }
}
