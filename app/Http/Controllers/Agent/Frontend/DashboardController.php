<?php

namespace App\Http\Controllers\Agent\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AgentApplicationCheck;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userData = Auth::guard('agent')->user();
        $data = AgentApplicationCheck::where('agent_user_id',$userData->id)->whereNull("applicant_id")->get();
        return view('pages.agent.index', [
            'title' => 'Agent Dashboard - LCC Data Future Managment',
            'breadcrumbs' => [],
            'user' => $userData,
            'recentData' => $data,
            'totalRecentApplication' => count($data)
        ]);
    }

    public function list(Request $request){
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        $userData = Auth::guard('agent')->user();
        $query = Applicant::orderByRaw(implode(',', $sorts))->where('agent_user_id', $userData->id);

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
                
                $agentCheck = AgentApplicationCheck::where('agent_user_id',$userData->id)->where("email",$list->users->email)->where("mobile",$list->users->phone)->get()->first();
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->title->name.' '.$list->first_name.' '.$list->last_name,
                    'dob' => $list->date_of_birth,
                    'applicationCheck' => isset($agentCheck->id) && !empty($agentCheck->id) ? $agentCheck->id : '',
                    'gender' => isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : '',
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
