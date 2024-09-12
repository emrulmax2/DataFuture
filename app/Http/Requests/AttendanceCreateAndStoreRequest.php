<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCreateAndStoreRequest extends FormRequest
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
            'attendanceInfo_tutor_id' => 'required',
            'attendanceInfo_start_time' => 'required',
            'attendanceInfo_end_time' => 'required',
            'plans_date_list_status' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'attendanceInfo_tutor_id.required' => 'This field is required',
            'attendanceInfo_start_time.required' => 'This field is required',
            'attendanceInfo_end_time.required' => 'This field is required',
            'plans_date_list_status.required' => 'This field is required',
        ];
    }
}
