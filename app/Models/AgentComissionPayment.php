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

        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function comissions(){
        return $this->hasMany(AgentComission::class, 'agent_comission_payment_id', 'id');
    }
}
