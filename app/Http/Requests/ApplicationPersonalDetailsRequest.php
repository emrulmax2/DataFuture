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
            'applicant_address' => 'required',

            'name' => 'required',
            'kins_relation_id' => 'required',
            'kins_mobile' => 'required',
            'kin_address' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'title_id.reuired' => 'The Title field is required.',
            'first_name.reuired' => 'The First Name(s) field is required.',
            'last_name.reuired' => 'The Last Name field is required.',
            'date_of_birth.reuired' => 'The DOB field is required.',
            'sex_identifier_id.reuired' => 'The Sex Id field is required.',
            'nationality_id.required' => 'The Nationality field is required.',
            'country_id.required' => 'The Country of Birth field is required.',
            'ethnicity_id.required' => 'The Ethnicity field is required.',
            //'phone.required' => 'The Home Phone field is required.',
            'mobile.required' => 'The Mobile Phone field is required.',
            'applicant_address.required' => 'The Applicant Address is required.',
            'name.required' => 'The Kin Name field is required.',
            'kins_relation_id.required' => 'The Kins Relation field is required.',
            'kins_mobile.required' => 'The Kins Mobile field is required.',
            'kin_address.required' => 'The Kins Address is required.',
            'disability_id.required_if' => 'You have to select at least one Disability while Disability Status is turned on.',
        ];
    }
}
