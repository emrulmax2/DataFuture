<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentOtherPersonalInformationRequest extends FormRequest
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
            'disability_status' => 'sometimes',
            'disability_id' => 'required_if:disability_status,1',
        ];
    }

    public function messages()
    {
        return [
            'disability_id.required_if' => 'You have to select at least one Disability while Disability Status is turned on.',
        ];
    }
}
