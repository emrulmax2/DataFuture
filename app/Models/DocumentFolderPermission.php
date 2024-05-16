<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFolderPermission extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'document_role_and_permission_id',
        'document_folder_id',
        'employee_id',
    ];

    public function folder(){
        return $this->belongsTo(DocumentFolder::class, 'document_folder_id');
    }

    public function role(){
        return $this->belongsTo(DocumentRoleAndPermission::class, 'document_role_and_permission_id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
