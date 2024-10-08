<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlansDateList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plan_id',
        'name',
        'date',
        'feed_given',
        'status',
        'canceled_reason',
        'proxy_tutor_id',
        'proxy_assigned_by',
        'proxy_assigned_at',
        'proxy_reason',
        'proxy_class_tutor_note',
        'canceled_by',
        'canceled_at',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function plan(){
        return $this->belongsTo(Plan::class, 'plan_id');
    }
    public function attendanceInformation() {
        return $this->hasOne(AttendanceInformation::class, 'plans_date_list_id');
    }
    public function attendances() {
        return $this->hasMany(Attendance::class, 'plans_date_list_id');
    }
    public function proxy() {
        return $this->belongsTo(User::class, 'proxy_tutor_id');
    }

    public function setDateAttribute($value) {  
        $this->attributes['date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
