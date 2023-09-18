<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentDisability extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'disability_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function disabilities(){
        return $this->belongsTo(Disability::class, 'disability_id');
    }
}
