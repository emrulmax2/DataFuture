<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsentPolicy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'department_id',
        'is_required',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function department(){
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function studentConsent(){
        return $this->hasMany(StudentConsent::class, 'consent_policy_id', 'id');
    }
}
