<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\PermissionCategory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PermissionTemplate;

class PermissionTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'role_id',
        'permission_category_id',
        'department_id',
        'type',
        'R',
        'W',
        'D',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function role(){
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function permissioncategory(){
        return $this->belongsTo(PermissionCategory::class, 'permission_category_id');
    }

    public function department(){
        return $this->belongsTo(Department::class, 'department_id');
    }
}
