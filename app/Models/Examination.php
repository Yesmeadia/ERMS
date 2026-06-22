<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Examination extends Model
{
    use HasFactory;

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
}
