<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionCategoryUpdateRequest extends FormRequest
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
            'department_id' => ['required', Rule::exists('departments', 'id')->whereNull('deleted_at')],
            /* A category is a sub-department, so its name only has to be
               unique within its own department: "Operations" under Finance and
               "Operations" under Facilities are two different things. Unique
               across the whole table, the second department could never have
               one. */
            'name' => [
                'required',
                'max:191',
                Rule::unique('permission_categories', 'name')
                    ->where('department_id', $this->department_id)
                    ->ignore($this->id),
            ],
        ];
    }

    public function messages()
    {
        return [
            'department_id.required' => 'Select a department.',
            'department_id.exists' => 'That department is no longer available.',
            'name.required' => 'Enter the sub department name.',
            'name.unique' => 'This department already has a sub department with that name.',
        ];
    }
}
