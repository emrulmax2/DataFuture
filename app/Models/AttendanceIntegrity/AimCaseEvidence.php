<?php

namespace App\Models\AttendanceIntegrity;

use Illuminate\Database\Eloquent\Model;

/** A file added to a case. Append-only; stored on the private local disk. */
class AimCaseEvidence extends Model
{
    protected $table = 'aim_case_evidence';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::updating(function () {
            throw new \LogicException('Case evidence cannot be changed.');
        });
        static::deleting(function () {
            throw new \LogicException('Case evidence cannot be deleted.');
        });
    }
}
