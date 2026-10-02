<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One choice for a policy question. Exactly one option per question is
 * correct; is_correct is HR-only and never reaches a staff-facing page.
 */
class PolicyQuestionOption extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'policy_question_id',
        'option_text',
        'is_correct',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function question(){
        return $this->belongsTo(PolicyQuestion::class, 'policy_question_id');
    }
}
