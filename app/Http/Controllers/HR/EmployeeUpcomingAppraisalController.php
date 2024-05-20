<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAppraisal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeUpcomingAppraisalController extends Controller
{
    public function index(){
        return view('pages.hr.portal.upcoming-appraisal', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Upcoming Appraisal', 'href' => 'javascript:void(0);']
            ],
            'activeEmployees' => Employee::where('status', 1)->orderBy('first_name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        $expireDate = Carbon::now()->addDays(60)->format('Y-m-d');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'due_on', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeAppraisal::orderByRaw(implode(',', $sorts))
                ->where('due_on', '<=', $expireDate)->whereNull('completed_on')
                ->whereHas('employee', function($q){
                    $q->where('status', 1);
                });
        
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
                    'url' => route('employee.appraisal', $list->employee_id),
                    'photo_url' => $list->employee->photo_url,
                    'name' => $list->employee->first_name.' '.$list->employee->last_name,
                    'designation' => (isset($list->employee->employment->employeeJobTitle->name) ? $list->employee->employment->employeeJobTitle->name : ''),
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
}
