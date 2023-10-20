<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentFrontendContactDetailsRequest extends FormRequest
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
            'phone' => 'required',
            'term_time_address_id' => 'required|numeric|gt:0',
            'permanent_address_id' => 'required|numeric|gt:0',
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => 'The Home Phone field is required.',
            'term_time_address_id.required' => 'Term time address is required.',
            'term_time_address_id.gt' => 'Term time address is required.',
            'term_time_address_id.numeric' => 'Term time address is required.',
            'permanent_address_id.required' => 'Permanent address is required.',
            'permanent_address_id.gt' => 'Permanent address is required.',
            'permanent_address_id.numeric' => 'Permanent address is required.',
        ];
    }
}
