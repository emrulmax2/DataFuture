<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeTermUpdateRequest;
use App\Models\EmployeeArchive;
use App\Models\EmployeeTerm;
use Illuminate\Http\Request;

class EmployeeTermController extends Controller
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
    public function update(EmployeeTermUpdateRequest $request, EmployeeTerm $term)
    {
        $employeeTermOld = EmployeeTerm::where('employee_id', $request->employee_id)->orderBy('id', 'DESC')->get()->first();
        $input = $request->all();
        
        $term->fill($input);
        $changes = $term->getDirty();
        $term->save();

        if($term->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['employee_id'] = $term->employee_id;
                $data['table'] = 'employee_terms';
                $data['field_name'] = $field;
                $data['field_value'] = $employeeTermOld->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                EmployeeArchive::create($data);
            endforeach;
        endif;

        
        if($term->wasChanged())
            return response()->json(["message"=>"updated"]);
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
