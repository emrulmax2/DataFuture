<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ProcessList;

class TaskList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'process_list_id',
        'name',
        'short_description',
        'interview',
        'upload',
        'external_link',
        'external_link_ref',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function processlist(){
        return $this->belongsTo(ProcessList::class, 'process_list_id');
    }

    public function users(){
        return $this->hasMany(TaskListUser::class, 'task_list_id', 'id');
    }

    public function statuses(){
        return $this->hasMany(TaskListStatus::class, 'task_list_id', 'id');
    }

    public function applicantTask(){
        return $this->hasMany(ApplicantTask::class, 'task_list_id', 'id');
    }

    public function applicant()
    {
        return $this->hasManyThrough('App\Models\Applicant', 'App\Models\ApplicantTask','task_list_id','id','id','applicant_id');
    }

}
