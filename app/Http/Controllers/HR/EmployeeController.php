<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeDataSaveRequest;
use App\Http\Requests\EmploymentDataSaveRequest;
use App\Models\HesaGender;
use App\Models\Title;
use App\Models\Country;
use App\Models\Department;
use App\Models\KinsRelation;
use App\Models\Ethnicity;
use App\Models\Disability;
use App\Models\EmployeeNoticePeriod;
use App\Models\EmployeeWorkType;
use App\Models\EmploymentPeriod;
use App\Models\EmploymentSspTerm;
use App\Models\Venue;
use App\Models\WorkType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EmployeeController extends Controller
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
        $titles = Title::all();
        $gender = HesaGender::all();
        $country = Country::all();
        $relation = KinsRelation::all();
        $ethnicity = Ethnicity::all();
        $disability = Disability::all();
        $venues = Venue::all();
        $workTypes = EmployeeWorkType::all();
        $departments = Department::all();
        $noticePeriods = EmployeeNoticePeriod::all();
        $employmentPeriods = EmploymentPeriod::all();
        $sspTerms = EmploymentSspTerm::all();

        return view('pages.employee.index',[
            'title' => 'Add new Employee - LCC Data Future Managment',
            'breadcrumbs' => [],
            'titles' => $titles,
            'gender' => $gender,
            'country' => $country,
            'relation' => $relation,
            'ethnicity' => $ethnicity,
            'disability' => $disability,
            'venues' => $venues,
            'workTypes' => $workTypes,
            'departments' => $departments,
            'noticePeriods' => $noticePeriods,
            'employmentPeriods' => $employmentPeriods,
            'sspTerms' => $sspTerms
        ]);
    }
    public function save(EmploymentDataSaveRequest $request)
    {
        Session::put([
            'title' => $request->title,
            'first_name' => $request->first_name,
            'sur_name' => $request->sur_name,
            'prev_sur_name' => $request->prev_sur_name,
            'telephone' => $request->telephone,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'sex' => $request->sex,
            'date_of_birth' => $request->date_of_birth,
            'ni_number' => $request->ni_number,
            'disability' => $request->disability,
            'nationality' => $request->nationality,
            'ethnicity' => $request->ethnicity,
            'car_reg_number' => $request->car_reg_number,
            'drive_license_number' => $request->drive_license_number,
            'address_line_1' => $request->address_line_1,
            'city' => $request->city,
            'state' => $request->state,
            'post_code' => $request->post_code,
            'country' => $request->country,
            "disability_status" =>$request->disability_status,
            "disability_id" => ($request->disability_id) ?? null
        ]);
        return response()->json(["data success",$data = session()->all()]);
    }

    public function saveEmployment(Request $request)
    {
        Session::put([
            'started_on' => $request->started_on,
            'punch_number' => $request->punch_number,
            'site_location' => $request->site_location,
            'employee_work_type_id' => $request->employee_work_type_id,
            'works_number' => $request->works_number,
            'job_title' => $request->job_title,
            'department' => $request->department,
            'office_telephone' => $request->office_telephone,
            'ext_no' => $request->ext_no,
            'mobile' => $request->mobile,
            'email' => $request->email
        ]);
        return response()->json(["data success",$data = session()->all()]);
    }

    public function saveEligibility(Request $request)
    {
        Session::put([
            'eligible_to_work' => $request->eligible_to_work,
            'workpermit_number' => $request->workpermit_number,
            'workpermit_expire' => $request->workpermit_expire,
            'doc_number' => $request->doc_number,
            'doc_expire' => $request->doc_expire,
            'doc_issue_country' => $request->doc_issue_country
        ]);
        return response()->json(["data success"]);
    }

    public function saveEmergencyContact(Request $request)
    {
        Session::put([
            'emergency_contact_name' => $request->emergency_contact_name,
            'relationship' => $request->relationship,
            'emergency_contact_address' => $request->emergency_contact_address,
            'emergency_contact_telephone' => $request->emergency_contact_telephone,
            'emergency_contact_mobile' => $request->emergency_contact_mobile,
            'emergency_contact_email' => $request->emergency_contact_email
        ]);
        return response()->json(["data success"]);
    }

    public function reviewShows()
    {

    }
    public function reviewDone()
    {
        
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
    public function update(Request $request, $id)
    {
        //
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
