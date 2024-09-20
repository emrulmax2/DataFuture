<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountCollectionReportRequest extends FormRequest
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
            'date_range' => 'required',
            'date_type' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'date_range.required' => 'Please select a date range.',
            'date_type.required' => 'Please check a date type.',
        ];
    }
}
