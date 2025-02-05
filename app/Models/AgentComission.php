<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentComission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'agent_user_id',
        'agent_comission_rule_id',
        'semester_id',
        'remittance_ref',
        'entry_date',
        'status',
        'agent_comission_payment_id',
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

    public function agent(){
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function agentuser(){
        return $this->belongsTo(AgentUser::class, 'agent_user_id');
    }

    public function rule(){
        return $this->belongsTo(AgentComissionRule::class, 'agent_comission_rule_id');
    }

    public function semester(){
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function comissions(){
        return $this->hasMany(AgentComissionDetail::class, 'agent_comission_id', 'id');
    }

    public function payment(){
        return $this->belongsTo(AgentComissionPayment::class, 'agent_comission_payment_id');
    }
}
