<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAttendanceTermStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'term_declaration_id',
        'status_id',
        'status_change_reason',
        'status_change_date',
        'status_end_date',
        'reason_for_engagement_ending_id',
        'qual_award_type',
        'qual_award_result_id',
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

    public function term(){
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }

    public function status(){
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
}