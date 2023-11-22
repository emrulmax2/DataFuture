<?php

namespace App\Http\Controllers;

use App\Models\PlanContent;
use App\Http\Requests\StorePlanContentRequest;
use App\Http\Requests\UpdatePlanContentRequest;

class PlanContentController extends Controller
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
     * @param  \App\Http\Requests\StorePlanContentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePlanContentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PlanContent  $planContent
     * @return \Illuminate\Http\Response
     */
    public function show(PlanContent $planContent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PlanContent  $planContent
     * @return \Illuminate\Http\Response
     */
    public function edit(PlanContent $planContent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePlanContentRequest  $request
     * @param  \App\Models\PlanContent  $planContent
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePlanContentRequest $request, PlanContent $planContent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PlanContent  $planContent
     * @return \Illuminate\Http\Response
     */
    public function destroy(PlanContent $planContent)
    {
        //
    }
}
