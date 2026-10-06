<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentOrderPayment extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'student_order_id',
        'student_id',
        'provider',
        'provider_session_id',
        'provider_payment_id',
        'token',
        'status',
        'amount',
        'currency',
        'checkout_url',
        'return_url',
        'expires_at',
        'paid_at',
        'failure_message',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'integer',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function order(){
        return $this->belongsTo(StudentOrder::class, 'student_order_id');
    }

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }
}
