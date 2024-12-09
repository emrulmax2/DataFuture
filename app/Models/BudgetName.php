<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetName extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'active',

        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function holders(){
        return $this->hasMany(BudgetNameHolder::class, 'budget_name_id', 'id');
    }

    public function getHolderHtmlAttribute(){
        $html = '';
        if(isset($this->holders) && $this->holders->count() > 0):
            $u = 1;
            $html .= '<div class="flex userLoader" data-id="'.$this->id.'">';
            foreach($this->holders as $usr):
                if($u > 4): break; endif;
                $photo_url = (isset($usr->user->employee->photo_url) && !empty($usr->user->employee->photo_url) ? $usr->user->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg'));
                $html .= '<div class="w-10 h-10 image-fit zoom-in '.($u > 1 ? ' -ml-5' : '').'">';
                    $html .= '<img alt="'.(isset($usr->user->employee->full_name) ? $usr->user->employee->full_name : 'Unknown Employee').'" class="rounded-full tabltooltip" src="'.$photo_url.'">';
                $html .= '</div>';
                $u++;
            endforeach;
            $html .= '</div>';
        endif;

        return $html;
    }

    public function requesters(){
        return $this->hasMany(BudgetNameRequester::class, 'budget_name_id', 'id');
    }

    public function getRequesterHtmlAttribute(){
        $html = '';
        if(isset($this->requesters) && $this->requesters->count() > 0):
            $u = 1;
            $html .= '<div class="flex userLoader" data-id="'.$this->id.'">';
            foreach($this->requesters as $usr):
                if($u > 4): break; endif;
                $photo_url = (isset($usr->user->employee->photo_url) && !empty($usr->user->employee->photo_url) ? $usr->user->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg'));
                $html .= '<div class="w-10 h-10 image-fit zoom-in '.($u > 1 ? ' -ml-5' : '').'">';
                    $html .= '<img alt="'.(isset($usr->user->employee->full_name) ? $usr->user->employee->full_name : 'Unknown Employee').'" class="rounded-full tabltooltip" src="'.$photo_url.'">';
                $html .= '</div>';
                $u++;
            endforeach;
            $html .= '</div>';
        endif;

        return $html;
    }

    public function approvers(){
        return $this->hasMany(BudgetNameApprover::class, 'budget_name_id', 'id');
    }

    public function getApproversHtmlAttribute(){
        $html = '';
        if(isset($this->approvers) && $this->approvers->count() > 0):
            $u = 1;
            $html .= '<div class="flex userLoader" data-id="'.$this->id.'">';
            foreach($this->approvers as $usr):
                if($u > 4): break; endif;
                $photo_url = (isset($usr->user->employee->photo_url) && !empty($usr->user->employee->photo_url) ? $usr->user->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg'));
                $html .= '<div class="w-10 h-10 image-fit zoom-in '.($u > 1 ? ' -ml-5' : '').'">';
                    $html .= '<img alt="'.(isset($usr->user->employee->full_name) ? $usr->user->employee->full_name : 'Unknown Employee').'" class="rounded-full tabltooltip" src="'.$photo_url.'">';
                $html .= '</div>';
                $u++;
            endforeach;
            $html .= '</div>';
        endif;

        return $html;
    }
}
