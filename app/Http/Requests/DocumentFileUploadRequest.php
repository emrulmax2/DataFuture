<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentFileUploadRequest extends FormRequest
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
            'documents' => 'required',
            'name' => 'required',
            'email_reminder' => 'sometimes',
            'subject' => 'required_if:email_reminder,1',
            'message' => 'required_if:email_reminder,1',
            'employee_ids' => 'required_if:email_reminder,1',

            'is_repeat_reminder' => 'sometimes',
            'single_reminder_date' => 'required_if:is_repeat_reminder,0',
            'frequency' => 'required_if:is_repeat_reminder,1',
            'repeat_reminder_start' => 'required_if:is_repeat_reminder,1',
        ];
    }

    public function messages(){
        return [
            'documents.required' => 'Please upload 1 or more document.',
            'name.required' => 'This field is required.',
            'subject.required_if' => 'This field is required.',
            'message.required_if' => 'This field is required.',
            'employee_ids.required_if' => 'This field is required.',

            'single_reminder_date.required_if' => 'This field is required.',
            'frequency.required_if' => 'This field is required.',
            'repeat_reminder_start.required_if' => 'This field is required.',

        ];
    }
}
