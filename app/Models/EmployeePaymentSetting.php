<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePaymentSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'pay_frequency',
        'tax_code',
        'payment_method',
        'subject_to_clockin',
        'holiday_base',
        'bank_holiday_auto_book',
        'pension_enrolled',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

}
