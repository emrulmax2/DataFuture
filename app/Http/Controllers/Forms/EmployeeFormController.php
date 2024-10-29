<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\Address;
use App\Models\ComonSmtp;
use App\Models\Country;
use App\Models\Department;
use App\Models\Disability;
use App\Models\Employee;
use App\Models\EmployeeBankDetail;
use App\Models\EmployeeEducationalQualification;
use App\Models\EmployeeEligibilites;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeJobTitle;
use App\Models\EmployeeNoticePeriod;
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
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class EmployeeFormController extends Controller
{
    public function index($employee_id = null){
        $the_id = Crypt::decrypt($employee_id);
        
        $titles = Title::all();
        // $gender = HesaGender::all();
        $sexIdentifier = SexIdentifier::orderBy('name', 'ASC')->get();
        $country = Country::orderBy('name', 'ASC')->get();
        $relation = KinsRelation::orderBy('name', 'ASC')->get();
        $ethnicity = Ethnicity::orderBy('name', 'ASC')->get();
        $disability = Disability::orderBy('name', 'ASC')->get();
        $venues = Venue::orderBy('name', 'asc')->get();
        $workTypes = EmployeeWorkType::orderBy('name', 'asc')->get();
        $departments = Department::orderBy('name', 'ASC')->get();
        $noticePeriods = EmployeeNoticePeriod::all();
        $employmentPeriods = EmploymentPeriod::all();
        $sspTerms = EmploymentSspTerm::all();
        $jobTitles = EmployeeJobTitle::orderBy('name', 'ASC')->get();
        $documentTypes = EmployeeWorkDocumentType::all();
        $workPermitTypes = EmployeeWorkPermitType::all();
        $qualEntries = HighestQualificationOnEntry::orderBy('name', 'ASC')->get();
        $employee = ($the_id > 0 ? Employee::find($the_id) : []);
        $PostCodeAPI = Option::where('category', 'ADDR_ANYWHR_API')->where('name', 'anywhere_api')->pluck('value')->first();


        return view('pages.forms.employee.index', [
            'title' => 'Employee or Contractor Data Collection - London Churchill College',
            'breadcrumbs' => [],
            'titles' => $titles,
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
            'employee' => $employee,
            'qualEntries' => $qualEntries,
            'logo' => Option::where('category', 'SITE_SETTINGS')->where('name','site_logo')->get()->first(),
            'postcode_api' => $PostCodeAPI,
        ]);
    }

    public function store(Request $request){
        $employee_id = $request->employee_id;
        $theEmployee = Employee::find($employee_id);
        $title = Title::find($request->title);
        $employeeName = (isset($title->name) && !empty($title->name) ? $title->name : '').' '.$request->first_name.' '.$request->last_name;

        if(isset($theEmployee->id) && $theEmployee->id > 0 && isset($theEmployee->status) && $theEmployee->status == 2):
            $personalAddress = Address::create([
                'address_line_1' => $request->emp_address_line_1,
                'address_line_2' => (!empty($request->emp_address_line_2) ? $request->emp_address_line_2 : null),
                'city' => $request->emp_city,
                'state' => null,
                'post_code' => $request->emp_post_code,
                'country' => (isset($request->emp_country) && !empty($request->emp_country) ? $request->emp_country : null),
                'active' => 1,
                'created_by' => 1,
            ]);

            $employee = Employee::find($employee_id);
            $employee->fill([
                'title_id' => $request->title,
                'first_name' => strtoupper($request->first_name),
                'last_name'  => strtoupper($request->last_name),
                'telephone'  => $request->telephone,
                'mobile'  => $request->mobile,
                'sex_identifier_id'=> $request->sex,
                'date_of_birth'  => date('Y-m-d', strtotime($request->date_of_birth)),
                'ni_number'  => (isset($request->national_insurance_num) && !empty($request->national_insurance_num) ? strtoupper($request->national_insurance_num) : null),
                'nationality_id'  => $request->nationality_id,
                'ethnicity_id'  => $request->ethnicity,
                'disability_status' => $request->disability_status ? 'Yes' : 'No',
                'address_id' =>   ($personalAddress->id && $personalAddress->id > 0 ? $personalAddress->id : null),
                'status' => 4,
            ]);
            $employee->save();
            $employee->disability()->sync($request->disability_id);

            $employment = Employment::create([
                "employee_id" => $employee_id, 
                'employee_work_type_id' => $request->employee_work_type,
                'utr_number' => (isset($request->utr_number) && !empty($request->utr_number) ? $request->utr_number : null),
                'email' => $employee->email
            ]);

            $eligible = (isset($request->eligible_to_work_status) && !empty($request->eligible_to_work_status) ? $request->eligible_to_work_status : 'No');
            $permit_id = ($eligible == 'Yes' && !empty($request->workpermit_type) ? $request->workpermit_type : 0);
            $EmployeeEligibilites = EmployeeEligibilites::create([
                'employee_id' => $employee_id,
                'eligible_to_work' => $eligible,
                'employee_work_permit_type_id' => ($permit_id > 0 ? $permit_id : null),
                'workpermit_number' => $permit_id == 3 && !empty($request->workpermit_number) ? $request->workpermit_number : null,
                'workpermit_expire' => $permit_id == 3 && !empty($request->workpermit_expire) ? date('Y-m-d', strtotime($request->workpermit_expire)) : null,
                'document_type' => $request->document_type,
                'doc_number' => $request->doc_number,
                'doc_expire' => (!empty($request->doc_expire) ? date('Y-m-d', strtotime($request->doc_expire)) : null),
                'doc_issue_country' =>$request->doc_issue_country,
            ]);

            $bankDetails = EmployeeBankDetail::create([
                'employee_id' => $employee_id,
                'beneficiary' => $request->beneficiary_name,
                'sort_code' => $request->sort_code,
                'ac_no' => $request->account_number,
                'active' => 1,
                'created_by' => 1,
            ]);

            $kinAddress = Address::create([
                'address_line_1' => $request->emc_address_line_1,
                'address_line_2' => (!empty($request->emc_address_line_2) ? $request->emc_address_line_2 : null),
                'city' => $request->emc_city,
                'state' => null,
                'post_code' => $request->emc_post_code,
                'country' => (isset($request->emc_country) && !empty($request->emc_country) ? $request->emc_country : null),
                'active' => 1,
                'created_by' => 1,
            ]);
            $kin = EmployeeEmergencyContact::create([
                'employee_id' => $employee_id,
                'emergency_contact_name' => $request->emergency_contact_name,
                'kins_relation_id' => $request->relationship,
                'address_id' => ($kinAddress && $kinAddress->id ? $kinAddress->id : null),
                'emergency_contact_telephone' => (isset($request->emergency_contact_telephone) && !empty($request->emergency_contact_telephone) ? $request->emergency_contact_telephone : null),
                'emergency_contact_mobile' => $request->emergency_contact_mobile,
                'emergency_contact_email' => (isset($request->emergency_contact_email) && !empty($request->emergency_contact_email) ? $request->emergency_contact_email : null),
            ]);

            $education = EmployeeEducationalQualification::create([
                'employee_id' => $employee_id,
                'highest_qualification_on_entry_id' => $request->highest_qualification_on_entry_id,
                'qualification_name' => $request->qualification_name,
                'award_body' => $request->award_body,
                'award_date' => (isset($request->award_date) && !empty($request->award_date) ? date('Y-m-d', strtotime('01-'.$request->award_date)) : null),
            ]);

            $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
            if(isset($commonSmtp->id) && $commonSmtp->id > 0):
                $configuration = [
                    'smtp_host'    => $commonSmtp->smtp_host,
                    'smtp_port'    => $commonSmtp->smtp_port,
                    'smtp_username'  => $commonSmtp->smtp_user,
                    'smtp_password'  => $commonSmtp->smtp_pass,
                    'smtp_encryption'  => $commonSmtp->smtp_encryption,
                    
                    'from_email'    => 'hr@lcc.ac.uk',
                    'from_name'    =>  'LCC HR Team',
                ];

                $subject = 'Employment/Contractor Form Submitted';

                $MAILHTML = 'Hi,<br/><br/>';
                $MAILHTML .= '<p>Employment/Contractor Form Submitted</p>';

                $MAILHTML .= '<br/>By, <br/>'.$employeeName;

                UserMailerJob::dispatch($configuration, ['hr@lcc.ac.uk'], new CommunicationSendMail($subject, $MAILHTML, []));
            endif;

            return response()->json(['msg' => 'Stored.'], 200);
        else:
            return response()->json(['msg' => 'something went wrong.'], 422);
        endif;
    }
}
