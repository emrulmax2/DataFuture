<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeApprisalDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_appraisal_id',
        'employee_document_id',
        'created_by',
        'updated_by',
    ];
    
    protected $dates = ['deleted_at'];


    public function document(){
        return $this->belongsTo(EmployeeDocuments::class, 'employee_document_id');
    }

    public function appraisal(){
        return $this->belongsTo(EmployeeAppraisal::class, 'employee_appraisal_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
