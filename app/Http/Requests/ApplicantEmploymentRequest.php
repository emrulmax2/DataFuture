<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicantEmploymentRequest extends FormRequest
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

            /* The address used to arrive as one `employment_address` value set
               by the Add Address popup. It is an inline block now, so each
               part is validated on its own and the message lands on the field
               it belongs to. */
            'employment_address_line_1' => 'required',
            'employment_address_city' => 'required',
            'employment_address_postal_zip_code' => 'required',
            'employment_address_country' => 'required',

            'contact_name' => 'required',
            'contact_position' => 'required',
            'contact_phone' => 'required',
            'contact_email' => 'nullable|email',

            'continuing' => 'sometimes',
            'end_date' => 'required_if:continuing,0',
        ];
    }

    public function messages()
    {
        return [
            'company_name.required' => 'This field is required.',
            'company_phone.required' => 'This field is required.',
            'position.required' => 'This field is required.',
            'start_date.required' => 'This field is required.',
            'employment_address_line_1.required' => 'This field is required.',
            'employment_address_city.required' => 'This field is required.',
            'employment_address_postal_zip_code.required' => 'This field is required.',
            'employment_address_country.required' => 'This field is required.',
            'contact_name.required' => 'This field is required.',
            'contact_position.required' => 'This field is required.',
            'contact_phone.required' => 'This field is required.',
            'contact_email.email' => 'Enter a valid email address.',
            'end_date.required_if' => 'Enter an end date, or tick Continuing.',
        ];
    }
}
