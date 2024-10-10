<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentNoteFollowupComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_note_id',
        'comment',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function note(){
        return $this->belongsTo(StudentNote::class, 'student_note_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reader(){
        return $this->hasMany(StudentNoteFollowupCommentRead::class, 'student_note_followup_comment_id', 'id');
    }

    public function getReaderHtmlAttribute(){
        $html = '';
        $reads = StudentNoteFollowupCommentRead::where('student_note_followup_comment_id', $this->id)->where('read', 1)->orderBy('readed_at', 'ASC')->get();
        if($reads->count() > 0):
            $i = 1;
            $html .= '<div class="flex seenWrap absolute t-0 r-0 -mt-3">';
            foreach($reads as $red):
                $html .= '<div class="w-6 h-6 image-fit zoom-in '.($i > 1 ? '-ml-5' : '').'">';
                    $html .= '<img alt="'.(isset($red->user->employee->full_name) && !empty($red->user->employee->full_name) ? $red->user->employee->full_name : $red->user->name).' at '.date('jS F, Y H:i', strtotime($red->readed_at)).'" class="tooltip rounded-full" src="'.(isset($red->user->employee->photo_url) && !empty($red->user->employee->photo_url) ? $red->user->employee->photo_url : asset('build/assets/images/avater.png')).'">';
                $html .= '</div>';

                $i++;
            endforeach;
            $html .= '</div>';
        endif;
        return $html;
    }

}
