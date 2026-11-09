<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationPersonalDetailsRequest extends FormRequest
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
            'title_id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'date_of_birth' => 'required|date',
            'sex_identifier_id' => 'required',
            'nationality_id' => 'required',
            'country_id' => 'required',
            'ethnicity_id' => 'required',
            'disability_status' => 'sometimes',
            'disability_id' => 'required_if:disability_status,1',
            
            'mobile' => 'required',

            /* The address is captured inline now, a field at a time, so it is
               validated the same way: one message against the field that is
               missing rather than a single "Address is required" for the set. */
            'applicant_address_line_1' => 'required',
            'applicant_address_city' => 'required',
            'applicant_address_postal_zip_code' => 'required',
            'applicant_address_country' => 'required',

            'name' => 'required',
            'kins_relation_id' => 'required',
            'kins_mobile' => 'required',

            'kin_address_line_1' => 'required',
            'kin_address_city' => 'required',
            'kin_address_postal_zip_code' => 'required',
            'kin_address_country' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'title_id.required' => 'The Title field is required.',
            'first_name.required' => 'The First Name(s) field is required.',
            'last_name.required' => 'The Last Name field is required.',
            'date_of_birth.required' => 'The DOB field is required.',
            'sex_identifier_id.required' => 'The Sex Id field is required.',
            'nationality_id.required' => 'The Nationality field is required.',
            'country_id.required' => 'The Country of Birth field is required.',
            'ethnicity_id.required' => 'The Ethnicity field is required.',
            //'phone.required' => 'The Home Phone field is required.',
            'mobile.required' => 'The Mobile Phone field is required.',
            'applicant_address_line_1.required' => 'Enter the first line of your address.',
            'applicant_address_city.required' => 'Enter your city or town.',
            'applicant_address_postal_zip_code.required' => 'Enter your post code.',
            'applicant_address_country.required' => 'Enter your country.',
            'name.required' => 'This field is required.',
            'kins_relation_id.required' => 'This field is required.',
            'kins_mobile.required' => 'This field is required.',
            'kin_address_line_1.required' => 'Enter the first line of their address.',
            'kin_address_city.required' => 'Enter their city or town.',
            'kin_address_postal_zip_code.required' => 'Enter their post code.',
            'kin_address_country.required' => 'Enter their country.',
            'disability_id.required_if' => 'You have to select at least one Disability while Disability Status is turned on.',
        ];
    }
}
