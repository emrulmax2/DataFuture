<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class StudentEmail extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $appends = ['pdf_url', 'document_list'];

    protected $fillable = [
        'student_id',
        'common_smtp_id',
        'email_template_id',
        'subject',
        'mail_pdf_file',
        'created_by',
        'updated_by',
    ];

    public function getPdfUrlAttribute()
    {
        if ($this->mail_pdf_file !== null && Storage::disk('local')->exists('public/students/'.$this->id.'/'.$this->mail_pdf_file)) {
            return Storage::disk('local')->url('public/students/'.$this->id.'/'.$this->mail_pdf_file);
        } else {
            return '';
        }
    }

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
        return $this->belongsTo(ComonSmtp::class, 'common_smtp_id');
    }

    // public function documents(){
    //     return $this->belongsToMany(StudentDocument::class, 'student_emails_attachments');
    // }
    
    public function template(){
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
    
    public function documents(){
        return $this->hasMany(StudentEmailsDocument::class, 'student_email_id', 'id');
    }

    public function getDocumentListAttribute()
    {
        $list = [];
        $emailDocs = StudentEmailsDocument::where('student_email_id', $this->id)->get();
        if($emailDocs->count() > 0):
            foreach($emailDocs as $ed):
                $list[$ed->id] = $ed->current_file_name;
            endforeach;
        endif;

        return $list;
    }
}
