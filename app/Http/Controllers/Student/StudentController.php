<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentUpdateStatusRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;

use App\Models\Applicant;
use App\Models\ApplicantTemporaryEmail;
use App\Models\AssessmentPlan;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\AttendanceCode;
use App\Models\AttendanceFeedStatus;
use App\Models\AttendanceInformation;
use App\Models\AwardingBody;
use App\Models\ComonSmtp;
use App\Models\Company;
use App\Models\ConsentPolicy;
use App\Models\Country;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\CourseCreationAvailability;
use App\Models\CourseCreationInstance;
use App\Models\CourseCreationVenue;
use App\Models\CourseModule;
use App\Models\Disability;
use App\Models\DocumentSettings;
use App\Models\EmailTemplate;
use App\Models\EmailVerificationCode;
use App\Models\Ethnicity;
use App\Models\FeeEligibility;
use App\Models\Grade;
use App\Models\Group;
use App\Models\HesaExamSittingVenue;
use App\Models\HesaGender;
use App\Models\HesaQualificationSubject;
use App\Models\HighestQualificationOnEntry;
use App\Models\InstanceTerm;
use App\Models\KinsRelation;
use App\Models\LetterSet;
use App\Models\MobileVerificationCode;
use App\Models\ModuleCreation;
use App\Models\Option;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\PreviousProvider;
use App\Models\ProcessList;
use App\Models\QualificationTypeIdentifier;
use App\Models\ReferralCode;
use App\Models\Religion;
use App\Models\Result;
use App\Models\Semester;
use App\Models\SexIdentifier;
use App\Models\SexualOrientation;
use App\Models\Signatory;
use App\Models\SlcAgreement;
use App\Models\SlcPaymentMethod;
use App\Models\SlcRegistration;
use App\Models\SlcRegistrationStatus;
use App\Models\SmsTemplate;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentAttendanceTermStatus;
use App\Models\StudentConsent;
use App\Models\StudentContact;
use App\Models\StudentCourseRelation;
use App\Models\StudentDocument;
use App\Models\StudentEmail;
use App\Models\StudentEmailsAttachment;
use App\Models\StudentEmailsDocument;
use App\Models\StudentLetter;
use App\Models\StudentProposedCourse;
use App\Models\Title;
use App\Models\User;
use App\Models\StudentSms;
use App\Models\StudentTask;
use App\Models\StudentWorkPlacement;
use App\Models\StudyMode;
use App\Models\TaskList;
use App\Models\TermDeclaration;
use App\Models\TermTimeAccommodationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use PDF;


