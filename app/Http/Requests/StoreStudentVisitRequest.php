<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentVisitRequest extends FormRequest
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
            'visit_type' => 'required|string|max:191',
            'visit_date' => 'required|date',
            'visit_duration' => 'required|string|max:191',
            'visit_notes' => 'nullable|string|max:1000',
            'term_declaration_id' => 'required_if:visit_type,academic',
            'plan_id' => 'required_if:visit_type,academic',
        ];
    }

    public function messages(): array
    {
        return [

            'visit_type.required'    => 'Visit type is required.',
            'visit_date.required'    => 'Visit date is required.',
            'visit_duration.required'=> 'Visit duration is required.',
            'plan_id.required_if'    => 'Module selection is required when visit type is academic.',
            'term_declaration_id.required_if' => 'Term declaration is required when visit type is academic.',
            
        ];
    }
}
