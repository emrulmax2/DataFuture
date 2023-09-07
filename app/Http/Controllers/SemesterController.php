<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SemesterRequests;
use App\Http\Requests\SemesterUpdateRequests;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SemesterController extends Controller
{
    public function index()
    {
        return view('pages/semester/index', [
            'title' => 'Semester - LCC Data Future Managment',
            'breadcrumbs' => [['label' => 'Semesters', 'href' => 'javascript:void(0);']]
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $total_rows = $count = Semester::count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = Semester::orderByRaw(implode(',', $sorts));
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
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(SemesterRequests $request){
        $data = Semester::create([
            'name'=> $request->name,
            'created_by' => auth()->user()->id
        ]);

        $semesters = Semester::all()->sortByDesc("name");
        Cache::forever('semesters', $semesters);

        return response()->json($data);
    }

    public function edit($id){
        $data = Semester::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(SemesterUpdateRequests $request, Semester $dataId){
        $data = Semester::where('id', $request->id)->update([
            'name'=> $request->name,
            'updated_by' => auth()->user()->id
        ]);

        $semesters = Semester::all()->sortByDesc("name");
        Cache::forever('semesters', $semesters);

        return response()->json($data);


        if($data->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }
    }

    public function destroy($id){
        $data = Semester::find($id)->delete();

        $semesters = Semester::all()->sortByDesc("name");
        Cache::forever('semesters', $semesters);

        return response()->json($data);
        

    }

    public function restore($id) {
        $data = Semester::where('id', $id)->withTrashed()->restore();

        $semesters = Semester::all()->sortByDesc("name");
        Cache::forever('semesters', $semesters);

        return response()->json($data);
    }
}
