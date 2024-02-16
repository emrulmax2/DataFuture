<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseModuleBaseAssesment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'assessment_type_id',
        'is_result_segment',
        'course_module_id',
        'assesment_code',
        'assesment_name',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function type(){
        return $this->belongsTo(AssessmentType::class, 'assessment_type_id', 'id');
    }

    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class,"resultsegment_in_coursemodules");
    }
}
