<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeEmergencyContactUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [

            'emergency_contact_name' => "required",
            'kins_relation_id' => "required",
            'address_line_1' => "required",
            'post_code' => "required",
            'state' => "required",
            'city' => "required",
            'country' => "required",
            'emergency_contact_mobile' => "required",

        ];
    }
}
