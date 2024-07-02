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
            'result' => 'required',
            'degree_award_date' => 'required|date',
            'highest_qualification_on_entry_id'=> 'required',
            'hesa_qualification_subject_id'=> 'required',
            'qualification_type_identifier_id'=> 'required',
            'previous_provider_id'=> 'required',
        ];
    }
}
