<?php

namespace App\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class ApplicantForgetPasswordUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required'
        ];
    }
}
