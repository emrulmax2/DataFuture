<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentHesaInstanceStoreRequest extends FormRequest
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
            'semester_id' => 'required',
            'course_creation_instance_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'semester_id.required' => 'This field is required.',
            'course_creation_instance_id.required' => 'Please check instance first.',
        ];
    }
}
