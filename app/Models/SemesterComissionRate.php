<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SemesterComissionRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'semester_id',
        'rate',

        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function semester(){
        return $this->belongsTo(Semester::class, 'semester_id');
    }
}
