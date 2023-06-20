<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantDisability extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'applicant_id',
        'disabilitiy_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function applicant(){
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function disabilities(){
        return $this->belongsTo(Disability::class, 'disabilitiy_id');
    }
}
