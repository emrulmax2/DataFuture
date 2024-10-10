<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentNoteFollowupCommentRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_note_id',
        'student_note_followup_comment_id',
        'user_id',
        'read',
        'readed_at',
    ];

    public function note(){
        return $this->belongsTo(StudentNote::class, 'student_note_id');
    }

    public function comment(){
        return $this->belongsTo(StudentNoteFollowupComment::class, 'student_note_followup_comment_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
