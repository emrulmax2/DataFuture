<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdmissionKinDetailsRequest extends FormRequest
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
            'name' => 'required',
            'kins_relation_id' => 'required',
            'kins_mobile' => 'required',
            'kin_address' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The Kin Name field is required.',
            'kins_relation_id.required' => 'The Kins Relation field is required.',
            'kins_mobile.required' => 'The Kins Mobile field is required.',
            'kin_address.required' => 'The Kins Address is required.',
        ];
    }
}
