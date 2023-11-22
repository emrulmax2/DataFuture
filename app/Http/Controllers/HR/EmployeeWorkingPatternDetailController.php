<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EmployeeWorkingPatternDetail;
use Illuminate\Http\Request;

class EmployeeWorkingPatternDetailController extends Controller
{
    public function list(Request $request){
        $employeeWorkingPatternId = (isset($request->employeeWorkingPatternId) && $request->employeeWorkingPatternId > 0 ? $request->employeeWorkingPatternId : 0);
        
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'day', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeWorkingPatternDetail::orderByRaw(implode(',', $sorts))->where('employee_working_pattern_id', $employeeWorkingPatternId);

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
                    'employee_working_pattern_id' => $list->employee_working_pattern_id,
                    'day' => $list->day,
                    'day_name' => $list->day_name,
                    'start' => $list->start,
                    'end' => $list->end,
                    'paid_br' => $list->paid_br,
                    'unpaid_br' => $list->unpaid_br,
                    'total' => $list->total
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(Request $request){
        $employee_id = $request->employee_id;
        $employee_working_pattern_id = $request->employee_working_pattern_id;
        $dayNames = ['1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fir', '6' => 'Sat', '7' => 'Sun'];

        $weekDays = $request->weekDays;
        $weekTotal = $request->weekTotal;
        $pattern = $request->pattern;

        if(!empty($weekDays)):
            foreach($weekDays as $wd):
                if(isset($pattern[$wd]) && !empty($pattern[$wd])):
                    $dayPattern = $pattern[$wd];
                    $dayID = $dayPattern['day'];

                    $data = [];
                    $data['employee_working_pattern_id'] = $employee_working_pattern_id;
                    $data['day'] = $dayID;
                    $data['day_name'] = $dayNames[$dayID];
                    $data['start'] = $dayPattern['start'];
                    $data['end'] = $dayPattern['end'];
                    $data['paid_br'] = $dayPattern['paid_br'];
                    $data['unpaid_br'] = $dayPattern['unpaid_br'];
                    $data['total'] = $dayPattern['total'];

                    $patternDayRow = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $employee_working_pattern_id)->where('day', $dayID)->get()->first();
                    if(!empty($patternDayRow) && isset($patternDayRow->id) && $patternDayRow->id > 0):
                        $data['updated_by'] = auth()->user()->id;
                        EmployeeWorkingPatternDetail::where('id', $patternDayRow->id)->update($data);
                    else:
                        $data['created_by'] = auth()->user()->id;
                        EmployeeWorkingPatternDetail::create($data);
                    endif;
                endif;
            endforeach;

            EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $employee_working_pattern_id)->whereNotIn('day', $weekDays)->forceDelete();

            return response()->json(['msg' => 'Successfully inserted'], 200);
        else:
            return response()->json(['msg' => 'Successfully inserted'], 422);
        endif;
    }

    public function edit(Request $request){
        $employeeWorkingPatternId = $request->employeeWorkingPatternId;

        $html = '';
        $totalHour = 0;
        $days = [];
        $empPatternDetails = EmployeeWorkingPatternDetail::orderBy('day', 'ASC')->where('employee_working_pattern_id', $employeeWorkingPatternId)->get();
        if(!empty($empPatternDetails)):
            foreach($empPatternDetails as $ptd):
                $days[] = $ptd->day;
                $hourArr = explode(':', $ptd->total);
                $totalHour += (isset($hourArr[0]) && $hourArr[0] > 0 ? (int) $hourArr[0] * 60 : 0);
                $totalHour += (isset($hourArr[1]) && $hourArr[1] > 0 ? (int) $hourArr[1] : 0);

                $html .= '<tr data-order="'.$ptd->day.'" class="patternRow patternRow_'.$ptd->day.'" id="patternRow_'.$ptd->day.'">';
                    $html .= '<td>';
                        $html .= $ptd->day_name;
                        $html .= '<input type="hidden" value="'.$ptd->day.'" name="pattern['.$ptd->day.'][day]"/>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<input type="text" placeholder="00:00" class="form-control w-full timeMask startTime" minlength="5" maxlength="5" value="'.$ptd->start.'" name="pattern['.$ptd->day.'][start]"/>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<input type="text" placeholder="00:00" class="form-control w-full timeMask endTime" minlength="5" maxlength="5"  value="'.$ptd->end.'"name="pattern['.$ptd->day.'][end]"/>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<input type="text" placeholder="00:00" class="form-control w-full timeMask paidBr" minlength="5" maxlength="5" value="'.$ptd->paid_br.'" name="pattern['.$ptd->day.'][paid_br]"/>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<input type="text" placeholder="00:00" class="form-control w-full timeMask unpaidBr" minlength="5" maxlength="5" value="'.$ptd->unpaid_br.'" name="pattern['.$ptd->day.'][unpaid_br]"/>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<input type="text" placeholder="00:00" class="form-control w-full timeMask rowTotal" minlength="5" readonly maxlength="5" value="'.$ptd->total.'" name="pattern['.$ptd->day.'][total]"/>';
                    $html .= '</td>';
                $html .= '</tr>';
            endforeach;
        endif;

        $hours = (intval(trim($totalHour)) / 60 >= 1) ? intval(intval(trim($totalHour)) / 60) : '00';
        $mins = (intval(trim($totalHour)) % 60 != 0) ? intval(trim($totalHour)) % 60 : '00';

        $weekTotal = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
        $weekTotal .= ':';
        $weekTotal .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;

        return response()->json(['days' => $days, 'html' => $html, 'weektotal' => $weekTotal], 200);
    }


    public function update(Request $request){
        $employee_id = $request->employee_id;
        $employee_working_pattern_id = $request->employee_working_pattern_id;
        $dayNames = ['1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fir', '6' => 'Sat', '7' => 'Sun'];

        $weekDays = $request->weekDays;
        $weekTotal = $request->weekTotal;
        $pattern = $request->pattern;

        if(!empty($weekDays)):
            foreach($weekDays as $wd):
                if(isset($pattern[$wd]) && !empty($pattern[$wd])):
                    $dayPattern = $pattern[$wd];
                    $dayID = $dayPattern['day'];

                    $data = [];
                    $data['employee_working_pattern_id'] = $employee_working_pattern_id;
                    $data['day'] = $dayID;
                    $data['day_name'] = $dayNames[$dayID];
                    $data['start'] = $dayPattern['start'];
                    $data['end'] = $dayPattern['end'];
                    $data['paid_br'] = $dayPattern['paid_br'];
                    $data['unpaid_br'] = $dayPattern['unpaid_br'];
                    $data['total'] = $dayPattern['total'];

                    $patternDayRow = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $employee_working_pattern_id)->where('day', $dayID)->get()->first();
                    if(!empty($patternDayRow) && isset($patternDayRow->id) && $patternDayRow->id > 0):
                        $data['updated_by'] = auth()->user()->id;
                        EmployeeWorkingPatternDetail::where('id', $patternDayRow->id)->update($data);
                    else:
                        $data['created_by'] = auth()->user()->id;
                        EmployeeWorkingPatternDetail::create($data);
                    endif;
                endif;
            endforeach;

            EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $employee_working_pattern_id)->whereNotIn('day', $weekDays)->forceDelete();

            return response()->json(['msg' => 'Successfully inserted'], 200);
        else:
            return response()->json(['msg' => 'Successfully inserted'], 422);
        endif;
    }
}
