<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanTask extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [ "id" ];

    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function eLearn(): BelongsTo
    {
        return $this->belongsTo(ELearningActivitySetting::class, 'e_learning_activity_setting_id');
    }

    public function uploads()
    {
        return $this->hasMany(PlanTaskUpload::class, 'plan_task_id');
    }

    public function getLastDateAttribute()
    {
        $required_date = false;
        $days_reminder = (isset($this->days_reminder) && $this->days_reminder > 0 ? $this->days_reminder : 0);
        $startDate = (isset($this->plan->attenTerm->start_date) && !empty($this->plan->attenTerm->start_date) ? date('Y-m-d', strtotime($this->plan->attenTerm->start_date)) : '');
        if(!empty($startDate)):
            $required_date = date('Y-m-d', strtotime('+'.$days_reminder.' days', strtotime($startDate)));
        endif;
        return $required_date;
    }
    
}
