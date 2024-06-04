<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentInfoReminder extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['employee_ids'];

    protected $fillable = [
        'document_info_id',
        'subject',
        'message',
        'is_repeat_reminder',
        'is_send_email',
        'single_reminder_date',
        'frequency',
        'repeat_reminder_start',
        'repeat_reminder_end',
        
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function setSingleReminderDateAttribute($value) {  
        $this->attributes['single_reminder_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getSingleReminderDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setRepeatReminderStartAttribute($value) {  
        $this->attributes['repeat_reminder_start'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getRepeatReminderStartAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setRepeatReminderEndAttribute($value) {  
        $this->attributes['repeat_reminder_end'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getRepeatReminderEndAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function getEmployeeIdsAttribute(){
        return DocumentInfoReminderEmployee::where('document_info_reminder_id', $this->id)->pluck('employee_id')->unique()->toArray();
    }

    public function info(){
        return $this->belongsTo(DocumentInfo::class, 'document_info_id');
    }

    public function employee(){
        return $this->hasMany(DocumentInfoReminderEmployee::class, 'document_info_reminder_id', 'id');
    }
}
