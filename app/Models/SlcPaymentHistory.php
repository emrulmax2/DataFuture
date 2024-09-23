<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlcPaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'transaction_date',
        'term_name',
        'ssn',
        'first_name',
        'last_name',
        'dob',
        'course_id',
        'course_code',
        'course_name',
        'year',
        'amount',
        'status',
        'slc_money_receipt_id',
        'error_code',
        'errors',
        
        'created_by',
        'updated_by',
    ];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function course(){
        return $this->belongsTo(Course::class, 'course_id');
    }
}
