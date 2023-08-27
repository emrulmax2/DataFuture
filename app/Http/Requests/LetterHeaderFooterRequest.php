<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LetterHeaderFooterRequest extends FormRequest
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
            'name' => 'required',
            'for' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Header name is required.',
            'for.required' => 'Please select a valid option'
        ];
    }
}
