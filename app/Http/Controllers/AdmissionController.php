<?php

namespace App\Http\Controllers;

use App\Exports\ArrayCollectionExport;
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
use App\Jobs\ProcessStudentDocuments;
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
use App\Mail\ResetPasswordLink;
use App\Models\AcademicYear;
use App\Models\Agent;
use App\Models\ApplicantInterview;
use App\Models\ApplicantLetter;
use App\Models\ApplicantProofOfId;
use App\Models\ApplicantUser;
use App\Models\ApplicationRejectedReason;
use App\Models\CourseCreationAvailability;
use App\Models\CourseCreationVenue;
use App\Models\EmailTemplate;
use App\Models\Employee;
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
use App\Models\Venue;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

use GuzzleHttp\Client;
use App\Traits\GenerateApplicantLetterTrait;
use Barryvdh\Debugbar\Facades\Debugbar as FacadesDebugbar;
use DebugBar\DebugBar;
use Illuminate\Auth\Events\Registered;

class AdmissionController extends Controller
{
    use GenerateApplicantLetterTrait;

    //I want to upate admission status of the applicant at class construction
    // public function __construct()
    // {
    //     $this->updateAdmissionStatus();
    // }
    // private function updateAdmissionStatus()
    // {
    //     $completedApplicantIds = [];
    //     //check status of applicants where all tasks are completed
    //     $applicants = Applicant::where('status_id', 3)->get();
    //     foreach ($applicants as $applicant) {
    //         if ($applicant->tasks->where('status', 'completed')->count() === $applicant->tasks->count()) {
    //             if( $applicant->status_id == 3) {

    //                 //$applicant->status_id = 4; // Assuming 4 is the next status for 'Completed'
    //                 // input id into an array
    //                 $completedApplicantIds[] = $applicant->id;
    //                 //$applicant->save();
    //             }
                
