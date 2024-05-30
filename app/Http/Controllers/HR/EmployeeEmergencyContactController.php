<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeEmergencyContactUpdateRequest;
use App\Models\Address;
use App\Models\EmployeeArchive;
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
    public function store(EmployeeEmergencyContactUpdateRequest $request)
    {

        $data = new EmployeeEmergencyContact();
        $data->fill($request->all());
        $data->save();

        if($data->id)
            return response()->json(['message' => 'Emergency contact successfully saved.',"data"=>['EmployeeEmergencyContactId'=>$data->id]], 200);
        else
            return response()->json(['message' => 'Emergency contact could not be saved'], 302);
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
        $contactOld = EmployeeEmergencyContact::where('employee_id', $request->employee_id)->orderBy('id', 'desc')->get()->first();
        $input = $request->all();
        
        $contact->fill($input);
        $changes = $contact->getDirty();
        $contact->save();

        if($contact->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['employee_id'] = $contact->employee_id;
                $data['table'] = 'employee_emergency_contacts';
                $data['field_name'] = $field;
                $data['field_value'] = $contactOld->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                EmployeeArchive::create($data);
            endforeach;
        endif;

        
        if($contact->wasChanged())
            return response()->json(["message"=>"updated",]);
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
