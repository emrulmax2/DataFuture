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
            'highest_academic' => 'required',
            'result' => 'required',
            'qualification_grade_id' => 'required',
            //'result' => 'required',
            'degree_award_date' => 'required',
            'previous_provider_id' => 'required',
            'qualification_type_identifier_id' => 'required',
            'hesa_qualification_subject_id' => 'required',
            'highest_qualification_on_entry_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'highest_academic.required' => 'This field is required.',
            'result.required' => 'This field is required.',
            'subjects.required' => 'This field is required.',
            //'result' => 'required',
            'qualification_grade_id.required' => 'This field is required.',
            'degree_award_date.required' => 'This field is required.',
            'previous_provider_id.required' => 'This field is required.',
            'qualification_type_identifier_id.required' => 'This field is required.',
            'hesa_qualification_subject_id.required' => 'This field is required.',
            'highest_qualification_on_entry_id.required' => 'This field is required.'
        ];
    }
}
