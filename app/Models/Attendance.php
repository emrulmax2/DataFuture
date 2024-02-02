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
        "student_id",
        "attendance_feed_status_id",
        "email_notification",	
        "sms_notification",	
        'created_by',
        'updated_by',
    ];
    public function feed(){
        return $this->belongsTo(AttendanceFeedStatus::class, 'attendance_feed_status_id');
    }

    public function updatedBy(): BelongsTo 
    {
        return $this->belongsTo(User::class,"updated_by");
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,"created_by");
    }

    
}
