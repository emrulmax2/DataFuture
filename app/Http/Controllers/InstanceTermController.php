<?php

namespace App\Http\Controllers;

use App\Models\InstanceTerm;
use Illuminate\Http\Request;
use App\Http\Requests\InstanceTermRequest;
use App\Models\User;

class InstanceTermController extends Controller
{
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $creationinstanceid = (isset($request->creationinstanceid) && $request->creationinstanceid > 0 ? $request->creationinstanceid : 0);

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        
        $query = InstanceTerm::where('course_creation_instance_id', $creationinstanceid);
        if(!empty($queryStr)):
             $query->where('term','LIKE','%'.$queryStr.'%');
             $query->where('start_date','LIKE','%'.$queryStr.'%');
             $query->where('end_date','LIKE','%'.$queryStr.'%');
        endif;

        $total_rows = $query->count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = InstanceTerm::where('course_creation_instance_id', $creationinstanceid)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('term','LIKE','%'.$queryStr.'%');
            $query->where('start_date','LIKE','%'.$queryStr.'%');
            $query->where('end_date','LIKE','%'.$queryStr.'%');
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
                    'term' => $list->term,
                    'session_term' => 'Term '.$list->session_term,
                    'start_date' => $list->start_date,
                    'end_date' => $list->end_date,
                    'total_teaching_weeks' => $list->total_teaching_weeks,
                    'teaching_start_date' => $list->teaching_start_date,
                    'teaching_end_date' => $list->teaching_end_date,
                    'revision_start_date' => $list->revision_start_date,
                    'revision_end_date' => $list->revision_end_date,
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
    public function store(InstanceTermRequest $request)
    {
        $request->request->add(['created_by' => auth()->user()->id]);
        $data = InstanceTerm::create($request->all());
        
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InstanceTerm  $instanceTerm
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = InstanceTerm::find($id);
        return view('pages/instanceterm/show', [
            'title' => 'Instance Terms - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Instance Terms', 'href' => route('instance.term.show', $data->course_creation_instance_id)],
                //['label' => 'Instance Terms', 'href' => route('instance.term.show')],
                ['label' => 'Instance Terms Details', 'href' => 'javascript:void(0);']
            ],
            'instanceterm' => $data
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InstanceTerm  $instanceTerm
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = InstanceTerm::find($id);

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
     * @param  \App\Models\InstanceTerm  $instanceTerm
     * @return \Illuminate\Http\Response
     */
    public function update(InstanceTermRequest $request)
    {
        $request->request->add(['updated_by' => auth()->user()->id]);
        $course_creation_instance_id = $request->course_creation_instance_id;
        $instanceTermId = $request->id;

        $instanceTerm = InstanceTerm::find($instanceTermId);
        $instanceTerm->fill($request->all());
        $instanceTerm->save();
        
        if($instanceTerm->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InstanceTerm  $instanceTerm
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = InstanceTerm::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = InstanceTerm::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
