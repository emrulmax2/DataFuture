{{--
    Role picker for the Assign modals (Assignments page and the employee profile tab). Posts
    `role_ids[]`. Ticking a role fills the policy picker below it with that role's policies; the
    page JS (resources/js/policy-assessment/assign-role-picker.js) does the filling and keeps
    track of what a role added and what HR added by hand.

    Up to PolicyAssignmentController::ROLE_CARDS_MAX roles are shown as checkbox cards (name,
    "n policies", and how many of them are not ready for the chosen exam level); more than that
    become one searchable multi-select. With no role to pick it is a note with a link to Roles.

    Needs $roles and $hasRoles (PolicyAssignmentController::assignOptions()): each role carries
    id, name, description, policy_ids, policies_count and not_ready [exam level => count]. The
    same data goes to the JS as JSON in data-roles. The modal supplies the "Role" label
    (id="<rolePickerId>_label") and the .error-role_ids slot.

    @include('pages.hr.policy-assessment.partials.results-role-picker', ['rolePickerId' => 'pa_assign_role'])
--}}
@php
    $paRoleId = (isset($rolePickerId) && is_string($rolePickerId) && $rolePickerId !== '' ? $rolePickerId : 'pa_role');
    $paRoles = (isset($roles) && is_array($roles) ? array_values($roles) : []);
    $paHasRoles = (!empty($paRoles) || !empty($hasRoles));
    $paRoleMode = (empty($paRoles) ? 'empty' : (count($paRoles) > \App\Http\Controllers\HR\PolicyAssessment\PolicyAssignmentController::ROLE_CARDS_MAX ? 'select' : 'cards'));
    $paRolesUrl = (\Illuminate\Support\Facades\Route::has('policy.assessment.role') ? route('policy.assessment.role') : null);
    /* Beginner is the level ticked when the modal opens, so the hints start out for a Beginner exam. */
    $paRoleLevel = \App\Support\PolicyLevel::BEGINNER;
    $paRoleLevelLabel = \App\Support\PolicyLevel::label($paRoleLevel);
    $paRoleExam = (preg_match('/^[AEIOU]/', $paRoleLevelLabel) ? 'an' : 'a').' '.$paRoleLevelLabel.' exam';
    $paRoleJson = [];
    foreach($paRoles as $paRole):
        $paRoleJson[] = [
            'id' => (int) $paRole['id'],
            'name' => (string) $paRole['name'],
            'policy_ids' => array_values(array_map('intval', (isset($paRole['policy_ids']) && is_array($paRole['policy_ids']) ? $paRole['policy_ids'] : []))),
            'not_ready' => (isset($paRole['not_ready']) && is_array($paRole['not_ready']) ? $paRole['not_ready'] : []),
        ];
    endforeach;
@endphp
<div class="pa-assign-roles pa-assign-roles--{{ $paRoleMode }}" data-role-picker data-role-mode="{{ $paRoleMode }}" data-roles="{{ json_encode($paRoleJson) }}" role="group" aria-labelledby="{{ $paRoleId }}_label">
    @if($paRoleMode == 'empty')
        <p class="pa-assign-roles__empty" data-role-empty>
            <i data-lucide="users"></i>
            <span>
                @if($paHasRoles)
                    No role is switched on &mdash; switch one on in
                @else
                    No roles yet &mdash; create one in
                @endif
                @if($paRolesUrl)<a href="{{ $paRolesUrl }}" target="_blank" rel="noopener">Roles</a>@else<strong>Roles</strong>@endif.
                Until then, choose the policies one by one below.
            </span>
        </p>
    @else
        <small class="pa-assign-hint pa-assign-roles__hint">
            Picking a role fills in the policies ticked for it. You can pick more than one.
            @if($paRolesUrl)<a href="{{ $paRolesUrl }}" class="pa-assign-roles__manage" target="_blank" rel="noopener">Manage roles</a>@endif
        </small>
        @if($paRoleMode == 'cards')
            <div class="pa-assign-roles__grid">
                @foreach($paRoles as $paRole)
                    @php
                        $paRoleCount = (int) $paRole['policies_count'];
                        $paRoleShort = (isset($paRole['not_ready'][$paRoleLevel]) ? (int) $paRole['not_ready'][$paRoleLevel] : 0);
                        $paRoleDescription = (isset($paRole['description']) ? (string) $paRole['description'] : '');
                    @endphp
                    <label class="pa-assign-role{{ $paRoleCount === 0 ? ' is-empty' : '' }}" for="{{ $paRoleId }}_{{ $paRole['id'] }}"@if($paRoleDescription !== '') title="{{ $paRoleDescription }}"@endif>
                        <input id="{{ $paRoleId }}_{{ $paRole['id'] }}" type="checkbox" name="role_ids[]" value="{{ $paRole['id'] }}" class="role_ids" autocomplete="off"{{ $paRoleCount === 0 ? ' disabled' : '' }}>
                        <span class="pa-assign-role__check" aria-hidden="true"><i data-lucide="check"></i></span>
                        <span class="pa-assign-role__copy">
                            <strong>{{ $paRole['name'] }}</strong>
                            <small>{{ $paRoleCount === 0 ? 'No active policies' : $paRoleCount.' '.\Illuminate\Support\Str::plural('policy', $paRoleCount) }}</small>
                            <small class="pa-assign-role__ready" data-role-ready="{{ $paRole['id'] }}"{{ $paRoleShort > 0 ? '' : ' hidden' }}>{{ $paRoleShort > 0 ? $paRoleShort.' not ready for '.$paRoleExam : '' }}</small>
                        </span>
                    </label>
                @endforeach
            </div>
        @else
            <select id="{{ $paRoleId }}" name="role_ids[]" multiple class="role_ids" placeholder="Search roles..." data-role-select>
                @foreach($paRoles as $paRole)
                    @php
                        $paRoleCount = (int) $paRole['policies_count'];
                        $paRoleShort = (isset($paRole['not_ready'][$paRoleLevel]) ? (int) $paRole['not_ready'][$paRoleLevel] : 0);
                    @endphp
                    <option value="{{ $paRole['id'] }}"{{ $paRoleCount === 0 ? ' disabled' : '' }}>{{ $paRole['name'] }} — {{ $paRoleCount === 0 ? 'no active policies' : $paRoleCount.' '.\Illuminate\Support\Str::plural('policy', $paRoleCount) }}{{ $paRoleShort > 0 ? ' ('.$paRoleShort.' not ready for '.$paRoleExam.')' : '' }}</option>
                @endforeach
            </select>
        @endif
    @endif
</div>
