<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocumentAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_document_id',
        'employee_id',
        'user_id',
        'impersonator_id',
        'event',
        'is_encrypted',
        'document_name',
        'ip_address',
        'user_agent',
    ];

    public function document(){
        return $this->belongsTo(EmployeeDocuments::class, 'employee_document_id')->withTrashed();
    }

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function impersonator(){
        return $this->belongsTo(User::class, 'impersonator_id')->withTrashed();
    }
}
