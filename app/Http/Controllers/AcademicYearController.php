<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AcademicYearRequest;
use App\Http\Requests\AcademicYearUpdateRequest;
use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearController extends Controller
{
    public function index()
    {
        return view('pages/academicyears/index', [
            'title' => 'Academic Years - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Academic Years', 'href' => 'javascript:void(0);']
            ],
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $total_rows = $count = AcademicYear::count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'asc']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = AcademicYear::orderByRaw(implode(',', $sorts));
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
                    'from_date' => $list->from_date,
                    'to_date' => $list->to_date,
                    'target_date_hesa_report' => $list->target_date_hesa_report,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function show($id)
    {
        return view('pages/academicyears/show', [
            'title' => 'Academic Years - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Academic Years', 'href' => route('academicyears')],
                ['label' => 'Academic Years Details', 'href' => 'javascript:void(0);']
            ],
            'academicyear' => AcademicYear::find($id),
        ]);
    }

    public function store(AcademicYearRequest $request){
        $data = AcademicYear::create([
            'name'=> $request->name,
            'code'=> $request->code,
            'from_date'=> date('Y-m-d', strtotime($request->from_date)),
            'to_date'=> date('Y-m-d', strtotime($request->to_date)),
            'target_date_hesa_report'=> date('Y-m-d', strtotime($request->target_date_hesa_report)),
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = AcademicYear::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(AcademicYearUpdateRequest $request, AcademicYear $dataId){      
        $data = AcademicYear::where('id', $request->id)->update([
            'name'=> $request->name,
            'code'=> $request->code,
            'from_date'=> date('Y-m-d', strtotime($request->from_date)),
            'to_date'=> date('Y-m-d', strtotime($request->to_date)),
            'target_date_hesa_report'=> date('Y-m-d', strtotime($request->target_date_hesa_report)),
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
        $data = AcademicYear::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = AcademicYear::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
