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
}
