<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeEmergencyContactUpdateRequest;
use App\Models\Address;
use App\Models\EmployeeEmergencyContact;
use Illuminate\Http\Request;

class EmployeeEmergencyContactController extends Controller
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    public function update(EmployeeEmergencyContactUpdateRequest $request, EmployeeEmergencyContact $contact)
    {
        $address = new Address();
        $address->fill($request->all());
        $address->save();

        $request->merge(['address_id' => $address->id]);
        $input = $request->all();
        
        $contact->fill($input);
        $changes = $contact->getDirty();
        $contact->save();

        
        if($contact->wasChanged())
            return response()->json(["message"=>"updated","data"=>$changes]);
        else
            return response()->json(["no update"]);
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
