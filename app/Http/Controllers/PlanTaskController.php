<?php

namespace App\Http\Controllers;

use App\Models\PlanTask;
use App\Http\Requests\StorePlanTaskRequest;
use App\Http\Requests\UpdatePlanTaskRequest;
use App\Models\ELearningActivitySetting;
use App\Models\Plan;

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
    public function create(Plan $plan, ELearningActivitySetting $activity)
    {
        
        return view('pages.tutor.module.task.create', [
            'title' => 'Tutor Dashboard - London Churchill College',
            'breadcrumbs' => [],
            "plan" =>$plan,
            "EActivitySettings"=>$activity
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePlanTaskRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePlanTaskRequest $request)
    {
        $eLearningActivity = ELearningActivitySetting::find($request->e_learning_activity_setting_id);

        $request->merge(["category"=>$eLearningActivity->category]);
        $request->merge(["logo"=>$eLearningActivity->logo]);
        $request->merge(["days_reminder"=>$eLearningActivity->days_reminder]);
        $request->merge(["is_mandatory"=>$eLearningActivity->is_mandatory]);
        $request->merge(["created_by"=>auth()->user()->id]);
        
        $planTask = new PlanTask();
        $planTask->fill($request->all());
        $planTask->save();


        
    if($planTask->id)
        return response()->json(['message' => 'Task successfully saved.',"data"=>['plan_task_id'=>$planTask->id,'plan_id'=>$request->plan_id]], 200);
    else
        return response()->json(['message' => 'Plan Task could not be saved'], 302);
        
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
