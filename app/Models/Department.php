<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'available_for',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function employment(){
        return $this->hasMany(Employment::class, 'department_id', 'id');
    }

    /** Job titles assigned to this department. */
    public function jobTitles(){
        return $this->hasMany(EmployeeJobTitle::class, 'department_id', 'id')->orderBy('name');
    }

    /** Sub departments, each holding its own permission template. */
    public function permissionCategories(){
        return $this->hasMany(PermissionCategory::class)->orderBy('name');
    }
}
