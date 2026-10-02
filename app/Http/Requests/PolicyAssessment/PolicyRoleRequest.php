<?php

namespace App\Http\Requests\PolicyAssessment;

use App\Models\PolicyDocument;
use App\Services\PolicyAssessmentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Add / edit a policy role (Lecturer, Admissions Officer, Finance ...) and
 * the policies ticked for it. The name must be unique among live roles and
 * at least one policy must be ticked; every ticked policy must still be in
 * the question bank (not archived).
 */
class PolicyRoleRequest extends FormRequest
{
    /**
     * Only policy-assessment managers. Checked here, before the rules run,
     * so the exists/unique rules cannot be used to probe for records.
     */
    public function authorize(): bool
    {
        return PolicyAssessmentService::canManage();
    }

    /** Same 403 message as the controllers give. */
    protected function failedAuthorization()
    {
        throw new AuthorizationException('You are not permitted to manage policy assessments.');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        $unique = Rule::unique('policy_roles', 'name')->whereNull('deleted_at');
        if(isset($role->id)):
            $unique->ignore($role->id);
        endif;

        /* One query for the whole list instead of an exists rule per tick.
           A value that is not a whole number is left to the policy_ids.* rule. */
        $allLive = function ($attribute, $value, $fail) {
            $ids = [];
            foreach((array) $value as $id):
                if(is_scalar($id) && ctype_digit((string) $id) && (int) $id > 0):
                    $ids[(int) $id] = true;
                endif;
            endforeach;
            if(empty($ids)):
                return;
            endif;

            if(PolicyDocument::whereIn('id', array_keys($ids))->count() != count($ids)):
                $fail('One of the ticked policies is no longer in the question bank. Reload the page and tick the policies again.');
            endif;
        };

        return [
            'name' => ['required', 'string', 'max:191', $unique],
            'description' => 'nullable|string|max:2000',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|in:0,1',
            'policy_ids' => ['bail', 'required', 'array', 'min:1', 'max:2000', $allLive],
            /* Plain digits only — the same test $allLive and the service use.
               `integer` would accept "+12", which they then drop, saving a
               role with fewer ticks than were sent (or none). */
            'policy_ids.*' => ['bail', 'regex:/^[1-9][0-9]*$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a role name.',
            'name.unique' => 'A role with this name already exists.',
            'sort_order.integer' => 'Sort order must be a whole number.',
            'policy_ids.required' => 'Tick at least one policy for this role.',
            'policy_ids.array' => 'Tick at least one policy for this role.',
            'policy_ids.min' => 'Tick at least one policy for this role.',
            'policy_ids.max' => 'Too many policies were sent. Reload the page and try again.',
            'policy_ids.*.regex' => 'One of the ticked policies was not recognised. Reload the page and try again.',
        ];
    }

    public function attributes(): array
    {
        return [
            'policy_ids' => 'policies',
        ];
    }
}
