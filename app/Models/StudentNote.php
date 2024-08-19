<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'term_declaration_id',
        'opening_date',
        'note',
        'phase',
        'followed_up',
        'follow_up_start',
        'follow_up_end',
        'follow_up_by',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function term() {
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }
    
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setOpeningDateAttribute($value) {  
        $this->attributes['opening_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getOpeningDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setFollowUpStartAttribute($value) {  
        $this->attributes['follow_up_start'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getFollowUpStartAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setFollowUpEndAttribute($value) {  
        $this->attributes['follow_up_end'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getFollowUpEndAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function followed(){
        return $this->belongsTo(User::class, 'follow_up_by');
    }

    public function document(){
        return $this->hasOne(StudentNotesDocument::class, 'student_note_id', 'id')->latestOfMany();
    }
}
