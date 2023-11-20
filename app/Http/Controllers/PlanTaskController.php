<?php

namespace App\Http\Controllers;

use App\Models\PlanTask;
use App\Http\Requests\StorePlanTaskRequest;
use App\Http\Requests\UpdatePlanTaskRequest;

class PlanTaskController extends Controller
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
     * @param  \App\Http\Requests\StorePlanTaskRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePlanTaskRequest $request)
    {
        $planTask = new PlanTask();
        $planTask->fill($request->all());
        $planTask->save();

        return response()->json(["msg"=>"Plan Task saved"],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PlanTask  $planTask
     * @return \Illuminate\Http\Response
     */
    public function show(PlanTask $planTask)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PlanTask  $planTask
     * @return \Illuminate\Http\Response
     */
    public function edit(PlanTask $planTask)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePlanTaskRequest  $request
     * @param  \App\Models\PlanTask  $planTask
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePlanTaskRequest $request, PlanTask $planTask)
    {
        
        $planTask->fill($request->all());

        
        if($planTask->isDirty()) {
            $planTask->save();
        return response()->json(["msg"=>"Plan Task updated"],200);
        } else {
            return response()->json(["msg"=>"No Updated Content"],302); 
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PlanTask  $planTask
     * @return \Illuminate\Http\Response
     */
    public function destroy(PlanTask $planTask)
    {
        //
    }
}
