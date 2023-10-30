<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpoyeePenssionSchemeRequest extends FormRequest
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
            'employee_info_penssion_scheme_id' => 'required',
            'joining_date' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'employee_info_penssion_scheme_id.required' => 'This field is required.',
            'joining_date.required' => 'This field is required.',
        ];
    }
}
