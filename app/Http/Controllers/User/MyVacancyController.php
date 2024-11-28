<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\HrVacancy;
use App\Models\User;
use Illuminate\Http\Request;

class MyVacancyController extends Controller
{
    public function index(){
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $employeeId = $employee->id;
        $userData = User::find($employee->user_id);
        $employment = Employment::where("employee_id", $employeeId)->get()->first();

        return view('pages.users.my-account.my-vacancies',[
            'title' => 'Vacancies - London Churchill College',
            'breadcrumbs' => [],
            'user' => $userData,
            'employee' => $employee,
            'employment' => $employment,
            'employees' => Employee::where('status', 1)->orderBy('first_name', 'ASC')->get(),
            'vacanties' => HrVacancy::where('active', 1)->get()->count()
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = HrVacancy::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('title','LIKE','%'.$queryStr.'%');
        endif;
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
                    'title' => $list->title,
                    'type' => (isset($list->type->name) && !empty($list->type->name) ? $list->type->name : ''),
                    'link' => (isset($list->link) && !empty($list->link) ? $list->link : ''),
                    'date' => (isset($list->date) && !empty($list->date) ? date('jS F, Y', strtotime($list->date)) : ''),
                    'document_url' => (isset($list->document_url) && !empty($list->document_url) ? $list->document_url : ''),
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'created_by'=> (isset($list->user->employee->full_name) && !empty($list->user->employee->full_name) ? $list->user->employee->full_name : $list->user->name),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
