<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternalLink extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'parent_id',
        'image',
        'link',
        'created_by',
        'updated_by',
        'description',
        'start_date',
        'end_date',
        'available_staff',
        'available_student',
        'active'
    ];
    
    protected $dates = ['deleted_at']; 

    public function children(){
        return $this->hasMany(InternalLink::class, 'parent_id', 'id');
    }

    public function parent(){
        return $this->belongsTo(InternalLink::class, 'parent_id');
    }

    public function setStartDateAttribute($value) {  
        $this->attributes['start_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getStartDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    public function setEndDateAttribute($value) {  
        $this->attributes['end_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getEndDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
