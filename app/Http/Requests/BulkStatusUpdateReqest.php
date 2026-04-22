<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkStatusUpdateReqest extends FormRequest
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
            'student_ids' => 'required',
            'status_id' => 'required',
            'term_declaration_id' => 'required',
            'status_change_date' => 'required',

            'status_end_date' => 'required_if:status_id,21,26,27,31,42,22,45',
            'reason_for_engagement_ending_id' => 'required_if:status_id,21,26,27,31,42,22,45',
        ];
    }

    public function messages()
    {
        return [
            'student_ids.required' => 'Student ids can not be empty.',
            'status_id.required' => 'This field is required.',
            'term_declaration_id.required' => 'This field is required.',
            'status_change_date.required' => 'This field is required.',
            'status_end_date.required_if' => 'This field is required.',
            'reason_for_engagement_ending_id.required_if' => 'This field is required.',
        ];
    }
}
