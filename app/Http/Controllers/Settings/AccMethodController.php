<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccMethodStoreRequest;
use App\Models\AccMethod;
use Illuminate\Http\Request;

class AccMethodController extends Controller
{
    public function index(){
        return view('pages.settings.accounts.methods', [
            'title' => 'Account Settings - London Churchill College',
            'subtitle' => 'Method Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Account Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Methods', 'href' => 'javascript:void(0);']
            ]
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

        $query = AccMethod::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('method_name','LIKE','%'.$queryStr.'%');
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
                    'method_name' => $list->method_name,
                    'status' => ($list->status == 1 ? $list->status : '2'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(AccMethodStoreRequest $request){
        $data = AccMethod::create([
            'method_name'=> $request->method_name,
            'status'=> (isset($request->status) && $request->status > 0 ? $request->status : 2),
            'created_by' => auth()->user()->id
        ]);

        return response()->json(['msg' => 'Method successfully created'], 200);
    }

    public function edit(Request $request){
        $data = AccMethod::find($request->row_id);

        return response()->json($data);
    }

    public function update(AccMethodStoreRequest $request){
        $data = AccMethod::where('id', $request->id)->update([
            'method_name'=> $request->method_name,
            'status'=> (isset($request->status) && $request->status > 0 ? $request->status : 2),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['msg' => 'Method year successfully updated'], 200);
    }

    public function destroy($id){
        $data = AccMethod::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = AccMethod::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $accMethod = AccMethod::find($id);
        $status = (isset($accMethod->status) && $accMethod->status == 1 ? 2 : 1);

        AccMethod::where('id', $id)->update([
            'status'=> $status,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Method Status successfully updated'], 200);
    }

}
