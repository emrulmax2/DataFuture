<?php

namespace App\Models\AttendanceIntegrity;

use Illuminate\Database\Eloquent\Model;

/** One entry in a case's history. Append-only: changing or deleting one throws. */
class AimCaseActivity extends Model
{
    protected $table = 'aim_case_activities';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
    ];

    protected static function booted()
    {
        static::updating(function () {
            throw new \LogicException('Case history entries cannot be changed.');
        });
        static::deleting(function () {
            throw new \LogicException('Case history entries cannot be deleted.');
        });
    }
}
