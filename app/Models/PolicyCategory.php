<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A group of policies as the college website shows them — Corporate,
 * Academic and Student Policies. Assigning a category assigns every active
 * policy in it.
 */
class PolicyCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'description',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function policies(){
        return $this->hasMany(PolicyDocument::class, 'policy_category_id', 'id');
    }

    public function activePolicies(){
        return $this->hasMany(PolicyDocument::class, 'policy_category_id', 'id')->where('is_active', 1)->orderBy('sort_order', 'ASC')->orderBy('title', 'ASC');
    }
}
