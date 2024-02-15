<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentWorkPlacementHourRequest;
use App\Models\CompanySupervisor;
use App\Models\StudentWorkPlacement;
use Illuminate\Http\Request;

class WorkPlacementController extends Controller
{
    public function getSupervisorByCompany(Request $request){
        $company_id = $request->theCompany;
        $res = '<option value="">Please Select</option>';

        $supervisors = CompanySupervisor::where('company_id', $company_id)->orderBy('name', 'ASC')->get();
        if($supervisors->count() > 0):
            foreach($supervisors as $sup):
                $res .= '<option value="'.$sup->id.'">'.$sup->name.'</option>';
            endforeach;
        endif;

        return response()->json(['res' => $res], 200);
    }

    public function storeHour(StudentWorkPlacementHourRequest $request){
        $student_id = $request->student_id;

        $workPlacement = StudentWorkPlacement::create([
            'student_id' => $student_id,
            'company_id' => $request->company_id,
            'company_supervisor_id' => $request->company_supervisor_id,
            'start_date' => (isset($request->start_date) && !empty($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null),
            'end_date' => (isset($request->end_date) && !empty($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null),
            'hours' => $request->hours,
            'contract_type' => $request->contract_type,

            'created_by' => auth()->user()->id
        ]);

        return response()->json(['res' => 'Success'], 200);
    }

    public function hourList(Request $request){
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $student_id = (isset($request->student_id) && $request->student_id > 0 ? $request->student_id : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentWorkPlacement::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'company' => (isset($list->company->name) && !empty($list->company->name) ? $list->company->name : ''),
                    'supervisor' => (isset($list->supervisor->name) && !empty($list->supervisor->name) ? $list->supervisor->name : ''),
                    'start_date' => (isset($list->start_date) && !empty($list->start_date) ? date('jS M, Y', strtotime($list->start_date)) : ''),
                    'end_date' => (isset($list->end_date) && !empty($list->end_date) ? date('jS M, Y', strtotime($list->end_date)) : ''),
                    'hours' => $list->hours,
                    'contract_type' => $list->contract_type,

                    'created_by'=> (isset($list->user->employee->full_name) && !empty($list->user->employee->full_name) ? $list->user->employee->full_name : 'Unknown Employee'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS M, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function editHour($id){
        $workplacement = StudentWorkPlacement::find($id);
        $company_id = $workplacement->company_id;
        $supervisor_id = $workplacement->company_supervisor_id;

        $supervisor_html = '<option value="">Please Select</option>';

        $supervisors = CompanySupervisor::where('company_id', $company_id)->orderBy('name', 'ASC')->get();
        if($supervisors->count() > 0):
            foreach($supervisors as $sup):
                $supervisor_html .= '<option '.($supervisor_id == $sup->id ? 'selected' : '').' value="'.$sup->id.'">'.$sup->name.'</option>';
            endforeach;
        endif;
        $workplacement['supervisor_html'] = $supervisor_html;

        return response()->json(['res' => $workplacement], 200);
    }

    public function updateHour(StudentWorkPlacementHourRequest $request){
        $student_id = $request->student_id;
        $id = $request->id;

        $workPlacement = StudentWorkPlacement::where('id', $id)->update([
            'company_id' => $request->company_id,
            'company_supervisor_id' => $request->company_supervisor_id,
            'start_date' => (isset($request->start_date) && !empty($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null),
            'end_date' => (isset($request->end_date) && !empty($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null),
            'hours' => $request->hours,
            'contract_type' => $request->contract_type,

            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['res' => 'Success'], 200);
    }

    public function destroyHour($id) {
        $studentWorkPlacement = StudentWorkPlacement::find($id)->delete();
        return response()->json(['res' => 'Success'], 200);
    }

    public function restoreHour(Request $request) {
        $data = StudentWorkPlacement::where('id', $request->row_id)->withTrashed()->restore();

        return response()->json(['res' => 'Success'], 200);
    }
}
