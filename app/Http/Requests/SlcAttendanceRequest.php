<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlcAttendanceRequest extends FormRequest
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
            'attendance_year' => 'required',
            'term_declaration_id' => 'required',
            'session_term' => 'required',

            'attendance_code_id' => 'required',
            'installment_amount' => 'required_if:attendance_code_id,1',
        ];
    }


    public function messages()
    {
        return [
            'confirmation_date.required' => 'This field is required.',
            'attendance_year.required' => 'This field is required.',
            'term_declaration_id.required' => 'This field is required.',
            'session_term.required' => 'This field is required.',
            'attendance_code_id.required' => 'This field is required.',
            'installment_amount.required_if' => 'This field is required.',
        ];
    }
}
