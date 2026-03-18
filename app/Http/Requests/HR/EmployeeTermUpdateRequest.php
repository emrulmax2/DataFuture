<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeTermUpdateRequest extends FormRequest
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
            'employee_notice_period_id' => "required",
            'employment_period_id' => "required",
            'employment_ssp_term_id' => "required",
            'provision_end' => "required_if:employment_period_id,3",
        ];
    }

    public function messages()
    {
        return [
            'employee_notice_period_id.required' => "This field is required.",
            'employment_period_id.required' => "This field is required.",
            'employment_ssp_term_id.required' => "This field is required.",
            'provision_end.required_if' => "This field is required.",
        ];
    }
}
