<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PearsonRegistrationConfirmationRequest extends FormRequest
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
            'document' => 'required',
            'status_id' => 'required',
            'term_declaration_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'document.required' => 'Please select pearson registration confirmation .xlsx file.',
            'status_id.required' => 'This field is required.',
            'term_declaration_id.required' => 'This field is required.'
        ];
    }
}