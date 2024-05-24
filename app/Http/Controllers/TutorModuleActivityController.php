<?php

namespace App\Http\Controllers;

use App\Models\ELearningActivitySetting;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\PlanContent;
use App\Models\PlanContentUpload;
use App\Models\PlansDateList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TutorModuleActivityController extends Controller
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
    public function create(PlansDateList $plansDateList, ELearningActivitySetting $activity)
    {
        

        return view('pages.tutor.module.activity.create', [
            'title' => 'Tutor Dashboard - London Churchill College',
            'breadcrumbs' => [],
            "plansDateList" =>$plansDateList,
            "EActivitySettings"=>$activity
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
            $eLearningActivity = ELearningActivitySetting::find($request->activity_settings_id);
            $planContents = new PlanContent();
            $planContents->plans_date_list_id = $request->plans_date_list_id;
            $planContents->e_learning_activity_setting_id = $eLearningActivity->id;
            $planContents->name = $request->name;
            $planContents->description = $request->description;
            $planContents->category = $eLearningActivity->category;
            $planContents->logo = $eLearningActivity->logo;
            $planContents->days_reminder = $eLearningActivity->days_reminder;
            $planContents->is_mandatory = $eLearningActivity->is_mandatory;
            $planContents->availibility_at = date("Y-m-d h:i:s",strtotime($request->availibility_at));
            $planContents->created_by = auth()->user()->id;
            $planContents->save();
            
        if($planContents->id)
            return response()->json(['message' => 'Document successfully uploaded.',"data"=>['plan_content_id'=>$planContents->id,'plans_date_list_id'=>$request->plans_date_list_id]], 200);
        else
            return response()->json(['message' => 'Document not uploaded'], 302);
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
    public function destroy($id)
    {
        //
    }
}