class StudentController extends Controller
{
    public function index(){
        $semesters = Cache::get('semesters', function () {
            return Semester::all()->sortByDesc("name");
        });
        $courses = Cache::get('courses', function () {
            return Course::all();
        });
        $statuses = Cache::get('statuses', function () {
            return Status::where('type', 'Student')->get();
        });
        
        
        return view('pages.students.live.index', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Students Live', 'href' => 'javascript:void(0);']
            ],
            'semesters' => $semesters,
            'courses' => $courses,
            'allStatuses' => $statuses,
            'academicYear' => AcademicYear::all()->sortByDesc('from_date'),
            'terms' => TermDeclaration::all()->sortByDesc('id'),
            'groups' => Group::all(),
        ]);
    }

    public function list(Request $request){
        parse_str($request->form_data, $form);
        $student_id = isset($form['student_id']) && !empty($form['student_id']) ? $form['student_id'] : '';

        $studentParams = isset($form['student']) && !empty($form['student']) ? $form['student'] : [];
        $groupParams = isset($form['group']) && !empty($form['group']) ? $form['group'] : [];
        $studentSearch = (isset($studentParams['stataus']) && $studentParams['stataus'] == 1 ? true : false);
        $groupSearch = (isset($groupParams['stataus']) && $groupParams['stataus'] == 1 ? true : false);

        $student_id = ($studentSearch ? $studentParams['student_id'] : ($groupSearch ? '' : $student_id));

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'registration_no', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        $Query = Student::orderByRaw(implode(',', $sorts));
        if(!empty($student_id)): $Query->where('registration_no', 'LIKE', '%'.$student_id.'%'); endif;
        if($studentSearch):
            foreach($studentParams as $field => $value):
                $$field = (isset($value) && !empty($value) ? ($field == 'student_dob' ? date('Y-m-d', strtotime($value)) :$value) : '');
            endforeach;

            if(!empty($student_name)): 
                $Query->where(function($q) use($student_name){
                    $q->where('first_name','LIKE','%'.$student_name.'%')->orWhere('last_name','LIKE','%'.$student_name.'%');
                }); 
            endif;
            if(!empty($student_dob)): $Query->where('date_of_birth', $student_dob); endif;
            if(!empty($student_post_code) || !empty($student_email) || !empty($student_mobile)):
                $Query->whereHas('contact', function($qr) use($student_post_code, $student_email, $student_mobile){
                    if(!empty($student_post_code)):
                        $qr->where('term_time_post_code', $student_post_code);
                    endif;
                    if(!empty($student_email)):
                        $qr->where(function($q) use($student_email){
                            $q->where('personal_email', $student_email)->orWhere('institutional_email', $student_email); 
                        });
                    endif;
                    if(!empty($student_mobile)):
                        $qr->where('mobile', $student_post_code);
                    endif;
                });
            endif;
            if(!empty($student_uhn)): $Query->where('uhn_no', $student_uhn); endif;
            if(!empty($student_ssn)): $Query->where('ssn_no', $student_ssn); endif;
            if(!empty($application_no)): $Query->where('application_no', $application_no); endif;
            if(!empty($student_status)): $Query->whereIn('status_id', $student_status); endif;
        endif;

        /*if($groupSearch):
            foreach($groupParams as $field => $value):
                $$field = (isset($value) && !empty($value) ? $value : '');
            endforeach;

            $course_creation_instance_ids = InstanceTerm::where('term_declaration_id', $attendance_semester)->pluck('course_creation_instance_id')->unique()->toArray();
            $course_creation_ids = CourseCreationInstance::where('academic_year_id', $academic_year)->whereIn('id', $course_creation_instance_ids)->pluck('course_creation_id')->unique()->toArray();
            
            if(!empty($student_type)):
                $tmp_cc_ids = CourseCreationAvailability::where('type', $student_type)->pluck('course_creation_id')->unique()->toArray();
                if(!empty($tmp_cc_ids)):
                    $course_creation_ids = array_merge($course_creation_ids, $tmp_cc_ids);
                endif;
            endif;
            $courseCreations = CourseCreation::whereIn('id', $course_creation_ids)->where('semester_id', $intake_semester)
                               ->where('course_id', $course)->pluck('id')->unique()->toArray();
            $studentsIds = StudentCourseRelation::whereIn('course_creation_id', $courseCreations)->where('active', 1)->pluck('student_id')->unique()->toArray();
            
            if(!empty($evening_weekend)): 
                $ew = StudentProposedCourse::where('full_time', $evening_weekend);
                if(!empty($studentsIds)):
                    $ew->whereIn('student_id', $studentsIds);
                endif;
                $studentsIds = $ew->pluck('student_id')->unique()->toArray();
                if(empty($studentsIds)):
                    $studentsIds = [0];
                endif;
            else:
                $studentsIds = !empty($studentsIds) ? $studentsIds : [0];
            endif;
            if(!empty($group_student_status)): $Query->whereIn('std.status_id', $group_student_status); endif;
            if(!empty($studentsIds)): $Query->whereIn('std.id', $studentsIds); endif;
        endif;*/

        $total_rows = $Query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 50));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);
        
        $Query = $Query->orderByRaw(implode(',', $sorts))->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'disability' =>  (isset($list->other->disability_status) && $list->other->disability_status > 0 ? $list->other->disability_status : 0),
                    'full_time' => (isset($list->activeCR->propose->full_time) && $list->activeCR->propose->full_time > 0) ? $list->activeCR->propose->full_time : 0, 
                    'registration_no' => (!empty($list->registration_no) ? $list->registration_no : $list->application_no),
                    'first_name' => $list->first_name,
                    'last_name' => $list->last_name,
                    'course'=> (isset($list->activeCR->creation->course->name) && !empty($list->activeCR->creation->course->name) ? $list->activeCR->creation->course->name : ''),
                    'semester'=> (isset($list->activeCR->creation->semester->name) && !empty($list->activeCR->creation->semester->name) ? $list->activeCR->creation->semester->name : ''),
                    'status_id'=> (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                    'url' => route('student.show', $list->id),
                    'photo_url' => $list->photo_url
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows, 'sp' => $studentParams]);
    }

    public function show($studentId){
        $student = Student::find($studentId);
        $referral = [];
        if(isset($student->referral_code) && !empty($student->referral_code) && isset($student->is_referral_varified) && $student->is_referral_varified == 1):
            $referralCode = $student->referral_code;
            $referral = ReferralCode::where('code', $referralCode)->first();
        endif;
        return view('pages.students.live.show', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student Details', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'allStatuses' => Status::where('type', 'Student')->get(),
            'titles' => Title::where('active', 1)->get(),
            'country' => Country::where('active', 1)->get(),
            'ethnicity' => Ethnicity::where('active', 1)->get(),
            'disability' => Disability::where('active', 1)->get(),
            'relations' => KinsRelation::where('active', 1)->get(),
            'bodies' => AwardingBody::all(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'instance' => CourseCreationInstance::all(),
            'documents' => DocumentSettings::where('live', '1')->orderBy('id', 'ASC')->get(),
            'feeelegibility' => FeeEligibility::where('active', 1)->get(),
            'sexualOrientation' => SexualOrientation::where('active', 1)->get(),
            'sexid' => SexIdentifier::where('active', 1)->get(),
            'hesaGender' => HesaGender::where('active', 1)->get(),
            'religion' => Religion::where('active', 1)->get(),
            'stdConsentIds' => StudentConsent::where('student_id', $studentId)->where('status', 'Agree')->pluck('consent_policy_id')->toArray(),
            'consent' => ConsentPolicy::all(),
            'referral' => $referral,
            'ttacom' => TermTimeAccommodationType::where('active', 1)->get(),
            'PreviousProviders' => PreviousProvider::all(),
            'QualificationTypeIdentifiers' => QualificationTypeIdentifier::all(),
            'HighestQualificationOnEntrys' => HighestQualificationOnEntry::all(),
            'HesaQualificationSubjects' => HesaQualificationSubject::all(),
            'HesaExamSittingVenues' => HesaExamSittingVenue::all(),
            'StudyModes' => StudyMode::where('active', 1)->orderBy('id', 'ASC')->get(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    public function courseDetails($studentId){

        $student = Student::with('crel','course')->where('id',$studentId)->get()->first();
        $courseRelationCreation = $student->crel->creation;
        $studentCourseAvailability = $courseRelationCreation->availability;
        $courseCreationQualificationData = $courseRelationCreation->qualification;
        $currentCourse = StudentProposedCourse::with('venue')->where('student_id',$student->id)
                        ->where('course_creation_id',$courseRelationCreation->id)
                        ->where('student_course_relation_id',$student->crel->id)
                        ->get()
                        ->first();
        $CourseCreationVenue = CourseCreationVenue::where('course_creation_id',$courseRelationCreation->id)->where('venue_id', $currentCourse->venue_id)->get()->first();
        
        return view('pages.students.live.course', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student Course', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'studentCourseAvailability' => $studentCourseAvailability,
            'allStatuses' => Status::where('type', 'Student')->get(),
            'instance' => CourseCreationInstance::all(),
            'feeelegibility' => FeeEligibility::all(),
            'proposedCourse' => StudentProposedCourse::where('student_id', $studentId)->first(),
            "courses" => Course::orderBy('name', 'ASC')->get(),
            "academicYears" => AcademicYear::orderBy('from_date', 'DESC')->get(),
            "semesters" => Semester::orderBy('id', 'DESC')->get(),
            "courseQualification" =>$courseCreationQualificationData,
            "slcCode" =>(!empty($CourseCreationVenue)) ? $CourseCreationVenue->slc_code : "UNKNOWN",
            "venue" =>(!empty($CourseCreationVenue)) ? $currentCourse->venue->name : "",
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    public function communications($studentId){
        return view('pages.students.live.communication', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student Communications', 'href' => 'javascript:void(0);'],
            ],
            'student' => Student::find($studentId),
            'allStatuses' => Status::where('type', 'Student')->get(),
            'smtps' => ComonSmtp::all(),
            'letterSet' => LetterSet::where('live', 1)->where('status', 1)->orderBy('letter_title', 'ASC')->get(),
            'signatory' => Signatory::all(),
            'smsTemplates' => SmsTemplate::where('live', 1)->where('status', 1)->orderBy('sms_title', 'ASC')->get(),
            'emailTemplates' => EmailTemplate::where('live', 1)->where('status', 1)->orderBy('email_title', 'ASC')->get(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    public function uploads($studentId){
        return view('pages.students.live.uploads', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student Documents', 'href' => 'javascript:void(0);'],
            ],
            'student' => Student::find($studentId),
            'allStatuses' => Status::where('type', 'Student')->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'docSettings' => DocumentSettings::where('live', '1')->get(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    public function notes($studentId){
        return view('pages.students.live.notes', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student Notes', 'href' => 'javascript:void(0);'],
            ],
            'student' => Student::find($studentId),
            'allStatuses' => Status::where('type', 'Student')->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'terms' => TermDeclaration::orderBy('id', 'desc')->get(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    public function process($studentId){
        $processGroup = [];
        $processList = ProcessList::where('phase', 'Live')->orderBy('id', 'ASC')->get();
        if(!empty($processList)):
            $i = 1;
            foreach($processList as $prl):
                $taskIds = [];
                foreach($prl->tasks as $tsk):
                    $taskIds[] = $tsk->id;
                endforeach;
                if(!empty($taskIds)):
                    $pendingTask = StudentTask::where('student_id', $studentId)->whereIn('task_list_id', $taskIds)->where('status', 'Pending')->get();
                    $inProgressTask = StudentTask::where('student_id', $studentId)->whereIn('task_list_id', $taskIds)->where('status', 'In Progress')->get();
                    $completedTask = StudentTask::where('student_id', $studentId)->whereIn('task_list_id', $taskIds)->where('status', 'Completed')->get();


                    $processGroup[$i]['name'] = $prl->name;
                    $processGroup[$i]['id'] = $prl->id;
                    $processGroup[$i]['pendingTask'] = $pendingTask;
                    $processGroup[$i]['inProgressTask'] = $inProgressTask;
                    $processGroup[$i]['completedTask'] = $completedTask;
                endif;
                $i++;
            endforeach;
        endif;

        return view('pages.students.live.process', [
            'title' => 'Live Student - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Process & Tasks', 'href' => 'javascript:void(0);'],
            ],
            'student' => Student::find($studentId),
            'allStatuses' => Status::where('type', 'Student')->get(),
            'process' => ProcessList::where('phase', 'Live')->orderBy('id', 'ASC')->get(),
            'existingTask' => StudentTask::where('student_id', $studentId)->pluck('task_list_id')->toArray(),
            'applicantPendingTask' => StudentTask::where('student_id', $studentId)->where('status', 'Pending')->get(),
            'applicantCompletedTask' => StudentTask::where('student_id', $studentId)->where('status', 'Completed')->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),

            'processGroup' => $processGroup,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    public function UploadStudentPhoto(Request $request){
        $applicant_id = $request->applicant_id;
        $student_id = $request->student_id;
        $applicantOldRow = Student::where('id', $student_id)->first();
        $oldPhoto = (isset($applicantOldRow->photo) && !empty($applicantOldRow->photo) ? $applicantOldRow->photo : '');

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/students/'.$student_id, $imageName, 'local');
        if(!empty($oldPhoto)):
            if (Storage::disk('local')->exists('public/students/'.$student_id.'/'.$oldPhoto)):
                Storage::delete('public/students/'.$student_id.'/'.$oldPhoto);
            endif;
        endif;

        $student = Student::find($student_id);
        $student->fill([
            'photo' => $imageName
        ]);
        $changes = $student->getDirty();
        $student->save();

        if($student->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'students';
                $data['field_name'] = $field;
                $data['field_value'] = $applicantOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        return response()->json(['message' => 'Photo successfully change & updated'], 200);
    }

    public function StudentIDFilter(Request $request){
        $SearchVal = $request->SearchVal;

        $html = '';
        $Query = Student::orderBy('registration_no', 'ASC')->where('registration_no', 'LIKE', '%'.$SearchVal.'%')->get();
        
        if($Query->count() > 0):
            foreach($Query as $qr):
                $html .= '<li>';
                    $html .= '<a href="'.$qr->registration_no.'" class="dropdown-item">'.$qr->registration_no.'</a>';
                $html .= '</li>';
            endforeach;
        else:
            $html .= '<li>';
                $html .= '<a href="javascript:void(0);" class="dropdown-item disable">Nothing found!</a>';
            $html .= '</li>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function slcHistory($studentId){
        $student = Student::find($studentId);
        $courseRelationId = (isset($student->crel->id) && $student->crel->id > 0 ? $student->crel->id : 0);
        $courseCreationID = (isset($student->crel->course_creation_id) && $student->crel->course_creation_id > 0 ? $student->crel->course_creation_id : 0);
        $firstCreationInstance = CourseCreationInstance::where('course_creation_id', $courseCreationID)->orderBy('id', 'ASC')->get()->first();

        return view('pages.students.live.slc-history', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student SLC History', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'ac_years' => AcademicYear::orderBy('from_date', 'DESC')->get(),
            'active_ac_year' => (isset($firstCreationInstance->academic_year_id) && $firstCreationInstance->academic_year_id > 0 ? $firstCreationInstance->academic_year_id : 0),
            'reg_status' => SlcRegistrationStatus::where('active', 1)->get(),
            'instances' => CourseCreationInstance::where('course_creation_id', $courseCreationID)->orderBy('academic_year_id', 'ASC')->get(),
            'attendanceCodes' => AttendanceCode::where('active', 1)->orderBy('code', 'ASC')->get(),
            'slcRegistrations' => SlcRegistration::where('student_id', $studentId)->where('student_course_relation_id', $courseRelationId)->orderBy('registration_year', 'ASC')->get(),
            'term_declarations' => TermDeclaration::orderBy('id', 'desc')->get(),
            'lastAssigns' => Assign::where('student_id', $studentId)->orderBy('id', 'desc')->get()->first(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    public function accounts($student_id){
        $student = Student::find($student_id);
        $courseRelationId = (isset($student->crel->id) && $student->crel->id > 0 ? $student->crel->id : 0);
        $courseCreationID = (isset($student->crel->course_creation_id) && $student->crel->course_creation_id > 0 ? $student->crel->course_creation_id : 0);

        return view('pages.students.live.accounts', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Accounts', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'agreements' => SlcAgreement::with('installments')->where('student_id', $student_id)->where('student_course_relation_id', $courseRelationId)->orderBy('id', 'ASC')->get(),
            'instances' => CourseCreationInstance::where('course_creation_id', $courseCreationID)->orderBy('academic_year_id', 'ASC')->get(),
            'term_declarations' => TermDeclaration::orderBy('id', 'desc')->get(),
            'lastAssigns' => Assign::where('student_id', $student_id)->orderBy('id', 'desc')->get()->first(),
            'paymentMethods' => SlcPaymentMethod::orderBy('name', 'ASC')->get(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    public function sendMobileVerificationCode(Request $request){
        $student_id = $request->student_id;
        $mobileNo = $request->mobileNo;

        $verificationCode = rand(100000, 999999);
        $mobileVerification = MobileVerificationCode::create([
            'student_id' => $student_id,
            'mobile' => $mobileNo,
            'code' => $verificationCode,
            'status' => 0,
            'created_by' => auth()->user()->id,
        ]);
        if($mobileVerification):
            $active_api = Option::where('category', 'SMS')->where('name', 'active_api')->pluck('value')->first();
            $textlocal_api = Option::where('category', 'SMS')->where('name', 'textlocal_api')->pluck('value')->first();
            $smseagle_api = Option::where('category', 'SMS')->where('name', 'smseagle_api')->pluck('value')->first();

            if($active_api == 1 && !empty($textlocal_api)):
                $response = Http::timeout(-1)->post('https://api.textlocal.in/send/', [
                    'apikey' => $textlocal_api, 
                    'message' => 'Your verification code: '.$verificationCode, 
                    'sender' => 'London Churchill College', 
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
                        'text' => 'Your verification code: '.$verificationCode
                    ]);
                //return response()->json(['Message' => $response->json()], 200);
            endif;
            return response()->json(['Message' => 'Verification code successfully send to the mobile nuber.'], 200);
        else:
            return response()->json(['Message' => 'Something went wrong. Please try later'], 422);
        endif;
    }

    public function verifyMobileVerificationCode(Request $request){
        $student_id = $request->student_id;
        $code = $request->code;
        $mobile = $request->mobile;

        $applicantCodes = MobileVerificationCode::where('student_id', $student_id)->where('mobile', $mobile)
                            ->where('code', $code)->where('status', '!=', 1)->orderBy('id', 'DESC')->get()->first();
        if(isset($applicantCodes->id) && $applicantCodes->id > 0):
            MobileVerificationCode::where('id', $applicantCodes->id)->update(['status' => 1]);
            StudentContact::where('student_id', $student_id)->update(['mobile_verification' => 1]);

            return response()->json(['suc' => 1], 200);
        else:
            return response()->json(['suc' => 2], 200);
        endif;
    }

    public function setTempCourse($student, $crel){
        Session::put(['student_temp_course_relation_'.$student => $crel]);

        return redirect()->route('student.show', $student);
    }

    public function setDefaultCourse($student){
        Session::forget('student_temp_course_relation_'.$student);

        return redirect()->route('student.show', $student);
    }


    public function AttendanceDetails(Student $student) {
        $termData = [];
        $data = [];
        $planDetails = [];
        $avarageDetails = [];
        $totalFeedListSet = [];
        $totalFullSetFeedList = [];
        $avarageTermDetails = [];
        $totalClassFullSet = [];
        $returnSet = [];
            $attendanceFeedStatus = AttendanceFeedStatus::all();
            $returnSet = $this->PlanWithAttendanceSet($student);
            
           
            $returnSet = array_merge($returnSet);
            
        // endforeach;
        $termData = $returnSet["termData"];
        $moduleNameList = $returnSet["moduleNameList"];
        $data = $returnSet["data"];
        $planDetails = $returnSet["planDetails"];
        $avarageDetails = $returnSet["avarageDetails"];
        $totalFeedListSet = $returnSet["totalFeedListSet"];
        $totalFullSetFeedList = $returnSet["totalFullSetFeedList"];
        $avarageTermDetails = $returnSet["avarageTermDetails"];
        $totalClassFullSet = $returnSet["totalClassFullSet"];

        
        return view('pages.students.live.attendance.index', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Accounts', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'dataSet' => $data,
            "term" =>$termData,
            "planDetails" => $planDetails,
            'avarageDetails' => $avarageDetails,
            "totalFeedList" => $totalFeedListSet,
            "totalFullSetFeedList"=>$totalFullSetFeedList,
            "avarageTotalPercentage"=>$avarageTermDetails,
            "totalClassFullSet" =>$totalClassFullSet,
            "attendanceFeedStatus" =>$attendanceFeedStatus,
            "moduleNameList" =>$moduleNameList,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }
    protected function PlanWithAttendanceSet(Student $student) {
            $termData = [];
            $data = [];
            $planDetails = [];
            $avarageDetails = [];
            $totalFeedListSet = [];
            $totalFullSetFeedList = [];
            $avarageTermDetails = [];
            $totalClassFullSet = [];
            $moduleNameList = [];
                $QueryInner = DB::table('plans_date_lists as pdl')
                            ->select( 'pdl.*','td.id as term_id',    'td.name as term_name','instance_terms.start_date','instance_terms.end_date', 'plan.module_creation_id as module_creation_id' , 'mc.module_name','mc.code as module_code', 'plan.id as plan_id' , 'gp.name as group_name', 'gp.id as group_id' )
                            ->leftJoin('plans as plan', 'plan.id', 'pdl.plan_id')
                            ->leftJoin('instance_terms', 'instance_terms.id', 'plan.instance_term_id')
                            ->leftJoin('assigns as assign', 'plan.id', 'assign.plan_id')
                            ->leftJoin('term_declarations as td', 'td.id', 'plan.term_declaration_id')
                            ->leftJoin('module_creations as mc', 'mc.id', 'plan.module_creation_id')
                            ->leftJoin('groups as gp', 'gp.id', 'plan.group_id')
                            ->where('assign.student_id', $student->id)
                            ->orderBy("pdl.date",'desc')
                            ->get();
                            $attendanceFeedStatus = AttendanceFeedStatus::all();
                if($QueryInner->isNotEmpty())
                    foreach($QueryInner as $list):
                        $moduleNameList[$list->plan_id] = (isset($list->module_code)) ? $list->module_name."-".$list->module_code : $list->module_name;
                        $attendance = Attendance::with(["feed","createdBy","updatedBy"])->where("student_id", $student->id)->where("plans_date_list_id",$list->id)->get()->first();
                        
                        if(isset($attendance)) {

                            $attendanceInformation =AttendanceInformation::with(["tutor","planDate"])->where("plans_date_list_id",$list->id)->get()->first();
                            if(isset($attendanceInformation->tutor))
                                $attendanceInformation->tutor->load(["employee"]);
                            if(!isset($arryBox[$list->term_id][$list->plan_id][$attendance->feed->code])) {
                                $arryBox[$list->term_id][$list->plan_id][$attendance->feed->code] = 0;
                            }
                            if(!isset($totalPresentFound[$list->term_id][$list->plan_id])) {
                                $totalPresentFound[$list->term_id][$list->plan_id] = 0;
                            }
                            if(!isset($totalAbsentFound[$list->term_id][$list->plan_id])) {
                                $totalAbsentFound[$list->term_id][$list->plan_id]=0;
                            }

                            $arryBox[$list->term_id][$list->plan_id][$attendance->feed->code] += 1;
                            $totalPresentFound[$list->term_id][$list->plan_id] += $attendance->feed->attendance_count;
                            $totalAbsentFound[$list->term_id][$list->plan_id] += ($attendance->feed->attendance_count==0)? 1 : 0;

                            $json = json_encode ($arryBox[$list->term_id][$list->plan_id], JSON_FORCE_OBJECT);
                            $replace = array('{', '}', "'", '"');
                            $totalFeedList = str_replace ($replace, "", $json);
                            $total = $totalPresentFound[$list->term_id][$list->plan_id] + $totalAbsentFound[$list->term_id][$list->plan_id];

                            $avaragePercentage[$list->term_id][$list->plan_id] = (($totalPresentFound[$list->term_id][$list->plan_id]/$total)*100);
                            $precision = 2;
                            $avarage = number_format($avaragePercentage[$list->term_id][$list->plan_id], $precision, '.', '');
                        

                            $data[$list->term_id][$list->plan_id][$list ->date] = [
                                    "id" => $list->id,
                                    "date" => date("d-m-Y", strtotime($list ->date)),
                                    "attendance_information" => isset($attendanceInformation) ? $attendanceInformation: null,
                                    "attendance"=> ($attendance) ?? null,
                                    "term_id"=> $list->term_id,
                                    "module_creation_id"=>$list->module_creation_id,
                                    "plan_id" => $list->plan_id,
                            ];
                            
                            $termData[$list->term_id] = [
                                "name" => $list->term_name,
                                "start_date" => $list->start_date,
                                "end_date" => $list->end_date,
                            ];
                            $planDetails[$list->term_id][$list->plan_id] = Plan::with(["tutor","personalTutor",'group'])->where('id',$list->plan_id)->get()->first();
                            
                            
                            $avarageDetails[$list->term_id][$list->plan_id] = $avarage;
                            $totalFeedListSet[$list->term_id][$list->plan_id] = $totalFeedList;

                            //total code list and total class list
                            if(!isset($totalBox[$list->term_id][$attendance->feed->code])) {
                                $totalBox[$list->term_id][$attendance->feed->code] = 0;
                            }
                            if(!isset($totalBoxPresentFound[$list->term_id])) {
                                $totalBoxPresentFound[$list->term_id] = 0;
                            }
                            if(!isset($totalBoxAbsentFound[$list->term_id])) {
                                $totalBoxAbsentFound[$list->term_id]=0;
                            }
                            $totalBox[$list->term_id][$attendance->feed->code] += 1;
                            $totalBoxPresentFound[$list->term_id] += $attendance->feed->attendance_count;
                            $totalBoxAbsentFound[$list->term_id] += ($attendance->feed->attendance_count==0)? 1 : 0;

                            $json = json_encode ($totalBox[$list->term_id], JSON_FORCE_OBJECT);
                            $replace = array('{', '}', "'", '"');
                            $totalFullSetFeedList[$list->term_id] = str_replace ($replace, "", $json);
                            $totalClassFullSet[$list->term_id] = $totalBoxPresentFound[$list->term_id] + $totalBoxAbsentFound[$list->term_id];

                            $avarageTotalPercentage[$list->term_id] = (($totalBoxPresentFound[$list->term_id]/$totalClassFullSet[$list->term_id])*100);
                            
                            $avarage= number_format($avarageTotalPercentage[$list->term_id], $precision, '.', '');
                            $avarageTermDetails[$list->term_id] = $avarage;
                        } else {
                            
                            foreach ($attendanceFeedStatus as $feedStatus):
                                $arryBox[$list->term_id][$list->plan_id][$feedStatus->code] =0;
                            endforeach;

                            if(!isset($totalPresentFound[$list->term_id][$list->plan_id])) {
                                $totalPresentFound[$list->term_id][$list->plan_id] = 0;
                            }
                            if(!isset($totalAbsentFound[$list->term_id][$list->plan_id])) {
                                $totalAbsentFound[$list->term_id][$list->plan_id]=0;
                            }

                            $totalPresentFound[$list->term_id][$list->plan_id] = 0;
                            $totalAbsentFound[$list->term_id][$list->plan_id] = 0;

                            $json = json_encode ($arryBox[$list->term_id][$list->plan_id], JSON_FORCE_OBJECT);
                            $replace = array('{', '}', "'", '"');
                            $totalFeedList = str_replace ($replace, "", $json);
                            $total = 0;

                            $avaragePercentage[$list->term_id][$list->plan_id] = 0;
                            $precision = 2;
                            $avarage =0;
                        

                            $data[$list->term_id][$list->plan_id][$list ->date] = [
                                    "id" => $list->id,
                                    "date" => date("d-m-Y", strtotime($list ->date)),
                                    "attendance_information" => null,
                                    "attendance"=> null,
                                    "term_id"=> $list->term_id,
                                    "module_creation_id"=>$list->module_creation_id,
                                    "plan_id" => $list->plan_id,
                            ];
                            
                            $termData[$list->term_id] = [
                                "name" => $list->term_name,
                                "start_date" => $list->start_date,
                                "end_date" => $list->end_date,
                            ];
                            $planDetails[$list->term_id][$list->plan_id] = Plan::with(["tutor","personalTutor"])->where('id',$list->plan_id)->get()->first();
                            
                            
                            $avarageDetails[$list->term_id][$list->plan_id] = $avarage;
                            $totalFeedListSet[$list->term_id][$list->plan_id] = $totalFeedList;

                            //total code list and total class list
                            foreach ($attendanceFeedStatus as $feedStatus):
                                $totalBox[$list->term_id][$feedStatus->code] = 0;
                            endforeach;
                            
                            if(!isset($totalBoxPresentFound[$list->term_id])) {
                                $totalBoxPresentFound[$list->term_id] = 0;
                            }
                            if(!isset($totalBoxAbsentFound[$list->term_id])) {
                                $totalBoxAbsentFound[$list->term_id]=0;
                            }

                            $json = json_encode ($totalBox[$list->term_id], JSON_FORCE_OBJECT);
                            $replace = array('{', '}', "'", '"');
                            $totalFullSetFeedList[$list->term_id] = str_replace ($replace, "", $json);
                            $totalClassFullSet[$list->term_id] = 0;

                            $avarageTotalPercentage[$list->term_id] = 0;
                            
                            $avarage= number_format($avarageTotalPercentage[$list->term_id], $precision, '.', '');
                            $avarageTermDetails[$list->term_id] = $avarage;
                        }
                    endforeach;
            return ["termData" => $termData,"data" => $data ,"planDetails" => $planDetails,"avarageDetails" => $avarageDetails,"totalFeedListSet" => $totalFeedListSet,
                    "totalFullSetFeedList" => $totalFullSetFeedList,"avarageTermDetails" => $avarageTermDetails,"totalClassFullSet" => $totalClassFullSet ,"moduleNameList" =>$moduleNameList];
    }
    

    public function ResultDetails(Student $student) {
        $grades = Grade::all();
        //$AssessmentPlans = AssessmentPlan::where('plan_id',$student->id)->get();
        $termData = [];

            $QueryInner = DB::table('plans as plan')
                        ->select('td.id as term_id','td.name as term_name','instance_terms.start_date','instance_terms.end_date', 'plan.module_creation_id as module_creation_id' , 'mc.module_name','mc.code as module_code', 'plan.id as plan_id' )
                        ->leftJoin('instance_terms', 'instance_terms.id', 'plan.instance_term_id')
                        ->leftJoin('assigns as assign', 'plan.id', 'assign.plan_id')
                        ->leftJoin('term_declarations as td', 'td.id', 'plan.term_declaration_id')
                        ->leftJoin('module_creations as mc', 'mc.id', 'plan.module_creation_id')
                        ->where('assign.student_id', $student->id)
                        ->orderBy("td.id",'desc')
                        ->get();

            foreach($QueryInner as $list):

                $resultByPlanGroup[$list->plan_id] = Result::with(["assementPlan","grade","createdBy","updatedBy"])->where("student_id", $student->id)->where("plan_id",$list->plan_id)->orderBy('id','DESC')->get()->groupBy(function($data) {
                    return $data->assessment_plan_id;
                });
                
                //$resultFinal = $resultByPlanGroup[$list->plan_id]->first();
                
                if(isset($resultByPlanGroup) && count($resultByPlanGroup[$list->plan_id])>0) {
                    
                    $data[$list->term_id][$list->plan_id] = [
                            "term_id"=> $list->term_id,
                            "module_creation_id"=>$list->module_creation_id,
                            "id" => $list->plan_id,
                            "results" => ($resultByPlanGroup[$list->plan_id]) ?? null
                    ];
                    
                    $termData[$list->term_id] = [
                        "name" => $list->term_name,
                        "start_date" => $list->start_date,
                        "end_date" => $list->end_date,
                    ];
                    $planDetails[$list->term_id][$list->plan_id] = Plan::with(["tutor","personalTutor"])->where('id',$list->plan_id)->get()->first();
                    

                    //total code list and total class list

                }
            endforeach;
        return view('pages.students.live.result.index', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Accounts', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'dataSet' => ($data) ?? null,
            "term" =>$termData,
            "grades" =>$grades,
            "planDetails" => $planDetails ?? null,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    public function AttendanceEditDetail(Student $student) {

            $attendanceFeedStatus = AttendanceFeedStatus::all();
            $termData = [];

                $QueryInner = DB::table('plans_date_lists as pdl')
                            ->select( 'pdl.*','td.id as term_id',    'td.name as term_name','instance_terms.start_date','instance_terms.end_date', 'plan.module_creation_id as module_creation_id' , 'mc.module_name','mc.code as module_code', 'plan.id as plan_id' )
                            ->leftJoin('plans as plan', 'plan.id', 'pdl.plan_id')
                            ->leftJoin('instance_terms', 'instance_terms.id', 'plan.instance_term_id')
                            ->leftJoin('assigns as assign', 'plan.id', 'assign.plan_id')
                            ->leftJoin('term_declarations as td', 'td.id', 'plan.term_declaration_id')
                            ->leftJoin('module_creations as mc', 'mc.id', 'plan.module_creation_id')
                            ->where('assign.student_id', $student->id)
                            ->orderBy("pdl.date",'desc')
                            ->get();
                foreach($QueryInner as $list):
                    $attendance = Attendance::with(["feed","createdBy","updatedBy"])->where("student_id", $student->id)->where("plans_date_list_id",$list->id)->get()->first();
                    
                    if($attendance) {
                        $attendanceInformation =AttendanceInformation::with(["tutor","planDate"])->where("plans_date_list_id",$list->id)->get()->first();
                        $attendanceInformation->tutor->load(["employee"]);
                        
                        if(!isset($arryBox[$list->term_id][$list->module_name."-".$list->module_code][$attendance->feed->code])) {
                            $arryBox[$list->term_id][$list->module_name."-".$list->module_code][$attendance->feed->code] = 0;
                        }
                        if(!isset($totalPresentFound[$list->term_id][$list->module_name."-".$list->module_code])) {
                            $totalPresentFound[$list->term_id][$list->module_name."-".$list->module_code] = 0;
                        }
                        if(!isset($totalAbsentFound[$list->term_id][$list->module_name."-".$list->module_code])) {
                            $totalAbsentFound[$list->term_id][$list->module_name."-".$list->module_code]=0;
                        }

                        $arryBox[$list->term_id][$list->module_name."-".$list->module_code][$attendance->feed->code] += 1;
                        $totalPresentFound[$list->term_id][$list->module_name."-".$list->module_code] += $attendance->feed->attendance_count;
                        $totalAbsentFound[$list->term_id][$list->module_name."-".$list->module_code] += ($attendance->feed->attendance_count==0)? 1 : 0;

                        $json = json_encode ($arryBox[$list->term_id][$list->module_name."-".$list->module_code], JSON_FORCE_OBJECT);
                        $replace = array('{', '}', "'", '"');
                        $totalFeedList = str_replace ($replace, "", $json);
                        $total = $totalPresentFound[$list->term_id][$list->module_name."-".$list->module_code] + $totalAbsentFound[$list->term_id][$list->module_name."-".$list->module_code];

                        $avaragePercentage[$list->term_id][$list->module_name."-".$list->module_code] = (($totalPresentFound[$list->term_id][$list->module_name."-".$list->module_code]/$total)*100);
                        $precision = 2;
                        $avarage = number_format($avaragePercentage[$list->term_id][$list->module_name."-".$list->module_code], $precision, '.', '');
                    

                    $data[$list->term_id][$list->module_name."-".$list->module_code][$list ->date] = [
                            "id" => $list->id,
                            "date" => date("d-m-Y", strtotime($list ->date)),
                            "attendance_information" => ($attendanceInformation) ?? null,
                            "attendance"=> ($attendance) ?? null,
                            "term_id"=> $list->term_id,
                            "module_creation_id"=>$list->module_creation_id,
                            "plan_id" => $list->plan_id,
                    ];
                    
                    $termData[$list->term_id] = [
                        "name" => $list->term_name,
                        "start_date" => $list->start_date,
                        "end_date" => $list->end_date,
                    ];
                    $planDetails[$list->term_id][$list->module_name."-".$list->module_code] = Plan::with(["tutor","personalTutor"])->where('id',$list->plan_id)->get()->first();
                    
                    
                    $avarageDetails[$list->term_id][$list->module_name."-".$list->module_code] = $avarage;
                    $totalFeedListSet[$list->term_id][$list->module_name."-".$list->module_code] = $totalFeedList;

                    //total code list and total class list
                    if(!isset($totalBox[$list->term_id][$attendance->feed->code])) {
                        $totalBox[$list->term_id][$attendance->feed->code] = 0;
                    }
                    if(!isset($totalBoxPresentFound[$list->term_id])) {
                        $totalBoxPresentFound[$list->term_id] = 0;
                    }
                    if(!isset($totalBoxAbsentFound[$list->term_id])) {
                        $totalBoxAbsentFound[$list->term_id]=0;
                    }
                    $totalBox[$list->term_id][$attendance->feed->code] += 1;
                    $totalBoxPresentFound[$list->term_id] += $attendance->feed->attendance_count;
                    $totalBoxAbsentFound[$list->term_id] += ($attendance->feed->attendance_count==0)? 1 : 0;

                    $json = json_encode ($totalBox[$list->term_id], JSON_FORCE_OBJECT);
                    $replace = array('{', '}', "'", '"');
                    $totalFullSetFeedList[$list->term_id] = str_replace ($replace, "", $json);
                    $totalClassFullSet[$list->term_id] = $totalBoxPresentFound[$list->term_id] + $totalBoxAbsentFound[$list->term_id];

                    $avarageTotalPercentage[$list->term_id] = (($totalBoxPresentFound[$list->term_id]/$totalClassFullSet[$list->term_id])*100);
                    
                    $avarage= number_format($avarageTotalPercentage[$list->term_id], $precision, '.', '');
                    $avarageTermDetails[$list->term_id] = $avarage;
                }
                endforeach;


        return view('pages.students.live.attendance.form', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Accounts', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'dataSet' => $data,
            "term" =>$termData,
            "planDetails" => $planDetails,
            'avarageDetails' => $avarageDetails,
            "totalFeedList" => $totalFeedListSet,
            "totalFullSetFeedList"=>$totalFullSetFeedList,
            "avarageTotalPercentage"=>$avarageTermDetails,
            "totalClassFullSet" =>$totalClassFullSet,
            "attendanceFeedStatus" =>$attendanceFeedStatus,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    public function getAllGroups(Request $request){
        $term_declaration_id = $request->term_declaration_id;
        $course = $request->course;

        $res = [];
        $groups = Group::select('name')->where('term_declaration_id', $term_declaration_id)->where('course_id', $course)->groupBy('name')->orderBy('name', 'ASC')->get();
        if(!empty($groups)):
            $i = 1;
            foreach($groups as $gr):
                $theGroup = Group::where('name', $gr->name)->where('course_id', $course)->where('term_declaration_id', $term_declaration_id)->orderBy('id', 'DESC')->get()->first();
                $res[$i]['id'] = $theGroup->id;
                $res[$i]['name'] = $theGroup->name;

                $i++;
            endforeach;
        endif;

        return response()->json(['res' => $res], 200);
    }

    public function workplacement($student_id){
        $student = Student::find($student_id);

        return view('pages.students.live.workplacement', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Work Placement', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'company' => Company::orderBy('name', 'ASC')->get(),
            'work_hours' => StudentWorkPlacement::where('student_id', $student_id)->sum('hours'),
            'placement' => StudentWorkPlacement::all(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    public function studentDocumentDownload(Request $request){ 
        $row_id = $request->row_id;

        $studentDoc = StudentDocument::where('id',$row_id)->withTrashed()->get()->first();
        $student_id = $studentDoc->student_id;
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/students/'.$student_id.'/'.$studentDoc->current_file_name, now()->addMinutes(5));
        return response()->json(['res' => $tmpURL], 200);
    }

    public function sendEmailVerificationCode(Request $request){
        $student_id = $request->student_id;
        $personal_email = $request->personal_email;
        $student = Student::find($student_id);

        $verificationCode = rand(100000, 999999);
        $emailVerification = EmailVerificationCode::create([
            'student_id' => $student_id,
            'email' => $personal_email,
            'code' => $verificationCode,
            'status' => 0,
            'created_by' => auth()->user()->id,
        ]);
        if($emailVerification):
            $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
            $configuration = [
                'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                
                'from_email'    => 'no-reply@lcc.ac.uk',
                'from_name'    =>  'London Churchill College',
            ];

            $MAILBODY = 'Dear '.$student->full_name.'<br/><br/>';
            $MAILBODY .= 'Your personal email address has been changed. Here is the verification code for new email address.<br/><br/>';
            $MAILBODY .= '<h1 style="font-size: 40px; font-weight: bold; margin: 0;">'.$verificationCode.'</h1><br/><br/>';
            $MAILBODY .= 'Best regards,<br/>';
            $MAILBODY .= 'London Churchill College';

            UserMailerJob::dispatch($configuration, [$personal_email], new CommunicationSendMail('Email Verification Code', $MAILBODY, []));

            return response()->json(['Message' => 'Verification code successfully send to the email address.'], 200);
        else:
            return response()->json(['Message' => 'Something went wrong. Please try later'], 422);
        endif;
    }

    public function verifyEmailVerificationCode(Request $request){
        $student_id = $request->student_id;
        $code = $request->code;
        $email = $request->email;

        $studentCodes = EmailVerificationCode::where('student_id', $student_id)->where('email', $email)
                            ->where('code', $code)->where('status', '!=', 1)->orderBy('id', 'DESC')->get()->first();
        if(isset($studentCodes->id) && $studentCodes->id > 0):
            EmailVerificationCode::where('id', $studentCodes->id)->update(['status' => 1]);
            //StudentContact::where('student_id', $student_id)->update(['personal_email_verification' => 1]);

            return response()->json(['suc' => 1], 200);
        else:
            return response()->json(['suc' => 2], 200);
        endif;
    }

    public function printStudentCommunications($student_id, $type){
        $student = Student::find($student_id);
        $address = '';
        if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0):
            if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1)):
                $address .= $student->contact->termaddress->address_line_1.'<br/>';
            endif;
            if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2)):
                $address .= $student->contact->termaddress->address_line_2.'<br/>';
            endif;
            if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city)):
                $address .= $student->contact->termaddress->city.', ';
            endif;
            if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state)):
                $address .= $student->contact->termaddress->state.', <br/>';
            endif;
            if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code)):
                $address .= $student->contact->termaddress->post_code.', ';
            endif;
            if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country)):
                $address .= '<br/>'.$student->contact->termaddress->country;
            endif;
        endif;

        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>Communication Sheets of '.$student->full_name.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: rgb(30, 41, 59);}
                                table{margin-left: 0px; border-collapse: collapse; width: 100%;}
                                figure{margin: 0;}
                                @page{margin-top: 115px;margin-left: 30px;margin-right: 30px;margin-bottom: 30px;}
                                header{position: fixed;left: 0px;right: 0px;height: 90px;margin-top: -90px;}
                                
                                .regInfoRow td{border-top: 1px solid gray;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}
                                .btn{display: inline-block; font-size: 10px; line-height: normal; font-weight: bold; color: #FFF; background: rgb(22 78 99); padding: 2px 5px; text-align: center;}
                                .btn-success{background: rgb(13 148 13);}
                                .btn-danger{background: rgb(185 28 28);}

                                .bodyContainer{font-size: 13px; line-height: normal; padding: 0 30px;}
                                .tableTitle{font-size: 22px; font-weight: bold; color: #000; line-height: 22px; margin: 0;}
                                .employeeInfo{line-height: normal;}
                                .mb-30{margin-bottom: 30px;}
                                .mb-20{margin-bottom: 20px;}
                                .mb-15{margin-bottom: 15px;}
                                .mb-10{margin-bottom: 10px;}
                                .text-justify{text-align: justify;}
                            
                                .table {width: 100%; text-align: left; text-indent: 0; border-color: inherit; border-collapse: collapse;}
                                .table th {border-style: solid;border-color: #e5e7eb;border-bottom-width: 2px;padding-left: 1.25rem;padding-right: 1.25rem;padding-top: 0.75rem;padding-bottom: 0.75rem;font-weight: 500;}
                                .table td {border-style: solid;border-color: #e5e7eb; border-bottom-width: 1px;padding-left: 1.25rem;padding-right: 1.25rem;padding-top: 0.75rem;padding-bottom: 0.75rem;}

                                .table.table-bordered th, .table.table-bordered td {border-left-width: 1px;border-right-width: 1px;border-top-width: 1px;}

                                .table.table-sm th {padding-left: 1rem;padding-right: 1rem;padding-top: 0.5rem;padding-bottom: 0.5rem;}
                                .table.table-sm td {padding-left: 1rem;padding-right: 1rem;padding-top: 0.5rem;padding-bottom: 0.5rem;}

                                .barTitle{padding: 5px 10px; background: rgb(226, 232, 240); font-size: 14px; font-weight: bold; line-height: normal;}
                                .spacer{padding: 5px 0 6px;}
                                .theLabel{vertical-align: top; padding: 0 10px 15px; width: 20%; font-weight: medium; font-size: 13px; color: rgb(100, 116, 139); line-height: normal;}
                                .theValue{vertical-align: top; padding: 0 10px 15px; width: 30%; font-weight: medium; font-size: 13px; color: rgb(30, 41, 59); line-height: normal;}
                                .theValue.tv-large{width: 80%;}

                                .pdfList{margin: 0; padding: 0 0 0 10px; }
                                .pdfList li{margin: 0 0 3px; font-size: 12px; line-height: normal; color: rgb(100, 116, 139);}
                            </style>';
            $PDFHTML .= '</head>';

            $PDFHTML .= '<body>';

                $PDFHTML .= '<header>';
                    $PDFHTML .= '<table>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td>';
                                $PDFHTML .= '<img style="height: 60px; width: atuo;" src="https://datafuture2.lcc.ac.uk/limon/LCC-Logo-01-croped.png"/>';
                            $PDFHTML .= '</td>';
                            $PDFHTML .= '<td class="text-right">';
                                $PDFHTML .= '<img style="height: 55px; width: auto;" alt="'.$student->full_name.'" src="'.(isset($student->photo) && !empty($student->photo) && Storage::disk('local')->exists('public/students/'.$student->id.'/'.$student->photo) ? url('storage/students/'.$student->id.'/'.$student->photo) : asset('build/assets/images/placeholders/200x200.jpg')).'">';
                                $PDFHTML .= '<span style="font-size: 10px; padding: 3px 0 0; font-weight: 700; display: block;">'.$student->full_name.'</span>';
                                $PDFHTML .= '<span style="font-size: 10px; padding: 0 0 0; font-weight: 700; display: block;">'.(!empty($student->registration_no) ? $student->registration_no : '').'</span>';
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</header>';

                $PDFHTML .= '<table class="mb-10">';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="barTitle text-center">Student Communication Sheet</td>';
                    $PDFHTML .= '</tr>';
                $PDFHTML .= '</table>';

                $PDFHTML .= '<table class="mb-10">';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="theLabel">Semester</td>';
                        $PDFHTML .= '<td class="theValue">'.(isset($student->course->semester->name) ? $student->course->semester->name : '').'</td>';
                    
                        $PDFHTML .= '<td class="theLabel">Programme Name</td>';
                        $PDFHTML .= '<td class="theValue">'.(isset($student->course->creation->course->name) ? $student->course->creation->course->name : '').'</td>';
                    $PDFHTML .= '</tr>';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="theLabel">Date of Birth</td>';
                        $PDFHTML .= '<td class="theValue">'.(isset($student->date_of_birth) && !empty($student->date_of_birth) ? date('jS F, Y', strtotime($student->date_of_birth)) : '').'</td>';

                        $PDFHTML .= '<td class="theLabel">Awarding Body</td>';
                        $PDFHTML .= '<td class="theValue">'.(isset($student->crel->creation->course->body->name) ? $student->crel->creation->course->body->name : '').'</td>';
                    $PDFHTML .= '</tr>';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="theLabel">Awarding Body Reg. No</td>';
                        $PDFHTML .= '<td class="theValue">'.(isset($student->crel->abody->reference) ? $student->crel->abody->reference : '').'</td>';
                    
                        $PDFHTML .= '<td class="theLabel">Date of Award</td>';
                        $PDFHTML .= '<td class="theValue"></td>';
                    $PDFHTML .= '</tr>';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="theLabel">Address</td>';
                        $PDFHTML .= '<td class="theValue">'.$address.'</td>';
                    $PDFHTML .= '</tr>';
                $PDFHTML .= '</table>';

                $PDFHTML .= '<table class="mb-10">';
                    if($type == 'letter' || $type == 'all'):
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td colspan="4" class="barTitle text-center">Letters</td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td colspan="4" style="padding: '.($type == 'all' ? '0 0 30px' : '0').';">';
                                $PDFHTML .= '<table class="table table-bordered table-sm mb-15">';
                                    $PDFHTML .= '<thead>';
                                        $PDFHTML .= '<tr>';
                                            $PDFHTML .= '<th class="text-left">#ID</th>';
                                            $PDFHTML .= '<th class="text-left">Type</th>';
                                            $PDFHTML .= '<th class="text-left">Subject</th>';
                                            $PDFHTML .= '<th class="text-left">Signatory</th>';
                                            $PDFHTML .= '<th class="text-left">Issued By</th>';
                                        $PDFHTML .= '</tr>';
                                    $PDFHTML .= '</thead>';
                                    $PDFHTML .= '<tbody>';
                                        $letters = StudentLetter::where('student_id', $student_id)->orderBy('id', 'DESC')->get();
                                        if($letters->count() > 0):
                                            foreach($letters as $ltr):
                                                $PDFHTML .= '<tr>';
                                                    $PDFHTML .= '<td class="text-left">'.$ltr->id.'</td>';
                                                    $PDFHTML .= '<td class="text-left">'.(isset($ltr->letterSet->letter_type) ? $ltr->letterSet->letter_type : '').'</td>';
                                                    $PDFHTML .= '<td class="text-left">'.(isset($ltr->letterSet->letter_title) ? $ltr->letterSet->letter_title : '').'</td>';
                                                    $PDFHTML .= '<td class="text-left">'.(isset($ltr->signatory->signatory_name) ? $ltr->signatory->signatory_name : '').'</td>';
                                                    $PDFHTML .= '<td class="text-left">';
                                                        $PDFHTML .= (isset($ltr->issuedBy->employee->full_name) ? '<strong>'.$ltr->issuedBy->employee->full_name.'</strong><br/>' : '<strong>'.$ltr->issuedBy->name.'</strong><br/>');
                                                        $PDFHTML .= (isset($ltr->issued_date) && !empty($ltr->issued_date) ? date('jS F, Y', strtotime($ltr->issued_date)) : '');
                                                    $PDFHTML .= '</td>';
                                                $PDFHTML .= '</tr>';
                                            endforeach;
                                        else:
                                            $PDFHTML .= '<tr><td colspan="5" class="text-center">No data found!</td></tr>';
                                        endif;
                                    $PDFHTML .= '</tbody>';
                                $PDFHTML .= '</table>';
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                    endif;
                    if($type == 'email' || $type == 'all'):
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td colspan="4" class="barTitle text-center">Emails</td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td colspan="4" style="padding: '.($type == 'all' ? '0 0 30px' : '0').';">';
                                $PDFHTML .= '<table class="table table-bordered table-sm mb-15">';
                                    $PDFHTML .= '<thead>';
                                        $PDFHTML .= '<tr>';
                                            $PDFHTML .= '<th class="text-left">#ID</th>';
                                            $PDFHTML .= '<th class="text-left">Subject</th>';
                                            $PDFHTML .= '<th class="text-left">From</th>';
                                            $PDFHTML .= '<th class="text-left">Issued By</th>';
                                        $PDFHTML .= '</tr>';
                                    $PDFHTML .= '</thead>';
                                    $PDFHTML .= '<tbody>';
                                        $emails = StudentEmail::where('student_id', $student_id)->orderBy('id', 'DESC')->get();
                                        if($emails->count() > 0):
                                            foreach($emails as $eml):
                                                $PDFHTML .= '<tr>';
                                                    $PDFHTML .= '<td class="text-left">'.$eml->id.'</td>';
                                                    $PDFHTML .= '<td class="text-left">'.(isset($eml->subject) ? $eml->subject : '').'</td>';
                                                    $PDFHTML .= '<td class="text-left">'.(isset($eml->smtp->smtp_user) ? $eml->smtp->smtp_user : '').'</td>';
                                                    $PDFHTML .= '<td class="text-left">';
                                                        $PDFHTML .= (isset($eml->user->employee->full_name) ? '<strong>'.$eml->user->employee->full_name.'</strong><br/>' : '<strong>'.$eml->user->name.'</strong><br/>');
                                                        $PDFHTML .= (isset($eml->created_at) && !empty($eml->created_at) ? date('jS F, Y', strtotime($eml->created_at)) : '');
                                                    $PDFHTML .= '</td>';
                                                $PDFHTML .= '</tr>';
                                            endforeach;
                                        else:
                                            $PDFHTML .= '<tr><td colspan="5" class="text-center">No data found!</td></tr>';
                                        endif;
                                    $PDFHTML .= '</tbody>';
                                $PDFHTML .= '</table>';
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                    endif;
                    if($type == 'sms' || $type == 'all'):
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td colspan="4" class="barTitle text-center">SMS</td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td colspan="4" style="padding: '.($type == 'all' ? '0 0 30px' : '0').';">';
                                $PDFHTML .= '<table class="table table-bordered table-sm mb-15">';
                                    $PDFHTML .= '<thead>';
                                        $PDFHTML .= '<tr>';
                                            $PDFHTML .= '<th class="text-left">#ID</th>';
                                            $PDFHTML .= '<th class="text-left">Subject</th>';
                                            $PDFHTML .= '<th class="text-left">Issued By</th>';
                                        $PDFHTML .= '</tr>';
                                    $PDFHTML .= '</thead>';
                                    $PDFHTML .= '<tbody>';
                                        $smss = StudentSms::where('student_id', $student_id)->orderBy('id', 'DESC')->get();
                                        if($smss->count() > 0):
                                            foreach($smss as $sms):
                                                $PDFHTML .= '<tr>';
                                                    $PDFHTML .= '<td class="text-left">'.$sms->id.'</td>';
                                                    $PDFHTML .= '<td class="text-left">'.(isset($sms->subject) ? $sms->subject : '').'</td>';
                                                    $PDFHTML .= '<td class="text-left">';
                                                        $PDFHTML .= (isset($sms->user->employee->full_name) ? '<strong>'.$sms->user->employee->full_name.'</strong><br/>' : '<strong>'.$sms->user->name.'</strong><br/>');
                                                        $PDFHTML .= (isset($sms->created_at) && !empty($sms->created_at) ? date('jS F, Y', strtotime($sms->created_at)) : '');
                                                    $PDFHTML .= '</td>';
                                                $PDFHTML .= '</tr>';
                                            endforeach;
                                        else:
                                            $PDFHTML .= '<tr><td colspan="5" class="text-center">No data found!</td></tr>';
                                        endif;
                                    $PDFHTML .= '</tbody>';
                                $PDFHTML .= '</table>';
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                    endif;
                $PDFHTML .= '</table>';

            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = str_replace(' ', '_', $student->full_name).'_communication_sheet.pdf';
        $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        return $pdf->download($fileName);
    }


    public function studentCopyProfilePhoto($page = 1, $limit = 2000){
        /*if($page > 0 && $limit > 0):
            $offset = ($page - 1) * $limit;
            $students = Student::whereNotNull('photo')->where('photo', 'not like', "%uploads/files%")->where('photo', 'not like', "%uploads/student_files%")->offset($offset)->limit($limit)->orderBy('id', 'ASC')->get();
            if(!empty($students)):
                foreach($students as $std):
                    if(isset($std->photo) && !empty($std->photo)):
                        $student_id = $std->id;
                        $photo = $std->photo;

                        $file_url = 'https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/uploads/student_files/'.$student_id.'/'.urlencode($photo);
                        if($this->remote_file_exists($file_url)):
                            if(!Storage::disk('local')->exists('public/students/'.$student_id)){
                                Storage::disk('local')->makeDirectory('public/students/'.$student_id);
                            }
                            if(!Storage::disk('local')->exists('public/students/'.$student_id.'/'.$photo)):
                                copy($file_url, Storage::disk('local')->path('public/students/'.$student_id.'/'.$photo));
                            endif;
                        endif;
                    endif;
                endforeach;
            endif;
        endif;*/

        /*$attachmentsCount = DB::table('email_issuing')->whereNotNull('attachments')->where('attachments', '!=', '')->count();
        if($page > 0 && $limit > 0):
            $offset = ($page - 1) * $limit;
            $attachments = DB::table('email_issuing')->whereNotNull('attachments')->where('attachments', '!=', '')->orderBy('id', 'ASC')->skip($offset)
                            ->take($limit)->get();
            if(!empty($attachments)):
                foreach($attachments as $atch):
                    if(isset($atch->attachments) && !empty($atch->attachments)):
                        $email_row_id = $atch->id;
                        $student_id = $atch->student_data_id;
                        $subject = $atch->subject;
                        $attachments_arr = explode(',', $atch->attachments);
                        if(!empty($attachments_arr)):
                            foreach($attachments_arr as $atr):
                                if(!empty($atr)):
                                    $fileNames = explode('.', $atr);
                                    $ext = end($fileNames);
                                    $data = [];
                                    $data['student_id'] = $student_id;
                                    $data['student_email_id'] = $email_row_id;
                                    $data['doc_type'] = $ext;
                                    $data['path'] = trim($atr);
                                    $data['display_file_name'] = $subject;
                                    $data['current_file_name'] = trim($atr);
                                    $data['created_by'] = $atch->issued_by;
                                    $data['created_at'] = (isset($atch->issued_date) && !empty($atch->issued_date) ? date('Y-m-d', strtotime($atch->issued_date)).' '.date('H:i:s') : date('Y-m-d H:i:s'));
                                    $studentDocument = StudentEmailsDocument::create($data);
                                endif;
                            endforeach;
                        endif;
                    endif;
                endforeach;
            endif;
        endif;*/

        /*$letterPDFS = DB::table('letter_issuing')->whereNotNull('pdf_name')->where('pdf_name', '!=', '')->where('letter_id', '>', 0)->count();
        if($page > 0 && $limit > 0):
            $offset = ($page - 1) * $limit;
            $pdfs = DB::table('letter_issuing')->whereNotNull('pdf_name')->where('pdf_name', '!=', '')->where('letter_id', '>', 0)->orderBy('id', 'ASC')->skip($offset)
                            ->take($limit)->get();
            if(!empty($pdfs)):
                foreach($pdfs as $pdf):
                    $letter = LetterSet::find($pdf->letter_id);
                    $data = [];
                    $data['student_id'] = $pdf->student_data_id;
                    $data['hard_copy_check'] = 0;
                    $data['doc_type'] = 'pdf';
                    $data['path'] = $pdf->pdf_name;
                    $data['display_file_name'] = (isset($letter->letter_title) && !empty($letter->letter_title) ? $letter->letter_title : $pdf->pdf_name);
                    $data['current_file_name'] = $pdf->pdf_name;
                    $data['created_by'] = $pdf->issued_by;
                    $studentDocument = StudentDocument::create($data);

                    if($studentDocument):
                        $noteUpdate = StudentLetter::where('id', $pdf->id)->update([
                            'student_document_id' => $studentDocument->id
                        ]);
                    endif;
                endforeach;
            endif;
        endif;*/

        return view('pages.students.live.copy-profile-photo', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Students Live', 'href' => 'javascript:void(0);'],
                ['label' => 'Copy Photo', 'href' => 'javascript:void(0);']
            ],
            'student' => Student::whereNotNull('photo')->where('photo', 'not like', "%uploads/files%")->where('photo', 'not like', "%uploads/student_files%")->get()->count(),
            //'student' => $attachmentsCount,
            //'student' => $letterPDFS,
            'student' => 0,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    function remote_file_exists($url){
        return str_contains(get_headers($url)[0], "200 OK");
    }

    public function studentUpdateStatus(StudentUpdateStatusRequest $request){
        $student_id = $request->student_id;
        $studentOld = Student::find($student_id);

        $status_id = $request->status_id;
        $term_declaration_id = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : null);
        $status_change_reason = (isset($request->status_change_reason) && !empty($request->status_change_reason) ? $request->status_change_reason : null);
        $status_change_date = (isset($request->status_change_date) && !empty($request->status_change_date) ? date('Y-m-d', strtotime($request->status_change_date)).' '.date('H:i:s') : date('Y-m-d H:i:s'));
        
        $student = Student::find($student_id);
        $student->fill([
            'status_id' => $status_id
        ]);
        $changes = $student->getDirty();
        $student->save();
        if($student->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'students';
                $data['field_name'] = $field;
                $data['field_value'] = $studentOld->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endforeach;

            $data = [];
            $data['student_id'] = $student_id;
            $data['term_declaration_id'] = $term_declaration_id;
            $data['status_id'] = $status_id;
            $data['status_change_reason'] = $status_change_reason;
            $data['status_change_date'] = $status_change_date;
            $data['created_by'] = auth()->user()->id;
            StudentAttendanceTermStatus::create($data);

            $status = Status::find($status_id);
            if(isset($status->process_list_id) && $status->process_list_id > 0):
                $processTask = TaskList::where('process_list_id', $status->process_list_id)->orderBy('id', 'ASC')->get();
                if(!empty($processTask) && $processTask->count() > 0 ):
                    foreach($processTask as $task):
                        $data = [];
                        $data['student_id'] = $student_id;
                        $data['task_list_id'] = $task->id;
                        $data['external_link_ref'] = (isset($task->external_link_ref) && !empty($task->external_link_ref) ? $task->external_link_ref : null);
                        $data['status'] = 'Pending';
                        $data['created_by'] = auth()->user()->id;

                        StudentTask::create($data);
                    endforeach;
                endif;
            endif;
            return response()->json(['message' => 'Student status successfully changed.'], 200);
        else:
            return response()->json(['message' => 'Nothing was changed. Please try again.'], 304);
        endif;
    }
}
