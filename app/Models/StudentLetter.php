<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentLetter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'letter_set_id',
        'signatory_id',
        'comon_smtp_id',
        'is_email_or_attachment',
        'student_document_id',
        'issued_by',
        'issued_date',
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

    public function document(){
        return $this->belongsTo(StudentDocument::class, 'student_document_id');
    }
}
