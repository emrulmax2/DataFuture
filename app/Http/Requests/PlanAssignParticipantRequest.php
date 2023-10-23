<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanAssignParticipantRequest extends FormRequest
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
            'assigned_user_ids' => 'required|array|min:1',
        ];
    }

    public function messages()
    {
        return [
            'assigned_user_ids.required' => 'This field is required.',
        ];
    }
}
