<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceInformation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table =  "attendance_informations";

    protected $fillable = [
        "plans_date_list_id",
        "tutor_id",
        "start_time",
        "end_time",	
        "note",	
        'created_by',
        'updated_by',
    ];

    public function tutor(){
        return $this->belongsTo(User::class, 'tutor_id');
    }
    public function planDate(){
        return $this->belongsTo(PlansDateList::class, 'plans_date_list_id');
    }
}
