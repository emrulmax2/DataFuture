<?php

namespace App\Models\AttendanceIntegrity;

use Illuminate\Database\Eloquent\Model;

/** A historical (term) or daily scan; the scheduler runs pending ones. */
class AimScanRun extends Model
{
    protected $table = 'aim_scan_runs';

    protected $guarded = ['id'];

    protected $casts = [
        'stats' => 'array',
    ];
}
