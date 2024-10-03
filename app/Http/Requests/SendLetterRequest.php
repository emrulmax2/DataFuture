<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendLetterRequest extends FormRequest
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
            'issued_date' => 'required',
            'letter_set_id' => 'required',
            'letter_body' => 'required',
            'send_in_email' => 'sometimes',
            'comon_smtp_id' => 'required_if:send_in_email,1',
        ];
    }

    public function messages()
    {
        return [
            'issued_date.required' => 'Letter issued date is a required field',
            'letter_set_id.required' => 'Letter set can nto be empty',
            'letter_body.required' => 'Letter body can not be empty.',
            'comon_smtp_id.required_if' => 'Please select a SMTP.'
        ];
    }
}
