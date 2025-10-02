<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportItAll extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = ['id'];

    protected $appends =  ['employee_name','issue_raised_by'];  


    public function getCreatedAt(){
        return $this->created_at->format('d M, Y h:i A');
    }
    public function issueType(){
        return $this->belongsTo(IssueType::class,'issue_type_id','id');
    }

    public function student(){
        return $this->belongsTo(Student::class,'student_id','id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id','id');
    }

    public function venue(){
        return $this->belongsTo(Venue::class,'venue_id','id');
    }

    public function uploads(){
        return $this->hasMany(ReportItAllUpload::class,'report_it_all_id','id');
    }

    public function createdBy(){
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function updatedBy(){
        return $this->belongsTo(User::class,'updated_by','id');
    }

    public function getEmployeeNameAttribute(){
        return isset($this->updatedBy->employee) ? $this->updatedBy->employee->full_name : (isset($this->createdBy->employee) ? $this->createdBy->employee->full_name : "");
    }

    public function getIssueRaisedByAttribute() {
        if(isset($this->employee_id)) {
            return $this->employee->full_name;
        } else
            return $this->student->full_name; 
    }
    
}
