<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportItAllLog extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = ['id'];

    public function reportItAll(){
        return $this->belongsTo(ReportItAll::class,'report_it_all_id','id');
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
}
