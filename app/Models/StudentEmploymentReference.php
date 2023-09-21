<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentEmploymentReference extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_employment_id',
        'name',
        'position',
        'phone',
        'email',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function employment(){
        return $this->belongsTo(StudentEmployment::class, 'student_employment_id', 'id');
    }
}
