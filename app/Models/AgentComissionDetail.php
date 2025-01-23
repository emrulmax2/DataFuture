<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentComissionDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'agent_comission_id',
        'slc_money_receipt_id',
        'comission_for',
        'amount',
        'status',

        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function setEntryDateAttribute($value) {  
        $this->attributes['entry_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getEntryDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function comission(){
        return $this->belongsTo(AgentComission::class, 'agent_comission_id');
    }

    public function receipt(){
        return $this->belongsTo(SlcMoneyReceipt::class, 'slc_money_receipt_id');
    }
}
