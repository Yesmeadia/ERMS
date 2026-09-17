<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineExamFailedLogin extends Model
{
    public $timestamps = false;

    protected $table = 'online_exam_failed_logins';

    protected $fillable = [
        'registration_number',
        'failure_reason',
        'ip_address',
        'user_agent',
        'dob_attempted',
        'attempted_at',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
    ];
}
