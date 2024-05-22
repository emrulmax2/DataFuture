<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeSentEmailRequest extends FormRequest
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
            'to_email' => 'required',
            'mail_body' => 'required',
            'subject' => 'required',
        ];
    }

    public function messages(){
        return [
            'to_email.required' => 'This field is required.',
            'mail_body.required' => 'This field is required.',
            'subject.required' => 'This field is required.',
        ];
    }
}
