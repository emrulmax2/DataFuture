<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaySlipUploadSync extends Model
{
    use HasFactory;

    protected $table = 'pay_slip_upload_syncs';

    protected $fillable = [
        'employee_id',
        'file_name',
        'file_path',
        'holiday_year_id',
        'type',
        'month_year',
        'is_file_exist',
        'file_transffered',
        'file_transffered_at',
        'email_transferred_at',
        'is_file_uploaded',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function holidayYear()
    {
        return $this->belongsTo(HrHolidayYear::class, 'holiday_year_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
