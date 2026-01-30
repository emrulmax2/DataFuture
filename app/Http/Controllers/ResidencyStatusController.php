<?php

namespace App\Http\Controllers;

use App\Models\ResidencyStatus;
use App\Http\Requests\StoreResidencyStatusRequest;
use App\Http\Requests\UpdateResidencyStatusRequest;
use App\Models\ComonSmtp;
use Illuminate\Http\Request;

class ResidencyStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $smtps = ComonSmtp::all();

        return view('pages.settings.residency-status.index', [
            'title' => 'Residency Status - London Churchill College',
            'subtitle' => 'Residency Status',
            'slug' => 'residency_status',
            'breadcrumbs' => [
                ['label' => 'Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Residency Status', 'href' => 'javascript:void(0);']
            ],
            'smtps' => $smtps
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreResidencyStatusRequest $request)
    {
       $data = ResidencyStatus::create([
            'name'=> $request->name,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(ResidencyStatus $residencyStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ResidencyStatus $residencyStatus)
    {
       $data = $residencyStatus;    

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResidencyStatusRequest $request, ResidencyStatus $residencyStatus)
    {
        $residencyStatus->update([
            'name'=> $request->name,
            'updated_by' => auth()->user()->id
        ]);
         if($residencyStatus->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }
    }

    /**
     * list of data for residency status.
     */
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $total_rows = $count = ResidencyStatus::count();
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

        $query = ResidencyStatus::orderByRaw(implode(',', $sorts));
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

    public function destroy($id){


        $data = ResidencyStatus::find($id)->delete();
        return response()->json($data);
    }

    public function forceDelete($id) 
    {
        $data = ResidencyStatus::where('id', $id)->withTrashed()->forceDelete();

        return response()->json($data);
    }

    public function restore($id) {
        $data = ResidencyStatus::where('id', $id)->withTrashed()->restore();

        return response()->json($data);
    }
}
