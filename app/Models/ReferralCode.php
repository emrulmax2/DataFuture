<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'agent_referral_codes';

    protected $fillable = [
        'code',
        'type',
        'user_id',
        'agent_user_id',
        'student_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function agent_user(){
        return $this->belongsTo(AgentUser::class, 'agent_user_id');
    }
    
    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function agentUser(){
        return $this->belongsTo(AgentUser::class, 'agent_user_id');
    }

    /**
     * Who the code belongs to, for the applicant's "Verified - ..." line.
     * Agent codes almost all carry agent_user_id (the agency, named by its
     * organisation); the two oldest carry a staff user_id instead. Student
     * codes name the student.
     */
    public function getOwnerNameAttribute(){
        if($this->agent_user_id && ($agent = optional($this->agentUser)->agent)){
            return $agent->organization ?: trim($agent->first_name.' '.$agent->last_name);
        }
        if($this->student_id && $this->student){
            return trim($this->student->first_name.' '.$this->student->last_name);
        }
        if($this->user_id && $this->user){
            return $this->user->name;
        }
        return '';
    }
}
