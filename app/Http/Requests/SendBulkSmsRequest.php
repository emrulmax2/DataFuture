<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendBulkSmsRequest extends FormRequest
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
    public function rules()
    {
        return [
            'subject' => 'required',
            'sms' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'subject.required' => 'Subject can not be empty',
            'sms.required' => 'SMS body should not be empty.'
        ];
    }
}
