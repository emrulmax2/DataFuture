<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeBankDetailRequest;
use App\Models\Employee;
use App\Models\EmployeeArchive;
use App\Models\EmployeeBankDetail;
use Illuminate\Http\Request;

class EmployeeBankDetailController extends Controller
{
    public function list(Request $request){
        $employee_id = (isset($request->employee_id) && $request->employee_id > 0 ? $request->employee_id : 0);
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 3);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeBankDetail::orderByRaw(implode(',', $sorts))->where('employee_id', $employee_id);
        if(!empty($queryStr)):
            $query->where('beneficiary','LIKE','%'.$queryStr.'%');
            $query->where('sort_code','LIKE','%'.$queryStr.'%');
            $query->where('ac_no','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        elseif($status == 1 || $status == 0):
            $query->where('active', $status);
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
                    'beneficiary' => $list->beneficiary,
                    'sort_code' => $list->sort_code,
                    'ac_no' => $list->ac_no,
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(EmployeeBankDetailRequest $request){
        $employee_id = $request->employee_id;
        $active = (isset($request->active) && $request->active > 0 ? $request->active : 0);

        $request->request->remove('active');
        $request->request->add(['active' => $active, 'created_by' => auth()->user()->id]);

        $bank = EmployeeBankDetail::create($request->all());
        $bankId = $bank->id;

        if($active == 1){
            EmployeeBankDetail::where('id', '!=', $bankId)->where('employee_id', $employee_id)->where('active', 1)->update(['active' => 0]);
        }

        return response()->json(['msg' => 'Bank Successfully inserted'], 200);
    }


    public function edit(Request $request){
        $editId = $request->editId;
        $bank = EmployeeBankDetail::find($editId);

        return response()->json(['res' => $bank], 200);
    }

    public function update(EmployeeBankDetailRequest $request){
        $id = $request->id;
        $employee_id = $request->employee_id;
        $active = (isset($request->active) && $request->active > 0 ? $request->active : 0);
        $bankOld = EmployeeBankDetail::find($id);

        $request->request->remove('active');
        $request->request->add(['active' => $active, 'updated_by' => auth()->user()->id]);

        $bank = EmployeeBankDetail::find($id);
        $bank->fill($request->input());
        $changes = $bank->getDirty();
        $bank->save();

        if($bank->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['employee_id'] = $employee_id;
                $data['table'] = 'employee_bank_details';
                $data['row_id'] = $id;
                $data['field_name'] = $field;
                $data['field_value'] = $bankOld->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                EmployeeArchive::create($data);
            endforeach;
        endif;

        if($active == 1){
            EmployeeBankDetail::where('id', '!=', $id)->where('employee_id', $employee_id)->where('active', 1)->update(['active' => 0]);
        }

        return response()->json(['msg' => 'Bank Successfully updated'], 200);
    }

    public function destroy($id){
        $data = EmployeeBankDetail::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = EmployeeBankDetail::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function changeStatus($id){
        $title = EmployeeBankDetail::find($id);
        $active = (isset($title->active) && $title->active == 1 ? 0 : 1);

        EmployeeBankDetail::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        if($active == 1):
            EmployeeBankDetail::where('id', '!=', $id)->where('active', 1)->update(['active' => 0]);
        endif;

        return response()->json(['message' => 'Status successfully updated'], 200);
    }
}
