<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternPay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Maatwebsite\Excel\Facades\Excel;

class SickLeaveController extends Controller
{
    public function index(){
        return view('pages.hr.portal.reports.sick', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Reports', 'href' => route('hr.portal.employment.reports.show')],
                ['label' => 'Sick Leave', 'href' => 'javascript:void(0);']
            ],
            'employees' => Employee::where('status', 1)->whereHas('payment', function($q){
                                $q->where('subject_to_clockin', 'Yes');
                            })->orderBy('first_name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        $employee_id = (!empty($request->employee_id) ? $request->employee_id : []);
        $no_of_days = (!empty($request->no_of_days) && $request->no_of_days > 0 ? $request->no_of_days : 0);
        $from_date = (!empty($request->from_date) ? $request->from_date : null);
        $to_date = (!empty($request->to_date) ? $request->to_date : null);

        $toDate = $to_date ? Carbon::parse($to_date)->endOfDay() : Carbon::today()->endOfDay();
        if ($no_of_days) {
            $fromDate = Carbon::today()->subDays($no_of_days)->startOfDay();
        } elseif ($from_date) {
            $fromDate = Carbon::parse($from_date)->startOfDay();
        } else {
            $fromDate = null;
        }

        $query = DB::table('employee_leaves')
                ->join('employees as emp', 'emp.id', '=', 'employee_leaves.employee_id')
                ->join('employee_leave_days', 'employee_leave_days.employee_leave_id', '=', 'employee_leaves.id')
                ->where('employee_leaves.leave_type', 3)->where('employee_leaves.status', 'Approved')
                ->where('employee_leave_days.status', 1)->where('employee_leaves.days', '>', 0);
        if ($employee_id): $query->whereIn('employee_leaves.employee_id', $employee_id); endif;

        if ($fromDate): $query->whereBetween('employee_leave_days.leave_date', [$fromDate->toDateString(), $toDate->toDateString()]); endif;
        $query->select(
                'employee_leaves.employee_id',
                'emp.first_name', 'emp.last_name', 
                DB::raw('COUNT(employee_leave_days.leave_date) AS total_days'),
                DB::raw('GROUP_CONCAT(employee_leave_days.leave_date ORDER BY employee_leave_days.leave_date SEPARATOR ", ") AS dates')
            )->groupBy('employee_leaves.employee_id');
            

        $total_rows = $query->get()->count();
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
                $employee_id = $list->employee_id;
                $employeePay = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)
                                ->orderByDesc('id')
                                ->first();
                $data[] = [
                    'sl' => $i,
                    'employee_id' => $list->employee_id,
                    'employee_name' => $list->first_name.' '.$list->last_name,
                    'contracted_hour' => (isset($employeePay->contracted_hour) && !empty($employeePay->contracted_hour) ? $employeePay->contracted_hour : '00:00'),
                    'no_of_days' => $list->total_days,
                    'dates' => $list->dates
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'rows' => $total_rows]);
    }

    public function exportList(Request $request){
        $employee_id = (!empty($request->employee_id) ? $request->employee_id : []);
        $no_of_days = (!empty($request->no_of_days) && $request->no_of_days > 0 ? $request->no_of_days : 0);
        $from_date = (!empty($request->from_date) ? $request->from_date : null);
        $to_date = (!empty($request->to_date) ? $request->to_date : null);

        $toDate = $to_date ? Carbon::parse($to_date)->endOfDay() : Carbon::today()->endOfDay();
        if ($no_of_days) {
            $fromDate = Carbon::today()->subDays($no_of_days)->startOfDay();
        } elseif ($from_date) {
            $fromDate = Carbon::parse($from_date)->startOfDay();
        } else {
            $fromDate = null;
        }

        $query = DB::table('employee_leaves')
                ->join('employees as emp', 'emp.id', '=', 'employee_leaves.employee_id')
                ->join('employee_leave_days', 'employee_leave_days.employee_leave_id', '=', 'employee_leaves.id')
                ->where('employee_leaves.leave_type', 3)->where('employee_leaves.status', 'Approved')
                ->where('employee_leave_days.status', 1)->where('employee_leaves.days', '>', 0);
        if ($employee_id): $query->whereIn('employee_leaves.employee_id', $employee_id); endif;

        if ($fromDate): $query->whereBetween('employee_leave_days.leave_date', [$fromDate->toDateString(), $toDate->toDateString()]); endif;
        $Query = $query->select(
                'employee_leaves.employee_id',
                'emp.first_name', 'emp.last_name', 
                DB::raw('COUNT(employee_leave_days.leave_date) AS total_days'),
                DB::raw('GROUP_CONCAT(employee_leave_days.leave_date ORDER BY employee_leave_days.leave_date SEPARATOR ", ") AS dates')
            )->groupBy('employee_leaves.employee_id')->get();
            

        $theCollection = [];
        $theCollection[1][] = "Employee";
        $theCollection[1][] = "Contracted Hour";
        $theCollection[1][] = "No Of Days";
        $theCollection[1][] = "Leave Days";
        

        $row = 2;
        if(!empty($Query)):
            foreach($Query as $list):
                $employee_id = $list->employee_id;
                $employeePay = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)
                                ->orderByDesc('id')
                                ->first();
                $theCollection[$row][] = $list->first_name.' '.$list->last_name;
                $theCollection[$row][] = (isset($employeePay->contracted_hour) && !empty($employeePay->contracted_hour) ? $employeePay->contracted_hour : '00:00');
                $theCollection[$row][] = $list->total_days;
                $theCollection[$row][] = $list->dates;

                $row++;
            endforeach;
        endif;
        
        $report_title = 'Sick_leave_report.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $report_title);
    }
}
