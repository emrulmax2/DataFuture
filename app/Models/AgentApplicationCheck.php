<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentApplicationCheck extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['full_name'];

    protected $fillable = [
        'agent_user_id',
        'applicant_id',
        'first_name',
        'last_name',
        'mobile',
        'email',
        'verify_code',
        'email_verify_code',
        'email_verified_at',
        'mobile_verified_at',
        'active',
        'created_by',
        'updated_by',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime'
    ];
    
    public function getFullNameAttribute() {
        return $this->first_name . ' ' . $this->last_name.'';
    }
}
