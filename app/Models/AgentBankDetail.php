<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentBankDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'beneficiary',
        'sort_code',
        'ac_no',
        'active',

        'created_by',
        'updated_by',
    ];


    protected $dates = ['deleted_at'];

    public function agent(){
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}
