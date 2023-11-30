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
            'emc_address_line_1' => "required",
            'emergency_contact_mobile' => "required",
        ];
    }

    public function messages()
    {
        return [
            'emergency_contact_name.required' => "This field is required.",
            'relationship.required' => "This field is required.",
            'emc_address_line_1.required' => "Address can not be empty.",
            'emergency_contact_mobile.required' => "This field is required.",
        ];
    }
}
