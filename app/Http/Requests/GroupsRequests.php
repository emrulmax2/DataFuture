<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupsRequests extends FormRequest
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
            'course_id' => 'required',
            'term_declaration_id' => 'required',
            'name' => ['required', 
                Rule::unique('groups')->where('course_id', $this->course_id)->where('term_declaration_id', $this->term_declaration_id)
            ]
        ];
    }
}
