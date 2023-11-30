<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeAddressUpdateRequest;
use App\Models\Address;
use App\Models\Employee;
use App\Models\EmployeeEmergencyContact;
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
    public function edit(Request $request)
    {
        $address = Address::find($request->address_id);
        return response()->json(['res' => $address], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EmployeeAddressUpdateRequest $request)
    {   
        $address_id = $request->address_id;
        $employee_id = $request->employee_id;
        $type = $request->type;

        $deleteAddress = Address::find($address_id)->delete();
        $address = Address::create([
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'post_code' => $request->post_code,
            'city' => $request->city,
            'country' => $request->country,
            'created_by' => auth()->user()->id,
        ]);
        
        if($type == 'emc'):
            $employeeContact = EmployeeEmergencyContact::where('employee_id', $employee_id)->get()->first();
            $employeeContact->fill(['address_id' => $address->id]);
            $changes = $employeeContact->getDirty();
            $employeeContact->save();
        else:
            $employee = Employee::find($employee_id);
            $employee->fill(['address_id' => $address->id]);
            $changes = $employee->getDirty();
            $employee->save();
        endif;

        return response()->json(['id' => $address->id], 200);
        
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
