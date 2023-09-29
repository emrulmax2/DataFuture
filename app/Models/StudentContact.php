<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'country_id',
        'permanent_country_id',
        'permanent_address_id',
        'term_time_address_id',
        'home',
        'term_time_accommodation_type_id',
        'mobile',
        'external_link_ref',
        'mobile_verification',
        'personal_email_verification',
        'personal_email',
        'permanent_post_code',
        'term_time_post_code',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function termaddress(){
        return $this->belongsTo(Address::class, 'term_time_address_id');
    }

    public function permaddress(){
        return $this->belongsTo(Address::class, 'permanent_address_id');
    }

    public function ttacom(){
        return $this->belongsTo(TermTimeAccommodationType::class, 'term_time_accommodation_type_id');
    }

    public function pcountry(){
        return $this->belongsTo(Country::class, 'permanent_country_id');
    }
}
