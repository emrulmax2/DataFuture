<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignGroupLeaderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Clearing every leader is a legitimate save, so the list may be
            // empty — but the group it belongs to has to be real.
            'group_leader_ids' => 'nullable|array',
            'group_leader_ids.*' => 'integer|exists:users,id',
            'yearid' => 'required|integer',
            'termid' => 'required|integer',
            'courseid' => 'required|integer',
            'groupid' => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'groupid.required' => 'The group could not be identified. Please reopen the tree and try again.',
        ];
    }
}
