<?php

namespace App\Http\Controllers;

use App\Models\ReportItAll;
use App\Http\Requests\StorereportItAllRequest;
use App\Http\Requests\UpdatereportItAllRequest;
use App\Models\Employee;
use App\Models\IssueType;
use App\Models\Venue;
use Illuminate\Http\Request;

class ReportAnyItForEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){

        $issueList = IssueType::where('availability','Employee')->get();
        $venues = Venue::where('active',1)->get();
        $employee = Employee::where('user_id', auth()->user()->id)->first();
        return view('pages.students.report-it.employee.index', [
            'title' => 'Report IT - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Report IT', 'href' => 'javascript:void(0);']
            ],
            'issueList' => $issueList,
            'employee' => $employee,
            'venues' => $venues,
        ]);
    }

    public function list(Request $request){

        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $employee = Employee::where('user_id', auth()->user()->id)->first();

        $total_rows = $count = ReportItAll::where('employee_id', $employee->id)->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = ReportItAll::with('employee', 'issueType', 'student')->where('employee_id', $employee->id)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            // search in employee name and student name
            $query->where(function($q) use ($queryStr) {
                $q->whereHas('employee', function($q) use ($queryStr) {
                    //concat first_name and last_name
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", '%'.$queryStr.'%');
                })->orWhereHas('student', function($q) use ($queryStr) {
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", '%'.$queryStr.'%');
                });
            });


        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        
        if($Query->isNotEmpty()):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'issue_type' => (isset($list->issueType->name) ? $list->issueType->name : ''),
                    'report_form' => (isset($list->student->first_name) ? 'Student' : (isset($list->employee->first_name) ? 'Employee' : '')),
                    'status' => ucfirst($list->status),
                    'ejt_name' => (isset($list->student->first_name) ? $list->student->registration_no : (isset($list->employee->employment->employeeJobTitle) ? $list->employee->employment->employeeJobTitle->name : '')),
                    'full_name' => (isset($list->student->first_name) ? $list->student->full_name : $list->employee->full_name),
                    'photourl' => (isset($list->student->photo_url) ? $list->student->photo_url : (isset($list->employee->photo_url) ? $list->employee->photo_url : '')),
                    'deleted_at' => $list->deleted_at,
                    'description' => $list->description,
                    'location' => $list->location,
                    'venue' => isset($list->venue) ? $list->venue->name : ''    
                ];
                $i++;
            endforeach;
        endif;
        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


}
