<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grade extends Model
{
    use HasFactory;

    
    public function courseModuleAssessment(): BelongsToMany
    {
        return $this->belongsToMany(CourseModuleBaseAssesment::class,"resultsegment_in_coursemodules");
    }
}
