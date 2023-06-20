<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdmissionCourseDetailsRequest extends FormRequest
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
            'course_creation_id' => 'required',
            'student_loan' => 'required',
            'other_funding' => 'required_if:student_loan,Other',
        ];
    }

    public function messages()
    {
        return [
            'course_creation_id.reuired' => 'The Course field is required.',
            'student_loan.reuired' => 'The Student Loan field is required.',
            'other_funding.required_if' => 'The Other Funding field is required.'
        ];
    }
}
