<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudetnAttendanceExcuseRequest extends FormRequest
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
            'reason' => 'required',
            'excuses' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'reason.required' => 'Reason can not be empty.',
            'excuses.required' => 'Please checked at least 1 date from Absent or Future date list.'
        ];
    }
}
