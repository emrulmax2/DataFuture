<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDocuments extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'document_setting_id',
        'hard_copy_check',
        'doc_type',
        'disk_type',
        'path',
        'display_file_name',
        'current_file_name',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function documentSetting(){
        return $this->belongsTo(DocumentSettings::class, 'document_setting_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
