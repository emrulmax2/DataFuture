<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendanceTermStatus;
use Illuminate\Http\Request;

class AttendanceTermStatusController extends Controller
{
    public function list(Request $request){
        $term_id = (isset($request->term_id) && $request->term_id > 0 ? $request->term_id : 0);
        $student_id = (isset($request->student_id) && $request->student_id > 0 ? $request->student_id : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentAttendanceTermStatus::orderByRaw(implode(',', $sorts))->where('student_id', $student_id)->where('term_declaration_id', $term_id);

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
                    'term' => (isset($list->term->name) && !empty($list->term->name) ? $list->term->name : ''),
                    'status' => (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                    'status_change_reason' => $list->status_change_reason,
                    'status_change_date' => (isset($list->status_change_date) && !empty($list->status_change_date) ? date('jS F, Y', strtotime($list->status_change_date)) : ''),
                    'created_by' => (isset($list->user->employee->full_name) && !empty($list->user->employee->full_name) ? $list->user->employee->full_name : $list->user->name),
                    'created_at' => (isset($list->created_at) && !empty($list->created_at) ? date('js F, Y', strtotime($list->created_at)) : ''),
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
