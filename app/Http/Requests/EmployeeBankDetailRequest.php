<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeBankDetailRequest extends FormRequest
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
            'beneficiary' => 'required',
            'sort_code' => 'required',
            'ac_no' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'beneficiary.required' => 'This field is required',
            'sort_code.required' => 'This field is required',
            'ac_no.required' => 'This field is required',
        ];
    }
}
