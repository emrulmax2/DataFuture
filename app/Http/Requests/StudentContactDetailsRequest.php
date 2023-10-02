<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentContactDetailsRequest extends FormRequest
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
            //'personal_email' => 'required',
            //'institutional_email_name' => 'required',

            'phone' => 'required',
            'mobile' => 'required',
            'term_time_address_id' => 'required|numeric|gt:0',
            'term_time_post_code' => 'required',
            'permanent_address_id' => 'required|numeric|gt:0',
            'permanent_post_code' => 'required',
        ];
    }

    public function messages()
    {
        return [
            //'personal_email.required' => 'The Email field is required.',
            //'institutional_email_name.required' => 'This field is required.',
            'phone.required' => 'The Home Phone field is required.',
            'mobile.required' => 'The Mobile Phone field is required.',
            'term_time_address_id.required' => 'Term time address is required.',
            'term_time_address_id.gt' => 'Term time address is required.',
            'term_time_address_id.numeric' => 'Term time address is required.',
            'term_time_post_code.required' => 'Term time post code is required.',
            'permanent_address_id.required' => 'Permanent address is required.',
            'permanent_address_id.gt' => 'Permanent address is required.',
            'permanent_address_id.numeric' => 'Permanent address is required.',
            'permanent_post_code.required' => 'Permanent post code is required.',
        ];
    }
}
