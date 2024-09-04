<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentOtherIdentificationUpdateRequest extends FormRequest
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
            'application_no' => 'required',
            'ssn_no' => 'required',
            //'uhn_no' => 'required',
            'registration_no' => 'required',
        ];
    }
}
