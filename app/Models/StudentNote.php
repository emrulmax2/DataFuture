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
        'followed_up_status',
        'followup_completed_by',
        'followup_completed_at',
        'is_flaged',
        'student_flag_id',
        'flaged_status',
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

    public function student() {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function term() {
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }
    
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function completed(){
        return $this->belongsTo(User::class, 'followup_completed_by');
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

    public function follows(){
        return $this->hasMany(StudentNoteFollowedBy::class, 'student_note_id', 'id');
    }

    public function flag(){
        return $this->belongsTo(StudentFlag::class, 'student_flag_id');
    }

    public function getFollowedTagAttribute(){
        $followedBy = StudentNoteFollowedBy::where('student_note_id', $this->id)->get();
        $html = '';
        if(!empty($followedBy)):
            foreach($followedBy as $follow):
                $html .= '<span class="bg-slate-200 text-xs text-primary font-medium inline-flex px-2 py-1 mr-1 mb-1 whitespace-nowrap">'.(isset($follow->user->employee->full_name) && !empty($follow->user->employee->full_name) ? $follow->user->employee->full_name : '').'</span>';
            endforeach;
        endif;

        return $html;
    }
}
