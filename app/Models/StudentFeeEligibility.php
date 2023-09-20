<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentFeeEligibility extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'fee_eligibility_id',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function elegibility(){
        return $this->belongsTo(FeeEligibility::class, 'fee_eligibility_id');
    }

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }
}
