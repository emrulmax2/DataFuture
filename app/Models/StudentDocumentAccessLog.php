<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDocumentAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_document_id',
        'student_id',
        'user_id',
        'impersonator_id',
        'event',
        'is_encrypted',
        'document_name',
        'ip_address',
        'user_agent',
    ];

    public function document(){
        return $this->belongsTo(StudentDocument::class, 'student_document_id')->withTrashed();
    }

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function impersonator(){
        return $this->belongsTo(User::class, 'impersonator_id')->withTrashed();
    }
}
