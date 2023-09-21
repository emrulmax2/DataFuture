<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentPersonalDetailsRequest extends FormRequest
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
            'gender' => 'required',
            'nationality_id' => 'required',
            'country_id' => 'required',
            'ethnicity_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'title_id.reuired' => 'The Title field is required.',
            'first_name.reuired' => 'The First Name(s) field is required.',
            'last_name.reuired' => 'The Last Name field is required.',
            'date_of_birth.reuired' => 'The DOB field is required.',
            'gender.reuired' => 'The Gender field is required.',
            'nationality_id.required' => 'The Nationality field is required.',
            'country_id.required' => 'The Country of Birth field is required.',
            'ethnicity_id.required' => 'The Ethnicity field is required.',
            
            //'disability_id.required_if' => 'You have to select at least one Disability while Disability Status is turned on.',
        ];
    }
}
