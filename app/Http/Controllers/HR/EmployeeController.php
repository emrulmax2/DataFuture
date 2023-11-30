<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeDataSaveRequest;
use App\Http\Requests\EmployeeDataUpdateRequest;
use App\Http\Requests\EmployeeEligibilityDataSaveRequest;
use App\Http\Requests\EmployeeEmergencyContactDataSaveRequest;
use App\Http\Requests\EmploymentDataSaveRequest;
use App\Models\Address;
use App\Models\HesaGender;
use App\Models\Title;
use App\Models\Country;
use App\Models\Department;
use App\Models\KinsRelation;
use App\Models\Ethnicity;
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
use App\Models\SexIdentifier;
use App\Models\StudentArchive;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.employee.index', [
            'title' => 'Employees - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Employees', 'href' => 'javascript:void(0);']
            ],
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Employee::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('first_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('last_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('mobile','LIKE','%'.$queryStr.'%');
            $query->orWhere('email','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        else:
            $query->where('status', $status);
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => (isset($list->title->name) ? $list->title->name.' ' : '').$list->first_name.' '.$list->last_name,
                    'jobtitle' => (isset($list->employment->employeeJobTitle->name) ? $list->employment->employeeJobTitle->name : ''),
                    'photourl' => $list->photo_url,
                    'work_type' => (isset($list->employment->employeeWorkType->name) ? $list->employment->employeeWorkType->name : ''),
                    'department' => (isset($list->employment->department->name) ? $list->employment->department->name : ''),
                    'works_number' => (isset($list->employment->works_number) ? $list->employment->works_number : ''),
                    'status' => $list->status,
                    'deleted_at' => $list->deleted_at,
                    'url' => route('profile.employee.view', $list->id)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $titles = Title::all();
        // $gender = HesaGender::all();
        $sexIdentifier = SexIdentifier::all();
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
        $jobTitles = EmployeeJobTitle::all();
        $documentTypes = EmployeeWorkDocumentType::all();
        $workPermitTypes = EmployeeWorkPermitType::all();
        
        return view('pages.employee.create',[
            'title' => 'Add new Employee - LCC Data Future Managment',
            'breadcrumbs' => [],
            'titles' => $titles,
            // 'gender' => $gender,
            'country' => $country,
            'relation' => $relation,
            'ethnicity' => $ethnicity,
            'disability' => $disability,
            'venues' => $venues,
            'workTypes' => $workTypes,
            'departments' => $departments,
            'noticePeriods' => $noticePeriods,
            'employmentPeriods' => $employmentPeriods,
            'sspTerms' => $sspTerms,
            'jobTitles' => $jobTitles,
            'documentTypes' => $documentTypes,
            'workPermitTypes' => $workPermitTypes,
            'sexIdentifier' => $sexIdentifier,
        ]);
    }
    public function save(EmployeeDataSaveRequest $request)
    {
        Session::put([
            'title' => $request->title,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'telephone' => $request->telephone,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'sex' => $request->sex,
            'date_of_birth' => $request->date_of_birth,
            'ni_number' => $request->ni_number,
            'nationality' => $request->nationality,
            'ethnicity' => $request->ethnicity,
            'car_reg_number' => $request->car_reg_number,
            'drive_license_number' => $request->drive_license_number,
            'address_line_1' => $request->emp_address_line_1,
            'address_line_2' => $request->emp_address_line_2,
            'city' => $request->emp_city,
            'post_code' => $request->emp_post_code,
            'country' => $request->emp_country,
            "disability_status" =>$request->disability_status,
            "disability_id" => ($request->disability_id) ?? null
        ]);
        return response()->json(["data success",$data = session()->all()]);
    }

    public function saveEmployment(EmploymentDataSaveRequest $request)
    {
        Session::put([
            'started_on' => $request->started_on,
            'punch_number' => $request->punch_number,
            'site_location' => $request->site_location,
            'employee_work_type' => $request->employee_work_type,
            'works_number' => $request->works_number,
            'job_title' => $request->job_title,
            'department' => $request->department,
            'office_telephone' => $request->office_telephone,
            'mobile' => $request->mobile,
            'username' => $request->email,
            'notice_period' => $request->notice_period,
            'ssp_term' => $request->ssp_term,
            'employment_period' => $request->employment_period,
            
        ]);
        return response()->json(["data success",$data = session()->all()]);
    }

    public function saveEligibility(EmployeeEligibilityDataSaveRequest $request)
    {
        Session::put([
            'eligible_to_work' => $request->eligible_to_work_status,
            'workpermit_type' => $request->workpermit_type,
            'workpermit_number' => $request->workpermit_number,
            'workpermit_expire' => $request->workpermit_expire,
            'document_type' => $request->document_type,
            'doc_number' => $request->doc_number,
            'doc_expire' => (!empty($request->doc_expire) ? date('Y-m-d', strtotime($request->doc_expire)) : null),
            'doc_issue_country' => $request->doc_issue_country
        ]);
        return response()->json(["data success",$data = session()->all()]);
    }

    public function saveEmergencyContact(EmployeeEmergencyContactDataSaveRequest $request)
    {
        Session::put([
            'emergency_contact_name' => $request->emergency_contact_name,
            'relationship' => $request->relationship,
            'emergency_contact_telephone' => $request->emergency_contact_telephone,
            'emergency_contact_email' => $request->emergency_contact_email,
            'emergency_contact_address_line_1' => $request->emc_address_line_1,
            'emergency_contact_address_line_2' => $request->emc_address_line_2,
            'emergency_contact_post_code' => $request->emc_post_code,
            'emergency_contact_city' => $request->emc_city,
            'emergency_contact_country' => $request->emc_country,
            'emergency_contact_mobile' => $request->emergency_contact_mobile,
        ]);


        return $this->store();

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $data = session()->all();
        
        $address = Address::create([
            'address_line_1' => Session::get('address_line_1'),
            'address_line_2' => Session::get('address_line_2'),
            'city' => Session::get('city'),
            'state' => Session::get('state'),
            'post_code' => Session::get('post_code'),
            'country' =>  Session::get('country'),
        ]);

        $name = Session::get('first_name'). " ". Session::get('last_name');

        $user = User::create([
            'name'=> $name,
            'email'=> Session::get('username'),
            'password'=> Hash::make('password'),
            'gender'=> "Male",
            'active'=> 1,
        ]);

        $employee = Employee::create([
            "title_id" => Session::get('title'),
            "user_id" =>  $user->id,
            "first_name" => strtoupper(Session::get('first_name')),
            "last_name"  => strtoupper(Session::get('last_name')),
            "telephone"  => Session::get('telephone'),
            "mobile"  => Session::get('mobile'),
            "email"  => Session::get('email'),
            "sex_identifier_id"=>Session::get('sex'),
            "date_of_birth"  => Session::get('date_of_birth') ,
            "ni_number"  => strtoupper(Session::get('ni_number')),
            "nationality_id"  => Session::get('nationality'),
            "ethnicity_id"  => Session::get('ethnicity'),
            "car_reg_number"  => Session::get('car_reg_number'),
            "drive_license_number"  => Session::get('drive_license_number'),
            "disability_status" => (Session::get('disability_status')) ? "Yes" : "No",
            "address_id" =>   $address->id,
        ]);

        $employee->disability()->sync(Session::get('disability_id'));

        
        $employment = Employment::create([
            "employee_id" => $employee->id, 
            'started_on' => Session::get('started_on'),
            'punch_number' => Session::get('punch_number'),
            'employee_work_type_id' => Session::get('employee_work_type'),
            'works_number' => Session::get('works_number'),
            'employee_job_title_id' => Session::get('job_title'),
            'department_id' => Session::get('department'),
            'office_telephone' => Session::get('office_telephone'),
            'mobile' => Session::get('mobile'),
            'email' => Session::get('email')
        ]);


        $EmployeeEligibilites = EmployeeEligibilites::create([
            "employee_id" => $employee->id,
            'eligible_to_work' => Session::get('eligible_to_work'),
            'employee_work_permit_type_id' => Session::get('workpermit_type'),
            'workpermit_number' => Session::get('workpermit_number'),
            'workpermit_expire' => Session::get('workpermit_expire'),
            'document_type' => Session::get('document_type'),
            'doc_number' => Session::get('doc_number'),
            'doc_expire' => (!empty(Session::get('doc_expire')) ? date('Y-m-d', strtotime(Session::get('doc_expire'))) : null),
            'doc_issue_country' => Session::get('doc_issue_country'),

        ]);
        $address = Address::create([
            'address_line_1' => Session::get('emergency_contact_address_line_1'),
            'address_line_2' => Session::get('emergency_contact_address_line_2'),
            'city' => Session::get('emergency_contact_city'),
            'state' => Session::get('emergency_contact_state'),
            'post_code' => Session::get('emergency_contact_post_code'),
            'country' =>  Session::get('emergency_contact_country'),
        ]);

        $EmployeeEmergencyContact = EmployeeEmergencyContact::create([
            'employee_id' => $employee->id,
            'emergency_contact_name' => Session::get('emergency_contact_name'),
            'kins_relation_id' => Session::get('relationship'),
            'address_id' => $address->id,
            'emergency_contact_telephone' => Session::get('emergency_contact_telephone'),
            'emergency_contact_mobile' => Session::get('emergency_contact_mobile'),
            'emergency_contact_email' => Session::get('emergency_contact_email'),


        ]);
        
        $employmentTerm = EmployeeTerm::create([
            "employee_id" => $employee->id,
            'employee_notice_period_id' => Session::get('notice_period'),
            'employment_ssp_term_id' => Session::get('ssp_term'),
            'employment_period_id' => Session::get('employment_period'), 
        ]);
        
        $siteLocations = Session::get('site_location');
        
        $employee->venues()->attach($siteLocations);
 

        return response()->json(["data success",$data = session()->all(),"user_id"=>$employee->id]);
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
    public function update(EmployeeDataUpdateRequest $request, Employee $employee)
    {
        $request->merge([
            'disability_status' => ($request->disability_status)? "Yes":"No",
            'status' => (isset($request->status) && $request->status > 0 ? 1 : 0)
        ]);
        $input = $request->all();
        $employee->fill($input);
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

    public function UploadEmployeePhoto(Request $request){
       
        $data = Employee::find($request->employee_id);
        $oldPhoto = (isset($data->photo) && !empty($data->photo) ? $data->photo : '');

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/employees/'.$data->id, $imageName, 'google');
        if(!empty($oldPhoto)):
            if (Storage::disk('google')->exists('public/employees/'.$data->id.'/'.$oldPhoto)):
                Storage::delete('public/employees/'.$data->id.'/'.$oldPhoto);
            endif;
        endif;

        $data2 = Employee::find($data->id);
        $data2->fill([
            'photo' => $imageName
        ]);
        $changes = $data2->getDirty();
        $data2->save();

        // if($data2->wasChanged() && !empty($changes)):
        //     foreach($changes as $field => $value):
        //         $dataArchive = [];
        //         $dataArchive['employee_id'] = $data->id;
        //         $dataArchive['table'] = 'employees';
        //         $dataArchive['field_name'] = $field;
        //         $dataArchive['field_value'] = $data->$field;
        //         $dataArchive['field_new_value'] = $value;
        //         $dataArchive['created_by'] = auth()->user()->id;

        //         EmployeeArchive::create($dataArchive);
        //     endforeach;
        // endif;

        return response()->json(['message' => 'Photo successfully change & updated'], 200);
    }
}
