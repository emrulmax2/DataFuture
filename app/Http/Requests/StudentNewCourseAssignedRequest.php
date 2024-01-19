<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentNewCourseAssignedRequest extends FormRequest
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
            'academic_year_id' => 'required',
            'semester_id' => 'required',
            'course_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'academic_year_id.required' => 'This field is required.',
            'semester_id.required' => 'This field is required.',
            'course_id.required' => 'This field is required.'
        ];
    }
}
