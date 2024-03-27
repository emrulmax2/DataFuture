<?php

namespace App\Http\Controllers;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Http\Requests\AdmissionContactDetailsRequest;
use App\Http\Requests\AdmissionCourseDetailsRequest;
use App\Http\Requests\AdmissionKinDetailsRequest;
use App\Http\Requests\AdmissionPersonalDetailsRequest;
use App\Http\Requests\ApplicantNoteRequest;
use App\Http\Requests\SendEmailRequest;
use App\Http\Requests\SendLetterRequest;
use App\Http\Requests\SendSmsRequest;
use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantContact;
use App\Models\ApplicantDisability;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\Semester;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ApplicantQualification;
use App\Models\ApplicantEmployment;
use App\Models\ApplicantKin;
use App\Models\ApplicantOtherDetail;
use App\Models\ApplicantProposedCourse;
use App\Models\ApplicantTemporaryEmail;
use App\Models\AwardingBody;
use App\Models\Country;
use App\Models\CourseCreationInstance;
use App\Models\Disability;
use App\Models\Ethnicity;
use App\Models\KinsRelation;
use App\Models\Title;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Mail; 
use Hash;
use App\Mail\ApplicantTempEmailVerification;
use App\Mail\CommunicationSendMail;

use App\Models\ApplicantDocument;
use App\Models\ApplicantDocumentList;
use App\Models\ApplicantEmail;
use App\Models\ApplicantEmailsAttachment;
use App\Models\ApplicantNote;
use App\Models\ApplicantSms;
use App\Models\ApplicantTask;
use App\Models\ApplicantTaskDocument;
use App\Models\ApplicantTaskLog;
use App\Models\ComonSmtp;
use App\Models\DocumentSettings;
use App\Models\Option;
use App\Models\ProcessList;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Mail as FacadesMail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use App\Jobs\UserMailerJob;
use App\Models\ApplicantFeeEligibility;
use App\Jobs\ProcessStudents;
use App\Jobs\ProcessNewStudentToUser;
use App\Jobs\ProcessStudentConsent;
use App\Jobs\ProcessStudentNoteDetails;
use App\Jobs\ProcessStudentTask;
use App\Jobs\ProcessStudentTaskDocument;
use App\Jobs\ProcessStudentQualification;
use App\Jobs\ProcessStudentContact;
use App\Jobs\ProcessStudentDisability;
use App\Jobs\ProcessStudentEmployement;
use App\Jobs\ProcessStudentKinDetail;
use App\Jobs\ProcessStudentProposedCourse;
use App\Jobs\ProcessStudentOtherDetails;
use App\Jobs\ProcessStudentProofOfId;
use App\Jobs\ProcessStudentFeeEligibility;
use App\Jobs\ProcessStudentSms;
use App\Jobs\ProcessStudentLetter;
use App\Jobs\ProcessStudentInterview;
use App\Jobs\ProcessStudentEmail;


