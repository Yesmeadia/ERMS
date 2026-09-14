<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Examination extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'academic_year',
        'registration_start_date',
        'registration_end_date',
        'hall_ticket_release_date',
        'status',
    ];

    protected $casts = [
        'registration_start_date' => 'date',
        'registration_end_date' => 'date',
        'hall_ticket_release_date' => 'date',
    ];

    /**
     * Get the students registered for this examination session.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'examination_id');
    }

    /**
     * Get the results registered for this examination session.
     */
    public function results(): HasMany
    {
        return $this->hasMany(StudentResult::class, 'examination_id');
    }

    /**
     * Get the hall ticket batches for this examination session.
     */
    public function hallTicketBatches(): HasMany
    {
        return $this->hasMany(HallTicketBatch::class, 'examination_id');
    }

    /**
     * Get the result export batches for this examination session.
     */
    public function resultBatches(): HasMany
    {
        return $this->hasMany(ResultBatch::class, 'examination_id');
    }

    /**
     * Get the active examination session.
     */
    public static function getActiveExam()
    {
        return self::whereIn('status', ['Registration Started', 'Registartion closed', 'Examination Ongoing', 'result published'])
            ->latest()
            ->first() ?? self::latest()->first();
    }

    /**
     * Check if registration is open for payments and candidate registrations.
     */
    public static function isRegistrationOpen(): bool
    {
        $activeExam = self::getActiveExam();
        if (! $activeExam) {
            return false;
        }

        return $activeExam->status === 'Registration Started';
    }

    /**
     * Check if results have been published for this examination.
     */
    public function isResultPublished(): bool
    {
        return strtolower(trim((string) $this->status)) === 'result published';
    }

    /**
     * Check if registration is closed.
     */
    public static function isRegistrationClosed(): bool
    {
        return ! self::isRegistrationOpen();
    }
}
