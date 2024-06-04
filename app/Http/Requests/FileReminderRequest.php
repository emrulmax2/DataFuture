<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileReminderRequest extends FormRequest
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
            'subject' => 'required',
            'message' => 'required',

            'is_repeat_reminder' => 'sometimes',
            'single_reminder_date' => 'required_if:is_repeat_reminder,0',
            'frequency' => 'required_if:is_repeat_reminder,1',
            'repeat_reminder_start' => 'required_if:is_repeat_reminder,1',

            'employee_ids' => 'required',
        ];
    }

    public function messages(){
        return [
            'subject.required' => 'This field is required.',
            'message.required' => 'This field is required.',

            'single_reminder_date.required_if' => 'This field is required.',
            'frequency.required_if' => 'This field is required.',
            'repeat_reminder_start.required_if' => 'This field is required.',

            'employee_ids.required' => 'This field is required.',
        ];
    }
}
