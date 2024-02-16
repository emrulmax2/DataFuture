<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlcCocUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function rules()
    {
        return [
            'confirmation_date' => 'required',
            'coc_type' => 'required',
            'actioned' => 'required'
        ];
    }


    public function messages()
    {
        return [
            'confirmation_date.required' => 'This field is required.',
            'coc_type.required' => 'This field is required.',
            'actioned.required' => 'This field is required.'
        ];
    }
}
