<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentAwardStoreRequest extends FormRequest
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
            'date_of_award' => 'required',
            'qual_award_result_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'date_of_award.required' => 'This field is required.',
            'qual_award_result_id.required' => 'This field is required.',
        ];
    }
}
