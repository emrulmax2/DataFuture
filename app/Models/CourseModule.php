<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'module_level_id',
        'name',
        'code',
        'status',
        'credit_value',
        'unit_value',
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

    public function course(){
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function level(){
        return $this->belongsTo(ModuleLevel::class, 'module_level_id');
    }

    public function assesments(){
        return $this->hasMany(CourseModuleBaseAssesment::class, 'course_module_id', 'id');
    }

    public function creation(){
        return $this->hasMany(ModuleCreation::class, 'course_module_id', 'id');
    }
}
