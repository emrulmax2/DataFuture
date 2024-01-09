<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded  = ['id'];
    
    protected $appends = ['full_name'];

    protected $dates = ['deleted_at'];
    
    public function getFullNameAttribute() {
        return $this->first_name . ' ' . $this->last_name.'';
    }

    public function AgentUser() {
        return $this->belongsTo(AgentUser::class, 'agent_user_id');
    }
}
