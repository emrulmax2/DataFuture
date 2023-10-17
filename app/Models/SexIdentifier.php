<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SexIdentifier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'is_hesa',
        'hesa_code',
        'is_df',
        'df_code',
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

    public function applicants(){
        return $this->hasMany(Applicant::class, 'sex_identifier_id', 'id');
    }

    public function students(){
        return $this->hasMany(Student::class, 'sex_identifier_id', 'id');
    }
}
