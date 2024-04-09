<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeAppraisalRequest;
use App\Models\Employee;
use App\Models\EmployeeAppraisal;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeAppraisalController extends Controller
{
    public function index($id){
        $employee = Employee::find($id);
        $userData = User::find($employee->user_id);
        $employment = Employment::where("employee_id",$id)->get()->first();
        return view('pages.employee.profile.appraisal', [
            'title' => 'Employee Appraisals - LCC Data Future Managment',
            'breadcrumbs' => [],
            "user" => $userData,
            "employee" => $employee,
            "employment" => $employment,
            'activeEmployees' => Employee::where('id', '!=', $id)->where('status', 1)->orderBy('first_name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        $employee = (isset($request->employee) && $request->employee > 0 ? $request->employee : 0);
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeAppraisal::where('employee_id', $employee)->orderByRaw(implode(',', $sorts));
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
                $dueOn = date('Y-m-d', strtotime($list->due_on));
                $completed_on = (isset($list->completed_on) && !empty($list->completed_on) ? date('Y-m-d', strtotime($list->completed_on)) : '');
                $data[] = [
                    'id' => $list->id,
                    'employee_id' => $list->employee_id,
                    'sl' => $i,
                    'due_on' => date('jS M, Y', strtotime($list->due_on)),
                    'completed_on' => (!empty($list->completed_on) ? date('jS M, Y', strtotime($list->completed_on)) : ''),
                    'next_due_on' => (!empty($list->next_due_on) ? date('jS M, Y', strtotime($list->next_due_on)) : ''),
                    'appraisedby' => (isset($list->appraisedby->first_name) ? $list->appraisedby->first_name.' ' : '').(isset($list->appraisedby->last_name) ? $list->appraisedby->last_name.' ' : ''),
                    'reviewedby' => (isset($list->reviewedby->first_name) ? $list->reviewedby->first_name.' ' : '').(isset($list->reviewedby->last_name) ? $list->reviewedby->last_name.' ' : ''),
                    'total_score' => $list->total_score,
                    'promotion_consideration' => $list->promotion_consideration,
                    'notes' => $list->notes,
                    'status' => (!empty($completed_on) && $completed_on <=  date('Y-m-d') ? 3 : ($dueOn < date('Y-m-d') ? 2 : 1)),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(EmployeeAppraisalRequest $request){
        $employee_id = $request->employee_id;

        $data = [];
        $data['employee_id'] = $employee_id;
        $data['due_on'] = date('Y-m-d', strtotime($request->due_on));
        /*$data['completed_on'] = (!empty($request->completed_on) ? date('Y-m-d', strtotime($request->completed_on)) : null);
        $data['next_due_on'] = (!empty($request->next_due_on) ? date('Y-m-d', strtotime($request->next_due_on)) : null);
        $data['appraised_by'] = (!empty($request->appraised_by) && $request->appraised_by > 0 ? $request->appraised_by : null);
        $data['reviewed_by'] = (!empty($request->reviewed_by) && $request->reviewed_by > 0 ? $request->reviewed_by : null);
        $data['total_score'] = (!empty($request->total_score) ? $request->total_score : null);
        $data['promotion_consideration'] = (isset($request->promotion_consideration) ? $request->promotion_consideration : 0);
        $data['notes'] = (!empty($request->notes) ? $request->notes : null);*/
        $data['created_by'] = auth()->user()->id;

        EmployeeAppraisal::create($data);

        return response()->json(['res' => 'Employee appraisal successfully inserted.'], 200);
    }


    public function update(EmployeeAppraisalRequest $request){
        $apprisal = EmployeeAppraisal::find($request->id);
        $employee_id = (isset($request->employee_id) && $request->employee_id > 0 ? $request->employee_id : $apprisal->employee_id);
        $id = $request->id;

        $nextDueOn = (!empty($request->next_due_on) ? date('Y-m-d', strtotime($request->next_due_on)) : null);

        $data = [];
        $data['employee_id'] = $employee_id;
        $data['due_on'] = date('Y-m-d', strtotime($request->due_on));
        $data['completed_on'] = (!empty($request->completed_on) ? date('Y-m-d', strtotime($request->completed_on)) : null);
        $data['next_due_on'] = $nextDueOn;
        $data['appraised_by'] = (!empty($request->appraised_by) && $request->appraised_by > 0 ? $request->appraised_by : null);
        $data['reviewed_by'] = (!empty($request->reviewed_by) && $request->reviewed_by > 0 ? $request->reviewed_by : null);
        $data['total_score'] = (!empty($request->total_score) ? $request->total_score : null);
        $data['promotion_consideration'] = (isset($request->promotion_consideration) ? $request->promotion_consideration : 0);
        $data['notes'] = (!empty($request->notes) ? $request->notes : null);
        $data['updated_by'] = auth()->user()->id;

        EmployeeAppraisal::where('id', $id)->update($data);
        if(!empty($nextDueOn) && $nextDueOn != ''):
            $data = [];
            $data['employee_id'] = $employee_id;
            $data['due_on'] = date('Y-m-d', strtotime($nextDueOn));
            $data['parent_employee_appraisal_id'] = $id;
            /*$data['completed_on'] = (!empty($request->completed_on) ? date('Y-m-d', strtotime($request->completed_on)) : null);
            $data['next_due_on'] = (!empty($request->next_due_on) ? date('Y-m-d', strtotime($request->next_due_on)) : null);
            $data['appraised_by'] = (!empty($request->appraised_by) && $request->appraised_by > 0 ? $request->appraised_by : null);
            $data['reviewed_by'] = (!empty($request->reviewed_by) && $request->reviewed_by > 0 ? $request->reviewed_by : null);
            $data['total_score'] = (!empty($request->total_score) ? $request->total_score : null);
            $data['promotion_consideration'] = (isset($request->promotion_consideration) ? $request->promotion_consideration : 0);
            $data['notes'] = (!empty($request->notes) ? $request->notes : null);*/
            $data['created_by'] = auth()->user()->id;

            EmployeeAppraisal::create($data);
        endif;

        return response()->json(['res' => 'Employee appraisal successfully inserted.'], 200);
    }

    public function edit(Request $request){
        $id = $request->editId;
        $employeeAppraisal = EmployeeAppraisal::find($id);

        return response()->json(['res' => $employeeAppraisal], 200);
    }

    public function getNote(Request $request){
        $rowID = $request->rowID;
        $employeeAppraisal = EmployeeAppraisal::find($rowID);
        $note = (isset($employeeAppraisal->notes) && !empty($employeeAppraisal->notes) ? $employeeAppraisal->notes : '');

        return response()->json(['notes' => $note], 200);
    }

    public function destroy(Request $request){
        $id = $request->recordID;
        $data = EmployeeAppraisal::find($id)->delete();

        return response()->json($data);
    }

    public function restore(Request $request) {
        $id = $request->recordID;
        $data = EmployeeAppraisal::where('id', $id)->withTrashed()->restore();

        return response()->json($data);
    }
}
