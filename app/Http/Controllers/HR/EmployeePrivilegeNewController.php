<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\PermissionSettingController;
use App\Models\Department;
use App\Models\DepartmentTemplate;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeePermission;
use App\Models\Employment;
use App\Models\User;
use App\Models\UserPrivilege;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeePrivilegeNewController extends Controller
{
    /**
     * Only HR users who hold the privilege-management key may read or write
     * employee privileges. Without this, any authenticated user could POST to
     * store() with an arbitrary employee_id and grant themselves every key.
     * Mirrors the condition the Privilege tab is rendered under
     * (resources/views/pages/employee/profile/partials/side-tabs.blade.php).
     */
    private function guardPrivilegeAccess(): void
    {
        $priv = auth()->user()->priv();

        $hrPortal = isset($priv['hr_porta']) && $priv['hr_porta'] == 1;
        $canPriv = (isset($priv['privilege_menu']) && $priv['privilege_menu'] == 1)
            || in_array(auth()->id(), [1, 7]);

        abort_unless($hrPortal && $canPriv, 403, 'You are not permitted to manage employee privileges.');
    }

    public function index($id){
        $this->guardPrivilegeAccess();

        $employee = Employee::with('title')->find($id);
        $user_id = $employee->user_id;

        $existingPermission = EmployeePermission::where('user_id', $user_id)->orderBy('id', 'desc')->first();

        // department_id records which template these permissions came from. Null
        // means they were set directly, so no template gets preselected (step 3).
        $department_id = (int) ($existingPermission->department_id ?? 0);

        // The full grouped list is always rendered so HR can grant permissions
        // without loading a template first (step 1). Whatever the employee already
        // has is ticked, regardless of which template it came from.
        $permissions = [
            $department_id => EmployeePermission::where('user_id', $user_id)
                ->pluck('value', 'key')
                ->toArray(),
        ];

        $permissionHtml = view('pages.employee.profile.permission-template', [
            'permissions' => $permissions,
            'department_id' => $department_id,
            'internalLinks' => PermissionSettingController::internalLinks(),
        ])->render();

        $departmentTemplates = DepartmentTemplate::pluck('department_id')->unique()->toArray();
        $departmentList = Department::whereIn('id', $departmentTemplates)->orderBy('name')->get();

        return view('pages.employee.profile.privilege-new',[
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [],
            'employee' => $employee,
            // Feeds the printable privileges report header.
            'employment' => Employment::where('employee_id', $id)->first(),
            'departments' => $departmentList,
            'department_id' => $department_id,
            'permissionHtml' => $permissionHtml,
        ]);
    }

    public function store(Request $request) {
        $this->guardPrivilegeAccess();

        $employee_id = $request->employee_id;
        $employee = Employee::find($employee_id);
        if (!$employee) {
            return response()->json(['res' => 'Employee not found.'], 404);
        }

        $user_id = $employee->user_id;
        $permissions = $request->permissions ?? [];

        DB::beginTransaction();

        try {
            // The form always posts the employee's complete permission set, so the
            // old rows are replaced wholesale. Unticking a box removes it (an
            // unchecked checkbox posts nothing), which a merge would never do.
            EmployeePermission::where('user_id', $user_id)->delete();

            $now = now();
            $insertData = [];

            foreach ($permissions as $department_id => $deptPermissions) {
                // Key 0 is the blank list rendered when no template is loaded.
                $department_id = $department_id > 0 ? $department_id : null;

                foreach ($deptPermissions as $key => $value) {
                    if (empty($value)) {
                        continue;
                    }

                    $insertData[] = [
                        'user_id' => $user_id,
                        'department_id' => $department_id,
                        'key' => $key,
                        'value' => $value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($insertData)) {
                EmployeePermission::insert($insertData);
            }

            DB::commit();
            return response()->json(['res' => 'User Privileges successfully saved.'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['res' => 'Failed to save privileges.', 'error' => $e->getMessage()], 500);
        }
    }



    /**
     * Revokes every permission this employee holds.
     *
     * Guarded exactly like store(): this takes access away, which is as
     * consequential as granting it, and it is not undoable from the UI.
     */
    public function reset(Request $request)
    {
        $this->guardPrivilegeAccess();

        $employee = Employee::find($request->employee_id);

        if (!$employee) {
            return response()->json(['res' => 'Employee not found.'], 404);
        }

        if (empty($employee->user_id)) {
            return response()->json(['res' => 'This employee has no linked user account.'], 422);
        }

        $user = User::find($employee->user_id);

        // A super admin is bypassed everywhere, so wiping their rows would delete
        // the record while changing nothing. Say so rather than pretend it worked.
        if ($user && $user->isSuperAdmin()) {
            return response()->json([
                'res' => 'This is a super admin account. It bypasses the privilege system, so its permissions cannot be revoked here.',
            ], 422);
        }

        $revoked = EmployeePermission::where('user_id', $employee->user_id)->count();
        $legacyRevoked = UserPrivilege::where('user_id', $employee->user_id)->count();

        DB::transaction(function () use ($employee) {
            EmployeePermission::where('user_id', $employee->user_id)->delete();

            // The legacy rows have to go too. They are no longer read, but
            // PRIVILEGE_SOURCE=legacy is the emergency rollback - and if these
            // survived, that rollback would silently hand a revoked person (a
            // leaver, say) their full access back.
            //
            // UserPrivilege soft-deletes, so this drops them out of priv() while
            // the rows remain in the database if they are ever needed.
            UserPrivilege::where('user_id', $employee->user_id)->delete();
        });

        Log::channel('privileges')->warning('permissions_revoked', [
            'employee_id' => $employee->id,
            'user_id' => $employee->user_id,
            'revoked_rows' => $revoked,
            'legacy_rows_soft_deleted' => $legacyRevoked,
            'by_user_id' => auth()->id(),
            'by' => auth()->user()->name,
        ]);

        return response()->json([
            'res' => 'All permissions revoked.',
            'revoked' => $revoked,
        ], 200);
    }

    public function getDepartmentPermissionTemplate(Request $request) {
        $this->guardPrivilegeAccess();

        // Cast so a cleared selection renders the blank list under key 0 rather
        // than an empty key, which would post as permissions[][...].
        $department_id = (int) $request->department_id;

        $templates = $department_id > 0
            ? DepartmentTemplate::where('department_id', $department_id)->pluck('value', 'key')->toArray()
            : [];

        $permissions = [
            $department_id => $templates
        ];

        $html = view('pages.employee.profile.permission-template', [
            'permissions' => $permissions,
            'department_id' => $department_id,
            'internalLinks' => PermissionSettingController::internalLinks(),
        ])->render();

        return response()->json(['html' => $html, 'department_id' => $department_id, 'permissions' => $permissions], 200);
    }

}
