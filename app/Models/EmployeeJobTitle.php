<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeJobTitle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'department_id',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    /** The department this title sits in. Null where none has been set. */
    public function department(){
        return $this->belongsTo(Department::class, 'department_id');
    }

    /** Employments holding this title, for the in-use count and the archive guard. */
    public function employments(){
        return $this->hasMany(Employment::class, 'employee_job_title_id', 'id');
    }
}
