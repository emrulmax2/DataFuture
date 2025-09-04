<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'applicant_task_id',
        'task_list_id',
        'assign_user',
        'external_link_ref',
        'status',
        'canceled_reason',
        'task_status_id',
        'created_by',
        'updated_by',
        'student_document_request_form_id',
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
    
    public function task(){
        return $this->belongsTo(TaskList::class, 'task_list_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'assign_user');
    }

    public function documents(){
        return $this->belongsToMany(StudentDocument::class, 'student_task_documents');
    }

    public function studentTaskStatus(){
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function logs(){
        return $this->hasMany(StudentTaskLog::class, 'student_tasks_id', 'id');
    }

    public function updatedBy(){
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }
    public function excuse(){
        return $this->hasOne(AttendanceExcuse::class, 'student_task_id', 'id')->latestOfMany();
    }

    public function studentDocumentRequestForm(){
        return $this->belongsTo(StudentDocumentRequestForm::class, 'student_document_request_form_id', 'id');
    }
    public function addressUpdateRequest(){
        return $this->hasOne(StudentAddressUpdateRequest::class, 'student_task_id', 'id')->latestOfMany();
    }
}
