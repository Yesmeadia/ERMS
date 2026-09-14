<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineQuestionOption extends Model
{
    protected $table = 'online_question_options';

    protected $fillable = [
        'question_id',
        'option_identifier',
        'option_text',
        'is_correct',
        'sort_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(OnlineQuestion::class, 'question_id');
    }
}
