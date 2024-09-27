<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class AgentReferralCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $fillable = [
        'code',
        'type',
        'user_id',
		'agent_user_id' ,
		'student_id' ,
        'active',
        'created_by',
        'updated_by'
    ];

    protected $dates = ['deleted_at'];

    public function AgentUser() {
        return $this->belongsTo(AgentUser::class, 'agent_user_id');
    }

    public function User() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Student() {
        return $this->belongsTo(Student::class, 'student_id');
    }

}
