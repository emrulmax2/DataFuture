<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayslipSyncUploadUpdateRequest extends FormRequest
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

    public function rules()
    {
        $rules = [];

        // Loop through the inputs to add conditional rules
        foreach ($this->input('id', []) as $index => $value) {
            if ($this->has("id.$index")) {
                
                $rules["id.$index"] = 'required|integer';
                $rules["employee_id.$index"] = 'required|integer|exists:employees,id';

            }
        }
        return $rules;
        
    }

    public function messages()
    {
        return [
            'id.*.required' => 'The ID is required. Please refresh the page and try again.',
            'employee_id.*.required' => 'Employee Selection of this position is required.',
        ];
    }
}
