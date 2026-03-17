<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeNoteRequest extends FormRequest
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
            'opening_date' => 'required|date',
            'content' => 'required',
            'reminder' => 'sometimes',
            'reminder_date' => 'required_if:reminder,1',
        ];
    }

    public function messages()
    {
        return [
            'opening_date.required' => 'The Opening Date field can not be empty.',
            'content.required' => 'The Note field can not be empty.',
            'reminder_date.required_if' => 'This field is required.',
        ];
    }
}
