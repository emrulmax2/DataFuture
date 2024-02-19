<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPlan;
use Illuminate\Http\Request;

class AssessmentPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    public function list(Request $request){
        $plan_id = (isset($request->planid) && $request->planid > 0 ? $request->planid : 0);
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AssessmentPlan::with('courseModuleBase','plan','results')->orderByRaw(implode(',', $sorts))->where('plan_id', $plan_id);

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
                    'name' => ucfirst($list->courseModuleBase->type->name). " - ". $list->courseModuleBase->type->code,
                    'published_at' => $list->published_at,
                    'resubmission_at' => $list->resubmission_at,
                    'resultFound' => $list->results->count() ? 1 : 0,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $assessmentPlan = new AssessmentPlan();
        $assessmentPlan->plan_id = $request->plan_id;
        $assessmentPlan->course_module_base_assesment_id = $request->course_module_base_assesment_id;
        $assessmentPlan->save();
        return response()->json(["Assessment Created"],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(AssessmentPlan $plan_assessment)
    {
        $plan_assessment->delete();

        if($plan_assessment->wasChanged())     

            return response()->json(["msg"=>"Removed"],200);
        else
            return response()->json(["msg"=>"Nothing Changed"],422);
    }

    public function restore($plan_assessment) {
        $data = AssessmentPlan::where('id', $plan_assessment)->withTrashed()->restore();

        return response()->json($data);
    }
}
