<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentBankStoreRequest extends FormRequest
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
        return [
            'beneficiary' => 'required',
            'sort_code' => 'required',
            'ac_no' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'beneficiary.required' => 'This field is required',
            'sort_code.required' => 'This field is required',
            'ac_no.required' => 'This field is required',
        ];
    }
}
