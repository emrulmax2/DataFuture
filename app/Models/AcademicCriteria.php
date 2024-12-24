<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicCriteria extends Model
{
    use HasFactory;
    protected $table = 'academic_criteria';
    protected $guarded = ['id'];
}
