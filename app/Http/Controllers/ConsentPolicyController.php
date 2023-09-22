<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsentPolicyRequest;
use App\Models\ConsentPolicy;
use App\Models\Department;
use Illuminate\Http\Request;

class ConsentPolicyController extends Controller
{
    public function index()
    {
        return view('pages/consent/index', [
            'title' => 'Consent Policy - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Consent Policies', 'href' => 'javascript:void(0);']
            ],
            'departments' => Department::all()
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

        $query = ConsentPolicy::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('description','LIKE','%'.$queryStr.'%');
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
                    'description' => $list->description,
                    'department' => isset($list->department->name) ? $list->department->name : '',
                    'is_required' => $list->is_required,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(ConsentPolicyRequest $request){
        $data = ConsentPolicy::create([
            'name'=> $request->name,
            'description'=> $request->description,
            'department_id'=> (isset($request->department_id) && $request->department_id > 0 ? $request->department_id : null),
            'is_required'=> $request->is_required,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = ConsentPolicy::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(ConsentPolicyRequest $request){      
        $data = ConsentPolicy::where('id', $request->id)->update([
            'name'=> $request->name,
            'description'=> $request->description,
            'department_id'=> (isset($request->department_id) && $request->department_id > 0 ? $request->department_id : null),
            'is_required'=> $request->is_required,
            'updated_by' => auth()->user()->id
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = ConsentPolicy::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = ConsentPolicy::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
