<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSmsContent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'sms_template_id',
        'subject',
        'sms'
    ];

    public function template(){
        return $this->belongsTo(SmsTemplate::class, 'sms_template_id');
    }

    public function sms(){
        return $this->hasMany(StudentSms::class, 'student_sms_content_id', 'id');
    }

}
