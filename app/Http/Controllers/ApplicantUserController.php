<?php

namespace App\Http\Controllers;

use App\Models\ApplicantUser;
use App\Http\Requests\StoreApplicantUserRequest;
use App\Http\Requests\UpdateApplicantUserRequest;

class ApplicantUserController extends Controller
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
     * @param  \App\Http\Requests\StoreApplicantUserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreApplicantUserRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApplicantUser  $applicantUser
     * @return \Illuminate\Http\Response
     */
    public function show(ApplicantUser $applicantUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ApplicantUser  $applicantUser
     * @return \Illuminate\Http\Response
     */
    public function edit(ApplicantUser $applicantUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateApplicantUserRequest  $request
     * @param  \App\Models\ApplicantUser  $applicantUser
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateApplicantUserRequest $request, ApplicantUser $applicantUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApplicantUser  $applicantUser
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApplicantUser $applicantUser)
    {
        //
    }
}
