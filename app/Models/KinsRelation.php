<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KinsRelation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'is_hesa',
        'hesa_code',
        'is_df',
        'df_code',
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