use App\Models\AcademicYear;
use App\Models\ApplicantInterview;
use App\Models\ApplicantLetter;
use App\Models\ApplicantProofOfId;
use App\Models\ApplicantUser;
use App\Models\EmailTemplate;
use App\Models\FeeEligibility;
use App\Models\LetterHeaderFooter;
use App\Models\LetterSet;
use App\Models\Signatory;
use App\Models\SmsTemplate;
use App\Models\JobBatch;
use App\Models\MobileVerificationCode;
use App\Models\SexIdentifier;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use App\Models\StudentFeeEligibility;
use App\Models\StudentProposedCourse;
use App\Models\StudentUser;
use App\Models\TaskList;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdmissionController extends Controller
{
    public function index(){
        
        $semesters = Cache::get('semesters', function () {
            return Semester::all()->sortByDesc("name");
        });
        $courses = Cache::get('courses', function () {
            return Course::all();
        });
        $statuses = Cache::get('statuses', function () {
            return Status::where('type', 'Applicant')->where('id', '>', 1)->get();
        });
        
        
        return view('pages.students.admission.index', [
            'title' => 'Admission Management - X LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => 'javascript:void(0);']
            ],
            'semesters' => $semesters,
            'courses' => $courses,
            'allStatuses' => $statuses
        ]);
    }

    public function list(Request $request){
        $semesters = (isset($request->semesters) && !empty($request->semesters) ? $request->semesters : []);
        $courses = (isset($request->courses) && !empty($request->courses) ? $request->courses : []);
        $statuses = (isset($request->statuses) && !empty($request->statuses) ? $request->statuses : []);
        $refno = (isset($request->refno) && !empty($request->refno) ? $request->refno : '');
        $firstname = (isset($request->firstname) && !empty($request->firstname) ? $request->firstname : '');
        $lastname = (isset($request->lastname) && !empty($request->lastname) ? $request->lastname : '');
        $dob = (isset($request->dob) && !empty($request->dob) ? date('Y-m-d', strtotime($request->dob)) : '');

        $courseCreationId = [];
        if(!empty($courses)):
            $courseCreations = CourseCreation::whereIn('course_id', $courses)->get();
            if(!$courseCreations->isEmpty()):
                foreach($courseCreations as $cc):
                    $courseCreationId[] = $cc->id;
                endforeach;
            else:
                $courseCreationId[1] = '0';
            endif;
        endif;

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Applicant::orderByRaw(implode(',', $sorts))->whereNotNull('submission_date');
        if(!empty($refno)): $query->where('application_no', $refno); endif;
        if(!empty($firstname)): $query->where('first_name', 'LIKE', '%'.$firstname.'%'); endif;
        if(!empty($lastname)): $query->where('last_name', 'LIKE', '%'.$lastname.'%'); endif;
        if(!empty($dob)): $query->where('date_of_birth', $dob); endif;
        if(!empty($statuses)): $query->whereIn('status_id', $statuses); else: $query->where('status_id', '>', 1); endif;
        if(!empty($semesters) || !empty($courseCreationId)):
            $query->whereHas('course', function($qs) use($semesters, $courses, $courseCreationId){
                if(!empty($semesters)): $qs->whereIn('semester_id', $semesters); endif;
                if(!empty($courses) && !empty($courseCreationId)): $qs->whereIn('course_creation_id', $courseCreationId); endif;
            });
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'application_no' => (empty($list->application_no) ? $list->id : $list->application_no),
                    'first_name' => ucfirst($list->first_name),
                    'last_name' => ucfirst($list->last_name),
                    'date_of_birth'=> $list->date_of_birth,
                    'course'=> (isset($list->course->creation->course->name) ? $list->course->creation->course->name : ''),
                    'semester'=> (isset($list->course->semester->name) ? $list->course->semester->name : ''),
                    'gender'=> (isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : ''),
                    'status_id'=> (isset($list->status->name) ? $list->status->name : ''),
                    'url' => route('admission.show', $list->id),
                    'ccid' => implode(',', $courses).' - '.implode(',', $courseCreationId)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function show($applicantId){
        return view('pages.students.admission.show', [
            'title' => 'Admission Management - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'allStatuses' => Status::where('type', 'Applicant')->where('id', '>', 1)->get(),
            'titles' => Title::all(),
            'country' => Country::all(),
            'ethnicity' => Ethnicity::all(),
            'disability' => Disability::all(),
            'relations' => KinsRelation::all(),
            'bodies' => AwardingBody::all(),
            'sexid' => SexIdentifier::all(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'instance' => CourseCreationInstance::all()->sortByDesc('id'),
            'tempEmail' => ApplicantTemporaryEmail::where('applicant_id', $applicantId)->orderBy('id', 'desc')->first(),
            'documents' => DocumentSettings::where('admission', '1')->orderBy('id', 'ASC')->get(),
            'feeelegibility' => FeeEligibility::all()
        ]);
    }


    public function updatePersonalDetails(AdmissionPersonalDetailsRequest $request){
        $applicant_id = $request->id;
        $applicantOldRow = Applicant::find($applicant_id);
        $otherDetailsOldRow = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();

        $ethnicity_id = $request->ethnicity_id;
        $disability_status = (isset($request->disability_status) && $request->disability_status > 0 ? $request->disability_status : 0);
        $disability_id = ($disability_status == 1 && isset($request->disability_id) && !empty($request->disability_id) ? $request->disability_id : []);
        $disabilty_allowance = ($disability_status == 1 && !empty($disability_id) && (isset($request->disabilty_allowance) && $request->disabilty_allowance > 0) ? $request->disabilty_allowance : 0);

        $proof_type = (isset($request->proof_type) && !empty($request->proof_type) ? $request->proof_type : '');
        $proof_id = (isset($request->proof_id) && !empty($request->proof_id) ? $request->proof_id : '');
        $proof_expiredate = (isset($request->proof_expiredate) && !empty($request->proof_expiredate) ? $request->proof_expiredate : '');
        $applicant_proof_of_id = (isset($request->applicant_proof_of_id) && $request->applicant_proof_of_id > 0 ? $request->applicant_proof_of_id : 0);

        $request->request->remove('ethnicity_id');
        $request->request->remove('disability_status');
        $request->request->remove('disability_id');
        $request->request->remove('disabilty_allowance');

        $request->request->remove('proof_type');
        $request->request->remove('proof_id');
        $request->request->remove('proof_expiredate');
        $request->request->remove('applicant_proof_of_id');

        if(!isset($applicantOldRow->application_no) || is_null($applicantOldRow->application_no)):
            $appNo = '2'.sprintf('%05d', $applicant_id);
            $request->merge(['application_no' => $appNo]);
        endif;

        $applicant = Applicant::find($applicant_id);
        $applicant->fill($request->input());
        $changes = $applicant->getDirty();
        $applicant->save();

        if($applicant->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicants';
                $data['field_name'] = $field;
                $data['field_value'] = $applicantOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;
        $request->request->remove('id');

        if(!empty($proof_type) || !empty($proof_id) || !empty($proof_expiredate) || $applicant_proof_of_id > 0):
            $applicantProof = ApplicantProofOfId::updateOrCreate([ 'applicant_id' => $applicant_id, 'id' => $applicant_proof_of_id ], [
                'applicant_id' => $applicant_id,
                'proof_type' => $proof_type,
                'proof_id' => $proof_id,
                'proof_expiredate' => $proof_expiredate,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]);
        endif;

        $otherDetails = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();
        $otherDetails->fill([
            'ethnicity_id' => $ethnicity_id,
            'disability_status' => $disability_status,
            'disability_status' => $disability_status,
            'disabilty_allowance' => $disabilty_allowance,
        ]);
        $changes = $otherDetails->getDirty();
        $otherDetails->save();

        if($otherDetails->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_other_details';
                $data['field_name'] = $field;
                $data['field_value'] = $otherDetailsOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;
        $applicantDisablities = ApplicantDisability::where('applicant_id', $applicant_id)->get();
        $existingIds = [];
        if(!empty($applicantDisablities)):
            foreach($applicantDisablities as $dis):
                $existingIds[] = $dis->disabilitiy_id;
            endforeach;
        endif;
        if($disability_status == 1 && !empty($disability_id)):
            $applicantDisablityDel = ApplicantDisability::where('applicant_id', $applicant_id)->forceDelete();
            foreach($disability_id as $disabilityID):
                $applicantDisabilitiesCr = ApplicantDisability::create([
                    'applicant_id' => $applicant_id,
                    'disabilitiy_id' => $disabilityID,
                    'created_by' => auth()->user()->id,
                ]);
            endforeach;

            $data = [];
            $data['applicant_id'] = $applicant_id;
            $data['table'] = 'applicant_disabilities';
            $data['field_name'] = 'disabilitiy_id';
            $data['field_value'] = implode(',', $existingIds);
            $data['field_new_value'] = implode(',', $disability_id);
            $data['created_by'] = auth()->user()->id;

            ApplicantArchive::create($data);
        else:
            if(!empty($existingIds)):
                $applicantDisablityDel = ApplicantDisability::where('applicant_id', $applicant_id)->forceDelete();
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_disabilities';
                $data['field_name'] = 'disabilitiy_id';
                $data['field_value'] = implode(',', $existingIds);
                $data['field_new_value'] = implode(',', $disability_id);
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endif;
        endif;


        return response()->json(['msg' => 'Personal Data Successfully Updated.'], 200);
    }

    public function updateContactDetails(AdmissionContactDetailsRequest $request){
        $applicant_id = $request->applicant_id;
        $applicant = Applicant::find($applicant_id);
        $contactOldRow = ApplicantContact::find($request->id);
        $email = $request->email;

        $request->request->remove('email');

        $contact = ApplicantContact::find($request->id);
        $contact->fill([
            'home' => $request->phone,
            'mobile' => $request->mobile,
            'address_line_1' => (isset($request->applicant_address_line_1) && !empty($request->applicant_address_line_1) ? $request->applicant_address_line_1 : null),
            'address_line_2' => (isset($request->applicant_address_line_2) && !empty($request->applicant_address_line_2) ? $request->applicant_address_line_2 : null),
            'state' => (isset($request->applicant_address_state) && !empty($request->applicant_address_state) ? $request->applicant_address_state : null),
            'post_code' => (isset($request->applicant_address_postal_zip_code) && !empty($request->applicant_address_postal_zip_code) ? $request->applicant_address_postal_zip_code : null),
            'city' => (isset($request->applicant_address_city) && !empty($request->applicant_address_city) ? $request->applicant_address_city : null),
            'country' => (isset($request->applicant_address_country) && !empty($request->applicant_address_country) ? $request->applicant_address_country : null),
            'updated_by' => auth()->user()->id
        ]);
        $changes = $contact->getDirty();
        $contact->save();

        if($applicant->users->email != $email):
            $tempEmail = ApplicantTemporaryEmail::create([
                'applicant_id' => $applicant_id,
                'email' => $email,
                'status' => 'Pending',
                'created_by' => auth()->user()->id
            ]);
            if($tempEmail):
                $applicantName = $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name;
                $url = route('varify.temp.email', $applicant_id);
                Mail::to($email)->send(new ApplicantTempEmailVerification($applicantName, $applicant->users->email, $email, $url));
            endif;
        endif;

        if($contact->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_contacts';
                $data['field_name'] = $field;
                $data['field_value'] = $contactOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Contact Details Successfully Updated.'], 200);
    }

    public function updateKinDetails(AdmissionKinDetailsRequest $request){
        $applicant_id = $request->applicant_id;
        $kinOldRow = ApplicantKin::find($request->id);

        $kin = ApplicantKin::find($request->id);
        $kin->fill([
            'name' => $request->name,
            'kins_relation_id' => $request->kins_relation_id,
            'mobile' => $request->kins_mobile,
            'email' => (isset($request->kins_email) && !empty($request->kins_email) ? $request->kins_email : null),
            'address_line_1' => (isset($request->kin_address_line_1) && !empty($request->kin_address_line_1) ? $request->kin_address_line_1 : null),
            'address_line_2' => (isset($request->kin_address_line_2) && !empty($request->kin_address_line_2) ? $request->kin_address_line_2 : null),
            'state' => (isset($request->kin_address_state) && !empty($request->kin_address_state) ? $request->kin_address_state : null),
            'post_code' => (isset($request->kin_address_postal_zip_code) && !empty($request->kin_address_postal_zip_code) ? $request->kin_address_postal_zip_code : null),
            'city' => (isset($request->kin_address_city) && !empty($request->kin_address_city) ? $request->kin_address_city : null),
            'country' => (isset($request->kin_address_country) && !empty($request->kin_address_country) ? $request->kin_address_country : null),
            'updated_by' => auth()->user()->id
        ]);
        $changes = $kin->getDirty();
        $kin->save();

        if($kin->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_kin';
                $data['field_name'] = $field;
                $data['field_value'] = $kinOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Next of Kin Details Successfully Updated.'], 200);
    }

    public function updateCourseAndProgrammeDetails(AdmissionCourseDetailsRequest $request){
        $applicant_id = $request->applicant_id;
        $ProposedCourseOldRow = ApplicantProposedCourse::find($request->id);

        $course_creation_id = $request->course_creation_id;
        $courseCreation = CourseCreation::find($course_creation_id);
        $studentLoan = $request->student_loan;
        $studentFinanceEngland = ($studentLoan == 'Student Loan' && isset($request->student_finance_england) && $request->student_finance_england > 0 ? $request->student_finance_england : null);
        $appliedReceivedFund = ($studentLoan == 'Student Loan' && isset($request->applied_received_fund) && $request->applied_received_fund > 0 ? $request->applied_received_fund : null);
        $fundReceipt = ($studentFinanceEngland == 1 && isset($request->fund_receipt) && $request->fund_receipt > 0 ? $request->fund_receipt : null);

        $proposedCourse = ApplicantProposedCourse::find($request->id);
        $proposedCourse->fill([
            'course_creation_id' => $course_creation_id,
            'semester_id' => $courseCreation->semester_id,
            'student_loan' => $studentLoan,
            'student_finance_england' => $studentFinanceEngland,
            'applied_received_fund' => $appliedReceivedFund,
            'fund_receipt' => $fundReceipt,
            'other_funding' => ($studentLoan == 'Others' && isset($request->other_funding) && !empty($request->other_funding) ? $request->other_funding : null),
            'full_time' => ((isset($courseCreation->has_evening_and_weekend) && $courseCreation->has_evening_and_weekend == 1) && (isset($request->full_time) && $request->full_time > 0) ? $request->full_time : 0),
            'updated_by' => auth()->user()->id
        ]);
        $changes = $proposedCourse->getDirty();
        $proposedCourse->save();

        $applicant_proof_of_id = (isset($request->applicant_proof_of_id) && $request->applicant_proof_of_id > 0 ? $request->applicant_proof_of_id : 0);
        $fee_eligibility_id = (isset($request->fee_eligibility_id) && $request->fee_eligibility_id > 0 ? $request->fee_eligibility_id : 0);
        if($fee_eligibility_id > 0):
            $applicantEligibility = ApplicantFeeEligibility::updateOrCreate([ 'applicant_id' => $applicant_id, 'id' => $applicant_proof_of_id ], [
                'applicant_id' => $applicant_id,
                'fee_eligibility_id' => $fee_eligibility_id,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]);
        endif;

        if($proposedCourse->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_proposed_courses';
                $data['field_name'] = $field;
                $data['field_value'] = $ProposedCourseOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Course & Programme Details Successfully Updated.'], 200);
    }

    public function updateQualificationStatus(Request $request){
        $applicant_id = $request->applicant;
        $status = $request->status;
        $otherDetailsOldRow = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();

        
        $otherDetails = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();
        $otherDetails->fill([
            'is_edication_qualification' => $status
        ]);
        $changes = $otherDetails->getDirty();
        $otherDetails->save();

        if($otherDetails->wasChanged() && !empty($changes)):
            if($status == 0){
                $eduQual = ApplicantQualification::where('applicant_id', $applicant_id)->delete();
            }
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_other_details';
                $data['field_name'] = $field;
                $data['field_value'] = $otherDetailsOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Education qualification status Successfully Updated.'], 200);
    }

    public function updateEmploymentStatus(Request $request){
        $applicant_id = $request->applicant;
        $status = $request->status;
        $otherDetailsOldRow = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();

        $otherDetails = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();
        $otherDetails->fill([
            'employment_status' => $status
        ]);
        $changes = $otherDetails->getDirty();
        $otherDetails->save();

        if($otherDetails->wasChanged() && !empty($changes)):
            if($status == 'Unemployed' || $status == 'Contractor' || $status == 'Consultant' || $status == 'Office Holder'){
                $eduQual = ApplicantEmployment::where('applicant_id', $applicant_id)->delete();
            }
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_other_details';
                $data['field_name'] = $field;
                $data['field_value'] = $otherDetailsOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Education qualification status Successfully Updated.'], 200);
    }

    public function admissionProcess($applicantId){
        $processGroup = [];
        $processList = ProcessList::where('phase', 'Applicant')->orderBy('id', 'ASC')->get();
        if(!empty($processList)):
            $i = 1;
            foreach($processList as $prl):
                $taskIds = [];
                foreach($prl->tasks as $tsk):
                    $taskIds[] = $tsk->id;
                endforeach;
                if(!empty($taskIds)):
                    $pendingTask = ApplicantTask::where('applicant_id', $applicantId)->whereIn('task_list_id', $taskIds)->where('status', 'Pending')->get();
                    $inProgressTask = ApplicantTask::where('applicant_id', $applicantId)->whereIn('task_list_id', $taskIds)->where('status', 'In Progress')->get();
                    $completedTask = ApplicantTask::where('applicant_id', $applicantId)->whereIn('task_list_id', $taskIds)->where('status', 'Completed')->get();


                    $processGroup[$i]['name'] = $prl->name;
                    $processGroup[$i]['id'] = $prl->id;
                    $processGroup[$i]['pendingTask'] = $pendingTask;
                    $processGroup[$i]['inProgressTask'] = $inProgressTask;
                    $processGroup[$i]['completedTask'] = $completedTask;
                endif;
                $i++;
            endforeach;
        endif;

        return view('pages.students.admission.process', [
            'title' => 'Admission Management - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => route('admission.show', $applicantId)],
                ['label' => 'Process', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'allStatuses' => Status::where('type', 'Applicant')->where('id', '>', 1)->get(),
            'process' => ProcessList::where('phase', 'Applicant')->orderBy('id', 'ASC')->get(),
            'existingTask' => ApplicantTask::where('applicant_id', $applicantId)->pluck('task_list_id')->toArray(),
            'applicantPendingTask' => ApplicantTask::where('applicant_id', $applicantId)->where('status', 'Pending')->get(),
            'applicantCompletedTask' => ApplicantTask::where('applicant_id', $applicantId)->where('status', 'Completed')->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'feeelegibility' => FeeEligibility::all(),

            'processGroup' => $processGroup
        ]);
    }

    public function admissionStoreProcessTask(Request $request){
        $task_list_ids = (isset($request->task_list_ids) && !empty($request->task_list_ids) ? $request->task_list_ids : []);
        $applicant_id = (isset($request->applicant_id) && $request->applicant_id ? $request->applicant_id : 0);
        $applicantRow = Applicant::find($applicant_id);

        if(!empty($task_list_ids) && $applicant_id > 0):
            $existingTaskIds = ApplicantTask::where('applicant_id', $applicant_id)->pluck('task_list_id')->toArray();
            $existingDiff = array_diff($existingTaskIds, $task_list_ids);
            $taskListDiff = array_diff($task_list_ids, $existingTaskIds);

            $numInsert = 0;
            $numDelete = 0;
            if(!empty($taskListDiff)):
                foreach($taskListDiff as $task):
                    $withTrashed = ApplicantTask::where('applicant_id', $applicant_id)->where('task_list_id', $task)->onlyTrashed()->get();
                    if(!empty($withTrashed) && $withTrashed->count() > 0):
                        $restoreTask = ApplicantTask::where('applicant_id', $applicant_id)->where('task_list_id', $task)->withTrashed()->restore();
                    else:
                        $data = [];
                        $data['applicant_id'] = $applicant_id;
                        $data['task_list_id'] = $task;
                        $data['status'] = 'Pending';
                        $data['created_by'] = auth()->user()->id;
                        $insertTask = ApplicantTask::create($data);
                    endif;
                    $numInsert += 1;
                endforeach;
            endif;
            if(!empty($existingDiff)):
                foreach($existingDiff as $task):
                    $deleteTask = ApplicantTask::where('applicant_id', $applicant_id)->where('task_list_id', $task)->delete();
                    $numDelete += 1;
                endforeach;
            endif;

            $applicantTasks = ApplicantTask::withTrashed()->where('applicant_id', $applicant_id)->get();
            if($applicantTasks->count() > 0 && $applicantRow->status_id < 3):
                $applicantData['status_id'] = 3;
                Applicant::where('id', $applicant_id)->update($applicantData);

                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicants';
                $data['field_name'] = 'status_id';
                $data['field_value'] = $applicantRow->status_id;
                $data['field_new_value'] = '3';
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endif;
            if($numInsert > 0):
                $message = 'Task list '.$numInsert.' item success fully inserted.';
                $message .= ($numDelete > 0 ? ' Previously inserted '.$numDelete.' item deleted.' : '');
            else:
                $message = 'No new task selected. ';
                $message .= ($numDelete > 0 ? ' Previously inserted '.$numDelete.' item deleted.' : '');
            endif;
            return response()->json(['message' => $message], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later or contact administrator.'], 422);
        endif;
    }

    public function admissionUploadTaskDocument(Request $request){
        $applicant_id = $request->applicant_id;
        $applicant_task_id = $request->applicant_task_id;
        $applicantTask = ApplicantTask::find($applicant_task_id);
        $taskName = (isset($applicantTask->task->name) && !empty($applicantTask->task->name) ? $applicantTask->task->name : '');
        $display_file_name = (isset($request->display_file_name) && !empty($request->display_file_name) ? $request->display_file_name : '');
        $taskName = (!empty($display_file_name) ? (!empty($taskName) ? $taskName.' - '.$display_file_name : $display_file_name) : $taskName);

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/applicants/'.$applicant_id, $imageName, 'google');

        $data = [];
        $data['applicant_id'] = $applicant_id;
        $data['hard_copy_check'] = 0;
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = Storage::disk('google')->url($path);
        $data['display_file_name'] = (!empty($taskName) ? $taskName : $imageName);
        $data['current_file_name'] = $imageName;
        $data['created_by'] = auth()->user()->id;
        $applicantDoc = ApplicantDocument::create($data);
        if($applicantDoc):
            $applicantTaskDoc = ApplicantTaskDocument::create([
                'applicant_task_id' => $applicant_task_id,
                'applicant_document_id' => $applicantDoc->id,
                'created_by' => auth()->user()->id
            ]);

            $applicantTaskLog = ApplicantTaskLog::create([
                'applicant_tasks_id' => $applicant_task_id,
                'actions' => 'Document',
                'field_name' => '',
                'prev_field_value' => '',
                'current_field_value' => Storage::disk('google')->url($path),
                'created_by' => auth()->user()->id
            ]);
        endif;

        return response()->json(['message' => 'Document successfully uploaded.'], 200);
    }

    public function admissionDeleteTask(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        $data = ApplicantTask::where('id', $recordid)->where('applicant_id', $applicant)->delete();
        $applicantTaskLog = ApplicantTaskLog::create([
            'applicant_tasks_id' => $recordid,
            'actions' => 'Delete',
            'field_name' => '',
            'prev_field_value' => '',
            'current_field_value' => 'Item Deleted',
            'created_by' => auth()->user()->id
        ]);
        return response()->json(['message' => 'Data deleted'], 200);
    }

    public function admissionCompletedTask(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $applicantRow = Applicant::find($applicant);

        $applicantTask = ApplicantTask::where('id', $recordid)->where('applicant_id', $applicant)->update(['status' => 'Completed', 'updated_by' => auth()->user()->id]);
        $applicantTaskLog = ApplicantTaskLog::create([
            'applicant_tasks_id' => $recordid,
            'actions' => 'Status Changed',
            'field_name' => 'status',
            'prev_field_value' => 'Pending',
            'current_field_value' => 'Completed',
            'created_by' => auth()->user()->id
        ]);
        $pendingTask = ApplicantTask::where('applicant_id', $applicant)->whereIn('status', ['Pending', 'In Progress'])->get();
        if($pendingTask->count() == 0 && $applicantRow->status_id < 4):
            $applicantData['status_id'] = 4;
            Applicant::where('id', $applicant)->update($applicantData);

            $data = [];
            $data['applicant_id'] = $applicant;
            $data['table'] = 'applicants';
            $data['field_name'] = 'status_id';
            $data['field_value'] = $applicantRow->status_id;
            $data['field_new_value'] = '4';
            $data['created_by'] = auth()->user()->id;

            ApplicantArchive::create($data);
        endif;
        return response()->json(['message' => 'Data deleted'], 200);
    }

    public function admissionPendingTask(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $applicantRow = Applicant::find($applicant);


        $applicantTask = ApplicantTask::where('id', $recordid)->where('applicant_id', $applicant)->update(['status' => 'Pending', 'updated_by' => auth()->user()->id]);
        $applicantTaskLog = ApplicantTaskLog::create([
            'applicant_tasks_id' => $recordid,
            'actions' => 'Status Changed',
            'field_name' => 'status',
            'prev_field_value' => 'Completed',
            'current_field_value' => 'Pending',
            'created_by' => auth()->user()->id
        ]);

        if($applicantRow->status_id > 3):
            $applicantData['status_id'] = 3;
            Applicant::where('id', $applicant)->update($applicantData);

            $data = [];
            $data['applicant_id'] = $applicant;
            $data['table'] = 'applicants';
            $data['field_name'] = 'status_id';
            $data['field_value'] = $applicantRow->status_id;
            $data['field_new_value'] = '3';
            $data['created_by'] = auth()->user()->id;

            ApplicantArchive::create($data);
        endif;
        return response()->json(['message' => 'Data updated'], 200);
    }

    public function admissionArchivedProcessList(Request $request) {
        $applicantId = (isset($request->applicantId) && $request->applicantId > 0 ? $request->applicantId : 0);
        $processId = (isset($request->processId) && $request->processId > 0 ? $request->processId : 0);

        $processList = ProcessList::where('id', $processId)->where('phase', 'Applicant')->orderBy('id', 'ASC')->get();
        $taskIds = [];
        if(!empty($processList)):
            foreach($processList as $prl):
                foreach($prl->tasks as $tsk):
                    $taskIds[] = $tsk->id;
                endforeach;
            endforeach;
        endif;


        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ApplicantTask::where('applicant_id', $applicantId);
        if(!empty($taskIds)):
            $query->whereIn('task_list_id', $taskIds);
        else:
            $query->where('task_list_id', '0');
        endif;
        $query->orderByRaw(implode(',', $sorts))->onlyTrashed();

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
                    'name' => $list->task->name,
                    'desc' => isset($list->task->short_description) && !empty($list->task->short_description) ? $list->task->short_description : '',
                    'deleted_at' => (!empty($list->deleted_at) ? date('d-m-Y H:i:s', strtotime($list->deleted_at)) : '')
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function admissionResotreTask(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        $data = ApplicantTask::where('id', $recordid)->where('applicant_id', $applicant)->withTrashed()->restore();
        $applicantTaskLog = ApplicantTaskLog::create([
            'applicant_tasks_id' => $recordid,
            'actions' => 'Restore',
            'field_name' => '',
            'prev_field_value' => '',
            'current_field_value' => 'Item Restored',
            'created_by' => auth()->user()->id
        ]);
        return response()->json(['message' => 'Data Restored'], 200);
    }

    public function admissionShowTaskStatuses(Request $request){
        $applicantTaskId = $request->taskId;
        $applicantTask = ApplicantTask::find($applicantTaskId);
        $taskStatuses = $applicantTask->task->statuses;

        $statusOpt = [];
        if(!empty($taskStatuses)):
            $html = '<label for="upload" class="form-label">Task Result <span class="text-danger">*</span></label>';
            foreach($taskStatuses as $ts):
                $taskStatus = TaskStatus::find($ts->task_status_id);
                $html .= '<div class="form-check mt-2">';
                    $html .= '<input '.($applicantTask->task_status_id == $taskStatus->id ? 'Checked' : '').' id="outc_task-status-'.$taskStatus->id.'" class="form-check-input resultStatus" type="radio" name="result_statuses" value="'.$taskStatus->id.'">';
                    $html .= '<label class="form-check-label" for="outc_task-status-'.$taskStatus->id.'">'.$taskStatus->name.'</label>';
                $html .= '</div>';
            endforeach;
            $statusOpt['suc'] = 1;
            $statusOpt['res'] = $html;
        else:
            $statusOpt['suc'] = 2;
            $statusOpt['res'] = '<div class="alert alert-pending-soft show flex items-start mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> No status found for this task.</div>';
        endif;

        return response()->json(['message' => $statusOpt], 200);
    }

    public function admissionTaskResultUpdate(Request $request){
        $applicant_id = $request->applicant_id;
        $applicant_task_id = $request->applicant_task_id;
        $result_statuses = (isset($request->result_statuses) ? $request->result_statuses : '');
        $applicantTaskOld = ApplicantTask::where('applicant_id', $applicant_id)->where('id', $applicant_task_id)->get()->first();

        if($result_statuses > 0):
            $data = [];
            $data['task_status_id'] = $result_statuses;
            $data['updated_by'] = auth()->user()->id;
            $applicantTask = ApplicantTask::where('applicant_id', $applicant_id)->where('id', $applicant_task_id)->update($data);
            $applicantTaskLog = ApplicantTaskLog::create([
                'applicant_tasks_id' => $applicant_task_id,
                'actions' => 'Task Status',
                'field_name' => 'task_status_id',
                'prev_field_value' => $applicantTaskOld->task_status_id,
                'current_field_value' => $result_statuses,
                'created_by' => auth()->user()->id
            ]);
            return response()->json(['message' => 'Result successfully updated.'], 200);
        else: 
            return response()->json(['message' => 'Error found!'], 422);
        endif;
    }

    public function admissionTaskLogList(Request $request){
        $applicantTaskId = $request->applicantTaskId;
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'desc']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ApplicantTaskLog::where('applicant_tasks_id', $applicantTaskId)->orderByRaw(implode(',', $sorts));
     
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
                $fieldName = '';
                $prevValue = '';
                $newValue = '';
                if($list->actions == 'Document'):
                    $fieldName = '';
                    $prevValue = '';
                    $newValue = '<a href="'.$list->current_field_value.'" download traget="_blank" class="text-success" style="white-space: normal; word-break: break-all;">'.$list->current_field_value.'</a>';
                elseif($list->actions == 'Restore'):
                    $fieldName = '';
                    $prevValue = '';
                    $newValue = $list->current_field_value;
                elseif($list->actions == 'Delete'):
                    $fieldName = '';
                    $prevValue = '';
                    $newValue = $list->current_field_value;
                elseif($list->actions == 'Task Status'):
                    $prevStatus = (!empty($list->prev_field_value) && $list->prev_field_value > 0 ? TaskStatus::find($list->prev_field_value)->name : '');
                    $newStatus = (!empty($list->current_field_value) && $list->current_field_value > 0 ? TaskStatus::find($list->current_field_value)->name : '');
                    $fieldName = $list->field_name;
                    $prevValue = $prevStatus;
                    $newValue = $newStatus;
                else:
                    $fieldName = $list->field_name;
                    $prevValue = $list->prev_field_value;
                    $newValue = $list->current_field_value;
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'actions' => $list->actions,
                    'field_name' => $fieldName,
                    'prev_field_value' => $prevValue,
                    'current_field_value' => $newValue,
                    'created_at' => (!empty($list->created_at) ? date('d-m-Y H:i:s', strtotime($list->created_at)) : ''),
                    'created_by' => ($list->created_by > 0 ? User::find($list->created_by)->name : '')
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function admissionPocessTaskUserList(Request $request){
        $task_id = $request->task_id;
        $task = TaskList::find($task_id);

        $html = '';
        if(isset($task->users) && $task->users->count() > 0):
            foreach($task->users as $tusr):
                $html .= '<tr>';
                    $html .= '<td>';
                        $html .= '<div class="block">';
                            $html .= '<div class="w-10 h-10 intro-x image-fit mr-5 inline-block">';
                                $html .= '<img alt="'.(isset($tusr->user->employee->full_name) ? $tusr->user->employee->full_name : 'Unknown Employee').'" class="rounded-full shadow" src="'.(isset($tusr->user->employee->photo_url) && !empty($tusr->user->employee->photo_url) ? $tusr->user->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'">';
                            $html .= '</div>';
                            $html .= '<div class="inline-block relative" style="top: -5px;">';
                                $html .= '<div class="font-medium whitespace-nowrap uppercase">'.(isset($tusr->user->employee->full_name) ? $tusr->user->employee->full_name : 'Unknown Employee').'</div>';
                                if(isset($tusr->user->employee->employment->employeeJobTitle->name) && !empty($tusr->user->employee->employment->employeeJobTitle->name)):
                                    $html .= '<div class="text-slate-500 text-xs whitespace-nowrap">'.$tusr->user->employee->employment->employeeJobTitle->name.'</div>';
                                endif;
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td>'.(isset($tusr->user->employee->employment->department->name) ? $tusr->user->employee->employment->department->name : '').'</td>';
                    $html .= '<td>'.(isset($tusr->user->employee->employment->employeeWorkType->name) ? $tusr->user->employee->employment->employeeWorkType->name : '').'</td>';
                    $html .= '<td>'.(isset($tusr->user->employee->employment->works_number) ? $tusr->user->employee->employment->works_number : '').'</td>';
                    $html .= '<td>';
                        if(isset($tusr->user->employee->status) && $tusr->user->employee->status == 1):
                            $html .= '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0">Active</span>';
                        elseif(isset($tusr->user->employee->status) && $tusr->user->employee->status == 2):
                            $html .= '<span class="btn inline-flex btn-danger w-auto px-2 text-white py-0 rounded-0">Inactive</span>';
                        endif;
                    $html .= '</td>';
                $html .= '</tr>';
            endforeach;
        else:
            $html .= '<tr>';
                $html .= '<td colspan="5">';
                    $html .= '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert">';
                        $html .= '<i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Assigned user not found';
                    $html .= '</div>';
                $html .= '</td>';
            $html .= '</tr>';
        endif;

        return response()->json(['res' => $html], 200);
    }

    public function admissionUploads($applicantId){
        return view('pages.students.admission.uploads', [
            'title' => 'Admission Management - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => route('admission.show', $applicantId)],
                ['label' => 'Uploads', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'allStatuses' => Status::where('type', 'Applicant')->where('id', '>', 1)->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'docSettings' => DocumentSettings::where('admission', '1')->get(),
            'feeelegibility' => FeeEligibility::all()
        ]);
    }

    public function AdmissionUploadDocuments(Request $request){
        $applicant_id = $request->applicant_id;
        $document_setting_id = $request->document_setting_id;
        $documentSetting = DocumentSettings::find($document_setting_id);
        $document_settings_name = (isset($documentSetting->name) && !empty($documentSetting->name) ? $documentSetting->name : '');
        $hard_copy_check = $request->hard_copy_check;
        $display_file_name = (isset($request->display_file_name) && !empty($request->display_file_name) ? $request->display_file_name : '');
        $display_file_name .= ($display_file_name != '' ? ($document_settings_name != '' ? ' - '.$document_settings_name : '') : $document_settings_name);
        

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/applicants/'.$applicant_id, $imageName, 'google');
        $data = [];
        $data['applicant_id'] = $applicant_id;
        $data['document_setting_id'] = ($document_setting_id > 0 ? $document_setting_id : 0);
        $data['hard_copy_check'] = ($hard_copy_check > 0 ? $hard_copy_check : 0);
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = Storage::disk('google')->url($path);
        $data['display_file_name'] = (!empty($display_file_name) ? $display_file_name : $imageName);
        $data['current_file_name'] = $imageName;
        $data['created_by'] = auth()->user()->id;
        $applicantDoc = ApplicantDocument::create($data);

        return response()->json(['message' => 'Document successfully uploaded.'], 200);
    }

    public function AdmissionUploadList(Request $request){
        $applicantId = (isset($request->applicantId) && !empty($request->applicantId) ? $request->applicantId : 0);
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ApplicantDocument::orderByRaw(implode(',', $sorts))->where('applicant_id', $applicantId);
        if(!empty($queryStr)):
            $query->where('display_file_name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'display_file_name' => (!empty($list->display_file_name) ? $list->display_file_name : 'Unknown'),
                    'hard_copy_check' => $list->hard_copy_check,
                    'doc_type' => strtoupper($list->doc_type),
                    'current_file_name'=> $list->current_file_name,
                    'url' => Storage::disk('google')->url('public/applicants/'.$list->applicant_id.'/'.$list->current_file_name),
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function AdmissionUploadDestroy(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $data = ApplicantDocument::find($recordid)->delete();
        return response()->json($data);
    }

    public function AdmissionUploadRestore(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $data = ApplicantDocument::where('id', $recordid)->withTrashed()->restore();

        response()->json($data);
    }

    public function admissionNotes($applicantId){
        return view('pages.students.admission.notes', [
            'title' => 'Admission Management - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => route('admission.show', $applicantId)],
                ['label' => 'Notes', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'allStatuses' => Status::where('type', 'Applicant')->where('id', '>', 1)->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'feeelegibility' => FeeEligibility::all()
        ]);
    }

    public function admissionStoreNotes(ApplicantNoteRequest $request){
        $applicant_id = $request->applicant_id;
        $note = ApplicantNote::create([
            'applicant_id'=> $applicant_id,
            'note'=> $request->content,
            'phase'=> 'Admission',
            'created_by' => auth()->user()->id
        ]);
        if($note):
            if($request->hasFile('document')):
                $document = $request->file('document');
                $documentName = time().'_'.$document->getClientOriginalName();
                $path = $document->storeAs('public/applicants/'.$applicant_id, $documentName, 'google');

                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['hard_copy_check'] = 0;
                $data['doc_type'] = $document->getClientOriginalExtension();
                $data['path'] = Storage::disk('google')->url($path);
                $data['display_file_name'] = $documentName;
                $data['current_file_name'] = $documentName;
                $data['created_by'] = auth()->user()->id;
                $applicantDocument = ApplicantDocument::create($data);

                if($applicantDocument):
                    $noteUpdate = ApplicantNote::where('id', $note->id)->update([
                        'applicant_document_id' => $applicantDocument->id
                    ]);
                endif;
            endif;
            return response()->json(['message' => 'Applicant Note successfully created'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function admissionNotesList(Request $request){
        $applicantId = (isset($request->applicantId) && !empty($request->applicantId) ? $request->applicantId : 0);
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ApplicantNote::orderByRaw(implode(',', $sorts))->where('applicant_id', $applicantId);
        if(!empty($queryStr)):
            $query->where('note','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $docURL = '';
                if(isset($list->applicant_document_id) && isset($list->document)):
                    $docURL = (isset($list->document->current_file_name) && !empty($list->document->current_file_name)  && Storage::diske('google')->exists('public/applicants/'.$list->applicant_id.'/'.$list->document->current_file_name) ? Storage::disk('google')->url('public/applicants/'.$list->applicant_id.'/'.$list->document->current_file_name) : '');
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'note' => (strlen(strip_tags($list->note)) > 40 ? substr(strip_tags($list->note), 0, 40).'...' : strip_tags($list->note)),
                    'url' => $docURL,
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function admissionShowNote(Request $request){
        $noteId = $request->noteId;
        $note = ApplicantNote::find($noteId);
        $html = '';
        $btns = '';
        if(!empty($note) && !empty($note->note)):
            $html .= '<div>';
                $html .= $note->note;
            $html .= '</div>';
            if(isset($note->applicant_document_id) && isset($note->document)):
                $docURL = (isset($note->document->current_file_name) && !empty($note->document->current_file_name) && Storage::disk('google')->exists('public/applicants/'.$note->applicant_id.'/'.$note->document->current_file_name) ? Storage::disk('google')->url('public/applicants/'.$note->applicant_id.'/'.$note->document->current_file_name) : '');
                if(!empty($docURL)):
                    $btns .= '<a download href="'.$docURL.'" class="btn btn-primary w-auto inline-flex"><i data-lucide="cloud-lightning" class="w-4 h-4 mr-2"></i>Download Attachment</a>';
                endif;
            endif;
        else:
            $html .= '<div class="alert alert-danger-soft show flex items-start mb-2" role="alert">
                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! No data foudn for this note.
                    </div>';
        endif;

        return response()->json(['message' => $html, 'btns' => $btns], 200);
    }

    public function admissionGetNote(Request $request){
        $noteId = $request->noteId;
        $theNote = ApplicantNote::find($noteId);
        $docURL = '';
        if(isset($theNote->applicant_document_id) && isset($theNote->document)):
            $docURL = (isset($theNote->document->current_file_name) && !empty($theNote->document->current_file_name) && Storage::disk('google')->exists('public/applicants/'.$theNote->applicant_id.'/'.$theNote->document->current_file_name) ? Storage::disk('google')->url('public/applicants/'.$theNote->applicant_id.'/'.$theNote->document->current_file_name) : '');
        endif;
        $theNote['docURL'] = $docURL;

        return response()->json(['res' => $theNote], 200);
    }

    public function admissionUpdateNote(ApplicantNoteRequest $request){
        $applicant_id = $request->applicant_id;
        $noteId = $request->id;
        $oleNote = ApplicantNote::find($noteId);
        $applicantDocumentId = (isset($oleNote->applicant_document_id) && $oleNote->applicant_document_id > 0 ? $oleNote->applicant_document_id : 0);

        $note = ApplicantNote::where('id', $noteId)->where('applicant_id', $applicant_id)->Update([
            'applicant_id'=> $applicant_id,
            'note'=> $request->content,
            'phase'=> 'Admission',
            'updated_by' => auth()->user()->id
        ]);
        if($request->hasFile('document')):
            if($applicantDocumentId > 0 && isset($oleNote->document->current_file_name) && !empty($oleNote->document->current_file_name)):
                if (Storage::disk('google')->exists('public/applicants/'.$applicant_id.'/'.$oleNote->document->current_file_name)):
                    Storage::disk('google')->delete('public/applicants/'.$applicant_id.'/'.$oleNote->document->current_file_name);
                endif;

                $ad = ApplicantDocument::where('id', $applicantDocumentId)->forceDelete();
            endif;

            $document = $request->file('document');
            $documentName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/applicants/'.$applicant_id, $documentName, 'google');

            $data = [];
            $data['applicant_id'] = $applicant_id;
            $data['hard_copy_check'] = 0;
            $data['doc_type'] = $document->getClientOriginalExtension();
            $data['path'] = Storage::disk('google')->url($path);
            $data['display_file_name'] = $documentName;
            $data['current_file_name'] = $documentName;
            $data['created_by'] = auth()->user()->id;
            $applicantDocument = ApplicantDocument::create($data);

            if($applicantDocument):
                $noteUpdate = ApplicantNote::where('id', $noteId)->update([
                    'applicant_document_id' => $applicantDocument->id
                ]);
            endif;
        endif;
        return response()->json(['message' => 'Applicant Note successfully updated'], 200);
    }

    public function admissionDestroyNote(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $applicantNote = ApplicantNote::find($recordid);
        $applicantDocumentID = (isset($applicantNote->applicant_document_id) && $applicantNote->applicant_document_id > 0 ? $applicantNote->applicant_document_id : 0);
        ApplicantNote::find($recordid)->delete();

        if($applicantDocumentID > 0):
            ApplicantDocument::find($applicantDocumentID)->delete();
        endif;

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function admissionRestoreNote(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $data = ApplicantNote::where('id', $recordid)->withTrashed()->restore();
        $applicantNote = ApplicantNote::find($recordid);
        $applicantDocumentID = (isset($applicantNote->applicant_document_id) && $applicantNote->applicant_document_id > 0 ? $applicantNote->applicant_document_id : 0);
        if($applicantDocumentID > 0):
            ApplicantDocument::where('id', $applicantDocumentID)->withTrashed()->restore();
        endif;
        return response()->json(['message' => 'Successfully restored'], 200);
    }

    public function admissionUploadApplicantPhoto(Request $request){
        $applicant_id = $request->applicant_id;
        $applicantOldRow = Applicant::where('id', $applicant_id)->first();
        $oldPhoto = (isset($applicantOldRow->photo) && !empty($applicantOldRow->photo) ? $applicantOldRow->photo : '');

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/applicants/'.$applicant_id, $imageName, 'local');
        if(!empty($oldPhoto)):
            if (Storage::disk('local')->exists('public/applicants/'.$applicant_id.'/'.$oldPhoto)):
                Storage::disk('local')->delete('public/applicants/'.$applicant_id.'/'.$oldPhoto);
            endif;
        endif;

        $applicant = Applicant::find($applicant_id);
        $applicant->fill([
            'photo' => $imageName
        ]);
        $changes = $applicant->getDirty();
        $applicant->save();

        if($applicant->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicants';
                $data['field_name'] = $field;
                $data['field_value'] = $applicantOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['message' => 'Photo successfully change & updated'], 200);
    }

    public function admissionCommunication($applicantId){
        return view('pages.students.admission.communication', [
            'title' => 'Admission Management - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => route('admission.show', $applicantId)],
                ['label' => 'Communication', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'allStatuses' => Status::where('type', 'Applicant')->where('id', '>', 1)->get(),
            'smtps' => ComonSmtp::all(),
            'letterSet' => LetterSet::all(),
            'signatory' => Signatory::all(),
            'smsTemplates' => SmsTemplate::all(),
            'emailTemplates' => EmailTemplate::all(),
            'feeelegibility' => FeeEligibility::all()
        ]);
    }

    public function admissionGetLetterSet(Request $request){
        $letterSetId = $request->letterSetId;
        $letterSet = LetterSet::find($letterSetId);

        return response()->json(['res' => $letterSet], 200);
    }

    public function admissionSendLetter(SendLetterRequest $request){
        $applicant_id = $request->applicant_id;
        $applicant = Applicant::find($applicant_id);
        $pin = time();

        $issued_date = (!empty($request->issued_date) ? date('Y-m-d', strtotime($request->issued_date)) : date('Y-m-d'));
        $letter_set_id = $request->letter_set_id;
        $letterSet = LetterSet::find($letter_set_id);
        $letter_title = (isset($letterSet->letter_title) && !empty($letterSet->letter_title) ? $letterSet->letter_title : 'Letter from LCC');

        $letter_body = $request->letter_body;
        $is_email_or_attachment = (isset($request->is_email_or_attachment) && $request->is_email_or_attachment > 0 ? $request->is_email_or_attachment : 1);

        $signatory_id = $request->signatory_id;

        $comon_smtp_id = $request->comon_smtp_id;
        $commonSmtp = ComonSmtp::find($comon_smtp_id);

        $data = [];
        $data['applicant_id'] = $applicant_id;
        $data['letter_set_id'] = $letter_set_id;
        $data['pin'] = $pin;
        $data['signatory_id'] = $signatory_id;
        $data['comon_smtp_id'] = $comon_smtp_id;
        $data['is_email_or_attachment'] = $is_email_or_attachment;
        $data['issued_by'] = auth()->user()->id;
        $data['issued_date'] = $issued_date;
        $data['created_by'] = auth()->user()->id;

        $letter = ApplicantLetter::create($data);
        $attachmentFiles = [];
        if($letter):
            /* Generate PDF Start */
            $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
            $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();
            $LetterHeader = LetterHeaderFooter::where('for_letter', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
            $LetterFooters = LetterHeaderFooter::where('for_letter', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get();
            $PDFHTML = '';
            $PDFHTML .= '<html>';
                $PDFHTML .= '<head>';
                    $PDFHTML .= '<title>'.$letter_title.'</title>';
                    $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                    $PDFHTML .= '<style>
                                    table{margin-left: 0px;}
                                    figure{margin: 0;}
                                    @page{margin-top: 95px;margin-left: 30px;margin-right: 30px;margin-bottom: 95px;}
                                    header{position: fixed;left: 0px;right: 0px;height: 80px;margin-top: -70px;}
                                    footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px;margin-bottom: -120px;}
                                    .pageCounter{position: relative;}
                                    .pageCounter:before{content: counter(page);position: relative;display: inline-block;}
                                    .pinRow td{border-bottom: 1px solid gray;}
                                    .text-center{text-align: center;}
                                    .text-left{text-align: left;}
                                    .text-right{text-align: right;}
                                </style>';
                $PDFHTML .= '</head>';
                $PDFHTML .= '<body>';
                    if(isset($LetterHeader->current_file_name) && !empty($LetterHeader->current_file_name) && Storage::disk('google')->exists('public/letterheaderfooter/header/'.$LetterHeader->current_file_name)):
                        $PDFHTML .= '<header>';
                            $PDFHTML .= '<img style="width: 100%; height: auto;" src="'.Storage::disk('google')->url('public/letterheaderfooter/header/'.$LetterHeader->current_file_name).'"/>';
                        $PDFHTML .= '</header>';
                    endif;

                    $PDFHTML .= '<footer>';
                        $PDFHTML .= '<table style="width: 100%; border: none; margin: 0; vertical-align: middle !important; font-family: serif; 
                                    font-size: 8pt; color: #000000; font-weight: bold; font-style: italic;border-spacing: 0;border-collapse: collapse;">';
                            if($LetterFooters->count() > 0):
                                $PDFHTML .= '<tr>';
                                    $PDFHTML .= '<td colspan="2" class="footerPartners" style="text-align: center; vertical-align: middle;">';
                                        $numberOfPartners = $LetterFooters->count();
                                        $pertnerWidth = ((100 - 2) - (int) $numberOfPartners) / (int) $numberOfPartners;

                                        foreach($LetterFooters as $lf):
                                            if(Storage::disk('google')->exists('public/letterheaderfooter/footer/'.$lf->current_file_name)):
                                                $PDFHTML .= '<img style=" width: '.$pertnerWidth.'%; height: auto; margin-left:.5%; margin-right:.5%;" src="'.Storage::disk('google')->url('public/letterheaderfooter/footer/'.$lf->current_file_name).'" alt="'.$lf->name.'"/>';
                                            endif;
                                        endforeach;
                                    $PDFHTML .= '</td>';
                                $PDFHTML .= '</tr>';
                            endif;
                            $PDFHTML .= '<tr class="pinRow">';
                                $PDFHTML .= '<td style="padding-bottom: 3px;">';
                                    $PDFHTML .= '<span class="pageCounter text-left"></span>';
                                $PDFHTML .= '</td>';
                                $PDFHTML .= '<td class="pinNumber text-right" style="padding-bottom: 3px;">';
                                    $PDFHTML .= 'pin - '.$pin;
                                $PDFHTML .= '</td>';
                            $PDFHTML .= '</tr>';

                            if(!empty($regNo) || !empty($regAt)):
                            $PDFHTML .= '<tr class="regInfoRow">';
                                $PDFHTML .= '<td colspan="2" class="text-center" style="padding-top: 3px;">';
                                    $PDFHTML .= (!empty($regNo) ? 'Company Reg. No. '.$regNo->value : '');
                                    $PDFHTML .= (!empty($regAt) ? (!empty($regNo) ? ', ' : '').$regAt->value : '');
                                $PDFHTML .= '</td>';
                            $PDFHTML .= '</tr>';
                            endif;
                        $PDFHTML .= '</table>';
                    $PDFHTML .= '</footer>';

                    $PDFHTML .= $letter_body;
                    if($signatory_id > 0):
                        $signatory = Signatory::find($signatory_id);
                        $PDFHTML .= '<p>';
                            $PDFHTML .= '<strong>Best Regards,</strong><br/>';
                            if(isset($signatory->signature) && !empty($signatory->signature) && Storage::disk('google')->exists('public/signatories/'.$signatory->signature)):
                                $signatureImage = Storage::disk('google')->url('public/signatories/'.$signatory->signature); 
                                $PDFHTML .= '<img src="'.$signatureImage.'" style="width:150px; height: auto;" alt=""/><br/>';
                            endif;
                            $PDFHTML .= $signatory->signatory_name.'<br/>';
                            $PDFHTML .= $signatory->signatory_post.'<br/>';
                            $PDFHTML .= 'London Churchill College';
                        $PDFHTML .= '</p>';
                    endif;
                $PDFHTML .= '</body>';
            $PDFHTML .= '</html>';

            $fileName = time().'_'.$applicant_id.'_Letter.pdf';
            $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true, 'dpi' => 72])
                ->setPaper('a4', 'portrait')
                ->setWarnings(false);
            $content = $pdf->output();
            Storage::disk('google')->put('public/applicants/'.$applicant_id.'/'.$fileName, $content );

            $data = [];
            $data['applicant_id'] = $applicant_id;
            $data['hard_copy_check'] = 0;
            $data['doc_type'] = 'pdf';
            $data['path'] = Storage::disk('google')->url('public/applicants/'.$applicant_id.'/'.$fileName);
            $data['display_file_name'] = $letter_title;
            $data['current_file_name'] = $fileName;
            $data['created_by'] = auth()->user()->id;
            $applicantDocument = ApplicantDocument::create($data);

            if($applicantDocument):
                $noteUpdate = ApplicantLetter::where('id', $letter->id)->update([
                    'applicant_document_id' => $applicantDocument->id
                ]);
            endif;
            /* Generate PDF End */


            $signatoryHTML = '';
            if($signatory_id > 0):
                $signatory = Signatory::find($signatory_id);
                $signatoryHTML .= '<p>';
                    $signatoryHTML .= '<strong>Best Regards,</strong><br/>';
                    if(isset($signatory->signature) && !empty($signatory->signature) && Storage::disk('google')->exists('public/signatories/'.$signatory->signature)):
                        $signatureImage = Storage::disk('google')->url('public/signatories/'.$signatory->signature);
                        $signatoryHTML .= '<img src="'.$signatureImage.'" style="width:150px; height: auto; margin: 10px 0 10px;" alt="'.$signatory->signatory_name.'"/><br/>';
                    endif;
                    $signatoryHTML .= $signatory->signatory_name.'<br/>';
                    $signatoryHTML .= $signatory->signatory_post.'<br/>';
                    $signatoryHTML .= 'London Churchill College';
                $signatoryHTML .= '</p>';
            else:
                $signatoryHTML .= '<p>';
                    $signatoryHTML .= '<strong>Best Regards,</strong><br/>';
                    $signatoryHTML .= 'The Academic Admin Dept.<br/>';
                    $signatoryHTML .= 'London Churchill College';
                $signatoryHTML .= '</p>';
            endif;

            $emailHTML = '';
            $emailHTML .= 'Dear '.$applicant->first_name.' '.$applicant->last_name.', <br/>';
            if($is_email_or_attachment == 2):
                $emailHTML .= '<p>Please Find the letter attached herewith. </p>';

                $attachmentFiles[] = [
                    "pathinfo" => 'public/applicants/'.$applicant_id.'/'.$fileName,
                    "nameinfo" => $fileName,
                    "mimeinfo" => 'application/pdf',
                    'disk'     => 'google'
                ];
            else:
                $emailHTML .= $letter_body;
            endif;
            $emailHTML .= $signatoryHTML;

            $configuration = [
                'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                
                'from_email'    => 'no-reply@lcc.ac.uk',
                'from_name'    =>  'London Churchill College',
            ];
            UserMailerJob::dispatch($configuration, $applicant->users->email, new CommunicationSendMail($letter_title, $emailHTML, $attachmentFiles));

            return response()->json(['message' => 'Letter successfully generated and distributed.'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try latter.'], 422);
        endif;
    }

    public function admissionCommunicationLetterList(Request $request){
        $applicantId = (isset($request->applicantId) && !empty($request->applicantId) ? $request->applicantId : 0);
        $queryStr = (isset($request->queryStrCML) && $request->queryStrCML != '' ? $request->queryStrCML : '');
        $status = (isset($request->statusCML) && $request->statusCML > 0 ? $request->statusCML : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = DB::table('applicant_letters as al')
                        ->select('al.*', 'ls.letter_type', 'ls.letter_title', 'sg.signatory_name', 'sg.signatory_post', 'ur.name as created_bys', 'adc.current_file_name')
                        ->leftJoin('letter_sets as ls', 'al.letter_set_id', '=', 'ls.id')
                        ->leftJoin('signatories as sg', 'al.signatory_id', '=', 'sg.id')
                        ->leftJoin('users as ur', 'al.issued_by', '=', 'ur.id')
                        ->leftJoin('applicant_documents as adc', 'al.applicant_document_id', '=', 'adc.id')
                        ->where('al.applicant_id', '=', $applicantId);
        if(!empty($queryStr)):
            $query->where('ls.letter_type','LIKE','%'.$queryStr.'%');
            $query->orWhere('ls.letter_title','LIKE','%'.$queryStr.'%');
            $query->orWhere('sg.signatory_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('sg.signatory_post','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->whereNotNull('al.deleted_at');
        else:
            $query->whereNull('al.deleted_at');
        endif;
        $query->orderByRaw(implode(',', $sorts));

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->offset($offset)
               ->limit($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $docURL = '';
                if(isset($list->applicant_document_id) && $list->applicant_document_id > 0 && isset($list->current_file_name)):
                    $docURL = (!empty($list->current_file_name) ? Storage::disk('google')->url('public/applicants/'.$list->applicant_id.'/'.$list->current_file_name) : '');
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'letter_type' => $list->letter_type,
                    'letter_title' => $list->letter_title,
                    'signatory_name' => (isset($list->signatory_name) && !empty($list->signatory_name) ? $list->signatory_name : ''),
                    'docurl' => $docURL,
                    'created_by'=> (isset($list->created_bys) ? $list->created_bys : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function admissionDestroyLetter(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        ApplicantLetter::find($recordid)->delete();

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function admissionRestoreLetter(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        ApplicantLetter::where('id', $recordid)->withTrashed()->restore();
        return response()->json(['message' => 'Successfully restored'], 200);
    }

    public function admissionCommunicationSendMail(SendEmailRequest $request){

        $applicantID = $request->applicant_id;
        $Applicant = Applicant::find($applicantID);

        $applicantEmail = ApplicantEmail::create([
            'applicant_id' => $applicantID,
            'comon_smtp_id' => $request->comon_smtp_id,
            'email_template_id' => (isset($request->email_template_id) && $request->email_template_id > 0 ? $request->email_template_id : NULL),
            'subject' => $request->subject,
            'body' => $request->body,
            'created_by' => auth()->user()->id,
        ]);

        $commonSmtp = ComonSmtp::find($request->comon_smtp_id);

        $configuration = [
            'smtp_host'    => $commonSmtp->smtp_host,
            'smtp_port'    => $commonSmtp->smtp_port,
            'smtp_username'  => $commonSmtp->smtp_user,
            'smtp_password'  => $commonSmtp->smtp_pass,
            'smtp_encryption'  => $commonSmtp->smtp_encryption,
            
            'from_email'    => $commonSmtp->smtp_user,
            'from_name'    =>  strtok($commonSmtp->smtp_user, '@'),
        ];

        if($applicantEmail):
            $emailHeader = LetterHeaderFooter::where('for_email', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
            $emailFooters = LetterHeaderFooter::where('for_email', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get();

            $MAILHTML = '';
            if(isset($emailHeader->current_file_name) && !empty($emailHeader->current_file_name) && Storage::disk('google')->exists('public/letterheaderfooter/header/'.$emailHeader->current_file_name)):
                $MAILHTML .= '<div style="margin: 0 0 30px 0;">';
                    $MAILHTML .= '<img style="width: 100%; height: auto;" src="'.Storage::disk('google')->url('public/letterheaderfooter/header/'.$emailHeader->current_file_name).'"/>';
                $MAILHTML .= '</div>';
            endif;
            $MAILHTML .= $request->body;
            if($emailFooters->count() > 0):
                $MAILHTML .= '<div style="text-align: center; vertical-align: middle; margin: 20px 0 0 0;">';
                    $numberOfPartners = $emailFooters->count();
                    $pertnerWidth = ((100 - 2) - (int) $numberOfPartners) / (int) $numberOfPartners;

                    foreach($emailFooters as $lf):
                        if(Storage::disk('google')->exists('public/letterheaderfooter/footer/'.$lf->current_file_name)):
                            $MAILHTML .= '<img style=" width: '.$pertnerWidth.'%; height: auto; margin-left:.5%; margin-right:.5%;" src="'.Storage::disk('google')->url('public/letterheaderfooter/footer/'.$lf->current_file_name).'" alt="'.$lf->name.'"/>';
                        endif;
                    endforeach;
                $MAILHTML .= '</div>';
            endif;

            if($request->hasFile('documents')):
                $documents = $request->file('documents');
                $docCounter = 1;
                $attachmentInfo = [];
                foreach($documents as $document):
                    $documentName = time().'_'.$document->getClientOriginalName();
                    $path = $document->storeAs('public/applicants/'.$applicantID, $documentName, 'google');

                    $data = [];
                    $data['applicant_id'] = $applicantID;
                    $data['hard_copy_check'] = 0;
                    $data['doc_type'] = $document->getClientOriginalExtension();
                    $data['path'] = Storage::disk('google')->url($path);
                    $data['display_file_name'] = $documentName;
                    $data['current_file_name'] = $documentName;
                    $data['created_by'] = auth()->user()->id;
                    $applicantDocument = ApplicantDocument::create($data);

                    if($applicantDocument):
                        $noteUpdate = ApplicantEmailsAttachment::create([
                            'applicant_email_id' => $applicantEmail->id,
                            'applicant_document_id' => $applicantDocument->id,
                            'created_by' => auth()->user()->id
                        ]);

                        $attachmentInfo[$docCounter++] = [
                            "pathinfo" => $path,
                            "nameinfo" => $document->getClientOriginalName(),
                            "mimeinfo" => $document->getMimeType(),
                            'disk'     => 'google'
                        ];
                        $docCounter++;
                    endif;
                endforeach;
                UserMailerJob::dispatch($configuration,$Applicant->users->email, new CommunicationSendMail($request->subject, $MAILHTML, $attachmentInfo))->now();
            else:
                UserMailerJob::dispatch($configuration, $Applicant->users->email, new CommunicationSendMail($request->subject, $MAILHTML, []))->now();
            endif;
            return response()->json(['message' => 'Email successfully sent to Applicant'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        endif;
    }

    public function admissionCommunicationMailList(Request $request){
        $applicantId = (isset($request->applicantId) && !empty($request->applicantId) ? $request->applicantId : 0);
        $queryStr = (isset($request->queryStrCME) && $request->queryStrCME != '' ? $request->queryStrCME : '');
        $status = (isset($request->statusCME) && $request->statusCME > 0 ? $request->statusCME : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ApplicantEmail::orderByRaw(implode(',', $sorts))->where('applicant_id', $applicantId);
        if(!empty($queryStr)):
            $query->where('subject','LIKE','%'.$queryStr.'%');
            $query->orWhere('body','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'subject' => $list->subject,
                    'smtp' => (isset($list->smtp->smtp_user) && !empty($list->smtp->smtp_user) ? $list->smtp->smtp_user : ''),
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function admissionCommunicationSendSms(SendSmsRequest $request){
        $applicantID = $request->applicant_id;
        $smsTemplateID = (isset($request->sms_template_id) && $request->sms_template_id > 0 ? $request->sms_template_id : NULL);
        $applicantSms = ApplicantSms::create([
            'applicant_id' => $applicantID,
            'sms_template_id' => $smsTemplateID,
            'subject' => $request->subject,
            'sms' => $request->sms,
            'created_by' => auth()->user()->id,
        ]);
        
        if($applicantSms):
            $applicantContact = ApplicantContact::where('applicant_id', $applicantID)->get()->first();
            if(isset($applicantContact->mobile) && !empty($applicantContact->mobile)):
                $active_api = Option::where('category', 'SMS')->where('name', 'active_api')->pluck('value')->first();
                $textlocal_api = Option::where('category', 'SMS')->where('name', 'textlocal_api')->pluck('value')->first();
                $smseagle_api = Option::where('category', 'SMS')->where('name', 'smseagle_api')->pluck('value')->first();
                if($active_api == 1 && !empty($textlocal_api)):
                    $response = Http::timeout(-1)->post('https://api.textlocal.in/send/', [
                        'apikey' => $textlocal_api, 
                        'message' => $request->sms, 
                        'sender' => 'London Churchill College', 
                        'numbers' => $applicantContact->mobile
                    ]);
                elseif($active_api == 2 && !empty($smseagle_api)):
                    $response = Http::withHeaders([
                            'access-token' => $smseagle_api,
                            'Content-Type' => 'application/json',
                        ])->withoutVerifying()->withOptions([
                            "verify" => false
                        ])->post('https://79.171.153.104/api/v2/messages/sms', [
                            'to' => [$applicantContact->mobile],
                            'text' => $request->sms
                        ]);
                endif;
                $message = 'SMS successfully stored and sent to the student.';
            else:
                $message = 'SMS stored into database but not sent due to missing mobile number.';
            endif;
            return response()->json(['message' => $message], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        endif;
    }

    public function admissionCommunicationSmsList(Request $request){
        $applicantId = (isset($request->applicantId) && !empty($request->applicantId) ? $request->applicantId : 0);
        $queryStr = (isset($request->queryStrCMS) && $request->queryStrCMS != '' ? $request->queryStrCMS : '');
        $status = (isset($request->statusCMS) && $request->statusCMS > 0 ? $request->statusCMS : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ApplicantSms::orderByRaw(implode(',', $sorts))->where('applicant_id', $applicantId);
        if(!empty($queryStr)):
            $query->where('subject','LIKE','%'.$queryStr.'%');
            $query->orWhere('sms','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'template' => isset($list->template->sms_title) && !empty($list->template->sms_title) ? $list->template->sms_title : '',
                    'subject' => $list->subject,
                    'sms' => (strlen(strip_tags($list->sms)) > 40 ? substr(strip_tags($list->sms), 0, 40).'...' : strip_tags($list->sms)),
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function admissionDestroyMail(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        $applicantMailAttachments = ApplicantEmailsAttachment::where('applicant_email_id', $recordid)->get();
        if(!empty($applicantMailAttachments)):
            foreach($applicantMailAttachments as $attachment):
                $applicantDoc = ApplicantDocument::find($attachment->applicant_document_id)->delete();
            endforeach;
        endif;
        ApplicantEmail::find($recordid)->delete();

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function admissionRestoreMail(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        ApplicantEmail::where('id', $recordid)->withTrashed()->restore();
        $applicantMailAttachments = ApplicantEmailsAttachment::where('applicant_email_id', $recordid)->get();
        if(!empty($applicantMailAttachments)):
            foreach($applicantMailAttachments as $attachment):
                $applicantDoc = ApplicantDocument::where('id', $attachment->applicant_document_id)->withTrashed()->restore();
            endforeach;
        endif;
        return response()->json(['message' => 'Successfully restored'], 200);
    }

    public function admissionGetMailTemplate(Request $request){
        $emailTemplateID = $request->emailTemplateID;
        $emailTemplate = EmailTemplate::find($emailTemplateID);

        return response()->json(['row' => $emailTemplate], 200);
    }

    public function admissionCommunicationMailShow(Request $request){
        $mailId = $request->recordId;
        $mail = ApplicantEmail::find($mailId);
        $heading = 'Mail Subject: <u>'.$mail->subject.'</u>';
        $html = '';
        $html .= '<div class="grid grid-cols-12 gap-4">';
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">Issued Date</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div>'.(isset($mail->created_at) && !empty($mail->created_at) ? date('jS F, Y', strtotime($mail->created_at)) : '').'</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">Issued By</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div>'.(isset($mail->user->name) ? $mail->user->name : 'Unknown').'</div>';
            $html .= '</div>';
            if(isset($mail->documents) && !empty($mail->documents)):
                $html .= '<div class="col-span-3">';
                    $html .= '<div class="text-slate-500 font-medium">Attachments</div>';
                $html .= '</div>';
                $html .= '<div class="col-span-9">';
                    foreach($mail->documents as $doc):
                        $html .= '<a target="_blank" class="mb-1 text-primary font-medium flex justify-start items-center" href="'.Storage::disk('google')->url('public/applicants/'.$doc->applicant_id.'/'.$doc->current_file_name).'" download><i data-lucide="disc" class="w-3 h3 mr-2"></i>'.$doc->current_file_name.'</a>';
                    endforeach;
                $html .= '</div>';
            endif;
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">Mail Description</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div class="mailContent">'.$mail->body.'</div>';
            $html .= '</div>';
        $html .= '</div>';

        return response()->json(['heading' => $heading, 'html' => $html], 200);
    }

    public function admissionDestroySms(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        ApplicantSms::find($recordid)->delete();

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function admissionRestoreSms(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        ApplicantSms::where('id', $recordid)->withTrashed()->restore();
        return response()->json(['message' => 'Successfully restored'], 200);
    }

    public function admissionGetSmsTemplate(Request $request){
        $smsTemplateId = $request->smsTemplateId;
        $smsTemplate = SmsTemplate::find($smsTemplateId);

        return response()->json(['row' => $smsTemplate], 200);
    }

    public function admissionCommunicationSmsShow(Request $request){
        $mailId = $request->recordId;
        $sms = ApplicantSms::find($mailId);
        $heading = 'Mail Subject: <u>'.$sms->subject.'</u>';
        $html = '';
        $html .= '<div class="grid grid-cols-12 gap-4">';
            if(isset($sms->template->sms_title) && !empty($sms->template->sms_title)):
                $html .= '<div class="col-span-3">';
                    $html .= '<div class="text-slate-500 font-medium">Template</div>';
                $html .= '</div>';
                $html .= '<div class="col-span-9">';
                    $html .= '<div>'.(isset($sms->template->sms_title) ? $sms->template->sms_title : 'Unknown').'</div>';
                $html .= '</div>';
            endif;
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">Issued Date</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div>'.(isset($sms->created_at) && !empty($sms->created_at) ? date('jS F, Y', strtotime($sms->created_at)) : '').'</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">Issued By</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div>'.(isset($sms->user->name) ? $sms->user->name : 'Unknown').'</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">SMS Text</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div class="mailContent">'.$sms->sms.'</div>';
            $html .= '</div>';
        $html .= '</div>';

        return response()->json(['heading' => $heading, 'html' => $html], 200);
    }

    public function admissionStudentStatusValidation(Request $request){
        $applicantID = $request->applicantID;
        $Proof = ApplicantProofOfId::where('applicant_id', $applicantID)->latest()->first();
        $eligible = ApplicantFeeEligibility::where('applicant_id', $applicantID)->latest()->first();
        $res = [];
        if((!isset($Proof->proof_type) || $Proof->proof_type == '') || (!isset($Proof->proof_id) || $Proof->proof_id == '') || (!isset($Proof->proof_expiredate) || $Proof->proof_expiredate == '') || (!isset($eligible->fee_eligibility_id) || $eligible->fee_eligibility_id == '')){
            $res['proof_type'] = !isset($Proof->proof_type) || $Proof->proof_type == '' ? ['suc' => 2, 'vals' => ''] : ['suc' => 1, 'vals' => $Proof->proof_type];
            $res['proof_id'] = !isset($Proof->proof_id) || $Proof->proof_id == '' ? ['suc' => 2, 'vals' => ''] : ['suc' => 1, 'vals' => $Proof->proof_id];
            $res['proof_expiredate'] = !isset($Proof->proof_expiredate) || $Proof->proof_expiredate == '' ? ['suc' => 2, 'vals' => ''] : ['suc' => 1, 'vals' => $Proof->proof_expiredate];
            $res['fee_eligibility_id'] = !isset($eligible->fee_eligibility_id) || $eligible->fee_eligibility_id == '' ? ['suc' => 2, 'vals' => ''] : ['suc' => 1, 'vals' => $eligible->fee_eligibility_id];

            $res['suc'] = 2;
        }else{
            $res['suc'] = 1;
        }
        return response(['msg' => $res], 200);
    }

    public function admissionStudentUpdateStatus(Request $request){
        $applicant_id = $request->applicantID;
        $statusidID = $request->statusidID;
        $rejectedReason = (isset($request->rejectedReason) && !empty($request->rejectedReason) ? $request->rejectedReason : Null);

        $applicantOldRow = Applicant::find($applicant_id);

        $statusData = [];
        $statusData['status_id'] = $statusidID;
        $statusData['rejected_reason'] = $rejectedReason;
        

        $applicant = Applicant::find($applicant_id);
        $applicant->fill($statusData);
        $changes = $applicant->getDirty();
        $applicant->save();

        if($applicant->wasChanged() && !empty($changes)):
            if($statusidID == 7):
                $existingProofId = (isset($applicantOldRow->proof->id) && $applicantOldRow->proof->id > 0 ? $applicantOldRow->proof->id : 0);
                $existingElegbId = (isset($applicantOldRow->feeeligibility->id) && $applicantOldRow->feeeligibility->id > 0 ? $applicantOldRow->feeeligibility->id : 0);

                $applicantProof = ApplicantProofOfId::updateOrCreate([ 'applicant_id' => $applicant_id, 'id' => $existingProofId ], [
                    'applicant_id' => $applicant_id,
                    'proof_type' => $request->proof_type,
                    'proof_id' => $request->proof_id,
                    'proof_expiredate' => $request->proof_expiredate,
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]);
                $applicantEligibility = ApplicantFeeEligibility::updateOrCreate([ 'applicant_id' => $applicant_id, 'id' => $existingElegbId ], [
                    'applicant_id' => $applicant_id,
                    'fee_eligibility_id' => $request->fee_eligibility_id,
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]);

                $academicYear = AcademicYear::orderBy('id', 'desc')->where('from_date', '<=', Carbon::now())->where('to_date', '>=', Carbon::now())->first();
                if(isset($academicYear->id) && $academicYear->id > 0):
                    $applicantPropCourse = ApplicantProposedCourse::where('applicant_id', $applicant_id)->update([
                        'academic_year_id' => $academicYear->id,
                        'updated_by' => auth()->user()->id,
                    ]);
                endif;

                /* Student Process Start */
                $bus = Bus::batch([
                    new ProcessNewStudentToUser($applicant),
                    new ProcessStudents($applicant),
                    new ProcessStudentNoteDetails($applicant),
                    new ProcessStudentTask($applicant),
                    new ProcessStudentTaskDocument($applicant),
                    new ProcessStudentQualification($applicant),
                    new ProcessStudentContact($applicant),
                    new ProcessStudentDisability($applicant),
                    new ProcessStudentEmployement($applicant),
                    new ProcessStudentProposedCourse($applicant),
                    new ProcessStudentKinDetail($applicant),
                    new ProcessStudentOtherDetails($applicant),
                    new ProcessStudentProofOfId($applicant),
                    new ProcessStudentFeeEligibility($applicant),
                    new ProcessStudentSms($applicant),
                    new ProcessStudentLetter($applicant),
                    new ProcessStudentInterview($applicant),
                    new ProcessStudentEmail($applicant),
                    new ProcessStudentConsent($applicant),
                ])->dispatch();
                
                session()->put("lastBatchId",$bus->id);
                /* Student Process END */
            endif;
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicants';
                $data['field_name'] = $field;
                $data['field_value'] = $applicantOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;

            return response()->json(['message' => 'Student status successfully updated'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function admissionInterviewLogList(Request $request){
        $applicantTaskId = (isset($request->applicantTaskId) && $request->applicantTaskId > 0 ? $request->applicantTaskId : 0);
        $applicantId = (isset($request->applicantId) && $request->applicantId > 0 ? $request->applicantId : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ApplicantInterview::where('applicant_id', $applicantId)->where('applicant_task_id', $applicantTaskId)->orderByRaw(implode(',', $sorts));

        
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
                    'date' => $list->interview_date,
                    'time' => $list->start_time.' - '.$list->end_time,
                    'result' => $list->interview_result,
                    'status' => $list->interview_status,
                    'interviewer' => (isset($list->user->name) ? $list->user->name : ''),
                    'file' => ($list->document) ? $list->document->path : ''
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    // Job Batch Function
    public function progressForStudentStoreProcess(Request $request) {
        try
        {
            $batchId = $request->id ?? session()->get('lastBatchId');

            if(JobBatch::where('id', $batchId)->count())
            {
                $response = JobBatch::where('id', $batchId)->first();
                 
                return response()->json($response);
            // } else {
            //     return response()->json(["total_jobs" => 0,"pending_jobs"=>0]);
            }
        }
        catch(Exception $e)
        {
            Log::error($e);
            //dd($e);
        }
    }
    public $applicant;
    public function convertStudentDemo() {

        // $this->applicant  = Applicant::find(1);  
        // $ApplicantUser = ApplicantUser::find($this->applicant->applicant_user_id);
        // $user = StudentUser::where(["email"=> $ApplicantUser->email])->get()->first();
        // $student = Student::where(["student_user_id"=> $user->id])->get()->first(); 
        
        // $getStudentCourseRelationData= StudentProposedCourse::where('student_id',$student->id)->get()->first();
        // //Begin
        // $applicantSetData = ApplicantFeeEligibility::where('applicant_id',$this->applicant->id)->get();

        // foreach($applicantSetData as $applicantSet):

        //     $dataArray = [
        //         'student_id' => $student->id,
        //         'student_course_relation_id' => $getStudentCourseRelationData->student_course_relation_id,
        //         'fee_eligibility_id' => ($applicantSet->fee_eligibility_id) ?? 'NULL',
        //         'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
        //     ];

        //     $data = new StudentFeeEligibility();

        //     $data->fill($dataArray);

        //     $data->save();
        //     unset ($dataArray);

        // endforeach;
    
    }

    public function sendMobileVerificationCode(Request $request){
        $applicant_id = $request->applicant_id;
        $mobileNo = $request->mobileNo;

        $verificationCode = rand(100000, 999999);
        $mobileVerification = MobileVerificationCode::create([
            'applicant_id' => $applicant_id,
            'mobile' => $mobileNo,
            'code' => $verificationCode,
            'status' => 0,
            'created_by' => auth()->user()->id,
        ]);
        if($mobileVerification):
            $siteName = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_name')->value('value');
            $siteName = (!empty($siteName) ? $siteName : 'London Churchill College');
            $active_api = Option::where('category', 'SMS')->where('name', 'active_api')->pluck('value')->first();
            $textlocal_api = Option::where('category', 'SMS')->where('name', 'textlocal_api')->pluck('value')->first();
            $smseagle_api = Option::where('category', 'SMS')->where('name', 'smseagle_api')->pluck('value')->first();
            $message = 'London Churchill College requested you to verify your mobile number. Please use your verification code '.$verificationCode.' to verify.  Thank you.';

            if($active_api == 1 && !empty($textlocal_api)):
                $response = Http::timeout(-1)->post('https://api.textlocal.in/send/', [
                    'apikey' => $textlocal_api, 
                    'message' => $message,
                    'sender' => $siteName, 
                    'numbers' => $mobileNo
                ]);
            elseif($active_api == 2 && !empty($smseagle_api)):
                $response = Http::withHeaders([
                        'access-token' => $smseagle_api,
                        'Content-Type' => 'application/json',
                    ])->withoutVerifying()->withOptions([
                        "verify" => false
                    ])->post('https://79.171.153.104/api/v2/messages/sms', [
                        'to' => [$mobileNo],
                        'text' => $message
                    ]);
                //return response()->json(['Message' => $response->json()], 200);
            endif;
            return response()->json(['Message' => 'Verification code successfully send to the mobile nuber.'], 200);
        else:
            return response()->json(['Message' => 'Something went wrong. Please try later'], 422);
        endif;
    }

    public function verifyMobileVerificationCode(Request $request){
        $applicant_id = $request->applicant_id;
        $code = $request->code;
        $mobile = $request->mobile;

        $applicantCodes = MobileVerificationCode::where('applicant_id', $applicant_id)->where('mobile', $mobile)
                            ->where('code', $code)->where('status', '!=', 1)->orderBy('id', 'DESC')->get()->first();
        if(isset($applicantCodes->id) && $applicantCodes->id > 0):
            MobileVerificationCode::where('id', $applicantCodes->id)->update(['status' => 1]);
            ApplicantContact::where('applicant_id', $applicant_id)->update(['mobile_verification' => 1]);

            return response()->json(['suc' => 1], 200);
        else:
            return response()->json(['suc' => 2], 200);
        endif;
    }

}
