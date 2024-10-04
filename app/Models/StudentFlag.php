<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentFlag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'color',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function raiser() {
        return $this->hasMany(StudentFlagRaiser::class, 'student_flag_id', 'id');
    }

    public function getRaiserTagAttribute(){
        $raisers = StudentFlagRaiser::where('student_flag_id', $this->id)->get();
        $html = '';
        if(!empty($raisers)):
            foreach($raisers as $aiser):
                $html .= '<span class="bg-slate-200 text-xs text-primary font-medium inline-flex px-2 py-1 mr-1 mb-1 whitespace-nowrap">'.(isset($aiser->user->employee->full_name) && !empty($aiser->user->employee->full_name) ? $aiser->user->employee->full_name : '').'</span>';
            endforeach;
        endif;

        return $html;
    }
}
