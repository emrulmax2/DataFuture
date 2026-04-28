<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;

    protected $table = 'login_logs';

    protected $fillable = [
        'actor_id',
        'actor_type',
        'guard_name',
        'session_id',
        'login_at',
        'logout_at',
        'logout_reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'login_at'  => 'datetime',
        'logout_at' => 'datetime',
    ];

    // ------------------------------------------------------------------ scopes

    /**
     * Open (not yet closed) log rows for a given actor.
     */
    public function scopeOpen($query, int $actorId, string $actorType)
    {
        return $query->where('actor_id', $actorId)
                     ->where('actor_type', $actorType)
                     ->whereNull('logout_at');
    }
}
