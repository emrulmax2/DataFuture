<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccBankStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'bank_name' => 'required',
            'sort_code' => 'nullable|string|min:8|max:8',
            'ac_number' => 'nullable|digits:8',
        ];
    }

    public function messages()
    {
        return [
            'bank_name' => 'This field is required.',
            'sort_code.min' => 'Min 8 characters.',
            'sort_code.max' => 'Max 8 characters.',
            'ac_number.digits' => 'Min 8 digits.',
        ];
    }
}
