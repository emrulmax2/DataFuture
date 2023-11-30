<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeDataSaveRequest extends FormRequest
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
            'title' => "required",
            'first_name' => "required",
            'last_name' => "required",
            'mobile' => "required",
            'email' => "required",
            'sex' => "required",
            'date_of_birth' => "required",
            //'ni_number' => "required",
            'nationality' => "required",
            'ethnicity' => "required",
            'emp_address_line_1' => "required",
        ];
    }

    public function messages()
    {
        return [
            'title.required' => "This field is required.",
            'first_name.required' => "This field is required.",
            'last_name.required' => "This field is required.",
            'mobile.required' => "This field is required.",
            'email.required' => "This field is required.",
            'sex.required' => "This field is required.",
            'date_of_birth.required' => "This field is required.",
            //'ni_number' => "required",
            'nationality.required' => "This field is required.",
            'ethnicity.required' => "This field is required.",
            'emp_address_line_1.required' => "Address can not be empty.",
        ];
    }
}
