<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'zone',
        'state',
        'contact_person',
        'mobile_number',
        'email',
        'status',
        'is_centre',
        'is_fine_enabled',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_centre' => 'boolean',
        'is_fine_enabled' => 'boolean',
    ];

    /**
     * Get the admins (users) assigned to this school.
     */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class, 'school_id');
    }

    /**
     * Get the students registered under this school.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'school_id');
    }

    /**
     * Get the payments made by this school.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'school_id');
    }

    /**
     * Check if registration fine (₹50/student) is applicable for this school.
     */
    public function isFineApplicable(): bool
    {
        if (array_key_exists('is_fine_enabled', $this->attributes) && !$this->is_fine_enabled) {
            return false;
        }

        $activeExam = Examination::whereIn('status', ['Registration Started', 'Registartion closed', 'Examination Ongoing'])
            ->latest()
            ->first() ?? Examination::latest()->first();

        if ($activeExam && $activeExam->without_fine_end_date) {
            return now('Asia/Kolkata')->greaterThan($activeExam->without_fine_end_date);
        }

        // Standard deadline: Registration without fine ends on August 15, 2026 11:59:59 PM IST.
        // Starting tomorrow (August 16, 2026 IST onwards), fine of ₹50 applies.
        $withoutFineCutoff = \Carbon\Carbon::parse('2026-08-15 23:59:59', 'Asia/Kolkata');
        return now('Asia/Kolkata')->greaterThan($withoutFineCutoff);
    }

    /**
     * Get fine amount per student for this school (Rupees 50.00 if applicable, 0.00 otherwise).
     */
    public function getFinePerStudentAmount(): float
    {
        return $this->isFineApplicable() ? 50.00 : 0.00;
    }
}
