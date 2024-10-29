<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeEducationalQualRequest extends FormRequest
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
            'highest_qualification_on_entry_id' => 'required',
            'qualification_name' => 'required_unless:highest_qualification_on_entry_id,1',
            'award_body' => 'required_unless:highest_qualification_on_entry_id,1',
            'award_date' => 'required_unless:highest_qualification_on_entry_id,1',
        ];
    }

    public function messages()
    {
        return [
            'highest_qualification_on_entry_id.required' => 'This field is required.',
            'qualification_name.required_unless' => 'This field is required.',
            'award_body.required_unless' => 'This field is required.',
            'award_date.required_unless' => 'This field is required.',
        ];
    }
}
