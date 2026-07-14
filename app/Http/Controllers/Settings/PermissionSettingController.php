<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentTemplate;
use App\Models\InternalLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionSettingController extends Controller
{
    /**
     * Department permission templates seed every employee's permissions, so
     * writing them is equivalent to granting privileges. Gate on the same key
     * the settings sidebar renders this section under (user_privilege).
     */
    private function guardSettingsAccess(): void
    {
        $priv = auth()->user()->priv();

        $allowed = (isset($priv['user_privilege']) && $priv['user_privilege'] == 1)
            || in_array(auth()->id(), [1, 7]);

        abort_unless($allowed, 403, 'You are not permitted to manage department permissions.');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->guardSettingsAccess();

        $permissions = DepartmentTemplate::all()->groupBy('department_id')->map(fn($items) => $items->pluck('value', 'key'));

        return view('pages.settings.permissions.index', [
            'title' => 'Departments Permissions - London Churchill College',
            'subtitle' => 'Permissions',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Permissions', 'href' => 'javascript:void(0);']
            ],
            'departments' => Department::get(),
            'permissions' => $permissions,
            'internalLinks' => $this->internalLinks(),
        ]);
    }

    /**
     * Internal links are rendered from the table rather than a fixed list so a
     * link added under Settings shows up here without a code change. Mirrors the
     * query the legacy privilege form uses (EmployeePrivilegeController::index).
     */
    public static function internalLinks()
    {
        return InternalLink::with('children')
            ->whereNull('parent_id')
            ->where('available_staff', 1)
            ->where('active', 1)
            ->orderBy('name', 'ASC')
            ->get();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->guardSettingsAccess();

        $permissions = $request->input('permissions', []);
        $departmentIds = array_keys($permissions);
        $insertData = [];
        $now = now();

        DB::beginTransaction();
        try {
            DepartmentTemplate::whereIn('department_id', $departmentIds)->delete();
            foreach ($permissions as $departmentId => $keys) {
                foreach ($keys as $key => $value) {
                    if (!empty($value)) {
                        $insertData[] = [
                            'department_id' => $departmentId,
                            'key' => $key,
                            // Not every key is a checkbox: the accounts type select stores
                            // 1/2/3 (Admin/User/Audit) and the temporary remote-access key
                            // stores a date range, so persist what was posted.
                            'value' => $value,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            if (!empty($insertData)) {
                DepartmentTemplate::insert($insertData);
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Permissions updated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating permissions: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to update permissions. Please try again.'
            ], 500);
        }
    }
}
