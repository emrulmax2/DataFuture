<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanTaskUpload extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [ "id" ];

    public function updatedBy(): BelongsTo 
    {
        return $this->belongsTo(User::class,'updated_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(PlanTask::class,'plan_task_id');
    }
}
