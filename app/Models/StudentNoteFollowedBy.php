<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentNoteFollowedBy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_note_id',
        'user_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function note(){
        return $this->belongsTo(StudentNote::class, 'student_note_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
