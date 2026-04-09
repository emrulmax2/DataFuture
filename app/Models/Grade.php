<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'turnitin_grade',
        'active',
        'created_by',
        'updated_by',
    ];

    
    public function courseModuleAssessment(): BelongsToMany
    {
        return $this->belongsToMany(CourseModuleBaseAssesment::class,"resultsegment_in_coursemodules");
    }
}
