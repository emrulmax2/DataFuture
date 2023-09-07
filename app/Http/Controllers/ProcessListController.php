<?php

namespace App\Http\Controllers;

use App\Models\ProcessList;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\ProcessListRequest;
use App\Http\Requests\ProcessListUpdateRequest;

class ProcessListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/processlist/index', [
            'title' => 'Process List - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Process List', 'href' => 'javascript:void(0);']
            ],
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

        $query = ProcessList::orderByRaw(implode(',', $sorts));
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
                    'phase' => $list->phase,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProcessListRequest $request)
    {
        $data = ProcessList::create([
            'name' => $request->name,
            'phase' => $request->phase,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProcessList  $processList
     * @return \Illuminate\Http\Response
     */
    public function show(ProcessList $processList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProcessList  $processList
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = ProcessList::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProcessList  $processList
     * @return \Illuminate\Http\Response
     */
    public function update(ProcessListUpdateRequest $request, ProcessList $dataId){      
        $data = ProcessList::where('id', $request->id)->update([
            'name' => $request->name,
            'phase' => $request->phase,
            'updated_by' => auth()->user()->id
        ]);

        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProcessList  $processList
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        $data = ProcessList::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = ProcessList::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
