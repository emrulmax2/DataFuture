<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcademicYearUpdateRequest extends FormRequest
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
            'name' => 'required|unique:academic_years,name,'. $this->id,
            'from_date' => 'required|unique:academic_years,from_date,'. $this->id,
            'to_date' => 'required|unique:academic_years,to_date,'. $this->id,
        ];
    }
}
