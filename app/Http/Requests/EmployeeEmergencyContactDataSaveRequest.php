<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeEmergencyContactDataSaveRequest extends FormRequest
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
            'relationship' => "required",
            'emergency_contact_address_line_1' => "required",
            'emergency_contact_post_code' => "required",
            'emergency_contact_state' => "required",
            'emergency_contact_city' => "required",
            'emergency_contact_country' => "required",
            'emergency_contact_mobile' => "required",
        ];
    }
}
