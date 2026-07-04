<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'transaction_id',
        'amount',
        'payment_method',
        'status',
        'paid_at',
        'razorpay_order_id',
        'razorpay_payment_id',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the school that made this payment.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Get the students paid for in this transaction.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'payment_student', 'payment_id', 'student_id')
            ->withPivot('amount')
            ->withTimestamps();
    }
}
