<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseQualification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'course_creation_qualifications';

    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function creation(){
        return $this->hasMany(CourseCreation::class);
    }
}
