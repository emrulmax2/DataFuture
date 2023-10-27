<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeDataUpdateRequest extends FormRequest
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
            'title_id' => "required",
            'first_name' => "required",
            'last_name' => "required",
            'sex_identifier_id' => "required",
            'date_of_birth' => "required",
            'ni_number' => "required",
            'nationality_id' => "required",
            'ethnicity_id' => "required",
            "user_id" => "required",
            "mobile" => "required",
        ];
    }
}
