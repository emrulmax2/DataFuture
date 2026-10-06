<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One time the member of staff left the test while an attempt was running:
 * they switched tab or minimised the browser ('tab'), or clicked outside the
 * test window ('window'). Records when they left, the question on screen and
 * how long they were away.
 *
 * A row with no returned_at means they are still away; submitting the
 * attempt closes it. Times are the server's — the page only says "left" and
 * "back". This is a record for HR, not a block: the test carries on.
 */
class PolicyAttemptEvent extends Model
{
    use HasFactory;

    const TYPE_TAB = 'tab';
    const TYPE_WINDOW = 'window';

    const TYPE_LABELS = [
        self::TYPE_TAB => 'Switched tab or minimised the browser',
        self::TYPE_WINDOW => 'Clicked outside the test window',
    ];

    protected $fillable = [
        'policy_attempt_id',
        'type',
        'question_no',
        'left_at',
        'returned_at',
        'seconds',
    ];

    protected $casts = [
        'left_at' => 'datetime',
        'returned_at' => 'datetime',
        'question_no' => 'integer',
        'seconds' => 'integer',
    ];

    public function attempt(){
        return $this->belongsTo(PolicyAttempt::class, 'policy_attempt_id');
    }

    public function getTypeLabelAttribute(){
        return (isset(self::TYPE_LABELS[$this->type]) ? self::TYPE_LABELS[$this->type] : self::TYPE_LABELS[self::TYPE_WINDOW]);
    }
}
