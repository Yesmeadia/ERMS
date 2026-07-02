<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'status' => 'boolean',
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
}
