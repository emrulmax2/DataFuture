<?php

namespace App\Http\Controllers\Settings\Studentoptions;

use App\Exports\ResultGradeExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResultGradeRequest;
use App\Http\Requests\ResultGradeUpdateRequest;
use App\Imports\ResultGradeImport;
use App\Models\Grade;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ResultGradeController extends Controller
{
    public function index()
    {
        return view('pages.settings.studentoption.grades.index', [
            'title' => 'Result Grades - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Result Grades', 'href' => 'javascript:void(0);']
            ],
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

        $query = Grade::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
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
                    'name' => $list->name,
                    'code' => $list->code,
                    'turnitin_grade' => $list->turnitin_grade,
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(ResultGradeRequest $request){
        $data = Grade::create([
            'name'=> $request->name,
            'code'=> (isset($request->code) ? $request->code : null),
            'turnitin_grade'=> (isset($request->turnitin_grade) ? $request->turnitin_grade : null),
            'active'=> (isset($request->active) && $request->active > 0 ? $request->active : 0),
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = Grade::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(ResultGradeUpdateRequest $request){      
        $data = Grade::where('id', $request->id)->update([
            'name'=> $request->name,
            'code'=> (isset($request->code) ? $request->code : null),
            'turnitin_grade'=> (isset($request->turnitin_grade) ? $request->turnitin_grade : null),
            'active'=> (isset($request->active) && $request->active > 0 ? $request->active : 0),
            'updated_by' => auth()->user()->id
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = Grade::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = Grade::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $title = Grade::find($id);
        $active = (isset($title->active) && $title->active == 1 ? 0 : 1);

        Grade::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }

    public function export(Request $request)
    {
        return Excel::download(new ResultGradeExport(), 'kin-relations.csv');        
    }

    public function import(Request $request) {
        $file = $request->file('file');
        
        Excel::import(new ResultGradeImport(),$file);
        return response()->json(['message' => 'Data Uploaded!'], 202);
    }
}
