<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleCreation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'instance_term_id',
        'course_module_id',
        'module_level_id',
        'module_name',
        'code',
        'status',
        'credit_value',
        'unit_value',
        'moodle_enrollment_key',
        'class_type',
        'submission_date',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function setSubmissionDateAttribute($value) {
        $this->attributes['submission_date'] = (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getSubmissionDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function term(){
        return $this->belongsTo(InstanceTerm::class, 'instance_term_id', 'id');
    }

    public function module(){
        return $this->belongsTo(CourseModule::class, 'course_module_id', 'id');
    }

    public function level(){
        return $this->belongsTo(ModuleLevel::class, 'module_level_id', 'id');
    }

    public function asses(){
        return $this->hasMany(Assessment::class, 'module_creation_id', 'id');
    }

}
