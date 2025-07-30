<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class StudentVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'visit_type',
        'visit_date',
        'visit_duration',
        'plan_id',
        'term_declaration_id',
        'plan_id',
        'visit_notes',
        'attendance_id',
        'attendance_deleted_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    //get date format for visit_date
    public function getVisitDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : null;
    }
    //set date format for visit_date
    public function setVisitDateAttribute($value)
    {
        $this->attributes['visit_date'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function attendanceDeletedBy()
    {
        return $this->belongsTo(User::class, 'attendance_deleted_by');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
    public function termDeclaration()
    {
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }
}
