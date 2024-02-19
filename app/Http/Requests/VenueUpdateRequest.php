<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VenueUpdateRequest extends FormRequest
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
            'name' => 'required|unique:venues,name,'. $this->id,
            'idnumber' => 'required|unique:venues,idnumber,'. $this->id,
            'ukprn' => 'required',
        ];
    }
}
