<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgentRequest extends FormRequest
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

            'first_name' => 'required',
            'last_name' => 'required',
            'code' => 'required',
            'organization' => 'required',
            //'email' => Rule::unique('agent_users')->ignore($this->id),
            //'email' => "unique:agent_users,email,$this->id,id",

        ];
    }
}
