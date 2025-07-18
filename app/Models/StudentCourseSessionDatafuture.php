<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseSessionDatafuture extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'student_stuload_information_id',
        'INVOICEHESAID',
        'RSNSCSEND',
        'ELQ',
        'FUNDCOMP',
        'FUNDLENGTH',
        'NONREGFEE',
        'FINSUPTYPE',
        'DISTANCE',
        'STUDYPROPORTION',

        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function elq(){
        return $this->belongsTo(EquivalentOrLowerQualification::class, 'ELQ');
    }

    public function fundcomp(){
        return $this->belongsTo(FundingCompletion::class, 'FUNDCOMP');
    }

    public function fundLength(){
        return $this->belongsTo(FundingLength::class, 'FUNDLENGTH');
    }

    public function nonregfee(){
        return $this->belongsTo(NonRegulatedFeeFlag::class, 'NONREGFEE');
    }
}
