<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlcAgreementUpdateRequest extends FormRequest
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
            'slc_coursecode' => 'required',
            'date' => 'required',
            'year' => 'required',
            'fees' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'slc_coursecode.required' => 'This field is required.',
            'date.required' => 'This field is required.',
            'year.required' => 'This field is required.',
            'fees.required' => 'This field is required.',
        ];
    }
}
