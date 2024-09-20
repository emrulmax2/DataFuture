<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ProcessList;
use Illuminate\Support\Facades\Storage;

class TaskList extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['image_url'];

    protected $fillable = [
        'process_list_id',
        'name',
        'short_description',
        'interview',
        'upload',
        'external_link',
        'external_link_ref',
        'status',
        'image',
        'image_path',
        'org_email',
        'id_card',
        'attendance_excuses',
        'pearson_reg',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The getter that return accessible URL for user photo.
     *
     * @var array
     */
    public function getImageUrlAttribute()
    {
        if ($this->image !== null && Storage::disk('local')->exists('public/process/'.$this->process_list_id.'/tasks/'.$this->id.'/'.$this->image)) {
            return Storage::disk('local')->url('public/process/'.$this->process_list_id.'/tasks/'.$this->id.'/'.$this->image);
        } else {
            return asset('build/assets/images/placeholders/200x200.jpg');
        }
    }

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
