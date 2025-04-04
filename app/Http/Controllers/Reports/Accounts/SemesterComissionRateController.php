<?php

namespace App\Http\Controllers\Reports\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemesterComissionRateStoreRequest;
use App\Models\SemesterComissionRate;
use Illuminate\Http\Request;

class SemesterComissionRateController extends Controller
{
    public function store(SemesterComissionRateStoreRequest $request){
        $id = (isset($request->id) && $request->id > 0 ? $request->id : 0);
        $semester_id = (isset($request->comr_semester_id) && $request->comr_semester_id > 0 ? $request->comr_semester_id : 0);
        if($id == 0 && $semester_id > 0):
            $row = SemesterComissionRate::where('semester_id', $semester_id)->get()->first();
            $id = (isset($row->id) && $row->id > 0 ? $row->id : $id);
        endif;

        $data = [
            'semester_id' => $semester_id,
            'rate' => $request->comission_rate
        ];
        if($id > 0):
            $data['updated_by'] = auth()->user()->id;

            SemesterComissionRate::where('id', $id)->update($data);
        else:
            $data['created_by'] = auth()->user()->id;

            SemesterComissionRate::create($data);
        endif;

        return response()->json(['msg' => 'Comission rate successfull inserted'], 200);
    }


    public function list(Request $request){
        $semester_id = (isset($request->semester_id) && !empty($request->semester_id) ? $request->semester_id : 0);
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = SemesterComissionRate::orderByRaw(implode(',', $sorts));
        if($semester_id > 0):
            $query->where('semester_id', $semester_id);
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
                    'semester_id' => $list->semester_id,
                    'semester_name' => (isset($list->semester->name) && !empty($list->semester->name) ? $list->semester->name : ''),
                    'rate' => (isset($list->rate) && $list->rate > 0 ? number_format($list->rate, 2).'%' : '0%'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function edit(Request $request){
        $row_id = $request->row_id;
        $row = SemesterComissionRate::find($row_id);

        return response()->json(['row' => $row], 200);
    }

    public function destroy($id){
        SemesterComissionRate::find($id)->delete();
        return response()->json(['row' => 'Successfully deleted.'], 200);
    }

    public function restore($id) {
        SemesterComissionRate::where('id', $id)->withTrashed()->restore();

        return response()->json(['row' => 'Successfully restored.'], 200);
    }
}
