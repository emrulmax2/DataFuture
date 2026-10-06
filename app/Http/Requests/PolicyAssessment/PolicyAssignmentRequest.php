<?php

namespace App\Http\Requests\PolicyAssessment;

use App\Services\PolicyAssessmentService;
use App\Support\PolicyLevel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * HR assigning policies to one or more members of staff, as a Beginner,
 * Intermediate or Expert exam. The Assign modal posts the roles HR ticked
 * (`role_ids`) together with the policy list those roles filled in and HR
 * may have added to or trimmed (`policy_ids`); that list is what is
 * assigned. Roles on their own assign every policy they cover.
 * `category_ids` (whole categories) is still accepted for older callers,
 * though no screen offers it any more.
 *
 * A role must be live and switched on: one that was archived or switched
 * off while the modal was open is refused here rather than quietly ignored.
 * The work itself is done by PolicyAssessmentService::assign().
 */
class PolicyAssignmentRequest extends FormRequest
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
        /* Ids are plain digits only — the same test the service applies when it
           cleans them. `integer` alone would let "+12" through here and then
           be dropped there, silently changing what gets assigned. */
        $id = 'regex:/^[1-9][0-9]*$/';

        /* The assign screens post `policies_listed`: the policy picker is then
           exactly what is assigned, so an emptied picker is an error rather
           than a cue to fall back on everything the ticked role holds. Without
           the marker (older callers) a role or category alone is still enough. */
        $policyList = ($this->boolean('policies_listed')
            ? 'required|array|min:1'
            : 'nullable|array|required_without_all:role_ids,category_ids');

        return [
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => ['required', $id, Rule::exists('employees', 'id')->whereNull('deleted_at')],
            'policy_ids' => $policyList,
            'policy_ids.*' => ['required', $id, Rule::exists('policy_documents', 'id')->whereNull('deleted_at')],
            'role_ids' => 'nullable|array',
            'role_ids.*' => ['required', $id, Rule::exists('policy_roles', 'id')->where('is_active', 1)->whereNull('deleted_at')],
            'category_ids' => 'nullable|array',
            'category_ids.*' => ['required', $id, Rule::exists('policy_categories', 'id')->whereNull('deleted_at')],
            'level' => ['required', 'string', Rule::in(PolicyLevel::all())],
            /* Every assignment is given a date to be done by. */
            'due_date' => 'required|date|after_or_equal:today',
            'send_email' => 'nullable|boolean',
            'note' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_ids.required' => 'Please choose at least one member of staff.',
            'employee_ids.min' => 'Please choose at least one member of staff.',
            'employee_ids.*.regex' => 'One of the chosen staff members could not be found.',
            'employee_ids.*.exists' => 'One of the chosen staff members could not be found.',
            'policy_ids.required' => 'Please pick a role or choose at least one policy.',
            'policy_ids.min' => 'Please pick a role or choose at least one policy.',
            'policy_ids.required_without_all' => 'Please pick a role or choose at least one policy.',
            'policy_ids.array' => 'Please pick a role or choose at least one policy.',
            'policy_ids.*.required' => 'One of the chosen policies could not be found.',
            'policy_ids.*.regex' => 'One of the chosen policies could not be found.',
            'policy_ids.*.exists' => 'One of the chosen policies could not be found.',
            'role_ids.array' => 'Please pick the role again.',
            'role_ids.*.required' => 'One of the chosen roles is no longer available. Please pick the role again.',
            'role_ids.*.regex' => 'One of the chosen roles is no longer available. Please pick the role again.',
            'role_ids.*.exists' => 'One of the chosen roles is no longer available. Please pick the role again.',
            'category_ids.*.regex' => 'One of the chosen categories could not be found.',
            'category_ids.*.exists' => 'One of the chosen categories could not be found.',
            'due_date.required' => 'Please choose a due date.',
            'due_date.date' => 'Please enter a valid due date.',
            'due_date.after_or_equal' => 'The due date cannot be in the past.',
            'note.max' => 'The note may not be longer than 1,000 characters.',
            'level.required' => 'Please choose a level: Beginner, Intermediate or Expert.',
            'level.string' => 'Please choose a level: Beginner, Intermediate or Expert.',
            'level.in' => 'Please choose a level: Beginner, Intermediate or Expert.',
        ];
    }
}
