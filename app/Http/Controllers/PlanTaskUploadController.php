<?php

namespace App\Http\Controllers;

use App\Models\PlanTaskUpload;
use App\Http\Requests\StorePlanTaskUploadRequest;
use App\Http\Requests\UpdatePlanTaskUploadRequest;
use App\Models\PlanTask;
use Illuminate\Support\Facades\Storage;

class PlanTaskUploadController extends Controller
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
     * @param  \App\Http\Requests\StorePlanTaskUploadRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePlanTaskUploadRequest $request)
    {
        
        $planTask = PlanTask::find($request->plan_task_id);
        // $planContents->updated_at = now();
        // $planContents->save(); 
        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        
        $path = $document->storeAs('public/plans/plan_task/'.$planTask->id, $imageName, 's3');
        
        $data = [];
        $data['user_id'] = auth()->user()->id;
        $data['plan_task_id'] = $planTask->id;
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['disk_type'] = 's3';
        $data['path'] = Storage::disk('s3')->url($path);
        $data['display_file_name'] = $document->getClientOriginalName();
        $data['current_file_name'] = $imageName;
        $data['created_by'] = auth()->user()->id;
        $studentDoc = PlanTaskUpload::create($data);

        if($studentDoc)
            return response()->json(['message' => 'Document successfully uploaded.'], 200);
        else
            return response()->json(['message' => 'Document not uploaded'], 302);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PlanTaskUpload  $planTaskUpload
     * @return \Illuminate\Http\Response
     */
    public function show(PlanTaskUpload $planTaskUpload)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PlanTaskUpload  $planTaskUpload
     * @return \Illuminate\Http\Response
     */
    public function edit(PlanTaskUpload $planTaskUpload)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePlanTaskUploadRequest  $request
     * @param  \App\Models\PlanTaskUpload  $planTaskUpload
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePlanTaskUploadRequest $request, PlanTaskUpload $planTaskUpload)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PlanTaskUpload  $planTaskUpload
     * @return \Illuminate\Http\Response
     */
    public function destroy(PlanTaskUpload $planTaskUpload)
    {
        //
    }
}
