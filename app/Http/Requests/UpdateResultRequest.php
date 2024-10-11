<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResultRequest extends FormRequest
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
            'grade_id' => 'required|array',
            'grade_id.*' => 'required|integer',
            'term_declaration_id' => 'required|array',
            'term_declaration_id.*' => 'required|integer',
            'published_at' => 'required|array',
            'published_at.*' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'grade_id.required' => 'Grade field is required',
            'grade_id.*.required' => 'Each grade field is required',
            'grade_id.*.integer' => 'Each grade field must be an integer',
            'term_declaration_id.required' => 'Term field is required',
            'term_declaration_id.*.required' => 'Each term field is required',
            'term_declaration_id.*.integer' => 'Each term field must be an integer',
            'published_at.required' => 'Published Date field is required',
            'published_at.*.required' => 'Each published date field is required',
            'published_at.*.date' => 'Each published date field must be a valid date',
        ];
    }
}
