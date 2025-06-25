<?php

namespace App\Http\Controllers\Settings\Studentoptions;

use App\Exports\OtherAcademicQualificationExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\OtherAcademicQualificationRequest;
use App\Imports\OtherAcademicQualificationImport;
use App\Models\OtherAcademicQualification;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OtherAcademicQualificationController extends Controller
{
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = OtherAcademicQualification::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 3):
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
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(OtherAcademicQualificationRequest $request){
        $data = OtherAcademicQualification::create([
            'name'=> $request->name,
            'active'=> (isset($request->active) && $request->active == 1 ? $request->active : '0'),
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = OtherAcademicQualification::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(OtherAcademicQualificationRequest $request){      
        $data = OtherAcademicQualification::where('id', $request->id)->update([
            'name'=> $request->name,
            'active'=> (isset($request->active) && $request->active == 1 ? $request->active : '0'),
            'updated_by' => auth()->user()->id
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = OtherAcademicQualification::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = OtherAcademicQualification::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $title = OtherAcademicQualification::find($id);
        $active = (isset($title->active) && $title->active == 1 ? 0 : 1);

        OtherAcademicQualification::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }

    public function export(Request $request)
    {
        return Excel::download(new OtherAcademicQualificationExport(), 'other-academic-qualification.csv');        
    }

    public function import(Request $request) {
        $file = $request->file('file');
        
        Excel::import(new OtherAcademicQualificationImport(),$file);
        return response()->json(['message' => 'Data Uploaded!'], 202);
    }
}
