<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeEligibilites;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeTerm;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeProfileController extends Controller
{
    public function index()
    {
        //
    }
    public function show($id)
    {
        $employee = Employee::find($id)->get()->first();
        $userData = User::find($employee->user_id);

        $terms = EmployeeTerm::where("employee_id",$id)->get()->first();
        $employment = Employment::where("employee_id",$id)->get()->first();
        $employeeEligibilites = EmployeeEligibilites::where("employee_id",$id)->get()->first();
        $emergencyContacts = EmployeeEmergencyContact::where("employee_id",$id)->get()->first();
        $employeeTerms = EmployeeTerm::where("employee_id",$id)->get()->first();

        return view('pages.employee.profile.show',[
            'title' => 'Welcome - LCC Data Future Managment',
            'breadcrumbs' => [],
            "user" => $userData,
            "employee" => $employee,
            "employment" => $employment,
            "employeeEligibilites" => $employeeEligibilites,
            "emergencyContacts" => $emergencyContacts,
            "employeeTerms" => $employeeTerms
        ]);
    }
    
}
