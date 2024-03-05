<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeArchive extends Model
{
    use HasFactory ,SoftDeletes;

    protected $fillable = [
        'employee_id',
        'table',
        'row_id',
        'field_name',
        'field_value',
        'field_new_value',

        'employee_user_id',
        'created_by',
        'updated_by',
    ];
    
    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    
    public function cuser(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
