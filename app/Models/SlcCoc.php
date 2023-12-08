<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlcCoc extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'slc_cocs';

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'course_creation_instance_id',
        'slc_attendance_id',
        'confirmation_date',
        'coc_type',
        'reason',
        'actioned',
        
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
