<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use App\Notifications\VerifyEmailForApplicant;
use Lab404\Impersonate\Models\Impersonate;

class ApplicantUser extends Authenticatable  implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, Impersonate;

    protected $fillable = [
        'email',
        'phone',
        'student_id',
        'password',
        'active',
        'email_verified_at', 
        'phone_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime'
    ];

    public function setPasswordAttribute($value)
    {
       $this->attributes['password'] = Hash::make($value);
    }

    public function  SendEmailVerificationNotification() {
        $this->notify(new VerifyEmailForApplicant($this));
    }
}
