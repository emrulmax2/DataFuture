<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetNameStoreRequest extends FormRequest
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
            'name' => 'required',
            'budget_holder_ids' => 'required',
            'budget_requester_ids' => 'required',
            'budget_approver_ids' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'This field is required.',
            'budget_holder_ids.required' => 'This field is required.',
            'budget_requester_ids.required' => 'This field is required.',
            'budget_approver_ids.required' => 'This field is required.'
        ];
    }
}
