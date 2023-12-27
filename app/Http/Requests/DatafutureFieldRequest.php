<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DatafutureFieldRequest extends FormRequest
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
            'datafuture_field_category_id' => 'required',
            'name' => 'required',
            'type' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'datafuture_field_category_id.required' => 'This field is required',
            'name.required' => 'This field is required',
            'type.required' => 'This field is required',
        ];
    }
}
