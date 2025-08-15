<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentUpdateStatusRequest extends FormRequest
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
            'status_id' => 'required',
            'status_change_date' => 'required',

            'is_assigned' => 'sometimes',
            'term_declaration_id' => 'required_if:is_assigned,1',
            //'term_declaration_id' => 'required',

            'status_end_date' => 'required_if:status_id,21,26,27,31,42',
            'reason_for_engagement_ending_id' => 'required_if:status_id,21,26,27,31,42',
        ];
    }

    public function messages()
    {
        return [
            'status_id.required' => 'This field is required.',
            'status_change_date.required' => 'This field is required.',
            'term_declaration_id.required_if' => 'This field is required.',
            'status_end_date.required_if' => 'This field is required.',
            'reason_for_engagement_ending_id.required_if' => 'This field is required.',
        ];
    }
}
