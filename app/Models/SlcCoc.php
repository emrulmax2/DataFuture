<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlcCoc extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'slc_cocs';

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'course_creation_instance_id',
        'slc_registration_id',
        'slc_attendance_id',
        'confirmation_date',
        'coc_type',
        'reason',
        'actioned',
        
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function setConfirmationDateAttribute($value) {  
        $this->attributes['confirmation_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getConfirmationDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(){
        return $this->hasMany(SlcCocDocument::class, 'slc_coc_id');
    }
}
