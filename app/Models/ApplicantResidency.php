<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantResidency extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'applicant_id',
        'residency_status_id',
        'created_by',
        'updated_by',
    ];

    public function residencyStatus()
    {
        return $this->belongsTo(ResidencyStatus::class, 'residency_status_id');
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }
}
