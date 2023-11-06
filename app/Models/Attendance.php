<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
