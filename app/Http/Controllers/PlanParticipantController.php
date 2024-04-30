<?php

namespace App\Http\Controllers;

use App\Models\PlanParticipant;
use App\Http\Requests\StorePlanParticipantRequest;
use App\Http\Requests\UpdatePlanParticipantRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlanParticipantController extends Controller
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
        $planid = (isset($request->planid) && !empty($request->planid) ? $request->planid : 0);
        //$dates = (isset($request->dates) && !empty($request->dates) ? date('Y-m-d', strtotime($request->dates)) : '');
        $status = (isset($request->status) && !empty($request->status) ? $request->status : '1');
        
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = PlanParticipant::orderByRaw(implode(',', $sorts));
        if(!empty($planid)): $query->where('plan_id', $planid); endif;
        //if(!empty($dates)): $query->where('date', $dates); endif;
        if($status == 2): $query->onlyTrashed(); endif;

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
                    'name' => (isset($list->user->employee) && !empty($list->user->employee)) ? $list->user->employee->title->name." ".$list->user->employee->first_name . " ". $list->user->employee->last_name : $list->user->name,
                    'type' => $list->type,
                    'images' => (isset($list->user->photo) && !empty($list->user->photo) && Storage::disk('google')->exists('public/users/'.$list->user->id.'/'.$list->user->photo) ? Storage::disk('google')->url('public/users/'.$list->user->id.'/'.$list->user->photo) : asset('build/assets/images/avater.png')),
                    'status' => '',
                    'deleted_at' => $list->deleted_at,
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
     * @param  \App\Http\Requests\StorePlanParticipantRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePlanParticipantRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PlanParticipant  $planParticipant
     * @return \Illuminate\Http\Response
     */
    public function show(PlanParticipant $planParticipant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PlanParticipant  $planParticipant
     * @return \Illuminate\Http\Response
     */
    public function edit(PlanParticipant $planParticipant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePlanParticipantRequest  $request
     * @param  \App\Models\PlanParticipant  $planParticipant
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePlanParticipantRequest $request, PlanParticipant $planParticipant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PlanParticipant  $planParticipant
     * @return \Illuminate\Http\Response
     */
    public function destroy(PlanParticipant $planParticipant)
    {
        //
    }
}
