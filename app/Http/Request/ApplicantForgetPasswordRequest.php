<?php

namespace App\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class ApplicantForgetPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email|exists:applicant_users',
        ];
    }
}
