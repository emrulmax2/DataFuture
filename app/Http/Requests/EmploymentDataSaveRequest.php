<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmploymentDataSaveRequest extends FormRequest
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
            'started_on' => "required|date_format:d-m-Y",
            'punch_number' => "required|integer",
            'site_location' => "required",
            'employee_work_type' => "required",
            'job_title' => "required",
            'department' => "required",
            'email' => "required|unique:users,email",
            'works_number' => 'required_if:employee_work_type,==,3|nullable|integer',
            'notice_period' => "required",
            'ssp_term' => "required",
            'employment_period' => "required",
        ];
    }
}
