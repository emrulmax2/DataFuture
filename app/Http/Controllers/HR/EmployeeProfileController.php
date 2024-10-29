<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Department;
use App\Models\Disability;
use App\Models\Employee;
use App\Models\EmployeeEligibilites;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeJobTitle;
use App\Models\EmployeeNoticePeriod;
use App\Models\EmployeeTerm;
use App\Models\EmployeeWorkDocumentType;
use App\Models\EmployeeWorkPermitType;
use App\Models\EmployeeWorkType;
use App\Models\Employment;
use App\Models\EmploymentPeriod;
use App\Models\EmploymentSspTerm;
use App\Models\Ethnicity;
use App\Models\HighestQualificationOnEntry;
use App\Models\KinsRelation;
use App\Models\Option;
use App\Models\SexIdentifier;
use App\Models\Title;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeProfileController extends Controller
{
    public function index()
    {
        //
    }
    public function show($id)
    {
        
        $employee = Employee::find($id);
        $userData = User::find($employee->user_id);
        $venues = Venue::all();
        $employment = Employment::where("employee_id", $id)->get()->first();
        $employeeEligibilites = EmployeeEligibilites::where("employee_id",$id)->get()->first();
        $emergencyContacts = EmployeeEmergencyContact::where("employee_id",$id)->get()->first();
        $employeeTerms = EmployeeTerm::where("employee_id",$id)->get()->first();
        $i = 0;
        $employmentVenue = [];
        foreach($employee->venues as $venue) {
            $employmentVenue[$i++] = $venue->id;
        }
        $titles = Title::all();
        $sexids = SexIdentifier::all();
        $ethnicities = Ethnicity::all();
        $countries = Country::all();
        $EmployeeWorkType = EmployeeWorkType::all();

        $relation = KinsRelation::all();
        $disability = Disability::all();
        $venues = Venue::all();
        $departments = Department::all();
        $noticePeriods = EmployeeNoticePeriod::all();
        $employmentPeriods = EmploymentPeriod::all();
        $sspTerms = EmploymentSspTerm::all();
        $jobTitles = EmployeeJobTitle::all();
        $documentTypes = EmployeeWorkDocumentType::all();
        $workPermitTypes = EmployeeWorkPermitType::all();
        $employeeDisablities = DB::table('employee_disability')->where('employee_id', $id)->pluck('disability_id')->toArray();
        $PostCodeAPI = Option::where('category', 'ADDR_ANYWHR_API')->where('name', 'anywhere_api')->pluck('value')->first();
        $qualEntries = HighestQualificationOnEntry::all();

        return view('pages.employee.profile.show',[
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [],
            "user" => $userData,
            "employee" => $employee,
            "employment" => $employment,
            "employeeEligibilites" => $employeeEligibilites,
            "emergencyContacts" => $emergencyContacts,
            "employeeTerms" => $employeeTerms,
            "titles" => $titles,
            'sexids' => $sexids,
            "ethnicity" => $ethnicities,
            "country" => $countries,
            "employeeWorkTypes" => $EmployeeWorkType,
            "relation" => $relation,
            "disability" =>$disability,
            "venue" =>$venues,
            "departments" => $departments,
            "noticePeriods" => $noticePeriods,
            "employmentPeriods" => $employmentPeriods,
            "sspTerms" => $sspTerms,
            "employeeJobTitles" => $jobTitles,
            "documentTypes" => $documentTypes,
            "workPermitTypes" => $workPermitTypes,
            "venues" => $venues,
            "employmentVenue" => $employmentVenue,
            "empDisIds" => $employeeDisablities,
            "postcodeApi" => $PostCodeAPI,
            "qualEntries" => $qualEntries,
        ]);
    }
    
}
