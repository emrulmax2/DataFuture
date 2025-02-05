<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentComissionPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'agent_user_id',
        'reference',
        'date',
        'amount',
        'status',
        'acc_transaction_id',

        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function comissions(){
        return $this->hasMany(AgentComission::class, 'agent_comission_payment_id', 'id');
    }

    public function agent(){
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function setDateAttribute($value) {  
        $this->attributes['date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function transaction(){
        return $this->belongsTo(AccTransaction::class, 'acc_transaction_id');
    }
}
