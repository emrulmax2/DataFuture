<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One permission key for one sub department (permission category) within a
 * department. The set of rows for a department + category pair is the template
 * the HR employee privilege screen loads.
 */
class DepartmentTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['department_id', 'permission_category_id', 'key', 'value'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function permissionCategory()
    {
        return $this->belongsTo(PermissionCategory::class);
    }
}
