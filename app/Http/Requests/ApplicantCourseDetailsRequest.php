<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ApplicantCourseDetailsRequest extends FormRequest
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
    public function withValidator(Validator $validator)
    {
        $refferelcode = $validator->getData()['referral_code'] ?? '';
        $validator->after(
            function ($validator) use ($refferelcode) {
          
                if (auth('agent')->user() && $refferelcode=="") {
                    $validator->errors()->add(
                        'referral_code',
                        'Refferal code required'
                    );
                }
            }
        );                            
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
            'employment_status' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'course_creation_id.reuired' => 'The Course field is required.',
            'student_loan.reuired' => 'The Student Loan field is required.',
            'other_funding.required_if' => 'The Other Funding field is required.',
            'employment_status.reuired' => 'The Employment Status field is required.',
        ];
    }
}
