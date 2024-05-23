<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTermDeclarationRequest extends FormRequest
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
            'name'=> 'required',
            'academic_year_id'=> 'required',
            'term_type_id'=> 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'total_teaching_weeks' => 'required',
            'teaching_start_date' => 'required|date',
            'teaching_end_date' => 'required|date',
            'revision_start_date' => 'required|date',
            'revision_end_date' => 'required|date',
        ];
    }
}
