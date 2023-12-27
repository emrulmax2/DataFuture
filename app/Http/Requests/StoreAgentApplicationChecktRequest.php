<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgentApplicationChecktRequest extends FormRequest
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
            'email' => 'required|email|unique:applicant_users,email',
            'mobile' => 'required|unique:applicant_users,phone',
            'first_name' => 'required',
            'last_name' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'email.required' => 'This field is required.',
            'email.unique' => 'This email already in the system.',
            'mobile.unique' => 'This mobile already in the system.',
            'mobile.required' => 'This filed is required',
            'first_name.required' => 'This field is required.',
            'last_name.required' => 'This field is required.',
        ];
    }
}
