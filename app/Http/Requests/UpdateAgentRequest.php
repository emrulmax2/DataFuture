<?php

namespace App\Http\Requests;

use App\Models\Agent;
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
        $agentUserId = Agent::find($this->id)->agent_user_id;
        return [

            'password' => 'nullable|string|required_with:confirmed',
            //'email' => Rule::unique('agent_users')->ignore($this->id),
            'email' => 'unique:agent_users,email,'. $agentUserId,
            'code' => 'unique:agent_users,code,'. $agentUserId

        ];
    }
}
