<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdmissionContactDetailsRequest extends FormRequest
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
            'email' => 'required',
            'mobile' => 'required',
            'applicant_address' => 'required',
            'mobile_verification' => 'required|in:1',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'The Email field is required.',
            'mobile.required' => 'The Mobile Phone field is required.',
            'applicant_address.required' => 'The Applicant Address is required.',
            'mobile_verification.required' => 'Please verified mobile number',
            'mobile_verification.in' => 'Please verified mobile number',
        ];
    }
}
