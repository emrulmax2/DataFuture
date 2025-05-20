<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseCreationsRequest extends FormRequest
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
            'semester_id' => 'required',
            'course_id' => 'required',
            'course_creation_qualification_id' => 'required',
            'duration' => 'required',
            'unit_length' => 'required',

            //'is_workplacement' => 'sometimes',
            //'required_hours' => 'required_if:is_workplacement,1'
        ];
    }
}
