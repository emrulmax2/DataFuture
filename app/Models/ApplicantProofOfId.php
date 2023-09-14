<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantProofOfId extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'applicant_id',
        'proof_type',
        'proof_id',
        'proof_expiredate',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function setProofExpiredateAttribute($value) {  
        $this->attributes['proof_expiredate'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }
    
    public function getProofExpiredateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function applicant(){
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }
}
