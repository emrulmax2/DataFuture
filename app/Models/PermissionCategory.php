<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissionCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'name',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function template(){
        return $this->hasMany(PermissionTemplate::class);
    }

    /** The department this category sits under; its `name` is the sub-department. */
    public function department(){
        return $this->belongsTo(Department::class);
    }
}
