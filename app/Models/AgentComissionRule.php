<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentComissionRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agent_user_id',
        'semester_id',
        'code',
        'comission_mode',
        'percentage',
        'amount',
        'period',
        'payment_type',

        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function agentuser(){
        return $this->belongsTo(AgentUser::class, 'agent_user_id');
    }

    public function semester(){
        return $this->belongsTo(Semester::class, 'semester_id');
    }
}
