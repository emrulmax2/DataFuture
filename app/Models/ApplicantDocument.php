<?php

namespace App\Models;

use Illuminate\Console\View\Components\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'applicant_id',
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

    public function applicant(){
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function documentSetting(){
        return $this->belongsTo(DocumentSettings::class, 'document_setting_id');
    }

    /*public function taskDocument(){
        return $this->hasOne(ApplicantTaskDocument::class, 'applicant_document_id', 'id');
    }*/
    
    public function tasks(){
        return $this->belongsToMany(Task::class, 'applicant_task_documents');
    }
    
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
