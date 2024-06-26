<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentType extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [ "id" ];

    public function updatedBy(): HasOne 
    {
        return $this->hasOne(User::class);
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
