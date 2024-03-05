<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmpoyeePenssionSchemeRequest;
use App\Models\EmployeeArchive;
use App\Models\EmployeePenssionScheme;
use Illuminate\Http\Request;

class EmployeePenssionSchemeController extends Controller
{
    public function list(Request $request){
        $employee_id = (isset($request->employee_id) && $request->employee_id > 0 ? $request->employee_id : 0);
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeePenssionScheme::orderByRaw(implode(',', $sorts))->where('employee_id', $employee_id);
        if(!empty($queryStr)):
            $query->where('joining_date','LIKE','%'.$queryStr.'%');
            $query->where('date_left','LIKE','%'.$queryStr.'%');
        endif;
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
                    'penssion' => (isset($list->penssion->name) ? $list->penssion->name : ''),
                    'joining_date' => (isset($list->joining_date) && !empty($list->joining_date) ? date('jS M, Y', strtotime($list->joining_date)) : ''),
                    'date_left' => (isset($list->date_left) && !empty($list->date_left) ? date('d-m-Y', strtotime($list->date_left)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(EmpoyeePenssionSchemeRequest $request){
        $employee_id = $request->employee_id;

        $adata = [];
        $adata['employee_id'] = $employee_id;
        $adata['employee_info_penssion_scheme_id'] = (isset($request->employee_info_penssion_scheme_id) && !empty($request->employee_info_penssion_scheme_id) ? $request->employee_info_penssion_scheme_id : null);
        $adata['joining_date'] = (isset($request->joining_date) && !empty($request->joining_date) ? date('Y-m-d', strtotime($request->joining_date)) : null);
        $adata['date_left'] = (isset($request->date_left) && !empty($request->date_left) ? date('Y-m-d', strtotime($request->date_left)) : null);
        $adata['created_by'] = auth()->user()->id;

        EmployeePenssionScheme::create($adata);

        return response()->json(['message' => 'Data successfully inserted'], 200);
    }

    public function edit(Request $request){
        $editId = $request->editId;
        $bank = EmployeePenssionScheme::find($editId);

        return response()->json(['res' => $bank], 200);
    }

    public function update(EmpoyeePenssionSchemeRequest $request){
        $employee_id = $request->employee_id;
        $id = $request->id;
        $penssionSchemeOld = EmployeePenssionScheme::find($id);

        $adata = [];
        $adata['employee_id'] = $employee_id;
        $adata['employee_info_penssion_scheme_id'] = (isset($request->employee_info_penssion_scheme_id) && !empty($request->employee_info_penssion_scheme_id) ? $request->employee_info_penssion_scheme_id : null);
        $adata['joining_date'] = (isset($request->joining_date) && !empty($request->joining_date) ? date('Y-m-d', strtotime($request->joining_date)) : null);
        $adata['date_left'] = (isset($request->date_left) && !empty($request->date_left) ? date('Y-m-d', strtotime($request->date_left)) : null);
        $adata['updated_by'] = auth()->user()->id;

        $employeePenssion = EmployeePenssionScheme::find($id);
        $employeePenssion->fill($adata);
        $changes = $employeePenssion->getDirty();
        $employeePenssion->save();

        if($employeePenssion->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['employee_id'] = $employee_id;
                $data['table'] = 'employee_penssion_schemes';
                $data['row_id'] = $id;
                $data['field_name'] = $field;
                $data['field_value'] = $penssionSchemeOld->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                EmployeeArchive::create($data);
            endforeach;
        endif;

        return response()->json(['message' => 'Data successfully updated'], 200);
    }

    public function destroy($id){
        $data = EmployeePenssionScheme::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = EmployeePenssionScheme::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
