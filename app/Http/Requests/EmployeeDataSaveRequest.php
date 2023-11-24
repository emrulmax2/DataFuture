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
            'address_line_1' => "required",
            'city' => "required",
            'state' => "required",
            'post_code' => "required",
            'country' => "required",
        ];
    }
}
