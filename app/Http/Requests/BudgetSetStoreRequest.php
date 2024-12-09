<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetSetStoreRequest extends FormRequest
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
            'budget_year_id' => 'required',
            'budget_name_ids' => 'required_if:budget_year_id,gt,1'
        ];
    }
    public function messages()
    {
        return [
            'budget_year_id.required' => 'This field is required.',
            'budget_name_ids.required_if' => 'Checked at lease one budget name.',
        ];
    }
}
