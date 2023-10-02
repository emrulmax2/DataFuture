<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
            'student_address_address_line_1' => 'required',
            'student_address_city' => 'required',
            'student_address_postal_zip_code' => 'required',
            'student_address_country' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'student_address_address_line_1.required' => 'This field is required.',
            'student_address_city.required' => 'This field is required.',
            'student_address_postal_zip_code.required' => 'This field is required.',
            'student_address_country.required' => 'This field is required.',
        ];
    }
}
