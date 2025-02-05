<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentQualificationRequest extends FormRequest
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
            'previous_provider_id' => 'required',

            'highest_qualification_on_entry_id' => 'required',
            
            'highest_academic' => 'required_unless:highest_qualification_on_entry_id,58',
            'qualification_grade_id' => 'required_unless:highest_qualification_on_entry_id,58',
            'degree_award_date' => 'required_unless:highest_qualification_on_entry_id,58',
            'qualification_type_identifier_id' => 'required_unless:highest_qualification_on_entry_id,58',
            'hesa_qualification_subject_id' => 'required_unless:highest_qualification_on_entry_id,58',
        ];
    }

    public function messages()
    {
        return [
            'previous_provider_id.required' => 'This field is required.',

            'highest_qualification_on_entry_id.required' => 'This field is required.',

            'qualification_type_identifier_id.required_unless' => 'This field is required.',
            'hesa_qualification_subject_id.required_unless' => 'This field is required.',
            'highest_academic.required_unless' => 'This field is required.',
            'qualification_grade_id.required_unless' => 'This field is required.',
            'degree_award_date.required_unless' => 'This field is required.',
        ];
    }
}
