<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentQualificationRequest extends FormRequest
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
            'highest_academic' => 'required',
            'awarding_body' => 'required',
            'subjects' => 'required',
            //'result' => 'required',
            'qualification_grade_id' => 'required',
            
        ];
    }

    public function messages()
    {
        return [
            'highest_academic.required' => 'This field is required.',
            'awarding_body.required' => 'This field is required.',
            'subjects.required' => 'This field is required.',
            //'result' => 'required',
            'qualification_grade_id.required' => 'This field is required.',
            
        ];
    }
}
