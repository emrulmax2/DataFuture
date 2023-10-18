<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentEmploymentRequest extends FormRequest
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
            'company_name' => 'required',
            'company_phone' => 'required',
            'position' => 'required',
            'start_date' => 'required',
            'contact_name' => 'required',
            'contact_position' => 'required',
            'contact_phone' => 'required',

            'continuing' => 'sometimes',
            'end_date' => 'required_if:continuing,0',
            'address_id' => 'required|numeric|gt:0'
        ];
    }

    public function messages()
    {
        return [
            'company_name.required' => 'This field is required',
            'company_phone.required' => 'This field is required',
            'position.required' => 'This field is required',
            'start_date.required' => 'This field is required',
            'contact_name.required' => 'This field is required',
            'contact_position.required' => 'This field is required',
            'contact_phone.required' => 'This field is required',
            'end_date.required_if' => 'This field is required',

            'address_id.required' => 'Company address is required.',
            'address_id.numeric' => 'Company address is required.',
            'gt.numeric' => 'Company address is required.',
        ];
    }
}
