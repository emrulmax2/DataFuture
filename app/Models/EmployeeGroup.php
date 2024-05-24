<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['member_ids'];

    protected $fillable = [
        'employee_id',
        'name',
        'type',
        
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function members(){
        return $this->hasMany(EmployeeGroupMember::class, 'employee_group_id', 'id');
    }

    public function getMemberIdsAttribute(){
        return EmployeeGroupMember::where('employee_group_id', $this->id)->pluck('employee_id')->unique()->toArray();
    }
}
