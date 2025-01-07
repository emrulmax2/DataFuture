<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentComission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'agent_comission_rule_id',
        'slc_money_receipt_id',
        'receipt_amount',
        'comission',
        'paid_date',
        'paid_amount',
        'remittance_ref',
        'status',
        'created_by',
        'updated_by',
    ];
}
