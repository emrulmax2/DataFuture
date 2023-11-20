<?php

namespace App\Http\Controllers;

use App\Models\PlanContentUpload;
use App\Http\Requests\StorePlanContentUploadRequest;
use App\Http\Requests\UpdatePlanContentUploadRequest;
use App\Models\PlanContent;
use App\Models\PlansDateList;
use Illuminate\Support\Facades\Storage;

class PlanContentUploadController extends Controller
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
     * @param  \App\Http\Requests\StorePlanContentUploadRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePlanContentUploadRequest $request)
    {
        $planDateList = PlansDateList::find($request->plans_date_list_id);
        $planContents = PlanContent::find($request->plan_content_id);
        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/plans/plan_date_list/'.$planDateList->id, $imageName, 'google');
        $data = [];
        $data['user_id'] = auth()->user()->id;
        $data['plan_content_id'] = $planContents->id;
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['disk_type'] = 'google';
        $data['path'] = Storage::disk('google')->url($path);
        $data['display_file_name'] = $document->getClientOriginalName();
        $data['current_file_name'] = $imageName;
        $data['created_by'] = auth()->user()->id;
        $studentDoc = PlanContentUpload::create($data);

        if($studentDoc)
            return response()->json(['message' => 'Document successfully uploaded.'], 200);
        else
            return response()->json(['message' => 'Document not uploaded'], 302);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PlanContentUpload  $planContentUpload
     * @return \Illuminate\Http\Response
     */
    public function show(PlanContentUpload $planContentUpload)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PlanContentUpload  $planContentUpload
     * @return \Illuminate\Http\Response
     */
    public function edit(PlanContentUpload $planContentUpload)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePlanContentUploadRequest  $request
     * @param  \App\Models\PlanContentUpload  $planContentUpload
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePlanContentUploadRequest $request, PlanContentUpload $planContentUpload)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PlanContentUpload  $planContentUpload
     * @return \Illuminate\Http\Response
     */
    public function destroy(PlanContentUpload $planContentUpload)
    {
        //
    }
}
