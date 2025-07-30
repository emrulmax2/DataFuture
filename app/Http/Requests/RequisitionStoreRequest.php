<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequisitionStoreRequest extends FormRequest
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
            'vendor_id' => 'required',
            'budget_year_id' => 'required',
            'budget_set_detail_id' => 'required',
            'required_by' => 'required',
            'first_approver' => 'required',
            'final_approver' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'vendor_id.required' => 'This field is required.',
            'budget_year_id.required' => 'This field is required.',
            'budget_set_detail_id.required' => 'This field is required.',
            'required_by.required' => 'This field is required.',
            'first_approver.required' => 'This field is required.',
            'final_approver.required' => 'This field is required.',
        ];
    }
}
