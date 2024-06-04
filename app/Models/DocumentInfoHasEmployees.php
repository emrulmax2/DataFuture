<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentInfoHasEmployees extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'document_info_id',
        'employee_id',
        'document_role_and_permission_id'
    ];

    public function info(){
        return $this->belongsTo(DocumentInfo::class, 'document_info_id');
    }

    public function role(){
        return $this->belongsTo(DocumentRoleAndPermission::class, 'document_role_and_permission_id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
