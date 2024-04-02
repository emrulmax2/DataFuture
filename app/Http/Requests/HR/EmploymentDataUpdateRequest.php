<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class EmploymentDataUpdateRequest extends FormRequest
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
            'punch_number' => "required",
            'site_location' => "required",
            'employee_work_type_id' => "required",
            'employee_job_title_id' => "required",
            'department_id' => "required",
            'works_number' => 'required_if:employee_work_type_id,==,3|nullable|integer',
            
        ];
    }
}
