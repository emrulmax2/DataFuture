<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatereportItStudentRequest extends FormRequest
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
            
            'issue_type_id' => 'required',
            'employee_id' => 'nullable|exists:employees,id',
            'student_id' => 'nullable|exists:students,id',
            'venue_id' => 'required|exists:venues,id',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
        ];
    }
}
