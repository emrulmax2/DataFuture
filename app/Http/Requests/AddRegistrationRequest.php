<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddRegistrationRequest extends FormRequest
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
            'confirmation_date' => 'required',
            'academic_year_id' => 'required',
            'registration_year' => 'required',
            //'course_creation_instance_id' => 'required',
            'instance_fees' => 'required',
            'slc_registration_status_id' => 'required',
            
            'confirm_attendance' => 'sometimes',
            'term_declaration_id' => 'required_if:confirm_attendance,1',
            'session_term' => 'required_if:confirm_attendance,1',

            'attendance_code_id' => 'required_if:confirm_attendance,1',
            'installment_amount' => 'required_if:attendance_code_id,1',
        ];
    }

    public function messages()
    {
        return [
            'confirmation_date.required' => 'This field is required.',
            'academic_year_id.required' => 'This field is required.',
            'registration_year.required' => 'This field is required.',
            //'course_creation_instance_id.required' => 'This field is required.',
            'instance_fees.required' => 'This field is required.',
            'slc_registration_status_id.required' => 'This field is required.',
            
            'term_declaration_id.required_if' => 'This field is required.',
            'session_term.required_if' => 'This field is required.',

            'attendance_code_id.required' => 'This field is required.',
            'installment_amount.required_if' => 'This field is required.',
        ];
    }
}
