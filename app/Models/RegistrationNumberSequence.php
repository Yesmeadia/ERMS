<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationNumberSequence extends Model
{
    use HasFactory;

    protected $table = 'registration_number_sequences';

    protected $fillable = [
        'range_start',
        'range_end',
        'next_number',
    ];
}
