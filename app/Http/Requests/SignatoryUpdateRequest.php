<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignatoryUpdateRequest extends FormRequest
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
            'signatory_name' => 'required',
            'signatory_post' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'signatory_name.required' => 'The Name field is required.',
            'signatory_post.required' => 'The Post field is required.'
        ];
    }
}
