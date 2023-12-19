<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\UserPrivilege;
use Illuminate\Http\Request;

class EmployeePrivilegeController extends Controller
{
    public function index($id){
        $employee = Employee::find($id);
        $employment = Employment::where("employee_id",$id)->get()->first();
        $categories = UserPrivilege::where('employee_id', $id)->where('user_id', $employee->user_id)->pluck('category')->toArray();
        $res = [];
        if(!empty($categories)):
            foreach($categories as $cat):
                $res[$cat] = UserPrivilege::where('employee_id', $id)->where('user_id', $employee->user_id)->where('category', $cat)
                             ->get()->pluck('access', 'name')->toArray();
            endforeach;
        endif;
        
        return view('pages.employee.profile.privilege',[
            'title' => 'Welcome - LCC Data Future Managment',
            'breadcrumbs' => [],
            "employee" => $employee,
            "employment" => $employment,
            'priv' => $res
        ]);
    }

    public function store(Request $request) {
        $employee_id = $request->employee_id;
        $employee = Employee::find($employee_id);
        $user_id = $employee->user_id;
        
        $oldDeleted = UserPrivilege::where('employee_id', $employee_id)->where('user_id', $user_id)->forceDelete();
        if(isset($request->permission) && !empty($request->permission)):
            foreach($request->permission as $category => $accesses):
                if(isset($accesses) && !empty($accesses)):
                    foreach($accesses as $name => $access):
                        $data = [];
                        $data['user_id'] = $user_id;
                        $data['employee_id'] = $employee_id;
                        $data['category'] = $category;
                        $data['name'] = $name;
                        $data['access'] = $access > 0 ? $access : 0;
                        $data['created_by'] = auth()->user()->id;

                        UserPrivilege::create($data);
                    endforeach;
                endif;
            endforeach;
        endif;

        return response()->json(['res' => 'User Privilege successfully inserted.'], 200);
    }
}
