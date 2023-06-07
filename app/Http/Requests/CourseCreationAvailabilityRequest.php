<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseCreationAvailabilityRequest extends FormRequest
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
            'admission_date' => 'required|date',
            'admission_end_date' => 'required|date',
            'course_start_date' => 'required|date',
            'course_end_date' => 'required|date',
            'last_joinning_date' => 'required|date',
            'type' => 'required'
        ];
    }
}
