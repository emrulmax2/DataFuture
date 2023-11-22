<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeWorkingPatternPayRequest;
use App\Models\Employee;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternPay;
use Illuminate\Http\Request;

class EmployeeWorkingPatternPayController extends Controller
{

    public function store(EmployeeWorkingPatternPayRequest $request){
        $employee_id = $request->employee_id;
        $employee_working_pattern_id = $request->employee_working_pattern_id;

        $salary = (isset($request->salary) ? $request->salary : 0);
        $hourlyRate = (isset($request->hourly_rate) ? $request->hourly_rate : 0);
        $effectiveFrom = (isset($request->effective_from) && !empty($request->effective_from) ? date('Y-m-d', strtotime($request->effective_from)) : null);
        $endTo = (isset($request->end_to) && !empty($request->end_to) ? date('Y-m-d', strtotime($request->end_to)) : NULL);

        $data = [];
        $data['employee_working_pattern_id'] = $employee_working_pattern_id;
        $data['effective_from'] = $effectiveFrom;
        $data['end_to'] = $endTo;
        $data['salary'] = $salary;
        $data['hourly_rate'] = $hourlyRate;
        $data['active'] = (isset($request->active) && $request->active > 0 ? $request->active : 0);
        $data['created_by'] = auth()->user()->id;

        EmployeeWorkingPatternPay::create($data);

        return response()->json(['res' => 'Employee Working Pattern Pay Details Successfully inserted.'], 200);
    }

    public function list(Request $request){
        $employeeWorkingPatternId = (isset($request->employeeWorkingPatternId) && $request->employeeWorkingPatternId > 0 ? $request->employeeWorkingPatternId : 0);
        
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeWorkingPatternPay::orderByRaw(implode(',', $sorts))->where('employee_working_pattern_id', $employeeWorkingPatternId);

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
                    'effective_from' => (isset($list->effective_from) && !empty($list->effective_from) ? date('d-m-Y', strtotime($list->effective_from)) : ''),
                    'end_to' => (isset($list->end_to) && !empty($list->end_to) ? date('d-m-Y', strtotime($list->end_to)) : ''),
                    'salary' => $list->salary,
                    'hourly_rate' => $list->hourly_rate,
                    'active' => $list->active,
                    'employee_working_pattern_id' => $list->employee_working_pattern_id
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function edit(Request $request){
        $payId = $request->payId;
        $patternId = $request->patternId;

        $pay = EmployeeWorkingPatternPay::with('pattern')->where('id', $payId)->where('employee_working_pattern_id', $patternId)->get()->first();
        $pay['efffected_from_modified'] = (isset($pay->effective_from) && !empty($pay->effective_from) ? date('Y-m-d', strtotime($pay->effective_from)) : '');

        return response()->json(['res' => $pay], 200);
    }

    public function update(EmployeeWorkingPatternPayRequest $request){
        $employee_id = $request->employee_id;
        $payId = $request->id;
        $employee_working_pattern_id = $request->employee_working_pattern_id;

        $salary = (isset($request->salary) ? $request->salary : 0);
        $hourlyRate = (isset($request->hourly_rate) ? $request->hourly_rate : 0);
        $effectiveFrom = (isset($request->effective_from) && !empty($request->effective_from) ? date('Y-m-d', strtotime($request->effective_from)) : null);
        $endTo = (isset($request->end_to) && !empty($request->end_to) ? date('Y-m-d', strtotime($request->end_to)) : NULL);

        $data = [];
        $data['employee_working_pattern_id'] = $employee_working_pattern_id;
        $data['effective_from'] = $effectiveFrom;
        $data['end_to'] = $endTo;
        $data['salary'] = $salary;
        $data['hourly_rate'] = $hourlyRate;
        $data['active'] = (isset($request->active) && $request->active > 0 ? $request->active : 0);
        $data['updated_by'] = auth()->user()->id;

        EmployeeWorkingPatternPay::where('id', $payId)->where('employee_working_pattern_id', $employee_working_pattern_id)->update($data);

        $res = [];
        $res['end'] = (!empty($endTo) ? 1 : 2);
        $res['employee_id'] = $employee_id;
        $res['employee_working_pattern_id'] = $employee_working_pattern_id;
        return response()->json(['res' => $res], 200);
    }

    public function getPattern(Request $request){
        $pattern_id = $request->pattern_id;
        $pattern = EmployeeWorkingPattern::find($pattern_id);

        return response()->json(['res' => $pattern], 200);
    }
}
