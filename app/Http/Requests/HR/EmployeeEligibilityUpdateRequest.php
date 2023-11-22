<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeEligibilityUpdateRequest extends FormRequest
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
            'workpermit_type' => 'required_if:eligible_to_work_status,==,"Yes"',
            'workpermit_number' => 'required_if:workpermit_type,2,3,4,5',
            'workpermit_expire' => 'required_if:workpermit_type,2,3,4,5',
            'document_type' => "required",
            'doc_number' => "required",
            'doc_expire' => "required",
            'doc_issue_country' => "required",
        ];
    }
}
