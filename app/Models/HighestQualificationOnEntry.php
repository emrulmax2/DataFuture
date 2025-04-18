<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HighestQualificationOnEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'highest_qualification_of_entry'; // Set the table name

    protected $fillable = [
        'name',
        'is_hesa',
        'hesa_code',
        'is_df',
        'df_code',
        'iso_code',
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
