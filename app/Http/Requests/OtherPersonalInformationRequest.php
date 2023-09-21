<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OtherPersonalInformationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'sexual_orientation_id' => 'required',
            'hesa_gender_id' => 'required',
            'religion_id' => 'required',
            'disability_status' => 'sometimes',
            'disability_id' => 'required_if:disability_status,1',
        ];
    }

    public function messages()
    {
        return [
            'sexual_orientation_id.reuired' => 'The field is required.',
            'hesa_gender_id.reuired' => 'The Gender Identity field is required.',
            'religion_id.reuired' => 'The field is required.',
            
            'disability_id.required_if' => 'You have to select at least one Disability while Disability Status is turned on.',
        ];
    }
}
