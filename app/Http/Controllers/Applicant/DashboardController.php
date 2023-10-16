<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userData = \Auth::guard('applicant')->user();
        
        return view('pages.applicant.index', [
            'title' => 'Applicant Dashboard - LCC Data Future Managment',
            'breadcrumbs' => [],
            'user' => $userData
        ]);
    }

    public function list(Request $request){
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Applicant::orderByRaw(implode(',', $sorts))->where('applicant_user_id', \Auth::guard('applicant')->user()->id);

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
                    'name' => $list->title->name.' '.$list->first_name.' '.$list->last_name,
                    'dob' => $list->date_of_birth,
                    'gender' => $list->gender,
                    'course' => (isset($list->course->creation->course->name) ? $list->course->creation->course->name : '').(isset($list->course->semester->name) ? ' - '.$list->course->semester->name : ''),
                    'submission_date' => $list->submission_date,
                    'status' => (!empty($list->submission_date) ? (isset($list->status->name) ? $list->status->name : 'Unknown') : 'Incomplete'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
