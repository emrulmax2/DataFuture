<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultsegmentInCoursemodules extends Model
{
    use HasFactory;
    protected $fillable = [
        'grade_id',
        'course_module_base_assesment_id',
    ];

    public function grade(){
        return $this->belongsTo(Grade::class);
    }
}