    //         }
    //     }
    //     FacadesDebugbar::info('Updating Admission Status for Applicants: ' . implode(', ', $completedApplicantIds));
    //     // Update the status of completed applicants
    //     //Applicant::whereIn('id', $completedApplicantIds)->update(['status_id' => 4]);
    // }
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
            'title' => 'Recruitment - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => 'javascript:void(0);']
            ],
            'semesters' => $semesters,
            'courses' => $courses,
            'allStatuses' => $statuses,
            'agents' => Agent::orderBy('first_name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        
        $semesters = (isset($request->semesters) && !empty($request->semesters) ? $request->semesters : []);
        $courses = (isset($request->courses) && !empty($request->courses) ? $request->courses : []);
        $statuses = (isset($request->statuses) && !empty($request->statuses) ? $request->statuses : []);
        $agents = (isset($request->agents) && !empty($request->agents) ? $request->agents : []);
        $refno = (isset($request->refno) && !empty($request->refno) ? $request->refno : '');
        $firstname = (isset($request->firstname) && !empty($request->firstname) ? $request->firstname : '');
        $lastname = (isset($request->lastname) && !empty($request->lastname) ? $request->lastname : '');
        
        $email = (isset($request->email) && !empty($request->email) ? $request->email : '');
        $phone = (isset($request->phone) && !empty($request->phone) ? $request->phone : '');

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
        
        if(!empty($email) || !empty($phone)):
            $query->whereHas('users', function($qs) use($email, $phone){
                if(!empty($email)): $qs->where('email', 'LIKE', '%'.$email.'%'); endif;
                if(!empty($phone)): $qs->where('phone', 'LIKE', '%'.$phone.'%'); endif;
            });
        endif;

        if(!empty($dob)): $query->where('date_of_birth', $dob); endif;
        if(!empty($statuses)): $query->whereIn('status_id', $statuses); else: $query->where('status_id', '>', 1); endif;
        if(!empty($semesters) || !empty($courseCreationId)):
            $query->whereHas('course', function($qs) use($semesters, $courses, $courseCreationId){
                if(!empty($semesters)): $qs->whereIn('semester_id', $semesters); endif;
                if(!empty($courses) && !empty($courseCreationId)): $qs->whereIn('course_creation_id', $courseCreationId); endif;
            });
        endif;
        if(!empty($agents)): $query->whereIn('agent_user_id', $agents); endif;

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
        
        if($Query->isNotEmpty()):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'application_no' => (empty($list->application_no) ? $list->id : $list->application_no),
                    'first_name' => ucfirst($list->first_name),
                    'last_name' => ucfirst($list->last_name),
                    'full_name' => ucfirst($list->first_name)." ".ucfirst($list->last_name),
                    
                    'date_of_birth'=> $list->date_of_birth,
                    'course'=> (isset($list->course->creation->course->name) ? $list->course->creation->course->name : ''),
                    'semester'=> (isset($list->course->semester->name) ? $list->course->semester->name : ''),
                    'full_time'=> (isset($list->course->full_time) ? "Yes": "No"),
                    'gender'=> (isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : ''),
                    'status_id'=> (isset($list->status->name) ? $list->status->name : ''),
                    'url' => route('admission.show', $list->id),
                    'ccid' => implode(',', $courses).' - '.implode(',', $courseCreationId),
                    'photo_url' => $list->photo_url,
                    'create_account' => false,
                    'apply_ready' => false,
                ];
                $i++;
            endforeach;
        else:
            if(!empty($refno)):
                $i = 1;
                $list = Student::where('application_no', $refno)->get()->first();
                $createPermission = auth()->user()->priv()['create_an_applicant'];
                $applicantFound = ApplicantUser::where('email', $list->contact->personal_email)->first();
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'application_no' => (empty($list->application_no) ? $list->id : $list->application_no),
                    'first_name' => ucfirst($list->first_name),
                    'last_name' => ucfirst($list->last_name),
                    'full_name' => ucfirst($list->first_name)." ".ucfirst($list->last_name),
                    
                    'date_of_birth'=> $list->date_of_birth,
                    'course'=> (isset($list->course->creation->course->name) ? $list->course->creation->course->name : ''),
                    'semester'=> (isset($list->course->semester->name) ? $list->course->semester->name : ''),
                    'full_time'=> (isset($list->course->full_time) ? "Yes": "No"),
                    'gender'=> (isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : ''),
                    'status_id'=> (isset($list->status->name) ? $list->status->name : ''),
                    'url' => route('admission.show', $list->id),
                    'ccid' => implode(',', $courses).' - '.implode(',', $courseCreationId),
                    'photo_url' => $list->photo_url,
                    'create_account' => (isset($createPermission) && $createPermission==1) ? true : false,
                    'apply_ready' => isset($applicantFound) ? $applicantFound->id : false,
                ];
            endif;
        endif;
        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    private function dataSetList(Request $request){
        
        $semesters = (isset($request->semesters) && !empty($request->semesters) ? $request->semesters : []);
        $courses = (isset($request->courses) && !empty($request->courses) ? $request->courses : []);
        $statuses = (isset($request->statuses) && !empty($request->statuses) ? $request->statuses : []);
        $agents = (isset($request->agents) && !empty($request->agents) ? $request->agents : []);
        $refno = (isset($request->refno) && !empty($request->refno) ? $request->refno : '');
        $firstname = (isset($request->firstname) && !empty($request->firstname) ? $request->firstname : '');
        $lastname = (isset($request->lastname) && !empty($request->lastname) ? $request->lastname : '');
        
        $email = (isset($request->email) && !empty($request->email) ? $request->email : '');
        $phone = (isset($request->phone) && !empty($request->phone) ? $request->phone : '');
        $semister_id = (isset($request->semister_id) && !empty($request->semister_id) ? $request->semister_id : '');

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
        
        if(!empty($email) || !empty($phone)):
            $query->whereHas('users', function($qs) use($email, $phone){
                if(!empty($email)): $qs->where('email', 'LIKE', '%'.$email.'%'); endif;
                if(!empty($phone)): $qs->where('phone', 'LIKE', '%'.$phone.'%'); endif;
            });
        endif;

        if(!empty($dob)): $query->where('date_of_birth', $dob); endif;
        if(!empty($statuses)): $query->whereIn('status_id', $statuses); else: $query->where('status_id', '>', 1); endif;
        if(!empty($semesters) || !empty($courseCreationId)):
            $query->whereHas('course', function($qs) use($semesters, $courses, $courseCreationId){
                if(!empty($semesters)): $qs->whereIn('semester_id', $semesters); endif;
                if(!empty($courses) && !empty($courseCreationId)): $qs->whereIn('course_creation_id', $courseCreationId); endif;
            });
        endif;
        if(!empty($agents)): $query->whereIn('agent_user_id', $agents); endif;
        

       

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        // $Query = $query->skip($offset)
        //        ->take($limit)
        $Query =  $query->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                
                $data[] = (object) [
                    'id' => $list->id,
                    'sl' => $i,
                    'application_no' => (empty($list->application_no) ? $list->id : $list->application_no),
                    'first_name' => ucfirst($list->first_name),
                    'last_name' => ucfirst($list->last_name),
                    'date_of_birth'=> $list->date_of_birth,
                    'course'=> (isset($list->course->creation->course->name) ? $list->course->creation->course->name : ''),
                    'semester'=> (isset($list->course->semester->name) ? $list->course->semester->name : ''),
                    'venue' => (isset($list->course->venue->name) ? $list->course->venue->name : ''),
                    'full_time'=> (isset($list->course->full_time) && $list->course->full_time == 1) ? "Yes": "No",
                    'gender'=> (isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : ''),
                    'status_id'=> (isset($list->status->name) ? $list->status->name : ''),
                    'url' => route('admission.show', $list->id),
                    'referral_code' => (isset($list->referral_code) && $list->referral_code != '') ? $list->referral_code: "",
                    'ccid' => implode(',', $courses).' - '.implode(',', $courseCreationId)

                ];
                $i++;
            endforeach;
        endif;
        
        return  $data;
    }
    public function export(Request $request){

        
        $dataSetArrray = $this->dataSetList($request);
       
        $statusList = TaskList::where('process_list_id', 1)->get();

        $statusCount = $statusList->count();
        $theCollection = [];
        $theCollection[1][0] = 'LCC Ref';
        $theCollection[1][1] = 'First Name';
        $theCollection[1][2] = 'Sur Name';
        $theCollection[1][3] = 'Date of Birth';
        $theCollection[1][4] = 'Course name';
        $theCollection[1][5] = 'Weekday/Weekend';
        $theCollection[1][6] = 'Semester';
        $theCollection[1][7] = 'Campus';
        $theCollection[1][8] = 'Status';
        $theCollection[1][9] = 'Referral Code';
        $statusIncrement = 10;
        foreach($statusList as $status) :
            $theCollection[1][$statusIncrement++] = $status->name;
        endforeach;

        $row = 2;
        if(!empty($dataSetArrray)):
            foreach($dataSetArrray as $data):
                $applicantTaskDataSet = ApplicantTask::with(['task','applicatnTaskStatus'])->where('applicant_id',$data->id)->get();
                //var_dump($statusList);
                
                $theCollection[$row][0] = $data->application_no;
                $theCollection[$row][1] = $data->first_name;
                $theCollection[$row][2] = $data->last_name;
                $theCollection[$row][3] = $data->date_of_birth;
                $theCollection[$row][4] = $data->course;
                $theCollection[$row][5] = $data->full_time;
                $theCollection[$row][6] = $data->semester;
                $theCollection[$row][7] = $data->venue;
                $theCollection[$row][8] = $data->status_id;
                $theCollection[$row][9] = $data->referral_code;
                $statusIncrement = 10;
                foreach($statusList as $status) :
                    $dataFound =0;
                    foreach($applicantTaskDataSet as $applicantTask)
                    if($applicantTask->task->name == $status->name) {
                        $theCollection[$row][$statusIncrement++] = ($applicantTask->status=="Completed" && isset($applicantTask->applicatnTaskStatus)) ? $applicantTask->applicatnTaskStatus->name : $applicantTask->status;
                        $dataFound =1;
                    }
                    if( $dataFound ==0)
                        $theCollection[$row][$statusIncrement++] = "";
                endforeach;
                
                $row++;
            endforeach;
        endif;
        return Excel::download(new ArrayCollectionExport($theCollection), str_replace(' ', '_', $dataSetArrray[0]->semester).'_excel.xlsx');
    }

    public function show($applicantId){
        return view('pages.students.admission.show', [
            'title' => 'Recruitment - London Churchill College',
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
            'venues' => Venue::all(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'instance' => CourseCreationInstance::all()->sortByDesc('id'),
            'courseCreationAvailibility' => CourseCreationAvailability::all()->filter(function($item) {
                if (Carbon::now()->between($item->admission_date, $item->admission_end_date)) {
                  return $item;
                }
            }),
            'tempEmail' => ApplicantTemporaryEmail::where('applicant_id', $applicantId)->orderBy('id', 'desc')->first(),
            'documents' => DocumentSettings::where('admission', '1')->orderBy('id', 'ASC')->get(),
            'feeelegibility' => FeeEligibility::all(),
            'reasons' => ApplicationRejectedReason::orderBy('name', 'asc')->get()
        ]);
    }

    public function CreateAccountForStudent(Request $request){

        $student = Student::find($request->input('student_id'));
        $mobileVerifiedAt = now();
        $emailVerifiedAt = now();
        // if(!isset($student->contact->mobile_verification)) {
        //     $mobileVerifiedAt = now();
        // } else {
        //     $mobileVerifiedAt = $student->contact->mobile_verified_at;
        // }
        // if(!isset($student->contact->personal_email_verification)) {
        //     $emailVerifiedAt = now();
        // } else {
        //     $emailVerifiedAt = $student->contact->personal_email_verified_at;
        // }

        // convert $student->date_of_birth to DDMMYYYY
        $dob = Carbon::parse($student->date_of_birth)->format('dmY');

        $ApplicantUser = ApplicantUser::create([
            'email' => $student->contact->personal_email,
            'phone' => $student->contact->mobile,
            'password' => Hash::make($dob),
            'student_id' => $student->id,
            'email_verified_at' => $emailVerifiedAt,
            'phone_verified_at' => $mobileVerifiedAt,
            'active' => 1,
        ]);

        //event(new Registered($ApplicantUser));
        $token = base64_encode($ApplicantUser->email);
        DB::table('password_resets')->insert([
            'email' => $ApplicantUser->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        Mail::to($ApplicantUser->email)->send(new ResetPasswordLink($token));

       return response()->json(['status' => 'success', 'message' => 'Account created successfully', 'data' => $ApplicantUser]); 
    }

    public function passwordChangeForApplicant(Request $request, ApplicantUser $applicant_user)
    {
        //$applicant_user = ApplicantUser::find($applicant_user);
        $token = base64_encode($applicant_user->email);
        if($applicant_user) {

                DB::table('password_resets')->insert([
                    'email' => $applicant_user->email, 
                    'token' => $token, 
                    'created_at' => Carbon::now()
                ]);

                Mail::to($applicant_user->email)->send(new ResetPasswordLink($token));

                return response()->json(['message'=>'A mail has been sent'],200);
        }else    
            return response()->json(['message'=>'No User Found'],422);
        
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
        $venue_id = (isset($request->venue_id) && $request->venue_id > 0 ? $request->venue_id : 0);
        $courseCreation = CourseCreation::find($course_creation_id);
        $studentLoan = $request->student_loan;
        $studentFinanceEngland = ($studentLoan == 'Student Loan' && isset($request->student_finance_england) && $request->student_finance_england > 0 ? $request->student_finance_england : null);
        $appliedReceivedFund = ($studentLoan == 'Student Loan' && isset($request->applied_received_fund) && $request->applied_received_fund > 0 ? $request->applied_received_fund : null);
        $fundReceipt = ($studentFinanceEngland == 1 && isset($request->fund_receipt) && $request->fund_receipt > 0 ? $request->fund_receipt : null);

        $courseVenue = CourseCreationVenue::where('course_creation_id', $course_creation_id)->where('venue_id', $venue_id)->get()->first();
        $venueEW = ((isset($courseVenue->evening_and_weekend) && $courseVenue->evening_and_weekend == 1) && (isset($courseVenue->weekends) && $courseVenue->weekends > 0) ? true : false );

        $proposedCourse = ApplicantProposedCourse::find($request->id);
        $proposedCourse->fill([
            'course_creation_id' => $course_creation_id,
            'semester_id' => $courseCreation->semester_id,
            'student_loan' => $studentLoan,
            'student_finance_england' => $studentFinanceEngland,
            'applied_received_fund' => $appliedReceivedFund,
            'venue_id' => $request->venue_id ?? NULL,
            'fund_receipt' => $fundReceipt,
            'other_funding' => ($studentLoan == 'Others' && isset($request->other_funding) && !empty($request->other_funding) ? $request->other_funding : null),
            'full_time' => ($venueEW && (isset($request->full_time) && $request->full_time > 0) ? $request->full_time : 0),
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
            'title' => 'Recruitment - London Churchill College',
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

            'processGroup' => $processGroup,
            'reasons' => ApplicationRejectedReason::orderBy('name', 'asc')->get()
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
                $statusRow = Status::find(3);
                if(isset($statusRow->letter_set_id) && $statusRow->letter_set_id > 0):
                    $this->sendLetterOnStatusChanged($applicant_id, 3);
                elseif(isset($statusRow->email_template_id) && $statusRow->email_template_id > 0):
                    $this->sendEmailOnStatusChanged($applicant_id, 3);
                endif;

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
        $path = $document->storeAs('public/applicants/'.$applicant_id, $imageName, 's3');

        $data = [];
        $data['applicant_id'] = $applicant_id;
        $data['hard_copy_check'] = $request->hard_copy_check;
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = Storage::disk('s3')->url($path);
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
                'current_field_value' => $applicantDoc->id,
                'created_by' => auth()->user()->id
            ]);

            if($applicantTask->task->interview == "Yes") {

                ApplicantInterview::create([
                    'user_id' =>auth()->user()->id,
                    'applicant_id' =>$applicant_id,
                    'applicant_task_id' => $applicantTask->id,
                    'applicant_document_id' => $applicantDoc->id,
                    'interview_date' => date("Y-m-d"),
                    'start_time' => NULL,
                    'end_time' => NULL,
                    'interview_result' =>'N/A',
                    'created_by' => auth()->user()->id,
                ]);

            }

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

        $applicantRow = Applicant::find($applicant);
        $pendingTask = ApplicantTask::where('applicant_id', $applicant)->whereIn('status', ['Pending', 'In Progress'])->get();
        if($pendingTask->count() == 0 && $applicantRow->status_id < 4):
            $applicantData['status_id'] = 4;
            Applicant::where('id', $applicant)->update($applicantData);
            $statusRow = Status::find(4);
            if(isset($statusRow->letter_set_id) && $statusRow->letter_set_id > 0):
                $this->sendLetterOnStatusChanged($applicant, 4);
            elseif(isset($statusRow->email_template_id) && $statusRow->email_template_id > 0):
                $this->sendEmailOnStatusChanged($applicant, 4);
            endif;

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
            $statusRow = Status::find(4);
            if(isset($statusRow->letter_set_id) && $statusRow->letter_set_id > 0):
                $this->sendLetterOnStatusChanged($applicant, 4);
            elseif(isset($statusRow->email_template_id) && $statusRow->email_template_id > 0):
                $this->sendEmailOnStatusChanged($applicant, 4);
            endif;

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
            $statusRow = Status::find(3);
            if(isset($statusRow->letter_set_id) && $statusRow->letter_set_id > 0):
                $this->sendLetterOnStatusChanged($applicant, 3);
            elseif(isset($statusRow->email_template_id) && $statusRow->email_template_id > 0):
                $this->sendEmailOnStatusChanged($applicant, 3);
            endif;

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
                    if(!empty($list->current_field_value) && !preg_match("/[a-z]/i", $list->current_field_value)):
                        $applicantDocument = ApplicantDocument::find($list->current_field_value);
                        $newValue = '<a data-id="'.$list->current_field_value.'" href="javascript:void(0);" class="text-success downloadDoc" style="white-space: normal; word-break: break-all;">'.$applicantDocument->current_file_name.'</a>';
                    else:
                        $newValue = 'Not Available';
                    endif;
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
            'title' => 'Recruitment - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => route('admission.show', $applicantId)],
                ['label' => 'Uploads', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'allStatuses' => Status::where('type', 'Applicant')->where('id', '>', 1)->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'docSettings' => DocumentSettings::where('admission', '1')->get(),
            'feeelegibility' => FeeEligibility::all(),
            'reasons' => ApplicationRejectedReason::orderBy('name', 'asc')->get()
        ]);
    }

    public function AdmissionUploadDocuments(Request $request){
        $applicant_id = $request->applicant_id;
        $document_setting_id = $request->document_setting_id;
        $documentSetting = DocumentSettings::find($document_setting_id);
        $document_settings_name = (isset($documentSetting->name) && !empty($documentSetting->name) ? $documentSetting->name : '');
        $hard_copy_check = $request->hard_copy_check;
        $display_file_name = (isset($request->display_file_name) && !empty($request->display_file_name) ? $request->display_file_name : '');
        $display_file_name = ($document_settings_name != '' ? $document_settings_name : '') . ($display_file_name != '' ? ($document_settings_name != '' ? ' - ' . $display_file_name : $display_file_name) : '');
        

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/applicants/'.$applicant_id, $imageName, 's3');
        $data = [];
        $data['applicant_id'] = $applicant_id;
        $data['document_setting_id'] = ($document_setting_id > 0 ? $document_setting_id : 0);
        $data['hard_copy_check'] = ($hard_copy_check > 0 ? $hard_copy_check : 0);
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = Storage::disk('s3')->url($path);
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
                    //'url' => ($list->path) ?? Storage::disk('s3')->url('public/applicants/'.$list->applicant_id.'/'.$list->current_file_name),
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
            'title' => 'Recruitment - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => route('admission.show', $applicantId)],
                ['label' => 'Notes', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'allStatuses' => Status::where('type', 'Applicant')->where('id', '>', 1)->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'feeelegibility' => FeeEligibility::all(),
            'reasons' => ApplicationRejectedReason::orderBy('name', 'asc')->get()
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
                $path = $document->storeAs('public/applicants/'.$applicant_id, $documentName, 's3');

                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['hard_copy_check'] = 0;
                $data['doc_type'] = $document->getClientOriginalExtension();
                $data['path'] = Storage::disk('s3')->url($path);
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
                /*if(isset($list->applicant_document_id) && isset($list->document)):
                    $docURL = (isset($list->document->current_file_name) && !empty($list->document->current_file_name)  && Storage::diske('s3')->exists('public/applicants/'.$list->applicant_id.'/'.$list->document->current_file_name) ? Storage::disk('s3')->url('public/applicants/'.$list->applicant_id.'/'.$list->document->current_file_name) : '');
                endif;*/
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'note' => (strlen(strip_tags($list->note)) > 40 ? substr(strip_tags($list->note), 0, 40).'...' : strip_tags($list->note)),
                    'applicant_document_id' => (isset($list->applicant_document_id) && $list->applicant_document_id ? $list->applicant_document_id : 0),
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
            if(isset($note->applicant_document_id) && $note->applicant_document_id > 0 && isset($note->document->current_file_name) && !empty($note->document->current_file_name)):
                //$docURL = (isset($note->document->current_file_name) && !empty($note->document->current_file_name) && Storage::disk('s3')->exists('public/applicants/'.$note->applicant_id.'/'.$note->document->current_file_name) ? Storage::disk('s3')->url('public/applicants/'.$note->applicant_id.'/'.$note->document->current_file_name) : '');
                $btns .= '<a data-id="'.$note->applicant_document_id.'" href="javascript:void(0);" class="downloadDoc btn btn-primary w-auto inline-flex"><i data-lucide="cloud-lightning" class="w-4 h-4 mr-2"></i>Download Attachment</a>';
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
            $docURL = (isset($theNote->document->current_file_name) && !empty($theNote->document->current_file_name) && Storage::disk('s3')->exists('public/applicants/'.$theNote->applicant_id.'/'.$theNote->document->current_file_name) ? Storage::disk('s3')->url('public/applicants/'.$theNote->applicant_id.'/'.$theNote->document->current_file_name) : '');
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
                if (Storage::disk('s3')->exists('public/applicants/'.$applicant_id.'/'.$oleNote->document->current_file_name)):
                    Storage::disk('s3')->delete('public/applicants/'.$applicant_id.'/'.$oleNote->document->current_file_name);
                endif;

                $ad = ApplicantDocument::where('id', $applicantDocumentId)->forceDelete();
            endif;

            $document = $request->file('document');
            $documentName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/applicants/'.$applicant_id, $documentName, 's3');

            $data = [];
            $data['applicant_id'] = $applicant_id;
            $data['hard_copy_check'] = 0;
            $data['doc_type'] = $document->getClientOriginalExtension();
            $data['path'] = Storage::disk('s3')->url($path);
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
            'title' => 'Recruitment - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => route('admission.show', $applicantId)],
                ['label' => 'Communication', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'allStatuses' => Status::where('type', 'Applicant')->where('id', '>', 1)->get(),
            'smtps' => ComonSmtp::all(),
            'letterSet' => LetterSet::where('admission', 1)->where('status', 1)->orderBy('letter_title', 'ASC')->get(),
            'signatory' => Signatory::all(),
            'smsTemplates' => SmsTemplate::where('admission', 1)->where('status', 1)->orderBy('sms_title', 'ASC')->get(),
            'emailTemplates' => EmailTemplate::where('admission', 1)->where('status', 1)->orderBy('email_title', 'ASC')->get(),
            'feeelegibility' => FeeEligibility::all(),
            'reasons' => ApplicationRejectedReason::orderBy('name', 'asc')->get()
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
        $is_email_or_attachment = 2;

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
            $generatedLetter = $this->generateLetter($applicant_id, $letter_title, $letter_body, $issued_date, $pin, $signatory_id);
    
            $data = [];
            $data['applicant_id'] = $applicant_id;
            $data['hard_copy_check'] = 0;
            $data['doc_type'] = 'pdf';
            $data['path'] = Storage::disk('s3')->url('public/applicants/'.$applicant_id.'/'.$generatedLetter['filename']);
            $data['display_file_name'] = $letter_title;
            $data['current_file_name'] = $generatedLetter['filename'];
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
            $emailHTML .= '<p>Please Find the letter attached herewith. </p>';
            $emailHTML .= $signatoryHTML;

            $attachmentFiles[] = [
                "pathinfo" => 'public/applicants/'.$applicant_id.'/'.$generatedLetter['filename'],
                "nameinfo" => $generatedLetter['filename'],
                "mimeinfo" => 'application/pdf',
                'disk'     => 's3'
            ];

            $configuration = [
                'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'vhiu icoh qbfe okxr'),
                'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                
                'from_email'    => 'no-reply@lcc.ac.uk',
                'from_name'    =>  'London Churchill College',
            ];
            UserMailerJob::dispatch($configuration, [$applicant->users->email], new CommunicationSendMail($letter_title, $emailHTML, $attachmentFiles));

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
                /*if(isset($list->applicant_document_id) && $list->applicant_document_id > 0 && isset($list->current_file_name)):
                    $docURL = (!empty($list->current_file_name) ? Storage::disk('s3')->url('public/applicants/'.$list->applicant_id.'/'.$list->current_file_name) : '');
                endif;*/
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'letter_type' => $list->letter_type,
                    'letter_title' => $list->letter_title,
                    'signatory_name' => (isset($list->signatory_name) && !empty($list->signatory_name) ? $list->signatory_name : ''),
                    'docurl' => (isset($list->applicant_document_id) && $list->applicant_document_id > 0 ? $list->applicant_document_id : 0),
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

            $MAILHTML = '';
            /*$emailHeader = LetterHeaderFooter::where('for_email', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
            $emailFooters = LetterHeaderFooter::where('for_email', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get();
            if(isset($emailHeader->current_file_name) && !empty($emailHeader->current_file_name) && Storage::disk('s3')->exists('public/letterheaderfooter/header/'.$emailHeader->current_file_name)):
                $MAILHTML .= '<div style="margin: 0 0 30px 0;">';
                    $MAILHTML .= '<img style="width: 100%; height: auto;" src="'.Storage::disk('s3')->url('public/letterheaderfooter/header/'.$emailHeader->current_file_name).'"/>';
                $MAILHTML .= '</div>';
            endif;*/
            $MAILHTML .= $request->body;
            /*if($emailFooters->count() > 0):
                $MAILHTML .= '<div style="text-align: center; vertical-align: middle; margin: 20px 0 0 0;">';
                    $numberOfPartners = $emailFooters->count();
                    $pertnerWidth = ((100 - 2) - (int) $numberOfPartners) / (int) $numberOfPartners;

                    foreach($emailFooters as $lf):
                        if(Storage::disk('s3')->exists('public/letterheaderfooter/footer/'.$lf->current_file_name)):
                            $MAILHTML .= '<img style=" width: '.$pertnerWidth.'%; height: auto; margin-left:.5%; margin-right:.5%;" src="'.Storage::disk('s3')->url('public/letterheaderfooter/footer/'.$lf->current_file_name).'" alt="'.$lf->name.'"/>';
                        endif;
                    endforeach;
                $MAILHTML .= '</div>';
            endif;*/

            if($request->hasFile('documents')):
                $documents = $request->file('documents');
                $docCounter = 1;
                $attachmentInfo = [];
                foreach($documents as $document):
                    $documentName = time().'_'.$document->getClientOriginalName();
                    $path = $document->storeAs('public/applicants/'.$applicantID, $documentName, 's3');

                    $data = [];
                    $data['applicant_id'] = $applicantID;
                    $data['hard_copy_check'] = 0;
                    $data['doc_type'] = $document->getClientOriginalExtension();
                    $data['path'] = Storage::disk('s3')->url($path);
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
                            'disk'     => 's3'
                        ];
                        $docCounter++;
                    endif;
                endforeach;
                UserMailerJob::dispatch($configuration,[$Applicant->users->email], new CommunicationSendMail($request->subject, $MAILHTML, $attachmentInfo));
            else:
                UserMailerJob::dispatch($configuration, [$Applicant->users->email], new CommunicationSendMail($request->subject, $MAILHTML, []));
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
                        $html .= '<a target="_blank" class="mb-1 text-primary font-medium flex justify-start items-center downloadDoc" data-id="'.$doc->id.'" href="javascript:void(0);" download><i data-lucide="disc" class="w-3 h3 mr-2"></i>'.$doc->current_file_name.'</a>';
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
        $rejectedReason = ((isset($request->rejectedReason) && $request->rejectedReason > 0) ? $request->rejectedReason : null);

        $applicant =  $applicantOldRow = Applicant::find($applicant_id);
       
        $applicant->status_id = $statusidID;
        $applicant->application_rejected_reason_id = $rejectedReason;
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
                    new ProcessStudentDocuments($applicant),
                ])->dispatch();
                
                session()->put("lastBatchId",$bus->id);
                /* Student Process END */
            elseif($statusidID == 6):
                $docuSealDoc = $this->sendDocusealForm($applicant_id);
            endif;

            $statusRow = Status::find($statusidID);
            if(isset($statusRow->letter_set_id) && $statusRow->letter_set_id > 0):
                $this->sendLetterOnStatusChanged($applicant_id, $statusidID);
            elseif(isset($statusRow->email_template_id) && $statusRow->email_template_id > 0):
                $this->sendEmailOnStatusChanged($applicant_id, $statusidID);
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
                    'file' => ($list->document) ? Storage::disk('s3')->temporaryUrl('public/applicants/'.$applicantId."/".$list->document->current_file_name, now()->addMinutes(120)) : '',
                    'doc_id' => $list->document->id,
                    'current_file_name' =>($list->document) ? $list->document->current_file_name : "",
                        
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

    public function admissionDocumentDownload(Request $request){
        $row_id = $request->row_id;

        $applicantDoc = ApplicantDocument::where('id',$row_id)->withTrashed()->get()->first();
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/applicants/'.$applicantDoc->applicant_id.'/'.$applicantDoc->current_file_name, now()->addMinutes(5));
        return response()->json(['res' => $tmpURL], 200);
    }

    public function sendLetterOnStatusChanged($applicant_id, $status_id){
        $statusRow = Status::find($status_id);
        $applicant = Applicant::find($applicant_id);
        $pin = time();
        $currentEmployee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $currentUserEmail = (isset($currentEmployee->employment->email) && !empty($currentEmployee->employment->email) ? $currentEmployee->employment->email : $currentEmployee->email );

        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        if(isset($commonSmtp->id) && $commonSmtp->id > 0 && isset($statusRow->letter_set_id) && $statusRow->letter_set_id > 0):
            $letter_set_id = $statusRow->letter_set_id;
            $letterSet = LetterSet::find($statusRow->letter_set_id);
            $signatory_id = (isset($statusRow->signatory_id) && $statusRow->signatory_id > 0 ? $statusRow->signatory_id : 0);
            $subject = 'Application Status Update';

            $issued_date = date('Y-m-d');
            $letter_title = (isset($letterSet->letter_title) && !empty($letterSet->letter_title) ? $letterSet->letter_title : 'Letter from LCC');

            $letter_body = $letterSet->description;
            $is_email_or_attachment = 2;

            $data = [];
            $data['applicant_id'] = $applicant_id;
            $data['letter_set_id'] = $letter_set_id;
            $data['pin'] = $pin;
            $data['signatory_id'] = $signatory_id;
            $data['comon_smtp_id'] = $commonSmtp->id;
            $data['is_email_or_attachment'] = $is_email_or_attachment;
            $data['issued_by'] = auth()->user()->id;
            $data['issued_date'] = $issued_date;
            $data['created_by'] = auth()->user()->id;

            $letter = ApplicantLetter::create($data);
            $attachmentFiles = [];
            if($letter):
                $generatedLetter = $this->generateLetter($applicant_id, $letter_title, $letter_body, $issued_date, $pin, $signatory_id);

                /*New Code Start*/
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['hard_copy_check'] = 0;
                $data['doc_type'] = 'pdf';
                $data['path'] = Storage::disk('s3')->url('public/applicants/'.$applicant_id.'/'.$generatedLetter['filename']);
                $data['display_file_name'] = $letter_title;
                $data['current_file_name'] = $generatedLetter['filename'];
                $data['created_by'] = auth()->user()->id;
                $applicantDocument = ApplicantDocument::create($data);

                if($applicantDocument):
                    $noteUpdate = ApplicantLetter::where('id', $letter->id)->update([
                        'applicant_document_id' => $applicantDocument->id
                    ]);
                endif;
                /*New Code End*/

                $signatoryHTML = '';
                $signatory = Signatory::find($signatory_id);
                if($signatory_id > 0 && isset($signatory->id) && $signatory->id > 0):
                    $signatoryHTML .= '<p>';
                        $signatoryHTML .= '<strong>Yours sincerely,</strong><br/>';
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
                $emailHTML .= '<p>Please find attached an important communication regarding your application. </p>';
                $emailHTML .= $signatoryHTML;

                $attachmentFiles[] = [
                    "pathinfo" => 'public/applicants/'.$applicant_id.'/'.$generatedLetter['filename'],
                    "nameinfo" => $generatedLetter['filename'],
                    "mimeinfo" => 'application/pdf',
                    'disk'     => 's3'
                ];
                if($status_id == 5):
                    $attachmentFiles[] = [
                        "pathinfo" => 'public/terms_and_condition.pdf',
                        "nameinfo" => 'terms_and_condition.pdf',
                        "mimeinfo" => 'application/pdf',
                        'disk'     => 'local'
                    ];
                endif;

                $configuration = [
                    'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                    'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                    'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                    'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                    'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                    
                    'from_email'    => 'no-reply@lcc.ac.uk',
                    'from_name'    =>  'London Churchill College',
                ];
                UserMailerJob::dispatch($configuration, [$applicant->users->email, $currentUserEmail], new CommunicationSendMail($subject, $emailHTML, $attachmentFiles));

                return true;
            else:
                return false;
            endif;
        else:
            return false;
        endif;
    }

    public function sendEmailOnStatusChanged($applicant_id, $status_id){
        $statusRow = Status::find(3);
        $applicant = Applicant::find($applicant_id);

        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        if(isset($commonSmtp->id) && $commonSmtp->id > 0 && isset($statusRow->email_template_id) && $statusRow->email_template_id > 0):
            $template = EmailTemplate::find($statusRow->email_template_id);
            $subject = 'Application Status Updated to '.$statusRow->name;

            $applicantEmail = ApplicantEmail::create([
                'applicant_id' => $applicant_id,
                'comon_smtp_id' => $commonSmtp->id,
                'email_template_id' => $statusRow->email_template_id,
                'subject' => $subject,
                'body' => $template->description,
                'created_by' => auth()->user()->id,
            ]);

            $configuration = [
                'smtp_host'    => $commonSmtp->smtp_host,
                'smtp_port'    => $commonSmtp->smtp_port,
                'smtp_username'  => $commonSmtp->smtp_user,
                'smtp_password'  => $commonSmtp->smtp_pass,
                'smtp_encryption'  => $commonSmtp->smtp_encryption,
                
                'from_email'    => $commonSmtp->smtp_user,
                'from_name'    =>  'London Churchill College',
            ];

            if($applicantEmail):
                $emailHeader = LetterHeaderFooter::where('for_email', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
                $emailFooters = LetterHeaderFooter::where('for_email', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get();

                $MAILHTML = '';
                if(isset($emailHeader->current_file_name) && !empty($emailHeader->current_file_name) && Storage::disk('s3')->exists('public/letterheaderfooter/header/'.$emailHeader->current_file_name)):
                    $MAILHTML .= '<div style="margin: 0 0 30px 0;">';
                        $MAILHTML .= '<img style="width: 100%; height: auto;" src="'.Storage::disk('s3')->url('public/letterheaderfooter/header/'.$emailHeader->current_file_name).'"/>';
                    $MAILHTML .= '</div>';
                endif;
                $MAILHTML .= $template->description;
                if($emailFooters->count() > 0):
                    $MAILHTML .= '<div style="text-align: center; vertical-align: middle; margin: 20px 0 0 0;">';
                        $numberOfPartners = $emailFooters->count();
                        $pertnerWidth = ((100 - 2) - (int) $numberOfPartners) / (int) $numberOfPartners;

                        foreach($emailFooters as $lf):
                            if(Storage::disk('s3')->exists('public/letterheaderfooter/footer/'.$lf->current_file_name)):
                                $MAILHTML .= '<img style=" width: '.$pertnerWidth.'%; height: auto; margin-left:.5%; margin-right:.5%;" src="'.Storage::disk('s3')->url('public/letterheaderfooter/footer/'.$lf->current_file_name).'" alt="'.$lf->name.'"/>';
                            endif;
                        endforeach;
                    $MAILHTML .= '</div>';
                endif;

                UserMailerJob::dispatch($configuration, [$applicant->users->email], new CommunicationSendMail($subject, $MAILHTML, []));
                return true;
            else:
                return false;
            endif;
        else:
            return false;
        endif;
    }

    public function sendDocusealForm($applicant_id){
        $applicant = Applicant::find($applicant_id);

        $DOCUSEALAPI = env("DOCUSEAL_API_KEY", false);
        $OFFER_ACCEPTANCE_TEMPLATE_ID = env("OFFER_ACCEPTANCE_FORM_TEMPLATE_ID", false);
        if($DOCUSEALAPI && $OFFER_ACCEPTANCE_TEMPLATE_ID):
            $address = (isset($applicant->contact->full_address) && !empty($applicant->contact->full_address) ? strip_tags($applicant->contact->full_address) : '');
            $postArray = [
                'template_id' => $OFFER_ACCEPTANCE_TEMPLATE_ID,
                'send_email' => true,
                'order' => 'preserved',
                'submitters' => [[
                        'role' => 'London Churchill College',
                        'email' => 'admission@lcc.ac.uk',//limon@lcc.ac.uk
                        'completed' => true,
                        'send_email' => true,
                        'fields' => [[
                                'name' => 'application_ref_no',
                                'default_value' => $applicant->application_no
                            ],[
                                'name' => 'title',
                                'default_value' => (isset($applicant->title->name) ? $applicant->title->name : '')
                            ],[
                                'name' => 'first_name',
                                'default_value' => $applicant->first_name
                            ],[
                                'name' => 'last_name',
                                'default_value' => $applicant->last_name
                            ],[
                                'name' => 'nationality',
                                'default_value' => (isset($applicant->nation->name) ? $applicant->nation->name : '')
                            ],[
                                'name' => 'country_of_birth',
                                'default_value' => (isset($applicant->country->name) ? $applicant->country->name : '')
                            ],[
                                'name' => 'gender',
                                'default_value' => (isset($applicant->sexid->name) ? $applicant->sexid->name : '')
                            ],[
                                'name' => 'date_of_birth',
                                'default_value' => (isset($applicant->date_of_birth) && !empty($applicant->date_of_birth) ? date('d-m-Y', strtotime($applicant->date_of_birth)) : '')
                            ],[
                                'name' => 'full_address',
                                'default_value' => $address
                            ],[
                                'name' => 'home_phone',
                                'default_value' => (isset($applicant->contact->home) ? $applicant->contact->home : '')
                            ],[
                                'name' => 'mobile_phone',
                                'default_value' => (isset($applicant->contact->mobile) ? $applicant->contact->mobile : '')
                            ],[
                                'name' => 'email_address',
                                'default_value' => (isset($applicant->users->email) ? $applicant->users->email : '')
                            ],[
                                'name' => 'course',
                                'default_value' => (isset($applicant->course->creation->course->name) ? $applicant->course->creation->course->name : '')
                            ],[
                                'name' => 'semester',
                                'default_value' => (isset($applicant->course->semester->name) ? $applicant->course->semester->name : '')
                            ],[
                                'name' => 'fees',
                                'default_value' => (isset($applicant->course->creation->fees) && !empty($applicant->course->creation->fees) ? '£'.number_format($applicant->course->creation->fees, 2) : '£0.00')
                            ],[
                                'name' => 'start_date',
                                'default_value' => (isset($applicant->course->creation->availability[0]->course_start_date) && !empty($applicant->course->creation->availability[0]->course_start_date) ? date('d-m-Y', strtotime($applicant->course->creation->availability[0]->course_start_date)) : '')
                            ]]
                    ],
                    [
                        'role' => 'Applicant',
                        'email' => $applicant->users->email, //$applicant->users->email limon@churchill.ac sakib@lcc.ac.uk
                    ]
                ]
            ];
            $client = new Client();
            $res = $client->request('POST', 'https://api.docuseal.co/submissions', 
                [
                    'headers' => [
                        "X-Auth-Token" => $DOCUSEALAPI,
                        "content-type" => "application/json",
                        "Accept" => "application/json"
                    ],
                    'body' => json_encode($postArray)
                ]
            );
            $statusCode = $res->getStatusCode();
            return $statusCode;
        else:
            return false;
        endif;
    }

    public function getEveningWeekendStatus(Request $request){
        $course_creation_id = $request->course_creation_id;
        $venue_id = $request->venue_id;

        $creationVenue = CourseCreationVenue::where('course_creation_id', $course_creation_id)->where('venue_id', $venue_id)->get()->first();
        if((isset($creationVenue->evening_and_weekend) && $creationVenue->evening_and_weekend == 1) && (isset($creationVenue->weekends) && $creationVenue->weekends > 0)):
            if($creationVenue->weekdays > 0):
                return response()->json(['weekends' => 1], 200);
            else:
                return response()->json(['weekends' => 2], 200);
            endif;
        else:
            return response()->json(['weekends' => 0], 200);
        endif;
    }

    public function rejectStudent(Request $request){
        $applicant_id = $request->applicantID;
        $status_id = $request->statusidID;
        $applicant =  $applicantOldRow = Applicant::find($applicant_id);
       
        $applicant->status_id = $status_id;
        $changes = $applicant->getDirty();
        $applicant->save();

        if($applicant->wasChanged() && !empty($changes)):
            $data = [];
            $data['applicant_id'] = $applicant_id;
            $data['table'] = 'applicants';
            $data['field_name'] = 'status_id';
            $data['field_value'] = $applicantOldRow->status_id;
            $data['field_new_value'] = $status_id;
            $data['created_by'] = auth()->user()->id;

            ApplicantArchive::create($data);
        endif;

        return response()->json(['msg' => 'Student status successfully updated!'], 200);
    }

}
