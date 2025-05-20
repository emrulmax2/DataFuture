<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkplacementDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'hours',
        'course_id',
        'start_date',
        'end_date',
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

    public function setStartDateAttribute($value) { 
        $this->attributes['start_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getStartDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setEndDateAttribute($value) {  
        $this->attributes['end_date'] = !empty($value) ? date('Y-m-d', strtotime($value)) : null;
    }
    
    public function getEndDateAttribute($value) {
        return !empty($value) ? date('d-m-Y', strtotime($value)) : null;
    }

    public function level_hours(){
        return $this->hasMany(LevelHours::class, 'workplacement_details_id')->orderBy('id', 'ASC');
    }

}
