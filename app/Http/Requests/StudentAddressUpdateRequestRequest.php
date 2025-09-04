<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentAddressUpdateRequestRequest extends FormRequest
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
            'validation_status' => 'sometimes',
            'address_line_1' => 'required_if:validation_status,NEW',
            'city' => 'required_if:validation_status,NEW',
            'postal_code' => 'required_if:validation_status,NEW',
            'document' => 'required_if:validation_status,NEW,In Progress',
        ];
    }

    public function messages()
    {
        return [
            'address_line_1.required_if' => 'This field is required.',
            'city.required_if' => 'This field is required.',
            'postal_code.required_if' => 'This field is required.',
            'document.required_if' => 'Please, upload a proof.',
        ];
    }
}
