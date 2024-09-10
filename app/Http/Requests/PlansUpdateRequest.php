<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlansUpdateRequest extends FormRequest
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
            //'group_id' => 'required',
            'rooms_id' => 'required',
            'class_type' => 'required',
            'tutor_id' => 'required_unless:class_type,Tutorial,Seminar',
            'personal_tutor_id' => 'required',
            'module_creation_id' => 'required',
            //'module_enrollment_key' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            //'submission_date' => 'required|date',
            'class_day' => 'required|in:sat,sun,mon,tue,wed,thu,fri',
        ];
    }
}
