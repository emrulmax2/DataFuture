<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceExcuseDay extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attendance_excuse_id',
        'plan_id',
        'plans_date_list_id',
        'active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function excuse(){
        return $this->belongsTo(AttendanceExcuse::class, 'attendance_excuse_id');
    }

    public function plandate(){
        return $this->belongsTo(PlansDateList::class, 'plans_date_list_id');
    }

    public function plan(){
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
