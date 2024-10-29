<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeEducationalQualRequest;
use App\Models\EmployeeEducationalQualification;
use Illuminate\Http\Request;

class EmployeeEducationalQualificationController extends Controller
{
    public function store(StoreEmployeeEducationalQualRequest $request){
        $employee_id = $request->employee_id;
        $id = (isset($request->employee_education_id) && $request->employee_education_id > 0 ? $request->employee_education_id : 0);

        $data = [
            'employee_id' => $employee_id,
            'highest_qualification_on_entry_id' => $request->highest_qualification_on_entry_id,
            'qualification_name' => $request->qualification_name,
            'award_body' => $request->award_body,
            'award_date' => (isset($request->award_date) && !empty($request->award_date) ? date('Y-m-d', strtotime($request->award_date)) : null),
        ];

        if($id > 0):
            $data['updated_by'] = auth()->user()->id;
            EmployeeEducationalQualification::where('id', $id)->update($data);
        else:
            $data['created_by'] = auth()->user()->id;
            EmployeeEducationalQualification::create($data);
        endif;

        return response()->json(['message' => 'success'], 200);
    }
}
