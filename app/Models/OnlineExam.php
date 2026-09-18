<?php

namespace App\Models;

use App\Enums\ExamStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlineExam extends Model
{
    use SoftDeletes;

    protected $table = 'online_exams';

    protected $fillable = [
        'name',
        'code',
        'description',
        'category_id',
        'exam_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'default_question_time_limit',
        'total_marks',
        'enable_camera',
        'enable_fullscreen',
        'max_fullscreen_violations',
        'allow_previous_question',
        'randomize_questions',
        'randomize_options',
        'show_result_immediately',
        'show_correct_answers',
        'show_question_marks',
        'show_rank',
        'enable_speed_bonus',
        'speed_bonus_formula',
        'max_bonus_per_question',
        'max_total_bonus',
        'max_eligible_students',
        'instructions',
        'status',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'duration_minutes' => 'integer',
        'default_question_time_limit' => 'integer',
        'total_marks' => 'decimal:2',
        'enable_camera' => 'boolean',
        'enable_fullscreen' => 'boolean',
        'max_fullscreen_violations' => 'integer',
        'allow_previous_question' => 'boolean',
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'show_result_immediately' => 'boolean',
        'show_correct_answers' => 'boolean',
        'show_question_marks' => 'boolean',
        'show_rank' => 'boolean',
        'enable_speed_bonus' => 'boolean',
        'max_bonus_per_question' => 'decimal:2',
        'max_total_bonus' => 'decimal:2',
        'max_eligible_students' => 'integer',
        'status' => ExamStatus::class,
        'published_at' => 'datetime',
        'created_by' => 'integer',
    ];

    /**
     * Category that this exam is conducted for.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryMaster::class, 'category_id');
    }

    /**
     * Administrator who created this exam.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Enrolled eligible students pivot.
     */
    public function examStudents(): HasMany
    {
        return $this->hasMany(OnlineExamStudent::class, 'online_exam_id');
    }

    /**
     * Direct relationship to Student records through enrollment.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'online_exam_students', 'online_exam_id', 'student_id')
            ->withPivot(['registration_number', 'is_eligible', 'eligibility_notes', 'enrolled_at'])
            ->withTimestamps();
    }

    /**
     * Assigned questions in this exam (ordered).
     */
    public function examQuestions(): HasMany
    {
        return $this->hasMany(OnlineExamQuestion::class, 'online_exam_id')->orderBy('sort_order');
    }

    /**
     * Questions attached to this exam.
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(OnlineQuestion::class, 'online_exam_questions', 'online_exam_id', 'question_id')
            ->withPivot(['sort_order', 'marks', 'negative_marks', 'time_limit_seconds'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Student examination sessions.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(OnlineExamSession::class, 'online_exam_id');
    }

    /**
     * Exam final results.
     */
    public function results(): HasMany
    {
        return $this->hasMany(OnlineExamResult::class, 'online_exam_id');
    }

    /**
     * Proctoring video recordings for this exam.
     */
    public function recordings(): HasMany
    {
        return $this->hasMany(OnlineExamRecording::class, 'online_exam_id');
    }

    /**
     * Check if exam is live according to current date and window.
     */
    public function isLiveNow(): bool
    {
        if ($this->status !== ExamStatus::PUBLISHED && $this->status !== ExamStatus::ACTIVE) {
            return false;
        }

        $now = now();
        $examStart = Carbon::parse($this->exam_date->format('Y-m-d').' '.$this->start_time);
        $examEnd = Carbon::parse($this->exam_date->format('Y-m-d').' '.$this->end_time);

        return $now->between($examStart, $examEnd);
    }

    /**
     * Calculate global exam start timestamp.
     */
    public function getStartDateTimeAttribute(): Carbon
    {
        return Carbon::parse($this->exam_date->format('Y-m-d').' '.$this->start_time);
    }

    /**
     * Calculate global exam end timestamp.
     */
    public function getEndDateTimeAttribute(): Carbon
    {
        return Carbon::parse($this->exam_date->format('Y-m-d').' '.$this->end_time);
    }

    /**
     * Validate whether the exam meets publishing criteria.
     * Enforces:
     * 1. At least 1 question assigned.
     * 2. Enrolled students > 0.
     * 3. Capacity rule: Enrolled students < 200 (max 199).
     */
    public function canPublish(): array
    {
        $questionCount = $this->examQuestions()->count();
        if ($questionCount === 0) {
            return [false, 'Cannot publish exam without questions. Please assign at least one question.'];
        }

        $studentCount = $this->examStudents()->where('is_eligible', true)->count();
        if ($studentCount === 0) {
            return [false, 'Cannot publish exam without enrolled students.'];
        }

        if ($studentCount >= 200) {
            return [false, 'This examination cannot be published because the maximum allowed number of eligible students is 199.'];
        }

        return [true, null];
    }
}
