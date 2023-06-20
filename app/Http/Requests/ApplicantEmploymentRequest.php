<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicantEmploymentRequest extends FormRequest
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
            'company_name' => 'required',
            'company_phone' => 'required',
            'position' => 'required',
            'start_date' => 'required',
            'employment_address' => 'required',
            'contact_name' => 'required',
            'contact_position' => 'required',
            'contact_phone' => 'required',

            'continuing' => 'sometimes',
            'end_date' => 'required_if:continuing,0',
        ];
    }
}
