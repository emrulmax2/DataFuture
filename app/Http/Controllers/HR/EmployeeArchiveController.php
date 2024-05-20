<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeArchive;
use App\Models\Employment;
use Illuminate\Http\Request;

class EmployeeArchiveController extends Controller
{
    public function index($id){
        $employee = Employee::find($id);
        $employment = Employment::where("employee_id",$id)->get()->first();

        return view('pages.employee.profile.archive',[
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [],
            'employee' => $employee,
            'employment' => $employment
        ]);
    }

    public function list(Request $request){
        $employee_id = (isset($request->employeeId) && !empty($request->employeeId) ? $request->employeeId : 0);
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeArchive::orderByRaw(implode(',', $sorts))->where('employee_id', $employee_id);
        if(!empty($queryStr)):
            $query->where('table','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_value','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_new_value','LIKE','%'.$queryStr.'%');
        endif;
        $query->where(function($q){
            $q->where('field_name', '!=', 'updated_by')->where('field_name', '!=', 'created_by')->where('field_name', '!=', 'created_at')
                ->where('field_name', '!=', 'updated_at');
        });
        $query->where(function($q){
            $q->whereNotNull('field_new_value')->whereNotNull('field_value');
        });

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
        //dd($Query);
        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $docURL = '';
                if(isset($list->employee_document_id) && isset($list->document)):
                    $docURL = (isset($list->document->current_file_name) && !empty($list->document->current_file_name) ? Storage::disk('s3')->url('public/employees/notes/'.$list->document->current_file_name) : '');
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'table' => (!empty($list->table) ? ucfirst(str_replace('_', ' ', $list->table)) : ''),
                    'row_id' => ($list->row_id > 0 ? ' #id: '.$list->row_id : ''),
                    'field_name' => $list->field_name,
                    'field_value' => $list->field_value,
                    'field_new_value' => $list->field_new_value,

                    'created_by'=> (isset($list->cuser->employee->full_name) ? $list->cuser->employee->full_name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
