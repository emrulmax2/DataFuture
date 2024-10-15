<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseCreationVenue extends Model
{
    protected $table = 'course_creation_venue';
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'course_creation_id',
        'venue_id',
        'slc_code',
        'evening_and_weekend',
        'weekdays',
        'weekends',
    ];
}
