<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResultComparisonRequest extends FormRequest
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
            "student_id"=> 'required|array',
            "assessment_plan_id"=> 'required|array',
            'noId.*' => 'nullable|integer',
            'id.*' => 'nullable|integer',
            'grade_id.*' => 'required_with:noId.*,id.*|integer|exists:grades,id',
        ];
    }

    public function messages()
    {
        return [
            'grade_id.*.required_with' => 'The grade ID is required when either Serial or ID is selected.',
        ];
    }

}
