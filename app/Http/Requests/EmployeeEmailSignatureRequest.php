<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeEmailSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Everything is optional: a blank field means "fall back to my HR record",
     * which is exactly how EmployeeEmailSignature::fieldsFor() reads it.
     */
    public function rules(): array
    {
        return [
            'display_name' => 'nullable|string|max:191',
            'qualifications' => 'nullable|string|max:191',
            'job_title' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'extension' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:60',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Please enter a valid email address for your signature.',
        ];
    }
}
