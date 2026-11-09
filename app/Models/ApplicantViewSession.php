<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A staff member's visit to one section of an applicant's record (see the
 * applicant_view_sessions migration). Written by AdmissionPresenceController.
 */
class ApplicantViewSession extends Model
{
    public const SECTIONS = ['details', 'process', 'uploads', 'notes', 'communication', 'esign', 'esign_request', 'log', 'timeline', 'other'];

    /** Heartbeats arrive every 15 s; a gap longer than this is idle time, not counted. */
    public const MAX_BEAT_GAP = 25;

    protected $fillable = [
        'applicant_id', 'user_id', 'section', 'route_name', 'url', 'started_at', 'last_seen_at', 'ended_at',
        'active_seconds', 'heartbeats', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Route name → section key, so the report can group by tab. */
    public static function sectionFor(?string $routeName): string
    {
        return match ($routeName) {
            'admission.show' => 'details',
            'admission.process' => 'process',
            'admission.uploads' => 'uploads',
            'admission.notes' => 'notes',
            'admission.communication' => 'communication',
            'admission.show.e.signature' => 'esign',
            'admission.show.e.signature.request' => 'esign_request',
            'admission.conversion.log' => 'log',
            'admission.timeline' => 'timeline',
            default => 'other',
        };
    }
}
