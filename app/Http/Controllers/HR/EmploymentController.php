<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmploymentDataUpdateRequest;
use App\Models\Employee;
use App\Models\EmployeeArchive;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Http\Request;

class EmploymentController extends Controller
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
    public function update(EmploymentDataUpdateRequest $request, Employment $employment)
    {
        $user_email = (isset($request->user_email) && !empty($request->user_email) ? $request->user_email : '');
        $employmentOld = Employment::where('employee_id', $request->employee_id)->get()->first();
        $employee = Employee::find($employment->employee_id);

        if(!empty($user_email) && $user_email != $employee->user->email):
            $existUser = User::where('email', $user_email)->where('id', '!=', $employee->user_id)->get()->count();
            if($existUser > 0):
                return response()->json(['res' => 'Email id already exist.'], 400);
            else:
                User::where('id', $employee->user_id)->update(['email' => $user_email]);
                $request->request->add(['email' => $user_email]);
            endif;
        endif;

        $input = $request->all();
        $employment->fill($input);
        $changes = $employment->getDirty();
        $employment->save();

        /* Sync Site Location */
        $employee->venues()->sync($request->site_location);

        if($employment->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['employee_id'] = $employment->employee_id;
                $data['table'] = 'employments';
                $data['field_name'] = $field;
                $data['field_value'] = $employmentOld->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                EmployeeArchive::create($data);
            endforeach;
        endif;

        
        if($employment->wasChanged())
            return response()->json(["message"=>"updated"], 200);
        else
            return response()->json(["no update"], 200);
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
