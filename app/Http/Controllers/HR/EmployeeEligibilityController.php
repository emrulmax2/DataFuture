<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeEligibilityUpdateRequest;
use App\Models\Employee;
use App\Models\EmployeeArchive;
use App\Models\EmployeeEligibilites;
use Illuminate\Http\Request;

class EmployeeEligibilityController extends Controller
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
    public function update(EmployeeEligibilityUpdateRequest $request,EmployeeEligibilites $eligibility)
    {
        $eligibilityOld = EmployeeEligibilites::where('employee_id', $request->employee_id)->get()->first();
        $mergeData = [   
            'eligible_to_work' => ($request->eligible_to_work_status)? "Yes":"No",
            'employee_work_permit_type_id'=> $request->workpermit_type,
            'workpermit_number'=> $request->workpermit_number,
            'workpermit_expire'=> $request->workpermit_expire,
            'document_type' => $request->document_type,
            'doc_number'=> $request->doc_number,
            'doc_expire'=> !empty($request->doc_expire) ? date('Y-m-d', strtotime($request->doc_expire)) : '',
            'doc_issue_country'=> $request->doc_issue_country
        ];
        
        $request->request->remove('url');
        $request->request->remove('eligible_to_work_status');
        $request->request->remove('workpermit_type');
        $request->request->remove('workpermit_number');
        $request->request->remove('workpermit_expire');
        $request->request->remove('document_type');
        $request->request->remove('doc_number');
        $request->request->remove('doc_expire');
        $request->request->remove('doc_issue_country');

        $request->merge($mergeData);

        $input = $request->all();

        $eligibility->fill($input);
        $changes = $eligibility->getDirty();
        $eligibility->save();

        if($eligibility->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['employee_id'] = $eligibility->employee_id;
                $data['table'] = 'employee_eligibilites';
                $data['field_name'] = $field;
                $data['field_value'] = $eligibilityOld->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                EmployeeArchive::create($data);
            endforeach;
        endif;

        
        if($eligibility->wasChanged())
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
