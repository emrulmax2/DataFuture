<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantTaskLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'applicant_tasks_id',
        'actions',
        'field_name',
        'prev_field_value',
        'current_field_value',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function logTask(){
        return $this->belongsTo(ApplicantTask::class, 'applicant_tasks_id');
    }
}
