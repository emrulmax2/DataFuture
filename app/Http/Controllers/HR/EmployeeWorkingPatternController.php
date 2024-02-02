<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeWorkPatternRequest;
use App\Http\Requests\EmployeeWorkPatterUpdateRequest;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\EmployeeWorkingPatternPay;
use Illuminate\Http\Request;

class EmployeeWorkingPatternController extends Controller
{
    public function list(Request $request){
        $status = (isset($request->status) ? $request->status : 1);
        $employee_id = (isset($request->employee_id) && $request->employee_id > 0 ? $request->employee_id : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeWorkingPattern::orderByRaw(implode(',', $sorts))->where('employee_id', $employee_id);
        if($status == 2):
            $query->onlyTrashed();
        else:
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
                    'effective_from' => date('jS M, Y', strtotime($list->effective_from)),
                    'end_to' => (!empty($list->end_to) ? date('jS M, Y', strtotime($list->end_to)) : ''),
                    'contracted_hour' => $list->contracted_hour,
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at,
                    'has_days' => (isset($list->patterns) ? $list->patterns->count() : 0),
                    'has_pays' => (isset($list->pays) ? $list->pays->count() : 0)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(EmployeeWorkPatternRequest $request){
        $employee_id = $request->employee_id;

        $active = (isset($request->active) && $request->active > 0 ? $request->active : 0);
        $salary = (isset($request->salary) ? $request->salary : 0);
        $hourlyRate = (isset($request->hourly_rate) ? $request->hourly_rate : 0);
        $effectiveFrom = (isset($request->effective_from) && !empty($request->effective_from) ? date('Y-m-d', strtotime($request->effective_from)) : null);
        $endTo = (isset($request->end_to) && !empty($request->end_to) ? date('Y-m-d', strtotime($request->end_to)) : NULL);

        $data = [];
        $data['employee_id'] = $employee_id;
        $data['effective_from'] = $effectiveFrom;
        $data['end_to'] = $endTo;
        $data['contracted_hour'] = (isset($request->contracted_hour) ? $request->contracted_hour : null);
        $data['active'] = $active;
        $data['created_by'] = auth()->user()->id;

        $pattern = EmployeeWorkingPattern::create($data);
        if($pattern):
            $data = [];
            $data['employee_working_pattern_id'] = $pattern->id;
            $data['effective_from'] = $effectiveFrom;
            $data['end_to'] = $endTo;
            $data['salary'] = $salary;
            $data['hourly_rate'] = $hourlyRate;
            $data['active'] = $active;
            $data['created_by'] = auth()->user()->id;

            EmployeeWorkingPatternPay::create($data);
            if($active == 1):
                EmployeeWorkingPattern::where('employee_id', $employee_id)->where('id', '!=', $pattern->id)->where('active', 1)->update(['active' => 0]);
            endif;
        endif;

        return response()->json(['msg' => 'Data successfully inserted.'], 200);
    }

    public function edit(Request $request){
        $id = $request->editId;
        $pattern = EmployeeWorkingPattern::find($id);
        $pattern['efffected_from_modified'] = (isset($pattern->effective_from) && !empty($pattern->effective_from) ? date('Y-m-d', strtotime($pattern->effective_from)) : '');

        return response()->json(['res' => $pattern], 200);
    }


    public function update(EmployeeWorkPatterUpdateRequest $request){
        $employee_id = $request->employee_id;
        $id = $request->id;

        $active = (isset($request->active) && $request->active > 0 ? $request->active : 0);
        $end_to = (isset($request->end_to) && !empty($request->end_to) ? date('Y-m-d', strtotime($request->end_to)) : Null);
        $active = (!empty($end_to) && $end_to < date('Y-m-d') ? 0 : $active);

        $data = [];
        $data['employee_id'] = $employee_id;
        $data['effective_from'] = (isset($request->effective_from) && !empty($request->effective_from) ? date('Y-m-d', strtotime($request->effective_from)) : null);
        $data['end_to'] = (isset($request->end_to) && !empty($request->end_to) ? date('Y-m-d', strtotime($request->end_to)) : NULL);
        $data['contracted_hour'] = (isset($request->contracted_hour) ? $request->contracted_hour : null);
        $data['active'] = $active;
        $data['updated_by'] = auth()->user()->id;

        EmployeeWorkingPattern::where('id', $id)->update($data);
        if($active == 1):
            EmployeeWorkingPattern::where('employee_id', $employee_id)->where('id', '!=', $id)->where('active', 1)->update(['active' => 0]);
        endif;

        return response()->json(['msg' => 'Data successfully inserted.'], 200);
    }

    public function destroy($id){
        EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $id)->delete();
        $data = EmployeeWorkingPattern::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $id)->withTrashed()->restore();
        $data = EmployeeWorkingPattern::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
