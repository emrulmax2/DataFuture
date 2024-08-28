<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentSms extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        //'sms_template_id',
        'student_sms_content_id',
        'phone',
        //'subject',
        //'sms',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function sms(){
        return $this->belongsTo(StudentSmsContent::class, 'student_sms_content_id');
    }
    
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /*public function template(){
        return $this->belongsTo(SmsTemplate::class, 'sms_template_id');
    }*/
}
