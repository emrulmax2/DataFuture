<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentFirstLoginData extends FormRequest
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
            'gender'=> 'required',
            'student_id'=> 'required',
            'nationality'=> 'required',
            'religion'=> 'required',
            'birth_country'=> 'required',
            'sex_identifier_id'=> 'required',
            'sexual_orientation'=> 'required',
        ];
    }
}
