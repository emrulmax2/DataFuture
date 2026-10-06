<?php

namespace App\Http\Controllers\HR\PolicyAssessment;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\PolicyBadge;
use App\Models\User;
use App\Services\PolicyAssessmentService;
use App\Support\PolicyLevel;

/**
 * Employee profile › Policy Assessments tab: one member of staff's badges
 * and assignments, with assign by role / retake / archive, badge revoke /
 * restore and attempt review. The table and every action reuse the
 * Assignments and badge endpoints (list with `employee`); the badges are
 * drawn here first so they show without waiting for a request.
 */
class EmployeePolicyAssessmentController extends Controller
{
    public function index($id, PolicyAssessmentService $service)
    {
        $this->guard();

        $employee = Employee::findOrFail($id);
        /* A timed test whose clock ran out is marked before the summary is counted. */
        $service->finaliseExpiredAttempts((int) $employee->id);
        $employment = Employment::where('employee_id', $id)->get()->first();
        $options = PolicyAssignmentController::assignOptions();

        /* Live badges on policies that still exist, gold first, newest first —
           the same set and order the tab's JS asks policy.assessment.badge.list for. */
        $badges = PolicyBadge::where('employee_id', $employee->id)
            ->whereHas('policy', function ($q) {
                $q->whereNull('policy_documents.deleted_at');
            })
            ->with(['policy.category' => function ($q) {
                $q->withTrashed();
            }])
            ->orderByRaw("FIELD(policy_badges.level, 'beginner', 'intermediate', 'expert') DESC")
            ->orderBy('awarded_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        return view('pages.employee.profile.policy-assessments', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [],
            'user' => User::find($employee->user_id),
            'employee' => $employee,
            'employment' => $employment,
            'roles' => $options['roles'],
            'hasRoles' => $options['hasRoles'],
            'policyGroups' => $options['policyGroups'],
            'levelMix' => $options['levelMix'],
            'levels' => PolicyLevel::labels(),
            'filterRoles' => PolicyAssignmentController::roleFilterOptions(),
            'summary' => $service->employeeSummary($employee->id),
            'badges' => $badges,
        ]);
    }

    private function guard(): void
    {
        abort_unless(PolicyAssessmentService::canManage(), 403, 'You are not permitted to manage policy assessments.');
    }
}
