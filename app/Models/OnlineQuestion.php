<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlineQuestion extends Model
{
    use SoftDeletes;

    protected $table = 'online_questions';

    protected $fillable = [
        'category_id',
        'subject',
        'topic',
        'difficulty',
        'question_type',
        'question_text',
        'explanation',
        'default_marks',
        'negative_marks',
        'time_limit_seconds',
        'multiple_select_criteria',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'question_type' => QuestionType::class,
        'default_marks' => 'decimal:2',
        'negative_marks' => 'decimal:2',
        'time_limit_seconds' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryMaster::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options(): HasMany
    {
        return $this->hasMany(OnlineQuestionOption::class, 'question_id')->orderBy('sort_order');
    }

    public function correctOptions(): HasMany
    {
        return $this->hasMany(OnlineQuestionOption::class, 'question_id')->where('is_correct', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(OnlineQuestionImage::class, 'question_id');
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(OnlineExamQuestion::class, 'question_id');
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(OnlineExam::class, 'online_exam_questions', 'question_id', 'online_exam_id')
            ->withPivot(['sort_order', 'marks', 'negative_marks', 'time_limit_seconds'])
            ->withTimestamps();
    }
}
