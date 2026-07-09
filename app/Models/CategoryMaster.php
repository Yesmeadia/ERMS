<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'code',
        'registration_fee',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'registration_fee' => 'decimal:2',
    ];

    /**
     * Get the students in this category.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'category_id');
    }
}
