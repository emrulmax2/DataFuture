<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicantResidencyAndCriminalConvictionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Residency Status
            'residency_status_id' => 'required',
            // Criminal Conviction
            'have_you_been_convicted' => 'required|in:0,1',
            'criminal_declaration' => 'accepted',
            'criminal_conviction_details' => 'required_if:have_you_been_convicted,1|max:1000',
            

        ];
    }


    public function messages(): array
    {
        return [
            'residency_status_id.required' => 'Please select your residency status.',
            'have_you_been_convicted.required' => 'Please indicate if you have been convicted of a criminal offence.',
            'have_you_been_convicted.in' => 'Invalid selection for criminal conviction.',
            'criminal_declaration.accepted' => 'You must accept the criminal conviction declaration.',
            'criminal_conviction_details.required_if' => 'Please provide details of your criminal conviction.',
            'criminal_conviction_details.max' => 'Criminal conviction details may not be greater than 1000 characters.',
        ];
    }
}
