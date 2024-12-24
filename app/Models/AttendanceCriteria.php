<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCriteria extends Model
{
    use HasFactory;
    protected $table = 'attendance_criteria';
    protected $guarded = ['id'];
}
