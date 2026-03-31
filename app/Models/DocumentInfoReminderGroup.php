<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentInfoReminderGroup extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'document_info_reminder_id',
        'employee_group_id'
    ];

    public function reminder(){
        return $this->belongsTo(DocumentInfoReminder::class, 'document_info_reminder_id');
    }

    public function group(){
        return $this->belongsTo(EmployeeGroup::class, 'employee_group_id');
    }
}
