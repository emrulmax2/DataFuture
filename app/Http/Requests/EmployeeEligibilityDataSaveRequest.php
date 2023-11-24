<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeEligibilityDataSaveRequest extends FormRequest
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
            'workpermit_number' => 'required_if:workpermit_type,>=,3',
            'workpermit_expire' => 'required_if:workpermit_type,>=,3',
            'document_type' => "required",
            'doc_number' => "required",
            'doc_expire' => "required",
            'doc_issue_country' => "required",
        ];
    }
}
