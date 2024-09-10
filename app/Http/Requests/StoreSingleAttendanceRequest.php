<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSingleAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required',
            'attendance_feed_status_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'student_id.required' => 'Student can not be empty.',
            'attendance_feed_status_id.required' => 'Please check attendance status.'
        ];
    }
}
