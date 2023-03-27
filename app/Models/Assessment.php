<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_creation_id',
        'course_module_base_assesment_id',
        'assessment_name',
        'assessment_code',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function moduleCreation(){
        return $this->belongsTo(ModuleCreation::class, 'module_creation_id', 'id');
    }

}
