<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeAddressUpdateRequest;
use App\Models\Address;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeAddressController extends Controller
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
    public function update(EmployeeAddressUpdateRequest $request, Employee $employee)
    {   
        
        $address = new Address();
        $address->fill($request->all());
        $address->save();

        // $request->merge(['address_id' => $address->id]);
        // $input = $request->all();
        
        $employee->fill(['address_id' => $address->id]);
        $changes = $employee->getDirty();
        $employee->save();

        $employee->disability()->sync($request->disability_id);

        
        if($employee->wasChanged())
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
