<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantEmployment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'applicant_id',
        'company_name',
        'company_phone',
        'position',
        'start_date',
        'end_date',
        'continuing',
        'address_line_1',
        'address_line_2',
        'state',
        'post_code',
        'city',
        'country',
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
    
    public function reference(){
        return $this->hasMany(EmploymentReference::class, 'applicant_employment_id', 'id');
    }

    public function referenceSingle()
    {
        return $this->hasOne(EmploymentReference::class, 'applicant_employment_id', 'id')->latestOfMany();
    }
}
