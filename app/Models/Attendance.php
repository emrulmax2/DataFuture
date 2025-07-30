<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "plans_date_list_id",
        "attendance_date",
        "attendance_captured_at",
        "plan_id",
        "student_id",	
        "attendance_feed_status_id",	
        "email_notification",	
        "sms_notification",	
        "notofication_date",	
        "notofied_by",	
        "attendence_excuse_id",	
        "class_type",	
        "prev_plan_id",	
        'created_by',
        'updated_by',
        'note',
    ];
    public function feed(){
        return $this->belongsTo(AttendanceFeedStatus::class, 'attendance_feed_status_id');
    }

    public function plan(){
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function updatedBy(): BelongsTo 
    {
        return $this->belongsTo(User::class,"updated_by");
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,"created_by");
    }

    public function planDateList() {
        return $this->belongsTo(PlansDateList::class, 'plans_date_list_id');
    }
    
}
