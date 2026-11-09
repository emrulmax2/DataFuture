<?php

namespace App\Models\AttendanceIntegrity;

use Illuminate\Database\Eloquent\Model;

/** One anomaly found by an Attendance Integrity rule (see Detection\Scanner). */
class AimFinding extends Model
{
    protected $table = 'aim_findings';

    protected $guarded = ['id'];

    protected $casts = [
        'evidence' => 'array',
    ];

    public const STATUSES = ['open' => 'Open', 'dismissed' => 'Dismissed', 'case' => 'In a case'];

    public const BASIS = [
        'fact' => 'Confirmed fact',
        'anomaly' => 'Anomaly',
        'unavailable' => 'Evidence not available',
    ];
}
