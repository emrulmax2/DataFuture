<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlcAgreement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'course_creation_instance_id',
        'slc_registration_id',
        'slc_coursecode',
        'is_self_funded',
        'date',
        'year',
        'fees',
        'no_of_installment',
        'discount',
        'total',
        'note',

        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setDateAttribute($value) {  
        $this->attributes['date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function installments(){
        return $this->hasMany(SlcInstallment::class, 'slc_agreement_id', 'id');
    }

    public function registration(){
        return $this->belongsTo(SlcRegistration::class, 'slc_registration_id');
    }
}
