<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComonSmtpUpdateRequest extends FormRequest
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
            'smtp_user' => 'required|unique:comon_smtps,smtp_user,'.$this->id,
            'smtp_pass' => 'required',
            'smtp_host' => 'required',
            'smtp_port' => 'required',
            'smtp_encryption' => 'required',
            'smtp_authentication' => 'required',
        ];
    }
}
