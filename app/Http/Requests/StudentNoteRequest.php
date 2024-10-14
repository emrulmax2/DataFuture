<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentNoteRequest extends FormRequest
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
            'followed_up' => 'sometimes',
            //'follow_up_start' => 'required_if:followed_up,yes',
            'follow_up_by' => 'required_if:followed_up,yes',
            'term_declaration_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'opening_date.required' => 'The Opening Date field can not be empty.',
            'content.required' => 'The Note field can not be empty.',
            'follow_up_by.required_if' => 'This field is required.',
            'term_declaration_id.required' => 'This field is required.'
        ];
    }
}
