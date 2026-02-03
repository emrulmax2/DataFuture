<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantCriminalConviction extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'applicant_id',
        'have_you_been_convicted',
        'criminal_conviction_details',
        'criminal_declaration',
        'created_by',
        'updated_by',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    
}
