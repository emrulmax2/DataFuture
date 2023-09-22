<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentConsent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'consent_policy_id',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function consent(){
        return $this->belongsTo(ConsentPolicy::class, 'consent_policy_id');
    }
}
