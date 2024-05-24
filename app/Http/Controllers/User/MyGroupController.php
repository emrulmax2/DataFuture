<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeGroupRequest;
use App\Models\Employee;
use App\Models\EmployeeGroup;
use App\Models\EmployeeGroupMember;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Http\Request;

class MyGroupController extends Controller
{
    public function index(){
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $employeeId = $employee->id;
        $userData = User::find($employee->user_id);
        $employment = Employment::where("employee_id", $employeeId)->get()->first();

        return view('pages.users.my-account.my-groups',[
            'title' => 'My Groups - London Churchill College',
            'breadcrumbs' => [],
            'user' => $userData,
            'employee' => $employee,
            'employment' => $employment,
            'employees' => Employee::where('status', 1)->orderBy('first_name', 'ASC')->get()
        ]);
    }

    public function store(EmployeeGroupRequest $request){
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $member_ids = (isset($request->employee_ids) && !empty($request->employee_ids) ? $request->employee_ids : []);

        $data = [];
        $data['employee_id'] = $employee->id;
        $data['name'] = $request->name;
        $data['type'] = (isset($request->type) && $request->type > 0 ? $request->type : 2);
        $data['created_by'] = auth()->user()->id;

        $group = EmployeeGroup::create($data);
        if($group->id):
            if(!empty($member_ids)):
                foreach($member_ids as $mem):
                    $data = [];
                    $data['employee_group_id'] = $group->id;
                    $data['employee_id'] = $mem;
                    $data['created_by'] = auth()->user()->id;

                    EmployeeGroupMember::create($data);
                endforeach;
            endif;
            return response()->json(['suc' => 1], 200);
        else:
            return response()->json(['suc' => 2], 200);
        endif;
    }

    public function list(Request $request){
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeGroup::where('employee_id', $employee->id)->orderByRaw(implode(',', $sorts));
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->name,
                    'type' => $list->type,
                    'members' => (isset($list->members) && $list->members->count() > 0 ? $list->members->count() : 0),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function edit(Request $request){
        $row_id = $request->row_id;
        $employeeGroup = EmployeeGroup::find($row_id);
        
        return response()->json(['res' => $employeeGroup], 200);
    }

    public function update(EmployeeGroupRequest $request){
        $employee_group_id = $request->id;
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $member_ids = (isset($request->employee_ids) && !empty($request->employee_ids) ? $request->employee_ids : []);
        $exist_members = EmployeeGroupMember::where('employee_group_id', $employee_group_id)->pluck('employee_id')->unique()->toArray();
        $removedMember = array_diff($exist_members, $member_ids);

        $data = [];
        $data['employee_id'] = $employee->id;
        $data['name'] = $request->name;
        $data['type'] = (isset($request->type) && $request->type > 0 ? $request->type : 2);
        $data['updated_by'] = auth()->user()->id;

        $group = EmployeeGroup::where('id', $employee_group_id)->update($data);

        if(!empty($member_ids)):
            foreach($member_ids as $mem):
                $row = EmployeeGroupMember::where('employee_group_id', $employee_group_id)->where('employee_id', $mem)->get()->count();
                if($row == 0):
                    $data = [];
                    $data['employee_group_id'] = $group->id;
                    $data['employee_id'] = $mem;
                    $data['created_by'] = auth()->user()->id;

                    EmployeeGroupMember::create($data);
                endif;
            endforeach;
        endif;
        if(!empty($removedMember)):
            EmployeeGroupMember::whereIn('employee_id', $removedMember)->where('employee_group_id', $employee_group_id)->forceDelete();
        endif;
        return response()->json(['suc' => 1], 200);
    }

    public function destroy($id){
        $data = EmployeeGroup::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = EmployeeGroup::where('id', $id)->withTrashed()->restore();
        response()->json($data);
    }
}
