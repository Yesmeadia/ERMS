<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'class_id',
        'category_id',
        'examination_id',
        'centre_id',
        'name',
        'gender',
        'dob',
        'father_name',
        'mother_name',
        'mobile_number',
        'registration_number',
        'status',
        'payment_status',
        'remarks',
        'photograph',
        'hall_ticket_number',
        'hall_ticket_issued_at',
    ];

    protected $casts = [
        'dob' => 'date',
        'hall_ticket_issued_at' => 'datetime',
    ];

    /**
     * Get the school that the student belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Get the centre of examination that the student belongs to.
     */
    public function centre(): BelongsTo
    {
        return $this->belongsTo(School::class, 'centre_id');
    }

    /**
     * Get the class of the student.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassMaster::class, 'class_id');
    }

    /**
     * Get the category of the student.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryMaster::class, 'category_id');
    }

    /**
     * Get the examination session the student is registered for.
     */
    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class, 'examination_id');
    }

    /**
     * Get the hall ticket associated with the student.
     */
    public function hallTicket(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(HallTicket::class, 'student_id');
    }

    /**
     * Get the result associated with the student.
     */
    public function result(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StudentResult::class, 'student_id');
    }

    /**
     * Get the payments associated with the student.
     */
    public function payments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_student', 'student_id', 'payment_id')
            ->withPivot('amount')
            ->withTimestamps();
    }

    /**
     * Get the attendance records for the student.
     */
    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    /**
     * Get the attendance logs for the student.
     */
    public function attendanceLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'student_id');
    }

    /**
     * Get the photo URL or a beautiful placeholder with initials.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photograph) {
            return asset('storage/' . $this->photograph);
        }
        
        return 'data:image/svg+xml;base64,' . base64_encode($this->generateInitialsAvatar());
    }

    /**
     * Generate inline initials avatar SVG (CWE-200 / GDPR compliance).
     */
    private function generateInitialsAvatar(): string
    {
        $initials = collect(explode(' ', $this->name))
            ->map(fn($n) => mb_substr($n, 0, 1))
            ->take(2)
            ->join('');
            
        return '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">
            <rect fill="#6366f1" width="128" height="128" rx="16"/>
            <text x="64" y="64" text-anchor="middle" dominant-baseline="central" fill="white" font-size="48" font-family="sans-serif" font-weight="bold">' 
            . htmlspecialchars($initials) . 
            '</text>
        </svg>';
    }

    /**
     * Generate a unique 5-digit registration number based on class and category.
     */
    public function issueRegistrationNumber(): string
    {
        if ($this->registration_number) {
            return $this->registration_number;
        }

        $className = strtolower($this->class->name);
        $classCode = strtolower($this->class->code);
        $categoryName = strtolower($this->category->name);
        $categoryCode = strtolower($this->category->code);

        // 1. Galaxy Categories: (GALAXY, GALAXY HS, GALAXY HSS (ARTS) and GALAXY HSS (SCIENCE))
        if (str_contains($categoryName, 'galaxy') || str_contains($categoryCode, 'galaxy')) {
            $start = 90001;
            $end = 99999;
            return $this->getNextSequentialRegistrationNumber($start, $end);
        }

        // 2. Planet Category:
        if (str_contains($categoryName, 'planet') || str_contains($categoryCode, 'planet')) {
            $start = 60001;
            $end = 69999;
            return $this->getNextSequentialRegistrationNumber($start, $end);
        }

        // 3. Class 5th / Category Rainbow 5:
        if (str_contains($className, '5th') || str_contains($classCode, '5') || str_contains($categoryName, 'rainbow 5') || str_contains($categoryName, 'rainbow5')) {
            $start = 50001;
            $end = 59999;
            return $this->getNextSequentialRegistrationNumber($start, $end);
        }

        // 4. Class 4th / Category Rainbow 4:
        if (str_contains($className, '4th') || str_contains($classCode, '4') || str_contains($categoryName, 'rainbow 4') || str_contains($categoryName, 'rainbow4')) {
            $start = 40001;
            $end = 49999;
            return $this->getNextSequentialRegistrationNumber($start, $end);
        }

        // 5. Class 3rd / Category Rainbow 3:
        if (str_contains($className, '3rd') || str_contains($classCode, '3') || str_contains($categoryName, 'rainbow 3') || str_contains($categoryName, 'rainbow3')) {
            $start = 30001;
            $end = 39999;
            return $this->getNextSequentialRegistrationNumber($start, $end);
        }

        // Fallback checks using any digits in class name or class code
        preg_match('/\d+/', $className . $classCode, $matches);
        if (!empty($matches)) {
            $digit = (int)$matches[0];
            if ($digit >= 1 && $digit <= 9) {
                $start = $digit * 10000 + 1;
                $end = $digit * 10000 + 9999;
                return $this->getNextSequentialRegistrationNumber($start, $end);
            }
        }

        // Hard fallback to a sequential 3xxxx number
        return $this->getNextSequentialRegistrationNumber(30001, 39999);
    }

    private function getNextSequentialRegistrationNumber(int $start, int $end): string
    {
        return DB::transaction(function () use ($start, $end) {
            // Lock the matching rows so concurrent requests must wait until
            // this transaction commits before they can read MAX(registration_number).
            $maxReg = self::whereBetween('registration_number', [(string)$start, (string)$end])
                ->orderBy('registration_number', 'desc')
                ->lockForUpdate()
                ->value('registration_number');

            $next = $maxReg ? (int)$maxReg + 1 : $start;
            return (string)$next;
        });
    }

    private function getRandomUniqueRegistrationNumber(int $start, int $end): string
    {
        $tries = 0;
        do {
            $rand = mt_rand($start, $end);
            $exists = self::where('registration_number', (string)$rand)->exists();
            $tries++;
        } while ($exists && $tries < 1000);

        if ($exists) {
            return $this->getNextSequentialRegistrationNumber($start, $end);
        }

        return (string)$rand;
    }

    /**
     * Get the student's registration fee based on CategoryMaster fee.
     * Fallback to ClassMaster fee if Category fee is 0.00.
     */
    public function getRegistrationFeeAttribute()
    {
        if ($this->category && $this->category->registration_fee > 0) {
            return (float) $this->category->registration_fee;
        }
        return $this->class ? (float) $this->class->registration_fee : 0.0;
    }
}
