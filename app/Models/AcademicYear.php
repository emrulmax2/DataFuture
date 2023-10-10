<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'is_hesa',
        'hesa_code',
        'is_df',
        'df_code',
        'from_date',
        'to_date',
        'target_date_hesa_report',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function getFromDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function getToDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function getTargetDateHesaReportAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function crc_instance(){
        return $this->hasMany(CourseCreationInstance::class, 'academic_year_id', 'id');
    }

    public function terms(){
        return $this->hasManyThrough(
            InstanceTerm::class,
            CourseCreationInstance::class,
            'academic_year_id',
            'course_creation_instance_id',
            'id',
            'id'
        );
    }
}
