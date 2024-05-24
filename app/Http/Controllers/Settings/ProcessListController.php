<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ProcessList;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\ProcessListRequest;
use App\Http\Requests\ProcessListUpdateRequest;
use Illuminate\Support\Facades\Storage;

class ProcessListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.settings.processlist.index', [
            'title' => 'Process List - London Churchill College',
            'subtitle' => 'Applicant Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
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
                    'image_url' => $list->image_url,
                    'phase' => $list->phase.($list->phase == 'Live' && isset($list->auto_feed) && $list->auto_feed == 'Yes' ? ' (Auto)' : ''),
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
        $process = ProcessList::create([
            'name' => $request->name,
            'phase' => $request->phase,
            'auto_feed' => ($request->phase == 'Live' && (isset($request->auto_feed) && !empty($request->auto_feed)) ? $request->auto_feed : 'No'),
            'created_by' => auth()->user()->id
        ]);
        if($process && $request->hasFile('photo')):
            $photo = $request->file('photo');
            $imageName = 'Process_'.$process->id.'_'.time() . '.' . $request->photo->getClientOriginalExtension();
            $path = $photo->storeAs('public/process/'.$process->id, $imageName, 'local');

            $processUpdate = ProcessList::where('id', $process->id)->update([
                'image' => $imageName,
                'image_path' => Storage::disk('local')->url($path)
            ]);
        endif;
        return response()->json($process);
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
        $processOldRow = ProcessList::find($request->id);   
        $data = ProcessList::where('id', $request->id)->update([
            'name' => $request->name,
            'phase' => $request->phase,
            'auto_feed' => ($request->phase == 'Live' && (isset($request->auto_feed) && !empty($request->auto_feed)) ? $request->auto_feed : 'No'),
            'updated_by' => auth()->user()->id
        ]);

        if($request->hasFile('photo')):
            $photo = $request->file('photo');
            $imageName = 'Process_'.$request->id.'_'.time() . '.' . $request->photo->getClientOriginalExtension();
            $path = $photo->storeAs('public/process/'.$request->id, $imageName, 'local');
            if(isset($processOldRow->image) && !empty($processOldRow->image)):
                if (Storage::disk('local')->exists('public/process/'.$request->id.'/'.$processOldRow->image)):
                    Storage::disk('local')->delete('public/process/'.$request->id.'/'.$processOldRow->image);
                endif;
            endif;
            
            $processUpdate = ProcessList::where('id', $request->id)->update([
                'image' => $imageName,
                'image_path' => Storage::disk('local')->url($path)
            ]);

        endif;

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
