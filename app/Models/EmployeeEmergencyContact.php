<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'emergency_contact_name',
        'kins_relation_id',
        'emergency_contact_address',
        'emergency_contact_telephone',
        'emergency_contact_mobile',
        'emergency_contact_email',
        'address_id'
    ];

    public function kin() {
        return $this->belongsTo(KinsRelation::class, 'kins_relation_id');
    }

    public function address() {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function getAddressInputAttribute(){
        $html = '';
        if($this->address_id > 0):
            $address = Address::where('id', $this->address_id)->get()->first();
            $html .= '<span class="text-slate-600 font-medium">'.$address->address_line_1.'</span><br/>';
            $html .= '<input type="hidden" name="emc_address_line_1" value="'.$address->address_line_1.'"/>';
            if(isset($address->address_line_2) && !empty($address->address_line_2)){
                $html .= '<span class="text-slate-600 font-medium">'.isset($address->address_line_2) && !empty($address->address_line_2).'</span><br/>';
            }
            $html .= '<input type="hidden" name="emc_address_line_2" value="'.(isset($address->address_line_2) && !empty($address->address_line_2) ? $address->address_line_2 : '').'"/>';

            $html .= '<span class="text-slate-600 font-medium">'.(isset($address->city) && !empty($address->city) ? $address->city.', ' : '').'</span>';
            $html .= '<input type="hidden" name="emc_city" value="'.(isset($address->city) && !empty($address->city) ? $address->city : '').'"/>';

            $html .= '<span class="text-slate-600 font-medium">'.(isset($address->post_code) && !empty($address->post_code) ? $address->post_code.', ' : '').'</span><br/>';
            $html .= '<input type="hidden" name="emc_post_code" value="'.(isset($address->post_code) && !empty($address->post_code) ? $address->post_code : '').'"/>';

            $html .= '<span class="text-slate-600 font-medium">'.(isset($address->country) && !empty($address->country) ? $address->country : '').'</span><br/>';
            $html .= '<input type="hidden" name="emc_country" value="'.(isset($address->country) && !empty($address->country) ? $address->country : '').'"/>';
            $html .= '<input type="hidden" name="emc_address_id" value="'.$address->id.'"/>';
        endif;
        return $html;
    }
}
