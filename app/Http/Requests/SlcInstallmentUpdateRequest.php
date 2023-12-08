<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlcInstallmentUpdateRequest extends FormRequest
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
            'installment_date' => 'required',
            'amount' => 'required',
            //'attendance_term' => 'required',
            'session_term' => 'required',
            'term' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'installment_date.required' => 'This field is required.',
            'amount.required' => 'required',
            //'attendance_term.required' => 'This field is required.',
            'session_term.required' => 'This field is required.',
            'term.required' => 'This field is required.',
        ];
    }
}
