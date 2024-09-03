<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentEmailsDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_email_id',
        'hard_copy_check',
        'doc_type',
        'disk_type',
        'path',
        'display_file_name',
        'current_file_name',
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

    public function student() {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function mails() {
        return $this->belongsTo(StudentEmail::class, 'student_email_id');
    }
}
