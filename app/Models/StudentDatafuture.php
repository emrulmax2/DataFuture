<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentDatafuture extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'NUMHUS',
        'CARELEAVER',
        'ENTRYQUALAWARDID',
        'ENGENDDATE',
        'RSNENGEND',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];
}
