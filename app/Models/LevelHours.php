<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelHours extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hours',
        'workplacement_details_id',
        'active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function learning_hours()
    {
        return $this->hasMany(LearningHours::class, 'level_hours_id');
    }

    public function studentWorkPlacements()
    {
        return $this->hasMany(StudentWorkPlacement::class, 'level_hours_id');
    }
}
