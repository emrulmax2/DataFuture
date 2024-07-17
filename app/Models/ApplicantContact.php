<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['full_address'];

    protected $fillable = [
        'applicant_id',
        'country_id',
        'permanent_country_id',
        'home',
        'mobile',
        'mobile_verification',
        'address_line_1',
        'address_line_2',
        'state',
        'post_code',
        'permanent_post_code',
        'city',
        'country',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function applicant(){
        return $this->belongsTo(Applicant::class, 'applicant_id', 'id');
    }
    public function getFullAddressAttribute(){
        $html = '';
        $html .= (isset($this->address_line_1) && !empty($this->address_line_1) ? $this->address_line_1.', ' : '');
        $html .= (isset($this->address_line_2) && !empty($this->address_line_2) ? '<br/>'.$this->address_line_2.', <br/>' : '<br/>');
        $html .= (isset($this->city) && !empty($this->city) ? $this->city.', ' : '');
        $html .= (isset($this->post_code) && !empty($this->post_code) ? $this->post_code.', <br/>' : '<br/>');
        $html .= (isset($this->country) && !empty($this->country) ? $this->country : '');
        return $html;
    }
}
