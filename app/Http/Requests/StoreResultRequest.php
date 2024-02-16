<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResultRequest extends FormRequest
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
            "grade_id"=> 'required|array',
        ];
    }

    public function messages()
    {
        return [
            'grade_id.required' => 'This field is required.',
        ];
    }
}
