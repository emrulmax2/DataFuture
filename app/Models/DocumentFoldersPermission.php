<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFoldersPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_role_and_permission_id',
        'document_folder_id',
        'employee_id',
    ];
}
