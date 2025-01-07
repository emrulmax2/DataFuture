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
}
