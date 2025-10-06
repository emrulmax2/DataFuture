<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminESignature extends Model
{
    use HasFactory;

        /**
        * The attributes that are mass assignable.
        *
        * @var array<int, string>
        */
    protected $fillable = [
        'user_id',
        'applicant_id',
        'smtp_email',
        'ip_address',
        'device',
        'browser',
        'os',
        'latitude',
        'longitude',
        'via_email',
        'via_sms',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
