<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentWorkPlacementHourRequest extends FormRequest
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
            'company_id' => 'required',
            'company_supervisor_id' => 'required',
            'start_date' => 'required',
            'hours' => 'required|min:1',
            'contract_type' => 'required',
        ];
    }
}
