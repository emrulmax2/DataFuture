<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantTaskDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'applicant_task_id',
        'applicant_document_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function applicantTask(){
        return $this->belongsTo(ApplicantTask::class,  'applicant_task_id');
    }

    public function applicantdoc(){
        return $this->belongsTo(ApplicantDocument::class,  'applicant_document_id');
    }
}
