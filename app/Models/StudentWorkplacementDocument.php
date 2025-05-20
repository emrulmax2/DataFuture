<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentWorkplacementDocument extends Model
{
    use HasFactory, SoftDeletes;

        protected $fillable = [
        'student_id',
        'student_work_placement_id',
        'hard_copy_check',
        'doc_type',
        'disk_type',
        'path',
        'display_file_name',
        'current_file_name',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    
}
