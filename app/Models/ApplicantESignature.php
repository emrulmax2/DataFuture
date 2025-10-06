<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantESignature extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'applicant_id',
        'ip_address',
        'device',
        'browser',
        'os',
        'latitude',
        'longitude',
        'viewed_via',
        'status',
        'video_consent',
        'declaration',
        'signature',
        'signed_date',
        'viewed_at',
        'submited_at',
        'email_sent_at',
        'email_read_at',
        'e_sign_view_at',
        'location_verify_at',
        'consented_esign_at',
        'finalized_at',
        'modified_at',
        'sign_req_finalized_at',
        'renamed_at',
    ];

     /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'video_consent' => 'boolean',
        'declaration' => 'boolean',
        'signed_date' => 'date',
    ];
}
