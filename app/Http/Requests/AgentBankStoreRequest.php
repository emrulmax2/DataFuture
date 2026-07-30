<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentBankStoreRequest extends FormRequest
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
    public function rules()
    {
        $rules = [
            'agent_id' => 'required|integer|exists:agents,id',
            'beneficiary' => 'required',
            'sort_code' => 'required',
            'ac_no' => 'required',
            'active' => 'nullable|in:1',
        ];

        if($this->routeIs('agent-user.update.bank')):
            $rules['id'] = 'required|integer|exists:agent_bank_details,id';
        endif;

        return $rules;
    }

    public function messages()
    {
        return [
            'agent_id.required' => 'Agent is required',
            'agent_id.exists' => 'Agent was not found',
            'beneficiary.required' => 'This field is required',
            'sort_code.required' => 'This field is required',
            'ac_no.required' => 'This field is required',
            'active.in' => 'Invalid status selected',
            'id.required' => 'Bank details record is required',
            'id.exists' => 'Bank details record was not found',
        ];
    }
}
