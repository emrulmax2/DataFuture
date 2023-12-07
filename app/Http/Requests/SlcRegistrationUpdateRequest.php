<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlcRegistrationUpdateRequest extends FormRequest
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
            'ssn' => 'required',
            'confirmation_date' => 'required',
            'academic_year_id' => 'required',
            'registration_year' => 'required',
            'course_creation_instance_id' => 'required',
            'slc_registration_status_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'ssn.required' => 'This field is required.',
            'confirmation_date.required' => 'This field is required.',
            'academic_year_id.required' => 'This field is required.',
            'registration_year.required' => 'This field is required.',
            'course_creation_instance_id.required' => 'This field is required.',
            'slc_registration_status_id.required' => 'This field is required.'
        ];
    }
}
