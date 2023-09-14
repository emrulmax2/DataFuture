<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentTaskDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_task_id',
        'student_document_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function studentTask(){
        return $this->belongsTo(StudentTask::class,  'student_task_id');
    }

    public function studentDoc(){
        return $this->belongsTo(StudentDocument::class,  'student_document_id');
    }
}
