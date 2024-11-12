<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentFlagRequest;
use App\Models\StudentFlag;
use App\Models\StudentFlagRaiser;
use App\Models\User;

class StudentFlagController extends Controller
{
    public function index()
    {
        return view('pages.settings.flag.index', [
            'title' => 'Student Flags - London Churchill College',
            'subtitle' => 'Student Flags',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Student Flags', 'href' => 'javascript:void(0);']
            ],
            'users' => User::whereHas('employee', function($q){
                            $q->where('active', 1);
                        })->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentFlag::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%')->orWhere('color','LIKE','%'.$queryStr.'%');
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
                    'name' => $list->name,
                    'color' => $list->color,
                    'raisers' => (isset($list->raiser_tag) && !empty($list->raiser_tag) ? $list->raiser_tag : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(StudentFlagRequest $request){
        $user_ids = (isset($request->user_ids) && !empty($request->user_ids) ? $request->user_ids : []);
        $StudentFlag = StudentFlag::create([
            'name'=> $request->name,
            'color'=> $request->color,
            'created_by' => auth()->user()->id
        ]);
        if($StudentFlag && !empty($user_ids)):
            foreach($user_ids as $user):
                StudentFlagRaiser::create([
                    'student_flag_id'=> $StudentFlag->id,
                    'user_id'=> $user
                ]);
            endforeach;
        endif;

        return response()->json(['message' => 'Flag successfully inserted'], 200);
    }

    public function edit($id){
        $flag = StudentFlag::where('id', $id)->get()->first();
        $flagRaisers = StudentFlagRaiser::where('student_flag_id', $id)->get();

        $raisers = [];
        if(!empty($flagRaisers) && $flagRaisers->count() > 0):
            $i = 1;
            foreach($flagRaisers as $fr):
                $raisers[$i] = $fr->user_id;
                $i++;
            endforeach;
        endif;
        $flag['raiser_ids'] = $raisers;

        return response()->json($flag);
    }

    public function update(StudentFlagRequest $request){  
        $user_ids = (isset($request->user_ids) && !empty($request->user_ids) ? $request->user_ids : []);  
        $data = StudentFlag::where('id', $request->id)->update([
            'name'=> $request->name,
            'color'=> $request->color,
            'updated_by' => auth()->user()->id
        ]);

        StudentFlagRaiser::where('student_flag_id', $request->id)->forceDelete();
        if(!empty($user_ids)):
            foreach($user_ids as $user):
                StudentFlagRaiser::create([
                    'student_flag_id'=> $request->id,
                    'user_id'=> $user
                ]);
            endforeach;
        endif;


        return response()->json(['message' => 'Data updated'], 200);
    }

    public function destroy($id){
        $data = StudentFlag::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = StudentFlag::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
