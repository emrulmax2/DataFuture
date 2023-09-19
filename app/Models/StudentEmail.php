<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentEmail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'comon_smtp_id',
        'email_template_id',
        'subject',
        'body',
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
    
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function smtp(){
        return $this->belongsTo(ComonSmtp::class, 'comon_smtp_id');
    }

    public function documents(){
        return $this->belongsToMany(StudentDocument::class, 'student_emails_attachments');
    }
    
    public function template(){
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
}
