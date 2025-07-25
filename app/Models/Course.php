<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SourceTuitionFee;
use App\Models\AwardingBody;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'degree_offered',
        'pre_qualification',
        'awarding_body_id',
        'source_tuition_fee_id',
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

    public function fee(){
        return $this->belongsTo(SourceTuitionFee::class, 'source_tuition_fee_id');
    }

    public function body(){
        return $this->belongsTo(AwardingBody::class, 'awarding_body_id');
    }

    public function cr_creation(){
        return $this->hasMany(CourseCreation::class, 'course_id', 'id');
    }

    public function modules(){
        return $this->hasMany(CourseModule::class);
    }

    public function groups(){
        return $this->hasMany(Group::class);
    }

    public function team(){
        return $this->hasOne(TutorMonitorTeam::class, 'course_id', 'id')->latestOfMany();
    }

    public function df(){
        return $this->hasMany(CourseBaseDatafutures::class, 'course_id', 'id');
    }

    public function dfQual(){
        return $this->hasMany(CourseBaseDatafutures::class, 'course_id', 'id')->whereHas('field', function($q){
                            $q->where('datafuture_field_category_id', 2);
                        })->where('course_id', $this->id);
    }
}
