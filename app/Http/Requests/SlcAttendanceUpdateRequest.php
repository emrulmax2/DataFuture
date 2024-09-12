<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlcAttendanceUpdateRequest extends FormRequest
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
            //'attendance_year' => 'required',
            'term_declaration_id' => 'required',
            'session_term' => 'required',
            'attendance_code_id' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'confirmation_date.required' => 'This field is required.',
            //'attendance_year.required' => 'This field is required.',
            'term_declaration_id.required' => 'This field is required.',
            'session_term.required' => 'This field is required.',
            'attendance_code_id.required' => 'This field is required.',
        ];
    }
}
