<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTermTypeRequest;
use App\Http\Requests\UpdateTermTypeRequest;
use App\Models\TermType;
use App\Models\User;

class TermTypeController extends Controller
{
    public function index(){
        return view('pages.settings.termtype.index', [
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

        $total_rows = $count = TermType::count();
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

        $query = TermType::orderByRaw(implode(',', $sorts));
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
        return view('pages.settings.termtype.show', [
            'title' => 'Awarding Body - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Awarding Body', 'href' => route('awardingbody')],
                ['label' => 'Awarding Body Details', 'href' => 'javascript:void(0);']
            ],
            'Term Type' => TermType::find($id),
        ]);
    }

    public function store(StoreTermTypeRequest $request){
        $data = TermType::create([
            'name'=> $request->name,
            'code'=> $request->code,
            'is_active'=> (isset($request->is_active) ? $request->is_active : 0),
            'created_by' => auth()->user()->id
        ]);

        return response()->json($data);
    }

    public function edit($id){
        $data = TermType::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(UpdateTermTypeRequest $request, TermType $dataId){
        $data = TermType::where('id', $request->id)->update([
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
        $data = TermType::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = TermType::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
