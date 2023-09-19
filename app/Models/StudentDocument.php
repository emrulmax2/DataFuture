<?php

namespace App\Models;

use Illuminate\Console\View\Components\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'document_setting_id',
        'hard_copy_check',
        'doc_type',
        'disk_type',
        'path',
        'display_file_name',
        'current_file_name',
        'created_by',
        'updated_by',
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

    public function documentSetting(){
        return $this->belongsTo(DocumentSettings::class, 'document_setting_id');
    }

    /*public function taskDocument(){
        return $this->hasOne(ApplicantTaskDocument::class, 'applicant_document_id', 'id');
    }*/

    // I am not sure about this line of code.... I don't think its working anywhere
        public function tasks(){
            return $this->belongsToMany(Task::class, 'student_task_documents');
        }
    // Please check :comment form Emrul

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
