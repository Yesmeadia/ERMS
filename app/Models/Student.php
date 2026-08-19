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
            ->withPivot('amount', 'base_amount', 'fine_amount')
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
     * The "booted" method of the model.
     * Soft-deleted students release their registration number so it can be reused by active students.
     */
    protected static function booted(): void
    {
        static::deleting(function (Student $student) {
            if (!$student->isForceDeleting() && $student->registration_number !== null) {
                $student->registration_number = null;
                $student->saveQuietly();
            }
        });
    }

    /**
     * Get registration number range [start, end] based on class and category.
     */
    public function getRegistrationNumberRange(): array
    {
        $className = strtolower($this->class?->name ?? '');
        $categoryName = strtolower($this->category?->name ?? '');
        $categoryCode = strtolower($this->category?->code ?? '');

        // 1. Galaxy Categories: (GALAXY, GALAXY HS, GALAXY HSS (ARTS) and GALAXY HSS (SCIENCE))
        if (str_contains($categoryName, 'galaxy') || str_contains($categoryCode, 'galaxy')) {
            return [90001, 99999];
        }

        // 2. Planet Category:
        if (str_contains($categoryName, 'planet') || str_contains($categoryCode, 'planet')) {
            return [60001, 69999];
        }

        // 3. Class 5th / Category Rainbow 5:
        if (str_contains($className, '5th') || str_contains($className, '5') || str_contains($categoryName, 'rainbow 5') || str_contains($categoryName, 'rainbow5')) {
            return [50001, 59999];
        }

        // 4. Class 4th / Category Rainbow 4:
        if (str_contains($className, '4th') || str_contains($className, '4') || str_contains($categoryName, 'rainbow 4') || str_contains($categoryName, 'rainbow4')) {
            return [40001, 49999];
        }

        // 5. Class 3rd / Category Rainbow 3:
        if (str_contains($className, '3rd') || str_contains($className, '3') || str_contains($categoryName, 'rainbow 3') || str_contains($categoryName, 'rainbow3')) {
            return [30001, 39999];
        }

        // Fallback checks using any digits in class name
        preg_match('/\d+/', $className, $matches);
        if (!empty($matches)) {
            $digit = (int)$matches[0];
            if ($digit >= 1 && $digit <= 9) {
                return [$digit * 10000 + 1, $digit * 10000 + 9999];
            }
        }

        // Hard fallback to a sequential 3xxxx number
        return [30001, 39999];
    }

    /**
     * Generate a unique 5-digit registration number based on class and category.
     */
    public function issueRegistrationNumber(): string
    {
        if ($this->registration_number) {
            return $this->registration_number;
        }

        [$start, $end] = $this->getRegistrationNumberRange();
        $allocated = self::allocateRegistrationNumbers($start, $end, 1);
        return $allocated[0];
    }

    /**
     * Concurrency-safe sequential allocation of registration numbers for active students.
     * Soft-deleted students do NOT reserve registration numbers.
     *
     * @param int $start Range start (e.g. 60001)
     * @param int $end Range end (e.g. 69999)
     * @param int $count Number of registrations to allocate
     * @return array<string> List of allocated registration numbers
     * @throws \RuntimeException If registration range is exhausted
     */
    public static function allocateRegistrationNumbers(int $start, int $end, int $count = 1): array
    {
        if ($count <= 0) {
            return [];
        }

        $allocationLogic = function () use ($start, $end, $count) {
            // Ensure sequence record exists for this range
            DB::table('registration_number_sequences')->insertOrIgnore([
                'range_start' => $start,
                'range_end' => $end,
                'next_number' => $start,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Acquire exclusive row lock on the sequence record
            $sequence = DB::table('registration_number_sequences')
                ->where('range_start', $start)
                ->where('range_end', $end)
                ->lockForUpdate()
                ->first();

            // Find current highest registration number used by ACTIVE students only
            $maxActiveReg = self::query()
                ->whereBetween('registration_number', [(string)$start, (string)$end])
                ->orderByRaw('CAST(registration_number AS UNSIGNED) DESC')
                ->value('registration_number');

            $currentHighestActive = $maxActiveReg ? (int)$maxActiveReg : ($start - 1);
            $candidate = max((int)($sequence->next_number ?? $start), $currentHighestActive + 1);

            $allocated = [];
            while (count($allocated) < $count) {
                if ($candidate > $end) {
                    throw new \RuntimeException("Registration number range exhausted for {$start}-{$end}.");
                }

                // Verify candidate is not occupied by an active student
                $isUsedByActive = self::query()->where('registration_number', (string)$candidate)->exists();
                if (!$isUsedByActive) {
                    // If previously occupied by a soft-deleted student, clear it to satisfy database unique constraint
                    self::onlyTrashed()->where('registration_number', (string)$candidate)->update(['registration_number' => null]);

                    $allocated[] = (string)$candidate;
                }
                $candidate++;
            }

            // Advance the sequence state
            DB::table('registration_number_sequences')
                ->where('id', $sequence->id)
                ->update([
                    'next_number' => $candidate,
                    'updated_at' => now(),
                ]);

            return $allocated;
        };

        if (DB::transactionLevel() > 0) {
            return $allocationLogic();
        }

        return DB::transaction($allocationLogic);
    }

    /**
     * Get the student's registration base fee based on CategoryMaster fee.
     * Fallback to ClassMaster fee if Category fee is 0.00.
     */
    public function getRegistrationFeeAttribute()
    {
        if ($this->category && $this->category->registration_fee > 0) {
            return (float) $this->category->registration_fee;
        }
        return $this->class ? (float) $this->class->registration_fee : 0.0;
    }

    /**
     * Get fine amount applicable for this student (₹50 if fine active for school, ₹0 otherwise).
     */
    public function getFineAmountAttribute(): float
    {
        $school = $this->school;
        return $school ? $school->getFinePerStudentAmount() : 0.0;
    }

    /**
     * Get total payable fee for this student (Base Fee + Fine Amount).
     */
    public function getTotalFeeAttribute(): float
    {
        return (float) $this->registration_fee + (float) $this->fine_amount;
    }

    /**
     * Mutator for student name (forces uppercase).
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = mb_strtoupper(trim($value));
    }

    /**
     * Mutator for father's name (forces uppercase).
     */
    public function setFatherNameAttribute($value)
    {
        $this->attributes['father_name'] = mb_strtoupper(trim($value));
    }

    /**
     * Mutator for mother's name (forces uppercase).
     */
    public function setMotherNameAttribute($value)
    {
        $this->attributes['mother_name'] = mb_strtoupper(trim($value));
    }
}
