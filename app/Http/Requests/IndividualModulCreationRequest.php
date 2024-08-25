<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndividualModulCreationRequest extends FormRequest
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
            'course_module_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'course_module_id.required' => 'This field is required.',
        ];
    }
}
