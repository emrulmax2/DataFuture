<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFlagRaiser extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'student_flag_id',
        'user_id',
    ];

    public function flag(){
        return $this->belongsTo(StudentFlag::class, 'student_flag_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
