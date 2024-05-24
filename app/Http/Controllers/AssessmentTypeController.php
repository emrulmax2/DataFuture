<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssessmentTypeRequest;
use App\Http\Requests\UpdateAssessmentTypeRequest;
use App\Models\AssessmentType;
use Illuminate\Http\Request;

class AssessmentTypeController extends Controller
{
    public function index(){
        return view('pages.settings.assessmenttype.index', [
            'title' => 'Awarding Body - London Churchill College',
            'subtitle' => 'Course Parameters',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Awarding Body', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $total_rows = $count = AssessmentType::count();
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

        $query = AssessmentType::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;
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
                    'code' => $list->code,
                    'is_active' => $list->is_active,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function show($id)
    {
        return view('pages.settings.assessmenttype.show', [
            'title' => 'Awarding Body - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Awarding Body', 'href' => route('awardingbody')],
                ['label' => 'Awarding Body Details', 'href' => 'javascript:void(0);']
            ],
            'Term Type' => AssessmentType::find($id),
        ]);
    }

    public function store(StoreAssessmentTypeRequest $request){
        $data = AssessmentType::create([
            'name'=> $request->name,
            'code'=> $request->code,
            'is_active'=> (isset($request->is_active) ? $request->is_active : 0),
            'created_by' => auth()->user()->id
        ]);

        return response()->json($data);
    }

    public function edit($id){
        $data = AssessmentType::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(UpdateAssessmentTypeRequest $request, AssessmentType $dataId){
        $data = AssessmentType::where('id', $request->id)->update([
            'name'=> $request->name,
            'code'=> $request->code,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json($data);


        if($data->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }
    }

    public function destroy($id){
        $data = AssessmentType::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = AssessmentType::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
