<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskListRequest extends FormRequest
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
            'process_list_id' => 'required',
            'name' => 'required',
            'assigned_users' => 'required|array|min:1',
            'external_link' => 'sometimes',
            'external_link_ref' => 'required_if:external_link,1',
            'task_statuses' => 'required_if:status,Yes',
        ];
    }
}
