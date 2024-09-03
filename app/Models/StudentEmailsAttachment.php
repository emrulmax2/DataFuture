<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentEmailsAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_email_id',
        'student_document_id',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function applicantEmail(){
        return $this->belongsTo(StudentEmail::class,  'student_email_id');
    }

    public function applicantdoc(){
        return $this->belongsTo(StudentDocument::class,  'student_document_id');
    }
}
