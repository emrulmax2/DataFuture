<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
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
                    'joining_date' => (isset($list->joining_date) && !empty($list->joining_date) ? date('d-m-Y', strtotime($list->joining_date)) : ''),
                    'date_left' => (isset($list->date_left) && !empty($list->date_left) ? date('d-m-Y', strtotime($list->date_left)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
