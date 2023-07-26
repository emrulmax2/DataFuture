<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantViewUnlock extends Model
{
    use HasFactory,SoftDeletes;

    //protected $guard = ['id'];
    protected $fillable = ['user_id', 'applicant_id','token','expired_at','created_by'];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at','expired_at'];

    public function setExpiredAtAttribute($value) {  
        $this->attributes['expired_at'] =  (!empty($value) ? date('Y-m-d H:i:s', strtotime($value)) : '');
    }
    public function getExpiredAtAttribute($value) {
        return (!empty($value) ? date('d-m-Y  H:i:s', strtotime($value)) : '');
    }
    
    public function applicant(){
        return $this->belongsTo(Applicant::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
