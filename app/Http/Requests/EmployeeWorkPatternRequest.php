<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeWorkPatternRequest extends FormRequest
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
            'effective_from' => 'required|date',
            'contracted_hour' => 'required|min:5|max:5',
            'salary' => 'required',
            'hourly_rate' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'effective_from.required' => 'This field is required.',
            'contracted_hour.required' => 'This filed is required',
            'contracted_hour.min' => 'Minimum 5 char. needed to validate.',
            'contracted_hour.max' => 'Maximum 5 char. needed to validate.',
            'salary.required' => 'This field is required.',
            'hourly_rate.required' => 'Insert valid value into Hour & Salary field to auto calculate the rate.',
        ];
    }
}
