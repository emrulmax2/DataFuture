<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Status;
use App\Http\Requests\StatusRequest;

class StatusController extends Controller
{
    public function index()
    {
        return view('pages/status/index', [
            'title' => 'Statuses - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Statuses', 'href' => 'javascript:void(0);']
            ],
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'asc']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Status::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $total_rows = $query->count();
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
                    'type' => $list->type,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(StatusRequest $request){
        $data = Status::create([
            'name'=> $request->name,
            'type'=> $request->type,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = Status::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(StatusRequest $request){      
        $data = Status::where('id', $request->id)->update([
            'name'=> $request->name,
            'type'=> $request->type,
            'updated_by' => auth()->user()->id
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = Status::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = Status::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
