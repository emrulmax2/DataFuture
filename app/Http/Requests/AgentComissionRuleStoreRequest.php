<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentComissionRuleStoreRequest extends FormRequest
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
            'comission_mode' => 'required',
            'percentage' => 'required_if:comission_mode,1',
            'amount' => 'required_if:comission_mode,2',
            'period' => 'required',
            'payment_type' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'comission_mode.required' => 'This field is required.',
            'percentage.required_if' => 'This field is required.',
            'amount.required_if' => 'This field is required.',
            'period.required' => 'This field is required.',
            'payment_type.required' => 'This field is required.',
        ];
    }
}
