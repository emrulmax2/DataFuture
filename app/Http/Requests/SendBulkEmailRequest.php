<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendBulkEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'comon_smtp_id' => 'required',
            'subject' => 'required',
            'body' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'comon_smtp_id.required' => 'SMTP field is required',
            'subject.required' => 'Subject can not be empty',
            'body.required' => 'Mail body should not be empty.'
        ];
    }
}
