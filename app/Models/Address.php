<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $appends = ['full_address'];

    protected $fillable = [
        'address_line_1',
        'address_line_2',
        'state',
        'post_code',
        'city',
        'country',
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

    public function getFullAddressAttribute(){
        $html = '';
        $html .= (isset($this->address_line_1) && !empty($this->address_line_1) ? $this->address_line_1.', ' : '');
        $html .= (isset($this->address_line_2) && !empty($this->address_line_2) ? '<br/>'.$this->address_line_2.', <br/>' : '<br/>');
        $html .= (isset($this->city) && !empty($this->city) ? $this->city.', ' : '');
        $html .= (isset($this->post_code) && !empty($this->post_code) ? $this->post_code.', <br/>' : '<br/>');
        $html .= (isset($this->country) && !empty($this->country) ? $this->country : '');
        return $html;
    }

    public function getFullAddressPdfAttribute(){
        $html = '';
        $html .= (isset($this->address_line_1) && !empty($this->address_line_1) ? $this->address_line_1.', ' : '');
        $html .= (isset($this->address_line_2) && !empty($this->address_line_2) ? ''.$this->address_line_2.', ' : '');
        $html .= (isset($this->city) && !empty($this->city) ? $this->city.', ' : '');
        $html .= (isset($this->post_code) && !empty($this->post_code) ? $this->post_code.', ' : '');
        $html .= (isset($this->country) && !empty($this->country) ? $this->country : '');
        return $html;
    }

    public function getFullAddressInputAttribute(){
        $html = '';
        $html .= '<span class="text-slate-600 font-medium">'.$this->address_line_1.', </span><br/>';
        $html .= '<input type="hidden" name="emp_address_line_1" value="'.$this->address_line_1.'"/>';
        if(isset($this->address_line_2) && !empty($this->address_line_2)){
            $html .= '<span class="text-slate-600 font-medium">'.$this->address_line_2.', </span><br/>';
        }
        $html .= '<input type="hidden" name="emp_address_line_2" value="'.(isset($this->address_line_2) && !empty($this->address_line_2) ? $this->address_line_2 : '').'"/>';

        $html .= '<span class="text-slate-600 font-medium">'.(isset($this->city) && !empty($this->city) ? $this->city.', ' : '').'</span>';
        $html .= '<input type="hidden" name="emp_city" value="'.(isset($this->city) && !empty($this->city) ? $this->city : '').'"/>';

        $html .= '<span class="text-slate-600 font-medium">'.(isset($this->post_code) && !empty($this->post_code) ? $this->post_code.', ' : '').'</span><br/>';
        $html .= '<input type="hidden" name="emp_post_code" value="'.(isset($this->post_code) && !empty($this->post_code) ? $this->post_code : '').'"/>';

        $html .= '<span class="text-slate-600 font-medium">'.(isset($this->country) && !empty($this->country) ? $this->country : '').'</span><br/>';
        $html .= '<input type="hidden" name="emp_country" value="'.(isset($this->country) && !empty($this->country) ? $this->country : '').'"/>';
        $html .= '<input type="hidden" name="emp_address_id" value="'.$this->id.'"/>';
        return $html;
    }
}
