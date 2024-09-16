<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReAssignClassRequest extends FormRequest
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
            'proxy_tutor_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'proxy_tutor_id.required' => 'This field is required'
        ];
    }
}
