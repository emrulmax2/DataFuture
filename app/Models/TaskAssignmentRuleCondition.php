<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAssignmentRuleCondition extends Model
{
    protected $fillable = ['rule_id', 'field', 'operator', 'value', 'sort_order'];

    protected $casts = [
        'value' => 'array',
        'sort_order' => 'integer',
    ];

    public function rule()
    {
        return $this->belongsTo(TaskAssignmentRule::class, 'rule_id');
    }
}
