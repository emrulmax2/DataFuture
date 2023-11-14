<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ELearningActivitySetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category',
        'logo',
        'name',
        'has_week',
        'days_reminder',
        'is_mandatory',
        'active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

}
