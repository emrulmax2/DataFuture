<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetYearStoreRequest extends FormRequest
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
            'title' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'This field is required.',
            'start_date.required' => 'This field is required.',
            'start_date.date' => 'Insert a valid date.',
            'end_date.required' => 'This field is required.',
            'end_date.date' => 'Insert a valid date.',
        ];
    }
}
