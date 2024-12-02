<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccAssetTypeStoreRequest;
use App\Models\AccAssetType;
use Illuminate\Http\Request;

class AccAssetTypeController extends Controller
{
    public function index(){
        return view('pages.settings.accounts.asset-type', [
            'title' => 'Account Settings - London Churchill College',
            'subtitle' => 'Assets Type Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Account Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Assets Type', 'href' => 'javascript:void(0);']
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

        $query = AccAssetType::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
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
                    'name' => $list->name,
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(AccAssetTypeStoreRequest $request){
        $bank = AccAssetType::create([
            'name'=> $request->name,
            'active'=> (isset($request->active) && $request->active > 0 ? $request->active : 0),
            'created_by' => auth()->user()->id
        ]);

        return response()->json(['msg' => 'Asset Type successfully created'], 200);
    }

    public function edit(Request $request){
        $data = AccAssetType::find($request->row_id);

        return response()->json($data);
    }

    public function update(AccAssetTypeStoreRequest $request){
        $bank = AccAssetType::where('id', $request->id)->update([
            'name'=> $request->name,
            'active'=> (isset($request->active) && $request->active > 0 ? $request->active : '0'),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['msg' => 'Assets Type data successfully updated'], 200);
    }

    public function destroy($id){
        $data = AccAssetType::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = AccAssetType::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $accMethod = AccAssetType::find($id);
        $status = (isset($accMethod->active) && $accMethod->active == 1 ? 0 : 1);

        AccAssetType::where('id', $id)->update([
            'active'=> $status,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Assets Type Status successfully updated'], 200);
    }
}
