<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One question as it was drawn into an attempt. The question text, the
 * shuffled option order, the options themselves and the correct answer are
 * all copied at draw time, so HR can review the attempt exactly as the member
 * of staff saw it even after the bank is edited.
 *
 * correct_option_id / correct_option_text / is_correct are HR-only and must
 * never be sent to a staff-facing page.
 */
class PolicyAttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_attempt_id',
        'policy_question_id',
        'sort_order',
        'question_text',
        'question_level',
        'option_order',
        'options_snapshot',
        'correct_option_id',
        'correct_option_text',
        'selected_option_id',
        'selected_option_text',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'option_order' => 'array',
        'options_snapshot' => 'array',
        'sort_order' => 'integer',
    ];

    public function attempt(){
        return $this->belongsTo(PolicyAttempt::class, 'policy_attempt_id');
    }

    public function question(){
        return $this->belongsTo(PolicyQuestion::class, 'policy_question_id')->withTrashed();
    }
}
