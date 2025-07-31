<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentUpdateStatusRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
use App\Models\CountryOfPermanentAddress;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\CourseCreationVenue;
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
use App\Models\LevelHours;
use App\Models\MobileVerificationCode;
use App\Models\ModduleCreation;
use App\Models\ModuleCreation;
use App\Models\Option;
use App\Models\OtherAcademicQualification;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\PreviousProvider;
use App\Models\ProcessList;
use App\Models\QualAwardResult;
use App\Models\QualificationGrade;
use App\Models\QualificationTypeIdentifier;
use App\Models\ReasonForEngagementEnding;
use App\Models\ReferralCode;
use App\Models\Religion;
use App\Models\Result;
use App\Models\Semester;
use App\Models\SexIdentifier;
use App\Models\SexualOrientation;
use App\Models\Signatory;
use App\Models\SlcAgreement;
use App\Models\SlcAttendance;
use App\Models\SlcCoc;
use App\Models\SlcMoneyReceipt;
use App\Models\SlcPaymentMethod;
use App\Models\SlcRegistration;
use App\Models\SlcRegistrationStatus;
use App\Models\SmsTemplate;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentAttendanceTermStatus;
use App\Models\StudentAward;
use App\Models\StudentConsent;
use App\Models\StudentContact;
use App\Models\StudentCourseRelation;
use App\Models\StudentDocument;
use App\Models\StudentEmail;
use App\Models\StudentFlag;
use App\Models\StudentLetter;
use App\Models\StudentProposedCourse;
use App\Models\Title;
use App\Models\User;
use App\Models\StudentSms;
use App\Models\StudentTask;
use App\Models\StudentUser;
use App\Models\StudentWorkPlacement;
use App\Models\StudyMode;
use App\Models\TaskList;
use App\Models\TermDeclaration;
use App\Models\TermTimeAccommodationType;
use App\Models\StudentStuloadInformation;
use App\Models\StudentWorkplacementDocument;
use App\Models\WorkplacementDetails;
use App\Models\WorkplacementSetting;
use Barryvdh\Debugbar\Facades\Debugbar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

use PDF;

class StudentController extends Controller
{
    public function index(){
        $semesters = Cache::get('semesters', function () {
            $semesters = Semester::all()->sortByDesc("name");
            $semesterData = [];
            foreach ($semesters as $semester):
                $studentProposedCourse = StudentProposedCourse::where('semester_id',$semester->id)->get()->first();
                if(isset($studentProposedCourse->id))
                    $semesterData[] = $semester;
            endforeach;
            return $semesterData;
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

            
            'smsTemplates' => SmsTemplate::where('live', 1)->where('status', 1)->orderBy('sms_title', 'ASC')->get(),
            'emailTemplates' => EmailTemplate::where('live', 1)->where('status', 1)->orderBy('email_title', 'ASC')->get(),
            'letterSet' => LetterSet::where('live', 1)->where('status', 1)->orderBy('letter_type', 'ASC')->orderBy('letter_title', 'ASC')->get(),
            'smtps' => ComonSmtp::orderBy('smtp_user', 'ASC')->get(),
            'signatory' => Signatory::orderBy('signatory_name', 'ASC')->get()
            
        ]);

    }

    public function list(Request $request) {

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
                        $qr->where('mobile', $student_mobile);
                    endif;
                });
            endif;
            if(!empty($student_uhn)): $Query->where('uhn_no', $student_uhn); endif;
            if(!empty($student_ssn)): $Query->where('ssn_no', $student_ssn); endif;
            if(!empty($application_no)): $Query->where('application_no', $application_no); endif;
            if(!empty($student_status)): $Query->whereIn('status_id', $student_status); endif;
        endif;
        if($groupSearch):
            
            foreach($groupParams as $field => $value):
                $$field = (isset($value) && !empty($value) ? $value : '');
            endforeach;
            //dd($groupParams);
            $studentsIds = [];
                $myRequest = new Request();

                $myRequest->setMethod('POST');

                if(isset($academic_year))
                    $myRequest->request->add(['academic_years' => $academic_year]);
                else
                    $myRequest->request->add(['academic_years' => '']);
                
                if(isset($attendance_semester))
                $myRequest->request->add(['term_declaration_ids' => $attendance_semester]);

                if(isset($course))
                    $myRequest->request->add(['courses' => $course]);
                if(isset($group))
                    $myRequest->request->add(['groups' => $group]);
                if(isset($intake_semester))
                    $myRequest->request->add(['intake_semesters' => $intake_semester]);
                if(isset($group_student_status))
                    $myRequest->request->add(['group_student_statuses' => $group_student_status]);
                if(isset($student_type))
                    $myRequest->request->add(['student_types' => $student_type]);
                if(isset($groupParams["evening_weekend"]))
                    $myRequest->request->add(['evening_weekends' => $groupParams["evening_weekend"]]);
                
                

                $studentsIds = $this->callTheStudentListForGroup($myRequest);
                

            if(!empty($studentsIds)): 
                $Query->whereIn('id', $studentsIds); 
            else:
                $Query->whereIn('id', [0]); 
            endif;
        endif;
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
                    'photo_url' => $list->photo_url,
                    'flag_html' => (isset($list->flag_html) && !empty($list->flag_html) ? $list->flag_html : ''),
                    'due' => $list->due,
                    'multi_agreement_status' => $list->multi_agreement_status
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
            'pcountry' => CountryOfPermanentAddress::orderBy('name', 'ASC')->where('active', 1)->get(),
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
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'qualgrades' => QualificationGrade::where('active', 1)->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
        ]);
    }

    public function courseDetails($studentId){

        $student = Student::with('course')->where('id', $studentId)->get()->first();
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
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            "CourseRelation" => $student->crel,
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
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
            'letterSet' => LetterSet::where('live', 1)->where('status', 1)->orderBy('letter_type', 'ASC')->orderBy('letter_title', 'ASC')->get(),
            'signatory' => Signatory::all(),
            'smsTemplates' => SmsTemplate::where('live', 1)->where('status', 1)->orderBy('sms_title', 'ASC')->get(),
            'emailTemplates' => EmailTemplate::where('live', 1)->where('status', 1)->orderBy('email_title', 'ASC')->get(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
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
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
        ]);
    }

    public function notes($studentId){
        $userData = \Auth::guard('web')->user();
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
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'flags' => StudentFlag::orderBy('id', 'ASC')->get(),
            'cuser' => $userData,
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
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
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
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
        $instances = CourseCreationInstance::where('course_creation_id', $courseCreationID)->orderBy('academic_year_id', 'ASC')->get();
        $default_inst_ids = ($instances->count() > 0 ? $instances->pluck('id')->unique()->toArray() : [0]);
        $stuload_instance_ids = StudentStuloadInformation::where('student_id', $studentId)->whereNotIn('course_creation_instance_id', $default_inst_ids)->where('student_course_relation_id', $courseRelationId)->pluck('course_creation_instance_id')->unique()->toArray();
        $availableInstanc_ids = array_merge($default_inst_ids, $stuload_instance_ids);

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
            'instances' => CourseCreationInstance::whereIn('id', $availableInstanc_ids)->orderBy('academic_year_id', 'ASC')->get(),
            'attendanceCodes' => AttendanceCode::where('active', 1)->orderBy('code', 'ASC')->get(),
            'slcRegistrations' => SlcRegistration::where('student_id', $studentId)->where('student_course_relation_id', $courseRelationId)->orderBy('registration_year', 'ASC')->get(),
            'term_declarations' => TermDeclaration::orderBy('id', 'desc')->get(),
            'lastAssigns' => Assign::where('student_id', $studentId)->orderBy('id', 'desc')->get()->first(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),

            'undefinedSlcAttendances' => SlcAttendance::where('student_id', $studentId)->where('slc_registration_id', 0)->orderBy('id', 'DESC')->get(),
            'undefinedSlcCocs' => SlcCoc::where('student_id', $student->id)->where(function($q){
                                    $q->where('slc_registration_id', 0)->orWhereNull('slc_registration_id');
                                })->orderBy('id', 'DESC')->get(),
            'studentAttendanceIds' => SlcAttendance::where('student_id', $studentId)->pluck('id')->unique()->toArray(),
            'can_add' => (isset(auth()->user()->priv()['slc_history_add']) && auth()->user()->priv()['slc_history_add'] == 1 ? true : false),
            'can_edit' => (isset(auth()->user()->priv()['slc_history_edit']) && auth()->user()->priv()['slc_history_edit'] == 1 ? true : false),
            'can_delete' => (isset(auth()->user()->priv()['slc_history_delete']) && auth()->user()->priv()['slc_history_delete'] == 1 ? true : false),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
        ]);
    }

    public function accounts($student_id){
        $student = Student::find($student_id);
        $courseRelationId = (isset($student->crel->id) && $student->crel->id > 0 ? $student->crel->id : 0);
        $courseCreationID = (isset($student->crel->course_creation_id) && $student->crel->course_creation_id > 0 ? $student->crel->course_creation_id : 0);

        $currentCourse = StudentProposedCourse::with('venue')->where('student_id',$student->id)
                        ->where('course_creation_id', $courseCreationID)
                        ->where('student_course_relation_id', $courseRelationId)
                        ->get()
                        ->first();
        $venue_id = (isset($currentCourse->venue_id) && $currentCourse->venue_id > 0 ? $currentCourse->venue_id : 0);
        $CourseCreationVenue = CourseCreationVenue::where('course_creation_id', $courseCreationID)->where('venue_id', $venue_id)->get()->first();

        return view('pages.students.live.accounts', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Accounts', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'agreements' => SlcAgreement::with('installments')->where('student_id', $student_id)->where(function($q) use($courseRelationId){
                                $q->where('student_course_relation_id', $courseRelationId)->orWhere('student_course_relation_id', 0)->orWhereNull('student_course_relation_id');
                            })->orderBy('id', 'ASC')->get(),
            'instances' => CourseCreationInstance::where('course_creation_id', $courseCreationID)->orderBy('academic_year_id', 'ASC')->get(),
            'term_declarations' => TermDeclaration::orderBy('id', 'desc')->get(),
            'lastAssigns' => Assign::where('student_id', $student_id)->orderBy('id', 'desc')->get()->first(),
            'paymentMethods' => SlcPaymentMethod::orderBy('name', 'ASC')->get(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'registrations' => SlcRegistration::where('student_course_relation_id', $courseRelationId)->where('student_id', $student_id)->get(),
            "slcCode" =>(isset($CourseCreationVenue->slc_code) && !empty($CourseCreationVenue->slc_code) ? $CourseCreationVenue->slc_code : ''),
            
            'can_add' => (isset(auth()->user()->priv()['student_account_add']) && auth()->user()->priv()['student_account_add'] == 1 ? true : false),
            'can_edit' => (isset(auth()->user()->priv()['student_account_edit']) && auth()->user()->priv()['student_account_edit'] == 1 ? true : false),
            'can_delete' => (isset(auth()->user()->priv()['student_account_delete']) && auth()->user()->priv()['student_account_delete'] == 1 ? true : false),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
        ]);
    }
    public function accountsInvoicePrint($student_id, $payment_id) {
        set_time_limit(300);
		$opt = Option::where('category', 'SITE_SETTINGS')->where('name','site_logo')->pluck('value', 'name')->toArray(); 
		$logoUrl = (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']) ? url('storage/'.$opt['site_logo']) : asset('build/assets/images/logo.svg'));
		
        
        // $pdf = PDF::loadView('pages.students.live.payment.pdf.moneyreceipt',compact('logoUrl'));
        // return $pdf->download('student_payment.pdf');
        $student = Student::find($student_id);
        //Not using currently this part
        $courseRelationId = (isset($student->crel->id) && $student->crel->id > 0 ? $student->crel->id : 0);
        $courseCreationID = (isset($student->crel->course_creation_id) && $student->crel->course_creation_id > 0 ? $student->crel->course_creation_id : 0);

        $currentCourse = StudentProposedCourse::with('venue')->where('student_id',$student->id)
                        ->where('course_creation_id', $courseCreationID)
                        ->where('student_course_relation_id', $courseRelationId)
                        ->get()
                        ->first();
        $venue_id = (isset($currentCourse->venue_id) && $currentCourse->venue_id > 0 ? $currentCourse->venue_id : 0);
        $CourseCreationVenue = CourseCreationVenue::where('course_creation_id', $courseCreationID)->where('venue_id', $venue_id)->get()->first();

        $agreements = SlcAgreement::with('installments')->where('student_id', $student_id)->where(function($q) use($courseRelationId){
                                $q->where('student_course_relation_id', $courseRelationId)->orWhere('student_course_relation_id', 0)->orWhereNull('student_course_relation_id');
                            })->orderBy('id', 'ASC')->get();
        //End of not using part

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
        $payment = SlcMoneyReceipt::find($payment_id);
        $statuses = Status::where('type', 'Student')->orderBy('id', 'ASC')->get();

        // return view('pages.students.live.payment.pdf.moneyreceipt', [
        //     'logoUrl' => $logoUrl,
        //     'student' => $student,
        //     'address' => $address,
        //     'payment' => SlcMoneyReceipt::find($payment_id),
        //     'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
        // ]);

        $pdf = PDF::loadView('pages.students.live.payment.pdf.moneyreceipt',compact('logoUrl','student','address','payment','statuses'));
        return $pdf->download('student_payment.pdf');
    }
    private function createInvoicePrintToStorage($student_id, $payment_id) {
        set_time_limit(300);
		$opt = Option::where('category', 'SITE_SETTINGS')->where('name','site_logo')->pluck('value', 'name')->toArray(); 
		$logoUrl = (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']) ? url('storage/'.$opt['site_logo']) : asset('build/assets/images/logo.svg'));

        $student = Student::find($student_id);
        //Not using currently this part
        $courseRelationId = (isset($student->crel->id) && $student->crel->id > 0 ? $student->crel->id : 0);
        $courseCreationID = (isset($student->crel->course_creation_id) && $student->crel->course_creation_id > 0 ? $student->crel->course_creation_id : 0);

        $currentCourse = StudentProposedCourse::with('venue')->where('student_id',$student->id)
                        ->where('course_creation_id', $courseCreationID)
                        ->where('student_course_relation_id', $courseRelationId)
                        ->get()
                        ->first();
        $venue_id = (isset($currentCourse->venue_id) && $currentCourse->venue_id > 0 ? $currentCourse->venue_id : 0);
        $CourseCreationVenue = CourseCreationVenue::where('course_creation_id', $courseCreationID)->where('venue_id', $venue_id)->get()->first();

        $agreements = SlcAgreement::with('installments')->where('student_id', $student_id)->where(function($q) use($courseRelationId){
                                $q->where('student_course_relation_id', $courseRelationId)->orWhere('student_course_relation_id', 0)->orWhereNull('student_course_relation_id');
                            })->orderBy('id', 'ASC')->get();
        //End of not using part

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
        $payment = SlcMoneyReceipt::find($payment_id);
        $statuses = Status::where('type', 'Student')->orderBy('id', 'ASC')->get();

        // return view('pages.students.live.payment.pdf.moneyreceipt', [
        //     'logoUrl' => $logoUrl,
        //     'student' => $student,
        //     'address' => $address,
        //     'payment' => SlcMoneyReceipt::find($payment_id),
        //     'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
        // ]);

        $pdf = PDF::loadView('pages.students.live.payment.pdf.moneyreceipt',compact('logoUrl','student','address','payment','statuses'));
       
        // Define the storage path (e.g., storage/app/public/student_payment.pdf)
        $fileName = 'student_payment_' . $student->id . '_' . $payment->id . '.pdf';
        $path = 'public/students/'.$student_id.'/'.$fileName;
        // Save the PDF to storage
        Storage::disk('s3')->put($path, $pdf->output());

        // Optionally, return the storage path or a response
        return ["path" => $path, "fileName" => $fileName];
    }
    public function sendMail($student_id, $payment_id) {

        $student = Student::find($student_id);
        $payment = SlcMoneyReceipt::find($payment_id);
        $siteName = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_name')->value('value');
        $siteEmail = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_email')->value('value');
        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();

                // $configuration = [
                //     'smtp_host' => 'sandbox.smtp.mailtrap.io',
                //     'smtp_port' => '2525',
                //     'smtp_username' => 'e8ae09cfefd325',
                //     'smtp_password' => 'ce7fa44b28281d',
                //     'smtp_encryption' => 'tls',
                    
                //     'from_email'    => 'no-reply@lcc.ac.uk',
                //     'from_name'    =>  'London Churchill College',
                // ];
        $configuration = [
            'smtp_host'    => $commonSmtp->smtp_host,
            'smtp_port'    => $commonSmtp->smtp_port,
            'smtp_username'  => $commonSmtp->smtp_user,
            'smtp_password'  => $commonSmtp->smtp_pass,
            'smtp_encryption'  => $commonSmtp->smtp_encryption,
            
            'from_email'    => $commonSmtp->smtp_user,
            'from_name'    =>  $siteName,
        ];
        
        if(empty($payment->mailed_pdf_file) || !Storage::disk('s3')->exists($payment->mailed_pdf_file)) {
            // If the PDF file does not exist, create it
            $InvoiceStorage = $this->createInvoicePrintToStorage($student_id, $payment_id);
            $payment->mailed_pdf_file = $InvoiceStorage['path'];
            $payment->save();
        }else {
            // If the PDF file already exists, use the existing path
            $InvoiceStorage = [
                'path' => $payment->mailed_pdf_file,
                'fileName' => basename($payment->mailed_pdf_file)
            ];
        }

        $message = '';
        $message .= 'Dear '.$student->full_name.',<br/>';
        $message .= 'We are pleased to confirm that we have received your payment.<br/>';
        $message .= 'Please find the attached document for a detailed breakdown of the transaction.<br/>';
        $message .= 'Thank you for your prompt payment. If you have any questions or require further information, please do not hesitate to contact us.<br/><br/>';
        $message .= 'Best regards,<br/>'.$siteName;

        $attachmentFiles = [];
        $attachmentFiles[] = [
            "pathinfo" => $InvoiceStorage['path'],
            "nameinfo" => $InvoiceStorage['fileName'],
            "mimeinfo" => 'application/pdf',
            "disk" => 's3'
        ];
        $studentEmails= [
            $student->contact->institutional_email,
            $student->contact->personal_email,
        ];
        UserMailerJob::dispatch($configuration,$studentEmails, new CommunicationSendMail('London Churchill College – Acknowledgement of Payment', $message, $attachmentFiles));



        return response()->json(['message' => 'Email sent successfully.']);

        
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
        $attendanceIndicator = [];
            $attendanceFeedStatus = AttendanceFeedStatus::all();
            $returnSet = $this->PlanWithAttendanceSet($student);
            
           
            $returnSet = array_merge($returnSet);
            
        // endforeach;
        $termData = $returnSet["termData"];
        $moduleNameList = $returnSet["moduleNameList"];
        $ClassType   = $returnSet["ClassType"];
        $data = $returnSet["data"];
        $planDetails = $returnSet["planDetails"];
        $avarageDetails = $returnSet["avarageDetails"];
        $totalFeedListSet = $returnSet["totalFeedListSet"];
        $totalFullSetFeedList = $returnSet["totalFullSetFeedList"];
        $avarageTermDetails = $returnSet["avarageTermDetails"];
        $totalClassFullSet = $returnSet["totalClassFullSet"];
        $termAttendanceFound = $returnSet["termAttendanceFound"];
        $lastAttendanceDate = $returnSet["lastAttendanceDate"];
        $attendanceIndicator = $returnSet["attendanceIndicator"];
        
        
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
            "ClassType" => $ClassType,
            "termAttendanceFound" =>$termAttendanceFound,
            "lastAttendanceDate"=>$lastAttendanceDate,
            "attendanceIndicator" => $attendanceIndicator,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'studentPlanIds' => Attendance::where('student_id', $student->id)->pluck('plan_id')->unique()->toArray(),
            'planSet' => Assign::where('student_id',$student->id)->pluck('plan_id')->unique()->toArray(),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
        ]);
    }

    public function VisitsDetails(Student $student) {
        // $student = Student::find($studentId);
        $studentVisits = $student->visits;
        
        $plans = Assign::where('student_id', $student->id)->pluck('plan_id')->toArray();
        $termDeclarationIds = Plan::whereIn('id', $plans)->pluck('term_declaration_id')->toArray();


         return view('pages.students.live.visits.index', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student Communications', 'href' => 'javascript:void(0);'],
            ],
            'studentVisits' => $studentVisits,
            'student' => Student::find($student->id),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            "term" =>"",
            "grades" => "",
            "planDetails" => $planDetails ?? null,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
            'termDeclarations' => TermDeclaration::whereIn('id', $termDeclarationIds)->orderBy('id', 'DESC')->get(),
            'moduleCreations' => [],
            'termNames' => TermDeclaration::pluck('name', 'id')->toArray(),
        ]);
    }

    protected function PlanWithAttendanceSet(Student $student) {

            $courseCreationIds = StudentCourseRelation::where('student_id', $student->id)->get()->pluck('course_creation_id')->toArray();
            $theInactiveCourse = StudentCourseRelation::where('student_id', $student->id)->where('active',0)->get()->first();
            $theActiveCourse = StudentCourseRelation::where('student_id', $student->id)->where('active',1)->get()->first();
            sort($courseCreationIds);
            
            $courseCreationActiveData = $student->crel->creation;
            $courseRelationSessionedCourseId = $courseCreationActiveData->id;
            $courseId = $courseCreationActiveData->course_id;
            $maxCourseCreationId = max($courseCreationIds);
            $minCourseCreationId = min($courseCreationIds);
            $planSet= Assign::where('student_id',$student->id)->pluck('plan_id')->unique()->toArray();
            ///$planSet= Attendance::where('student_id', $student->id)->pluck('plan_id')->unique()->toArray();

            $termData = [];
            $lastAttendanceDate = [];
            $data = [];
            $planDetails = [];
            $avarageDetails = [];
            $totalFeedListSet = [];
            $totalFullSetFeedList = [];
            $avarageTermDetails = [];
            $totalClassFullSet = [];
            $moduleNameList = [];
            $ClassType = [];
            $arryBox = [];
                $QueryPart = DB::table('plans_date_lists as pdl')
                            ->select( 'pdl.*','td.id as term_id',
                                        'td.name as term_name',
                                        'instance_terms.start_date',
                                        'instance_terms.end_date', 
                                        'plan.module_creation_id as module_creation_id' , 
                                        'mc.module_name','mc.code as module_code', 
                                        'plan.id as plan_id' , 
                                        'gp.name as group_name', 
                                        'gp.id as group_id','assign.attendance as indicator', 'instance_terms.id as instance_term_id')
                            ->leftJoin('plans as plan', 'plan.id', 'pdl.plan_id')
                            ->leftJoin('instance_terms', 'instance_terms.id', 'plan.instance_term_id')
                            ->leftJoin('assigns as assign', 'plan.id', 'assign.plan_id')
                            ->leftJoin('term_declarations as td', 'td.id', 'plan.term_declaration_id')
                            ->leftJoin('module_creations as mc', 'mc.id', 'plan.module_creation_id')
                            ->leftJoin('groups as gp', 'gp.id', 'plan.group_id')
                            ->where('assign.student_id', $student->id)
                            ->whereIn('plan.id',$planSet)
                            ->where('plan.course_creation_id','>=',$courseRelationSessionedCourseId);

                            if($courseRelationSessionedCourseId < $maxCourseCreationId && $courseRelationSessionedCourseId >= $minCourseCreationId) {

                                $arrayCurrentKey = array_search($courseRelationSessionedCourseId, $courseCreationIds);
                                $nextCourseCreationId = $courseCreationIds[$arrayCurrentKey+1];
                                
                                //Debugbar::addMessage($nextCourseCreationId, 'nextCourseCreationId');
                                
                                if($theInactiveCourse->course_creation_id > $theActiveCourse->course_creation_id) {
                                    
                                }else
                                    $QueryPart->where('plan.course_creation_id','<',$nextCourseCreationId);

                            }

                            if($courseId > 0) {
                                $QueryPart->where('plan.course_id', $courseId);
                            }
                            $QueryInner = $QueryPart->orderBy("pdl.date",'desc')->get();
                            
                            $attendanceFeedStatus = AttendanceFeedStatus::all();
                $termAttendanceFound = [];
                $attendanceIndicator = [];
                $i=0;
                 
                if($QueryInner->isNotEmpty())
                foreach($QueryInner as $list):
                    // if(in_array($list->instance_term_id, $instanceTermIds)) {
                    
                    $attendance = Attendance::with(["feed","createdBy","updatedBy"])->where("student_id", $student->id)->where("plans_date_list_id",$list->id)->get()->first();
                    $attendanceIndicator[$list->term_id]  = ($list->indicator===0) ? 0:1;
                    $termAttendanceFound[$list->term_id] = false;
                    if(isset($attendance)) {

                        $moduleNameList[$list->plan_id] = (isset($list->module_code)) ? $list->module_name."-".$list->module_code : $list->module_name;
                        // attendance indicator check
                        
                        
                        $termAttendanceFound[$list->term_id] = true;
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
                        
                        if(!isset($lastAttendanceDate[$list->term_id])) {

                            
                            $lastAttendanceDate[$list->term_id] = "N/A";
                            
                            
                        }
                        
                        if($attendance->feed->attendance_count==1) {

                            
                            if(strtotime($list->date) >strtotime($lastAttendanceDate[$list->term_id])) {
                                
                                    //Debugbar::addMessage($list->term_id);
                                    //Debugbar::warning("attendance Date: ".$list->date);
                                    //Debugbar::info("term current previous attendance: ".$lastAttendanceDate[$list->term_id]);
                                    $lastAttendanceDate[$list->term_id] = $list->date;
                                    //Debugbar::info("term current last attendance: ".$lastAttendanceDate[$list->term_id]);
                            }

                        }
                        $planSet = Plan::with(["tutor","personalTutor",'creations','room'])->where('id',$list->plan_id)->get()->first();
                        $planDetails[$list->term_id][$list->plan_id] = $planSet;
                        $ClassType[$list->plan_id] = (isset($planSet->class_type)) ? $planSet->class_type : "N/A";
                        
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

                        // Remove zero content
                        $totalBox[$list->term_id] = array_filter($totalBox[$list->term_id], function($value) {
                            return $value != 0;
                        });
                        
                        //Feed List Set Start
                        $json = json_encode ($totalBox[$list->term_id], JSON_FORCE_OBJECT);                        
                        $replace = array('{', '}', "'", '"');
                        $intermediate = str_replace ($replace, " ", $json);
                        //End Feed List Set

                        // Add a space after each colon
                        $totalFullSetFeedList[$list->term_id] = preg_replace('/:/', ': ', $intermediate);
                        
                        $totalClassFullSet[$list->term_id] = $totalBoxPresentFound[$list->term_id] + $totalBoxAbsentFound[$list->term_id];

                        $avarageTotalPercentage[$list->term_id] = (($totalBoxPresentFound[$list->term_id]/$totalClassFullSet[$list->term_id])*100);
                        
                        $avarage= number_format($avarageTotalPercentage[$list->term_id], $precision, '.', '');
                        $avarageTermDetails[$list->term_id] = $avarage;
                    } else {

                        $moduleNameList[$list->plan_id] = (isset($list->module_code)) ? $list->module_name."-".$list->module_code : $list->module_name;

                        

                        if(!isset($totalPresentFound[$list->term_id][$list->plan_id])) {
                            $totalPresentFound[$list->term_id][$list->plan_id] = 0;
                        }
                        if(!isset($totalAbsentFound[$list->term_id][$list->plan_id])) {
                            $totalAbsentFound[$list->term_id][$list->plan_id]=0;
                        }
                        if(!isset($totalPresentFound[$list->term_id][$list->plan_id])) {
                            $totalPresentFound[$list->term_id][$list->plan_id] = 0;
                        }
                        if(!isset($totalAbsentFound[$list->term_id][$list->plan_id])) {
                            $totalAbsentFound[$list->term_id][$list->plan_id] = 0;
                        }
                        
                        if(!isset($totalAbsentFound[$list->term_id][$list->plan_id])) {
                            $avaragePercentage[$list->term_id][$list->plan_id] = 0;
                        }

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
                        $planSet = Plan::with(["tutor","personalTutor",'creations','room'])->where('id',$list->plan_id)->get()->first();
                        $planDetails[$list->term_id][$list->plan_id] = $planSet;
                        $ClassType[$list->plan_id] = (isset($planSet->class_type)) ? $planSet->class_type : "N/A";
                        
                        if(!isset($totalFeedListSet[$list->term_id][$list->plan_id])) {
                            
                            $totalFeedListSet[$list->term_id][$list->plan_id] = "";
                        }

                        if(!isset($avarageDetails[$list->term_id][$list->plan_id])) {
                            $avarageDetails[$list->term_id][$list->plan_id] = 0;
                        }

                        //total code list and total class list
                        foreach ($attendanceFeedStatus as $feedStatus):
                            if(!isset($totalBox[$list->term_id][$feedStatus->code])) {
                                $totalBox[$list->term_id][$feedStatus->code] = 0;
                            }
                        endforeach;
                        
                        if(!isset($totalBoxPresentFound[$list->term_id])) {
                            $totalBoxPresentFound[$list->term_id] = 0;
                        }
                        if(!isset($totalBoxAbsentFound[$list->term_id])) {
                            $totalBoxAbsentFound[$list->term_id]=0;
                        }
                        
                        $replace = array('{', '}', "'", '"');
                        
                        if(!isset($totalFullSetFeedList[$list->term_id])) {
                            $totalFullSetFeedList[$list->term_id] = "";
                        }
                        if(!isset($totalClassFullSet[$list->term_id])) {
                            $totalClassFullSet[$list->term_id] = 0;
                        }
                        if(!isset($avarageTotalPercentage[$list->term_id])) {
                            $avarageTotalPercentage[$list->term_id] = 0;
                        }
                        
                        if(!isset($avarageTermDetails[$list->term_id])) {
                            $avarageTermDetails[$list->term_id] = 0;
                        }
                    }
                // }
                endforeach;

                $attendance4prev = Attendance::with(["feed","createdBy","updatedBy"])->where("student_id", $student->id)->WhereNotNull("prev_plan_id")->get();   
                
                foreach($attendance4prev as $attendance):
                        $list = PlansDateList::with('plan')->where('id',$attendance->plans_date_list_id )->get()->first();
                        $plan = Plan::find($attendance->plan_id);
                        $termAttendanceFound[$plan->term_declaration_id] = true;
                        // attendance indicator check
                        //$attendanceIndicator[$plan->term_declaration_id]  = ($attendance->assign->indicator===0) ? 0:1;

                        $moduleNameList[$plan->id] = (isset($plan->creations->module)) ? $plan->creations->module->name."-".$plan->creations->module->code : $plan->creations->module->name;
                        
                        $attendanceInformation =AttendanceInformation::with(["tutor","planDate"])->where("plans_date_list_id",$attendance->plans_date_list_id)->get()->first();
                        if(isset($attendanceInformation->tutor))
                            $attendanceInformation->tutor->load(["employee"]);
                        if(!isset($arryBox[$plan->term_declaration_id][$plan->id][$attendance->feed->code])) {
                            $arryBox[$plan->term_declaration_id][$plan->id][$attendance->feed->code] = 0;
                        }
                        if(!isset($totalPresentFound[$plan->term_declaration_id][$plan->id])) {
                            $totalPresentFound[$plan->term_declaration_id][$plan->id] = 0;
                        }
                        if(!isset($totalAbsentFound[$plan->term_declaration_id][$plan->id])) {
                            $totalAbsentFound[$plan->term_declaration_id][$plan->id]=0;
                        }

                        $arryBox[$plan->term_declaration_id][$plan->id][$attendance->feed->code] += 1;
                        $totalPresentFound[$plan->term_declaration_id][$plan->id] += $attendance->feed->attendance_count;
                        $totalAbsentFound[$plan->term_declaration_id][$plan->id] += ($attendance->feed->attendance_count==0)? 1 : 0;

                        $json = json_encode ($arryBox[$plan->term_declaration_id][$plan->id], JSON_FORCE_OBJECT);
                        $replace = array('{', '}', "'", '"');
                        $totalFeedList = str_replace ($replace, "", $json);
                        $total = $totalPresentFound[$plan->term_declaration_id][$plan->id] + $totalAbsentFound[$plan->term_declaration_id][$plan->id];

                        $avaragePercentage[$plan->term_declaration_id][$plan->id] = (($totalPresentFound[$plan->term_declaration_id][$plan->id]/$total)*100);
                        $precision = 2;
                        $avarage = number_format($avaragePercentage[$plan->term_declaration_id][$plan->id], $precision, '.', '');
                    
                        $data[$plan->term_declaration_id][$plan->id][$list->date] = [
                                "id" => $list->id,
                                "date" => date("d-m-Y", strtotime($list->date)),
                                "attendance_information" => isset($attendanceInformation) ? $attendanceInformation: null,
                                "attendance"=> ($attendance) ?? null,
                                "term_id"=> $plan->term_declaration_id,
                                "module_creation_id"=>$list->module_creation_id,
                                "plan_id" => $plan->id,
                                "prev_plan_id" => Plan::find($attendance->prev_plan_id),
                        ];
                        
                        $termData[$plan->term_declaration_id] = [
                            "name" => $plan->attenTerm->name,
                            "start_date" => $plan->creations->term->start_date,
                            "end_date" => $plan->creations->term->end_date,
                        ];
                        
                        if(!isset($lastAttendanceDate[$plan->term_declaration_id])) {

                            
                            $lastAttendanceDate[$plan->term_declaration_id] = "N/A";
                            
                            
                        }
                        
                        if($attendance->feed->attendance_count==1) {

                            
                            if(strtotime($list->date) >strtotime($lastAttendanceDate[$plan->term_declaration_id])) {
                                
                                    //Debugbar::addMessage($plan->term_declaration_id);
                                    //Debugbar::warning("attendance Date: ".$list->date);
                                    //Debugbar::info("term current previous attendance: ".$lastAttendanceDate[$plan->term_declaration_id]);
                                    $lastAttendanceDate[$plan->term_declaration_id] = $list->date;
                                    //Debugbar::info("term current last attendance: ".$lastAttendanceDate[$plan->term_declaration_id]);
                            }

                        }
                        
                        $planSet = Plan::with(["tutor","personalTutor",'creations','group','room'])->where('id',$plan->id)->get()->first();
                        $planDetails[$list->term_id][$list->plan_id] = $planSet;
                        $ClassType[$list->plan_id] = (isset($planSet->class_type)) ? $planSet->class_type : "N/A";
                        
                        $avarageDetails[$plan->term_declaration_id][$plan->id] = $avarage;
                        $totalFeedListSet[$plan->term_declaration_id][$plan->id] = $totalFeedList;

                        //total code list and total class list
                        if(!isset($totalBox[$plan->term_declaration_id][$attendance->feed->code])) {
                            $totalBox[$plan->term_declaration_id][$attendance->feed->code] = 0;
                        }
                        
                        if(!isset($totalBoxPresentFound[$plan->term_declaration_id])) {
                            $totalBoxPresentFound[$plan->term_declaration_id] = 0;
                        }
                        if(!isset($totalBoxAbsentFound[$plan->term_declaration_id])) {
                            $totalBoxAbsentFound[$plan->term_declaration_id]=0;
                        }
                        $totalBox[$plan->term_declaration_id][$attendance->feed->code] += 1;
                        $totalBoxPresentFound[$plan->term_declaration_id] += $attendance->feed->attendance_count;
                        $totalBoxAbsentFound[$plan->term_declaration_id] += ($attendance->feed->attendance_count==0)? 1 : 0;
                        //Feed List Set
                        $json = json_encode ($totalBox[$plan->term_declaration_id], JSON_FORCE_OBJECT);
                        $replace = array('{', '}', "'", '"');
                        $totalFullSetFeedList[$plan->term_declaration_id] = str_replace ($replace, " ", $json);
                        //End Feed List Set
                        $totalClassFullSet[$plan->term_declaration_id] = $totalBoxPresentFound[$plan->term_declaration_id] + $totalBoxAbsentFound[$plan->term_declaration_id];

                        $avarageTotalPercentage[$plan->term_declaration_id] = (($totalBoxPresentFound[$plan->term_declaration_id]/$totalClassFullSet[$plan->term_declaration_id])*100);
                        
                        $avarage= number_format($avarageTotalPercentage[$plan->term_declaration_id], $precision, '.', '');
                        $avarageTermDetails[$plan->term_declaration_id] = $avarage;
                    
                endforeach;

            return [ "lastAttendanceDate"=>$lastAttendanceDate,
                     "termData" => $termData,
                     "data" => $data ,
                     "planDetails" => $planDetails,
                     "avarageDetails" => $avarageDetails,
                     "totalFeedListSet" => $totalFeedListSet, 
                     "termAttendanceFound" =>$termAttendanceFound,
                     "totalFullSetFeedList" => $totalFullSetFeedList,
                     "avarageTermDetails" => $avarageTermDetails,
                     "totalClassFullSet" => $totalClassFullSet ,
                     "ClassType" =>$ClassType,
                     "attendanceIndicator" =>$attendanceIndicator,
                     "moduleNameList" =>$moduleNameList];



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
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
        ]);
    }

    public function AttendanceEditDetail(Student $student) {

            $attendanceFeedStatus = AttendanceFeedStatus::all();
            $termData = [];
            $ClassType = [];
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
                    $moduleNameList[$list->plan_id] = (isset($list->module_code)) ? $list->module_name."-".$list->module_code : $list->module_name;
                    if($attendance) {
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
                    $planSet = Plan::with(["tutor","personalTutor",'creations'])->where('id',$list->plan_id)->get()->first();
                    $planDetails[$list->term_id][$list->plan_id] = $planSet;
                    $ClassType[$list->plan_id] = (isset($planSet->class_type)) ? $planSet->class_type : "N/A";
                        
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

                    $totalBox[$list->term_id] = array_filter($totalBox[$list->term_id], function($value) {
                        return $value != 0;
                    });
                    
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
            "moduleNameList" =>$moduleNameList,
            "ClassType" =>$ClassType,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
        ]);
    }

    public function printAllAttendanceDetails(Student $student) {
        $termData = [];
        $data = [];
        $planDetails = [];
        $avarageDetails = [];
        $totalFeedListSet = [];
        $totalFullSetFeedList = [];
        $avarageTermDetails = [];
        $totalClassFullSet = [];
        $returnSet = [];
        $attendanceIndicator = [];
            $attendanceFeedStatus = AttendanceFeedStatus::all();
            $returnSet = $this->PlanWithAttendanceSet($student);
            
           
            $returnSet = array_merge($returnSet);
            
        // endforeach;
        $termData = $returnSet["termData"];
        $moduleNameList = $returnSet["moduleNameList"];
        $ClassType   = $returnSet["ClassType"];
        $data = $returnSet["data"];
        $planDetails = $returnSet["planDetails"];
        $avarageDetails = $returnSet["avarageDetails"];
        $totalFeedListSet = $returnSet["totalFeedListSet"];
        $totalFullSetFeedList = $returnSet["totalFullSetFeedList"];
        $avarageTermDetails = $returnSet["avarageTermDetails"];
        $totalClassFullSet = $returnSet["totalClassFullSet"];
        $termAttendanceFound = $returnSet["termAttendanceFound"];
        $lastAttendanceDate = $returnSet["lastAttendanceDate"];
        $attendanceIndicator = $returnSet["attendanceIndicator"];
        
        
        $fileName = 'attendance_of_'.$student->registration_no.'_'.$student->first_name.'_'.$student->last_name.'.pdf';
        // $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
        //     ->setPaper('a4', 'portrait')
        //     ->setWarnings(false);
        // return $pdf->download($fileName);

        $dataSet = $data;
        $term = $termData;
        $planDetails = $planDetails;
        $avarageDetails = $avarageDetails;
        $totalFeedList = $totalFeedListSet;
        $totalFullSetFeedList = $totalFullSetFeedList;
        $avarageTotalPercentage = $avarageTermDetails;
        $totalClassFullSet  = $totalClassFullSet;
        $attendanceFeedStatus  = $attendanceFeedStatus;
        $moduleNameList = $moduleNameList;
        $ClassType = $ClassType;
        $termAttendanceFound = $termAttendanceFound;
        $lastAttendanceDate =  $lastAttendanceDate;
        $attendanceIndicator = $attendanceIndicator;

        $statuses = Status::where('type', 'Student')->orderBy('id', 'ASC')->get();
        $pdf = PDF::loadView('pages.students.live.attendance.print',compact('student','dataSet','term','planDetails','avarageDetails','totalFeedList','totalFullSetFeedList','avarageTotalPercentage','totalClassFullSet','attendanceIndicator','moduleNameList','ClassType','termAttendanceFound','lastAttendanceDate','attendanceIndicator','statuses'));
        return $pdf->download($fileName);

        // return view('pages.students.live.attendance.print', [
        //     'student' => $student,
        //     'dataSet' => $data,
        //     "term" =>$termData,
        //     "planDetails" => $planDetails,
        //     'avarageDetails' => $avarageDetails,
        //     "totalFeedList" => $totalFeedListSet,
        //     "totalFullSetFeedList"=>$totalFullSetFeedList,
        //     "avarageTotalPercentage"=>$avarageTermDetails,
        //     "totalClassFullSet" =>$totalClassFullSet,
        //     "attendanceFeedStatus" =>$attendanceFeedStatus,
        //     "moduleNameList" =>$moduleNameList,
        //     "ClassType" => $ClassType,
        //     "termAttendanceFound" =>$termAttendanceFound,
        //     "lastAttendanceDate"=>$lastAttendanceDate,
        //     "attendanceIndicator" => $attendanceIndicator,
        //     'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        // ]);
    }
    public function getAllTerms(Request $request) {
        $academicYearList = $request->academic_years;
        $course = $request->course;

        $termDec= TermDeclaration::whereIn('academic_year_id',$academicYearList)->orderBy('id','DESC')->get();

        if(!empty($termDec)):
            $i = 1;
            foreach($termDec as $gr):
                $res[$i]['id'] = $gr->id;
                $res[$i]['name'] = $gr->name;

                $i++;
            endforeach;
        endif;

        return response()->json(['res' => $res], 200);
    }

    public function getAllIntakes(Request $request) {

        $academicYearList = $request->academic_years;
        $term_declaration_ids = $request->term_declaration_ids;
        $courses = $request->course;

        $res = [];

        $termDeclarationIds = TermDeclaration::whereIn('academic_year_id', $academicYearList)->pluck('id')->unique()->toArray();
        
        if(!empty($termDeclarationIds)):
            
            $courseCreationInstanceIds = InstanceTerm::whereIn('term_declaration_id', $termDeclarationIds)->pluck('course_creation_instance_id')->unique()->toArray();
           
            if(!empty($courseCreationInstanceIds)):
                $courseCreationIds = CourseCreationInstance::whereIn('academic_year_id', $academicYearList)->whereIn('id', $courseCreationInstanceIds)->pluck('course_creation_id')->unique()->toArray();
                if(!empty($courseCreationIds)):
                    if(!empty($courses)) {
                       
                        $semesterIds = CourseCreation::whereIn('id', $courseCreationIds)->whereIn('course_id',$courses)->pluck('semester_id')->unique()->toArray();
                    } else
                        $semesterIds = CourseCreation::whereIn('id', $courseCreationIds)->pluck('semester_id')->unique()->toArray();

                    if(!empty($semesterIds)):
                        $semesters = Semester::whereIn('id', $semesterIds)->orderBy('id', 'DESC')->get();
                        if(!empty($semesters)):
                            $i = 1;
                            foreach($semesters as $sem):

                                $studentFound = StudentProposedCourse::where('semester_id',$sem->id)->whereIn('academic_year_id', $academicYearList)->get()->first();
                                
                                if(isset($studentFound->id)) {
                                    $res[$i]['id'] = $sem->id;
                                    $res[$i]['name'] = $sem->name;
                                    $i++;
                                }
                                
                            endforeach;
                        endif;
                    endif;
                endif;
            endif;
        endif;

        if(!empty($res)):
            return response()->json(['res' => $res], 200);
        else:
            return response()->json(["message"=> "No relation Found","errors"=>["academic_year_id"=> "No Relation Found"]], 422);
        endif;
    }

    public function getAllCourses(Request $request) {
            $academicYears = $request->academic_years;
            $term_declaration_ids = $request->term_declaration_ids;
            $data = [];


            $courseCreationInstanceIds = InstanceTerm::whereIn('term_declaration_id', $term_declaration_ids)->pluck('course_creation_instance_id')->unique()->toArray();
           
            if(!empty($courseCreationInstanceIds)):
                $courseCreationIds = CourseCreationInstance::whereIn('id', $courseCreationInstanceIds)->whereIn('academic_year_id', $academicYears)->pluck('course_creation_id')->unique()->toArray();
                if(!empty($courseCreationIds)):
                    $courseCreations = DB::table('course_creations as cc') 
                        ->select('cc.id', 'cc.course_id', 'cr.name')
                        ->leftJoin('courses as cr', 'cr.id', 'cc.course_id')
                        ->whereRaw('cc.id IN (SELECT MAX(id) FROM course_creations WHERE id IN ('.implode(',', $courseCreationIds).') GROUP BY (course_id))')
                        ->get();
                    if(!empty($courseCreations)):
                        $i = 1;
                        foreach($courseCreations as $ccrs):
                            $data[$i]['id'] = $ccrs->course_id;
                            $data[$i]['name'] = $ccrs->name;
                            $i++;
                        endforeach;
                    endif;
                endif;
            endif;
    
            if(!empty($data)):
                return response()->json(['res' => $data], 200);
            else:
                return response()->json(['res' => ''], 304);
            endif;

    }

    public function getAllStatuses(Request $request) {

        $term_declaration_ids = $request->term_declaration_ids;

        $courses = $request->courses;
        $groups = $request->groups;
        // $CourseCreationsList = CourseCreation::with("course")->whereIn('course_id',$courses)->get();
         $res = [];
        // foreach ($CourseCreationsList as $coursesData): 
        //     $course[$courses->course->id] = $coursesData->id;
        // endforeach;
        if(isset($groups) && count($groups) >0 && isset($courses) && count($courses)>0) {
            $groupsIDList = Group::select('id')->whereIn('term_declaration_id', $term_declaration_ids)->whereIn('course_id', $courses)->whereIn('name',$groups)->groupBy('id')->get()->pluck('id')->toArray();
            $planList = Plan::with("assign")->whereIn('term_declaration_id', $term_declaration_ids)->whereIn('course_id',$courses)->whereIn('group_id',$groupsIDList)->orderBy('id', 'ASC')->get();
        }elseif(isset($courses)&& count($courses)>0) {
            
            $courseCreationInstanceIds = InstanceTerm::whereIn('term_declaration_id', $term_declaration_ids)->pluck('course_creation_instance_id')->unique()->toArray();

            $courseCreationIds = CourseCreationInstance::whereIn('id', $courseCreationInstanceIds)->whereHas('creation', function($q) use($courses){
                                    $q->whereIn('course_id', $courses);
                                })->pluck('course_creation_id')->unique()->toArray();

            $planList = Plan::with("assign")->whereIn('term_declaration_id', $term_declaration_ids)->whereIn('course_creation_id',$courseCreationIds)->whereIn('course_id',$courses)->orderBy('id', 'ASC')->get();

        } else {
            $planList = Plan::with("assign")->whereIn('term_declaration_id', $term_declaration_ids)->orderBy('id', 'ASC')->get();
        }
            if($planList->isNotEmpty()):
                
                $studentsIds = [];
                foreach($planList as $plan):
                    if(isset($plan->assign)):
                        foreach($plan->assign as $assingData):

                            if(!in_array($assingData->student_id, $studentsIds)):
                                $studentsIds[] = $assingData->student_id;
                            endif;
                        endforeach;
                    endif;
                endforeach;
                
                $i = 1;
                $ListStudentStatus = Student::whereIn('id',$studentsIds)->get()->pluck('status_id')->unique()->toArray();
                $ListStudent = Status::whereIn('id',$ListStudentStatus)->get();
                foreach ($ListStudent as $status):
                    //dd(array_search($student->status->name, array_column($res, 'name')));
                    //if(array_search($student->status->name, array_column($res, 'name')) !== false) {
                        $res[$i]['id'] = $status->id;
                        $res[$i]['name'] = $status->name;
                        $i++;
                    //}
                endforeach;
            endif;
            
        
        return response()->json(['res' => $res], 200);
    }

    public function callTheStudentListForGroup(Request $request) {
        

        $academic_years = $request->academic_years;
        $term_declaration_ids = $request->term_declaration_ids;
        $courses = $request->courses;
        $groups = $request->groups;
        $intake_semesters = $request->intake_semesters;
        $group_student_statuses = $request->group_student_statuses;
        $student_types = $request->student_types;
        $evening_weekends = $request->evening_weekends;
        
        $studentIds = [];


        $QueryInner = StudentCourseRelation::with('creation');
        $QueryInner->where('active','=',1);
        if(!empty($evening_weekends) && ($evening_weekends==0 || $evening_weekends==1))
            $QueryInner->where('full_time',$evening_weekends);
        if(!empty($academic_years) && count($academic_years)>0)
            $QueryInner->where('academic_year_id',$academic_years);
        

            $studentIds =  $QueryInner->whereHas('creation', function($q) use($intake_semesters,$courses){
                    if(!empty($intake_semesters))
                        $q->whereIn('semester_id', $intake_semesters);
                    if(!empty($courses))
                        $q->whereIn('course_id', $courses);
            })->pluck('student_id')->unique()->toArray();

            $studentsListByEveningSemesterAndCourse = $studentIds;

        if(!empty($term_declaration_ids) && count($term_declaration_ids)>0) {

            if(!empty($groups)) {
                $groups = Group::whereIn('name',$groups)->pluck('id')->unique()->toArray();
            }
            $innerQuery = Plan::whereIn('term_declaration_id', $term_declaration_ids);

                if(!empty($groups)) {
                    $innerQuery->whereIn('group_id', $groups);
                }

            $planList = $innerQuery->whereHas('course', function($q) use($courses,$academic_years){
                if(!empty($courses))
                $q->whereIn('course_id', $courses);
                if(!empty($academic_years))
                $q->whereIn('academic_year_id', $academic_years);
                

            })->pluck('id')->unique()->toArray();

            $studentsListByTerm = Assign::whereIn("plan_id",$planList)->pluck('student_id')->unique()->toArray();
            $studentIds = [];
            foreach($studentsListByEveningSemesterAndCourse as $intakeStudent):

            if(in_array($intakeStudent,$studentsListByTerm)) {
                $studentIds[] = $intakeStudent;
            }
            endforeach;
            
        }

        //this part will use both term and intake and open
        if(!empty($student_types) && count($student_types)>0) {

            $innerQuery = Student::with('courseRelationsList');
            if(!empty($studentIds)) {
                $innerQuery->whereIn('id',$studentIds);
            }
            $studentsListByStudentType = $innerQuery->whereHas('courseRelationsList', function($q) use($student_types){
                $q->whereIn('type', $student_types);
            })->pluck('id')->unique()->toArray();

            $studentIds = $studentsListByStudentType;

        }
        if(!empty($group_student_statuses) && count($group_student_statuses)>0) {

                $innerQuery = Student::whereIn('status_id',$group_student_statuses);
                if(!empty($studentIds)) {
                    $innerQuery->whereIn('id',$studentIds);
                }
                $studentsListByStatus = $innerQuery->pluck('id')->unique()->toArray();

                $studentIds = $studentsListByStatus;
                
        }
            //endof the part

        sort($studentIds);

        return $studentIds;
    }
    public function getAllStudentByGroupType(Request $request) {


        $academic_years = $request->academic_years;
        $term_declaration_ids = $request->term_declaration_ids;
        $courses = $request->courses;
        $groups = $request->groups;
        $intake_semesters = $request->intake_semesters;
        $group_student_statuses = $request->group_student_statuses;
        $student_types = $request->student_types;
        $evening_weekends = $request->evening_weekends;
        
        $res = [];

        if(!empty($student_types) && count($student_types)>0)
            $studentsListByStudentType = Student::with('activeCR')->whereHas('activeCR', function($q) use($student_types){
           
            $q->whereIn('type', $student_types);
        })->pluck('id')->unique()->toArray();
        else
        $studentsListByStudentType = [];

        

        if(!empty($group_student_statuses) && count($group_student_statuses)>0)
        $studentsListByStatus = Student::whereIn('status_id',$group_student_statuses)->pluck('id')->unique()->toArray();
        else 
        $studentsListByStatus = []; 

        $QueryInner = StudentCourseRelation::with('creation');
        $QueryInner->where('active', '=', 1);
        if(!empty($evening_weekends) && ($evening_weekends==0 || $evening_weekends==1))
            $QueryInner->where('full_time',$evening_weekends);
        if(!empty($academic_years) && count($academic_years)>0)
            $QueryInner->where('academic_year_id',$academic_years);
            
        $studentsListByEveningSemesterAndCourse = $QueryInner->whereHas('creation', function($q) use($intake_semesters,$courses){

                if(!empty($intake_semesters))
                    $q->whereIn('semester_id', $intake_semesters);

                if(!empty($courses))
                    $q->whereIn('course_id', $courses);


        })->pluck('student_id')->unique()->toArray();


        if(count($term_declaration_ids)>0) {

            $planList = Plan::whereIn('term_declaration_id', $term_declaration_ids)->whereHas('course', function($q) use($courses,$academic_years,$groups){
                if(!empty($courses))
                $q->whereIn('course_id', $courses);
                if(!empty($academic_years))
                $q->whereIn('academic_year_id', $academic_years);
                if(!empty($groups)) {
                    $groups = Group::whereIn('name',$groups)->pluck('id')->unique()->toArray();
                    $q->whereIn('group_id', $groups);
                }

            })->pluck('id')->unique()->toArray();

            $studentsListByTerm = Assign::whereIn("plan_id",$planList)->pluck('student_id')->unique()->toArray();

            $commonStudentList = array_intersect($studentsListByStudentType, $studentsListByStatus, $studentsListByEveningSemesterAndCourse,$studentsListByTerm);

            if(!empty($studentsListByStudentType) && !empty($studentsListByStatus) && !empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = array_intersect($studentsListByStudentType, $studentsListByStatus, $studentsListByEveningSemesterAndCourse);

            } else if(empty($studentsListByStudentType) && !empty($studentsListByStatus) && !empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = array_intersect($studentsListByStatus, $studentsListByEveningSemesterAndCourse);

            }else if(empty($studentsListByStudentType) && empty($studentsListByStatus) && !empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = $studentsListByEveningSemesterAndCourse;

            }else if(!empty($studentsListByStudentType) && !empty($studentsListByStatus) && empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = array_intersect($studentsListByStudentType, $studentsListByStatus);

            }else if(!empty($studentsListByStudentType) && empty($studentsListByStatus) && empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = $studentsListByStudentType;

            }else if(empty($studentsListByStudentType) && !empty($studentsListByStatus) && empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = $studentsListByStatus;
            }

             if(count($commonStudentList)>0) 
                $commonStudentList = array_intersect($commonStudentList, $studentsListByTerm);
             else
                $commonStudentList = $studentsListByTerm;

            

        } else {

            if(!empty($studentsListByStudentType) && !empty($studentsListByStatus) && !empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = array_intersect($studentsListByStudentType, $studentsListByStatus, $studentsListByEveningSemesterAndCourse);

            } else if(empty($studentsListByStudentType) && !empty($studentsListByStatus) && !empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = array_intersect($studentsListByStatus, $studentsListByEveningSemesterAndCourse);

            }else if(empty($studentsListByStudentType) && empty($studentsListByStatus) && !empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = $studentsListByEveningSemesterAndCourse;

            }else if(!empty($studentsListByStudentType) && !empty($studentsListByStatus) && empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = array_intersect($studentsListByStudentType, $studentsListByStatus);

            }else if(!empty($studentsListByStudentType) && empty($studentsListByStatus) && empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = $studentsListByStudentType;

            }else if(empty($studentsListByStudentType) && !empty($studentsListByStatus) && empty($studentsListByEveningSemesterAndCourse)) {

                $commonStudentList = $studentsListByStatus;

            }
            
        }
        
        

        $res["academic_year"] = $this->getAcademicYearListByStudentList($commonStudentList);
        
        
        $res["intake_semester"] = $this->getIntakeSemesterListByStudentList($commonStudentList);
        
        $res["attendance_semester"]= $this->getAttendanceSemesterListByStudentList($commonStudentList);
        $res["course"]= $this->getCoursesListByStudentList($commonStudentList);
        $res["group_student_status"]= $this->getStatusListByStudentList($commonStudentList);
        $res["evening_weekend"]= $this->getEveningWeekendListByStudentList($commonStudentList);
        $res["group"]= $this->getGroupListByStudentList($commonStudentList);
        $res["student_type"]= $this->getTypeListByStudentList($commonStudentList);
 
        return response()->json(['res' => $res,], 200);
        
    }
    protected function getAcademicYearListByStudentList($studentList) {
        $res = [];
        $i = 1;
        
        $academicYears = StudentProposedCourse::whereIn('student_id',$studentList)->pluck('academic_year_id')->unique()->toArray();
        
        $list = AcademicYear::whereIn('id',$academicYears)->orderBy('id', 'DESC')->get();
        foreach ($list as $data):
                        
            $res[$i]['id'] = $data->id;
            $res[$i]['name'] = $data->name;
            $i++;
    
        endforeach;
        
        return $res;
    }
    protected function getIntakeSemesterListByStudentList($studentList) {

        $res = [];
        $i = 1;
        $semesterSet = StudentProposedCourse::whereIn('student_id',$studentList)->pluck('semester_id')->unique()->toArray();
        $list = Semester::whereIn('id',$semesterSet)->orderBy('name', 'DESC')->get();
        foreach ($list as $data):
            $res[$i]['id'] = $data->id;
            $res[$i]['name'] = $data->name;
            $i++;
        endforeach;
        

        return $res;
    }

    protected function getCoursesListByStudentList($studentList) {

        $res = [];
        $i = 1;
        $course_creation_id = StudentCourseRelation::whereIn('student_id',$studentList)->pluck('course_creation_id')->unique()->toArray();
        $courseId = CourseCreation::whereIn('id',$course_creation_id)->pluck('course_id')->unique()->toArray();
        $list = Course::whereIn('id',$courseId)->orderBy('name', 'DESC')->get();

        foreach ($list as $data):
            $res[$i]['id'] = $data->id;
            $res[$i]['name'] = $data->name;
            $i++;
        endforeach;
        return $res;
    }

    protected function getStatusListByStudentList($studentList) {

        $status_id = Student::whereIn('id',$studentList)->pluck('status_id')->unique()->toArray();
        $list = Status::whereIn('id',$status_id)->orderBy('name', 'DESC')->get();
        $res = [];
        $i = 1;
        foreach ($list as $data):
            $res[$i]['id'] = $data->id;
            $res[$i]['name'] = $data->name;
            $i++;
        endforeach;
        return $res;
    }
    protected function getEveningWeekendListByStudentList($studentList) {

        $list = StudentProposedCourse::whereIn('student_id',$studentList)->pluck('full_time')->unique()->toArray();
     
        $res = [];
        $i = 1;
        foreach ($list as $data):
            
            $res[$i]['id'] = $data;
            $res[$i]['name'] = (isset($data) && $data==1)  ? "Yes" : "No";
            $i++;
        endforeach;
        return $res;
    }

    protected function getGroupListByStudentList($studentList) {

        $studentsListByTerm = Assign::whereIn("student_id",$studentList)->pluck('plan_id')->unique()->toArray();
        $groups = Plan::whereIn('id', $studentsListByTerm)->pluck('group_id')->unique()->toArray();
        $list = Group::whereIn('id', $groups)->orderBy('name', 'ASC')->groupBy('name')->get();
        $res = [];
        $i = 1;

        foreach ($list as $data):
            
            $res[$i]['id'] = $data->name;
            $res[$i]['name'] = $data->name;
            $i++;
        endforeach;

        return $res;

    }

    protected function getAttendanceSemesterListByStudentList($studentList) {
        
        $studentsListByTerm = Assign::whereIn("student_id",$studentList)->pluck('plan_id')->unique()->toArray();
        $attendanceTerms = Plan::whereIn('id', $studentsListByTerm)->pluck('term_declaration_id')->unique()->toArray();
        $list = TermDeclaration::whereIn('id', $attendanceTerms)->orderBy('name', 'DESC')->get();
        $res = [];
        $i = 1;
        foreach ($list as $data):
            $res[$i]['id'] = $data->id;
            $res[$i]['name'] = $data->name;
            $i++;
        endforeach;
        return $res;

    }

    protected function getTypeListByStudentList($studentList) {
        $res = [];
        $i = 1;
        $list = StudentCourseRelation::whereIn('student_id',$studentList)->pluck('type')->unique()->toArray();

        foreach ($list as $data):
            $res[$i]['id'] = $data;
            $res[$i]['name'] = $data;
            $i++;
        endforeach;
        return $res;
    }
    
    public function workplacement($student_id) {
        $student = Student::find($student_id);

        $courseStartDate = (isset($student->crel->course_start_date) && !empty($student->crel->course_start_date) ? date('Y-m-d', strtotime($student->crel->course_start_date)) : date('Y-m-d', strtotime($student->crel->creation->available->course_start_date)) );
        $courseId = $student->crel->creation->course_id;

        $workPlacementDetails = WorkplacementDetails::with('level_hours')->where('course_id', $courseId)
                                ->where(function($q) use($courseStartDate){
                                    $q->whereNull('end_date')->where('start_date', '<=', $courseStartDate);
                                })->orWhere(function($q) use($courseStartDate){
                                    $q->whereNotNull('end_date')->where('start_date', '<=', $courseStartDate)->where('end_date', '>=', $courseStartDate);
                                })->orderBy('id', 'DESC')->get()->first();

        $assign_modules = Assign::where('student_id', $student_id)
                                ->with(['plan.creations' => function($query) {
                                    $query->select('id', 'module_name');
                                }])
                                ->get()
                                ->pluck('plan.creations')
                                ->unique('id')
                                ->values();

        if($workPlacementDetails && $workPlacementDetails->id):
            $total_hours_calculations = LevelHours::with('learning_hours')->where('workplacement_details_id', $workPlacementDetails->id)->get();
            $confirmed_hours = [];
            if($total_hours_calculations->count() > 0):
                foreach($total_hours_calculations as $lavelHour):
                    if(isset($lavelHour->learning_hours) && $lavelHour->learning_hours->count() > 0):
                        foreach($lavelHour->learning_hours as $learningHour):
                            $confirmedHours = StudentWorkPlacement::where('student_id', $student_id)->where('workplacement_details_id', $workPlacementDetails->id)->where('level_hours_id', $lavelHour->id)
                                                        ->where('learning_hours_id', $learningHour->id)->where('status', 'Confirmed')->sum('hours');
                            $confirmed_hours[$learningHour->id]['lavel_hours'] = $lavelHour->name;
                            $confirmed_hours[$learningHour->id]['learning_hours'] = $learningHour->name;
                            $confirmed_hours[$learningHour->id]['confirmed_hours'] = ($confirmedHours > 0 ? $confirmedHours.' Hours' : '');

                        endforeach;
                    endif;
                endforeach;
            endif;
        endif;

        return view('pages.students.live.workplacement', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Work Placement', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'company' => Company::where('active', 1)->orderBy('name', 'ASC')->get(),
            'work_hours' => StudentWorkPlacement::where('student_id', $student_id)->where('status', 'Confirmed')->sum('hours'),
            'placement' => StudentWorkPlacement::all(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'workplacement_details' => $workPlacementDetails,
            'workplacement_settings' => WorkplacementSetting::all(),
            'assign_modules' => $assign_modules,
            'total_hours_calculations' => $total_hours_calculations ?? [],
            'confirmed_hours' => $confirmed_hours,
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
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


    public function studentCheckStatus(Request $request){
        $student_id = $request->student_id;
        $status_id = $request->theStatus;
        $term_id = $request->theTerm;

        $term = TermDeclaration::find($term_id);

        $status = Status::find($status_id);
        $status_indicator = (isset($status->active) && $status->active == 0 ? 0 : 1);

        $res = [];
        $res['indicator'] = $status_indicator;
        $res['notice'] = 0;
        $term_status = StudentAttendanceTermStatus::where('student_id', $student_id)->where('term_declaration_id', $term_id)->orderBy('id', 'DESC')->get()->first();
        $term_indicator = (isset($term_status->id) && $term_status->id > 0 && isset($term_status->status->active) ? ($term_status->status->active == 0 ? 0 : 1) : $status_indicator);

        if($status_indicator != $term_indicator):
            $res['notice'] = 1;
            $res['term_indicator'] = $term_indicator;
            $res['msg'] = $term->name.' attendance indicator was <span class="font-medium underline">'.($term_indicator == 1 ? 'Enabled' : 'Disabled').'</span>. But the selected status indicator is <span class="font-medium underline">'.($status_indicator == 1 ? 'Enabled' : 'Disabled').'</span>. Please, make sure you want to continue with that or Change the indicator as per your requirements.';
        endif;

        return response()->json(['res' => $res], 200);
    }


    public function studentUpdateStatus(StudentUpdateStatusRequest $request){
        $student_id = $request->student_id;
        $studentOld = Student::find($student_id);
        $lastTermStatus = StudentAttendanceTermStatus::where('student_id', $student_id)->orderBy('id', 'DESC')->get()->first();
        $lastTermId = (isset($lastTermStatus->term_declaration_id) && $lastTermStatus->term_declaration_id > 0 ? $lastTermStatus->term_declaration_id : 0);

        $status_id = $request->status_id;
        $statusDetails = Status::find($status_id);
        $term_declaration_id = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : null);
        $status_change_reason = (isset($request->status_change_reason) && !empty($request->status_change_reason) ? $request->status_change_reason : null);
        $status_change_date = (isset($request->status_change_date) && !empty($request->status_change_date) ? date('Y-m-d', strtotime($request->status_change_date)).' '.date('H:i:s') : date('Y-m-d H:i:s'));
        

        $plan_ids = Plan::where('term_declaration_id', $term_declaration_id)->pluck('id')->unique()->toArray();
        $statusActive = (isset($statusDetails->active) && $statusDetails->active == 0 ? 0 : 1);
        $attendance_indicator = (isset($request->attendance_indicator) && $request->attendance_indicator > 0 ? $request->attendance_indicator : 0);

        $endStatuses = [21, 26, 27, 31, 42];
        $qual_award_type = (in_array($status_id, $endStatuses) && $request->reason_for_engagement_ending_id == 1 && !empty($request->qual_award_type) ? $request->qual_award_type : null);
        $qual_award_result_id = (in_array($status_id, $endStatuses) && $request->reason_for_engagement_ending_id == 1 && !empty($request->qual_award_result_id) ? $request->qual_award_result_id : null);
        

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

            if(isset($statusDetails->process_list_id) && $statusDetails->process_list_id > 0):
                $processTask = TaskList::where('process_list_id', $statusDetails->process_list_id)->orderBy('id', 'ASC')->get();
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

            $data = [];
            $data['student_id'] = $student_id;
            $data['term_declaration_id'] = $term_declaration_id;
            $data['status_id'] = $status_id;
            $data['status_change_reason'] = $status_change_reason;
            $data['status_change_date'] = $status_change_date;
            
            $data['status_end_date'] = (in_array($status_id, $endStatuses) && !empty($request->status_end_date) ? date('Y-m-d', strtotime($request->status_end_date)) : null);
            $data['reason_for_engagement_ending_id'] = (in_array($status_id, $endStatuses) && !empty($request->reason_for_engagement_ending_id) ? $request->reason_for_engagement_ending_id : null);
            $data['qual_award_type'] = $qual_award_type;
            $data['qual_award_result_id'] = $qual_award_result_id;
            $data['created_by'] = auth()->user()->id;

            StudentAttendanceTermStatus::create($data);
            if((!empty($qual_award_type) || !empty($qual_award_result_id)) && $statusDetails->eligible_for_award == 1):
                StudentAward::updateOrCreate([ 'student_id' => $student_id, 'student_course_relation_id' => $student->crel->id ], [
                    'student_id' => $student_id,
                    'student_course_relation_id' => $student->crel->id,
                    'qual_award_result_id' => $qual_award_result_id,
                    'qual_award_type' => $qual_award_type,
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]);
            endif;

            if(!empty($plan_ids)):
                //$assigns = Assign::whereIn('plan_id', $plan_ids)->where('student_id', $student_id)->update(['attendance' => $statusActive]);
                $assigns = Assign::whereIn('plan_id', $plan_ids)->where('student_id', $student_id)->update(['attendance' => $attendance_indicator]);
            endif;

            return response()->json(['message' => 'Student status successfully changed.'], 200);
        else:
            if(isset($lastTermStatus->id) && $lastTermStatus->id > 0):
                $data = [];
                $data['status_change_reason'] = $status_change_reason;
                $data['status_change_date'] = $status_change_date;
                $data['updated_by'] = auth()->user()->id;
                $data['status_end_date'] = (in_array($status_id, $endStatuses) && !empty($request->status_end_date) ? date('Y-m-d', strtotime($request->status_end_date)) : null);
                $data['reason_for_engagement_ending_id'] = (in_array($status_id, $endStatuses) && !empty($request->reason_for_engagement_ending_id) ? $request->reason_for_engagement_ending_id : null);
                $data['qual_award_type'] = $qual_award_type;
                $data['qual_award_result_id'] = $qual_award_result_id;
                StudentAttendanceTermStatus::where('id', $lastTermStatus->id)->update($data);

                if((!empty($qual_award_type) || !empty($qual_award_result_id)) && $statusDetails->eligible_for_award == 1):
                    StudentAward::updateOrCreate([ 'student_id' => $student_id, 'student_course_relation_id' => $student->crel->id ], [
                        'student_id' => $student_id,
                        'student_course_relation_id' => $student->crel->id,
                        'qual_award_result_id' => $qual_award_result_id,
                        'qual_award_type' => $qual_award_type,
                        'created_by' => auth()->user()->id,
                        'updated_by' => auth()->user()->id,
                    ]);
                endif;

                return response()->json(['message' => 'Related data updated except Status & Term Declaration'], 200);
            else:
                return response()->json(['message' => 'Nothing was changed. Please try again later.'], 304);
            endif;
        endif;
    }
    public function verifyEmail(Request $request){
        $student_user_id = $request->student_user_id;
        $temp_email = $request->email;


        $student = StudentUser::find($student_user_id);
        if(isset($temp_email) && $temp_email!="") {
            $student->temp_email = $temp_email;
            $student->temp_email_verify_code = $student_user_id.Rand('1000','9999');
        }

        $changes = $student->getDirty();
        $student->save();
        if($student->wasChanged() && !empty($changes)):
            if(isset($temp_mobile) && $temp_mobile!="") {
                return response()->json(['message' => 'A message is sent to your new phone'], 200);
            }else 
                return response()->json(['message' => 'A email send to your new mail. please checkk to verify'], 200);
        else:
            return response()->json(['message' => 'Nothing was changed. Please try again.'], 304);
        endif;
    }

    public function verifyMobile(Request $request){
        $student_user_id = $request->student_user_id;
        $temp_mobile = $request->mobile;
        $student = StudentUser::find($student_user_id);
        if(isset($temp_mobile) && $temp_mobile!="") {
            
            $student->temp_mobile = $temp_mobile;
            $student->temp_mobile_verify_code = Rand('1000','9999');

            $active_api = Option::where('category', 'SMS')->where('name', 'active_api')->pluck('value')->first();
            $textlocal_api = Option::where('category', 'SMS')->where('name', 'textlocal_api')->pluck('value')->first();
            $smseagle_api = Option::where('category', 'SMS')->where('name', 'smseagle_api')->pluck('value')->first();

            if($active_api == 1 && !empty($textlocal_api)):
                $response = Http::timeout(-1)->post('https://api.textlocal.in/send/', [
                    'apikey' => $textlocal_api, 
                    'message' => "One Time Password (OTP) for your application account is ".$student->temp_mobile_verify_code.
                                    ".use this OTP to complete the application. OTP will valid for next 24 hours.", 
                    'sender' => 'London Churchill College', 
                    'numbers' => $student->temp_mobile
                ]);
            elseif($active_api == 2 && !empty($smseagle_api)):
                $response = Http::withHeaders([
                    'access-token' => $smseagle_api,
                    'Content-Type' => 'application/json',
                ])->withoutVerifying()->withOptions([
                    "verify" => false
                ])->post('https://79.171.153.104/api/v2/messages/sms', [
                    'to' => [$student->temp_mobile],
                    'text' => "One Time Password (OTP) for your application account is ".$student->temp_mobile_verify_code.
                                ".Use this OTP to complete the application. OTP will valid for next 24 hours",
                ]);
            endif;

        }
        $changes = $student->getDirty();
        $student->save();
        if($student->wasChanged() && !empty($changes)):
            if(isset($temp_mobile) && $temp_mobile!="") {

                return response()->json(['message' => 'A message is sent to your new phone'], 200);
            }else 
                return response()->json(['message' => 'A email send to your new mail. please checkk to verify'], 200);
        else:
            return response()->json(['message' => 'Nothing was changed. Please try again.'], 304);
        endif;
    }
    public function verifiedEmail(Request $request) {

        
        $studentUserFound = StudentUser::where('temp_email_verify_code',$request->code)->get()->first();
        if(isset($studentUserFound->id)) {

            $student = Student::where('student_user_id',$studentUserFound->id)->get()->first();
            $studentOld  = StudentContact::where('student_id',$student->id)->get()->first();
            $studentContact = StudentContact::where('student_id',$student->id)->get()->first();
            $studentContact->personal_email = $studentUserFound->temp_email;
            $studentContact->personal_email_verification = 1;

            $changes = $studentContact->getDirty();
            $studentContact->save();

            if($studentContact->wasChanged() && !empty($changes)):
                $studentUserFound->temp_email_verify_code = NULL;
                $studentUserFound->temp_email = NULL;
                $studentUserFound->save();

                
                foreach($changes as $field => $value):
                    $data = [];
                    $data['student_id'] = $student->id;
                    $data['table'] = 'student_contacts';
                    $data['field_name'] = $field;
                    $data['field_value'] = $studentOld->$field;
                    $data['field_new_value'] = $value;
                    $data['created_by'] = (isset(auth()->user()->id)) ? auth()->user()->id : auth('student')->user()->id;

                    StudentArchive::create($data);
                endforeach;

                if(isset(auth()->user()->id))
                    return redirect()->route('staff.dashboard')->with('verifySuccessMessage', 'Student Personal Email Information Updated');
                elseif(isset(auth('student')->user()->id))
                    return redirect()->route('students.dashboard')->with('verifySuccessMessage', 'Your Personal Email Information Updated');
                else
                    return redirect()->route('students.login')->with('verifySuccessMessage', 'Your Personal Email Information Updated');
            else:
                return route('/');
            endif;
        }
    }
    public function verifiedMobile(Request $request) {

        $studentUserFound = StudentUser::where('temp_mobile_verify_code',$request->code)->get()->first();
        if(isset($studentUserFound->id)) {
            
           
            $student= Student::where('student_user_id',$studentUserFound->id)->get()->first();
            $studentOld  = StudentContact::where('student_id',$student->id)->get()->first();
            $studentContact = StudentContact::where('student_id',$student->id)->get()->first();
            $studentContact->mobile = $studentUserFound->temp_mobile;
            $studentContact->mobile_verification = 1;

            $changes = $studentContact->getDirty();
            $studentContact->save();
            
            if($studentContact->wasChanged() && !empty($changes)):
                $studentUserFound->temp_mobile_verify_code = NULL;
                $studentUserFound->temp_mobile = NULL;
                $studentUserFound->save();

                foreach($changes as $field => $value):
                    $data = [];
                    $data['student_id'] = $student->id;
                    $data['table'] = 'student_contacts';
                    $data['field_name'] = $field;
                    $data['field_value'] = $studentOld->$field;
                    $data['field_new_value'] = $value;
                    $data['created_by'] = (isset(auth()->user()->id)) ? auth()->user()->id : auth('student')->user()->id;

                    StudentArchive::create($data);
                endforeach;
                return response()->json(['message' => 'Mobile Update Succefully'], 200);
            else:
                return response()->json(['message' => 'Nothing was changed. Please try again.'], 304);
            endif;
        }
    }
    
    public function getCoursesByIntakeOrTerm(Request $request){
        $intakeSemester = (isset($request->intakeSemester) && !empty($request->intakeSemester) ? $request->intakeSemester : []);
        $attenSemesters = (isset($request->attenSemesters) && !empty($request->attenSemesters) ? $request->attenSemesters : []);
        $course_ids = [];
        if(!empty($intakeSemester)):
            $course_ids = CourseCreation::whereIn('semester_id', $intakeSemester)->whereHas('course', function($q){
                        $q->where('active', DB::raw('1'));
                    })->pluck('course_id')->unique()->toArray();
        elseif($attenSemesters):
            $courseCreationInst = InstanceTerm::whereIn('term_declaration_id', $attenSemesters)->pluck('course_creation_instance_id')->unique()->toArray();
            if(!empty($courseCreationInst)):
                $courseCreationIds = CourseCreationInstance::whereIn('id', $courseCreationInst)->pluck('course_creation_id')->unique()->toArray();
                if(!empty($courseCreationIds)):
                    $course_ids = CourseCreation::whereIn('id', $courseCreationIds)->whereHas('course', function($q){
                        $q->where('active', DB::raw('1'));
                    })->pluck('course_id')->unique()->toArray();
                endif;
            endif;
        endif;
        $courses = [];
        if(!empty($course_ids)):
            $i = 1;
            foreach($course_ids as $cr):
                $course = Course::find($cr);
                $courses[$i]['id'] = $cr;
                $courses[$i]['name'] = $course->name;

                $i += 1;
            endforeach;
        endif;

        if(!empty($courses)):
            return response()->json(['res' => $courses], 200);
        else:
            return response()->json(['res' => 'Error!'], 422);
        endif;
    }

    public function getGroupByCourseAndTerms(Request $request){
        $terms = (isset($request->attenSemesters) && count($request->attenSemesters) > 0 ? $request->attenSemesters : []);
        $courses = (isset($request->courses) && count($request->courses) > 0 ? $request->courses : []);
            
        $groups = Group::select('name')->whereIn('course_id', $courses);
        if(!empty($terms) && count($terms) > 0):
            $groups->whereIn('term_declaration_id', $terms);
        endif;
        $groups = $groups->groupBy('name')->orderBy('name', 'ASC')->get();
        $res = [];
        if(!empty($groups)):
            $i = 1;
            foreach($groups as $gr):
                $res[$i]['id'] = $gr->name;
                $res[$i]['name'] = $gr->name;

                $i++;
            endforeach;
        endif;

        
        return response()->json(['res' => $res], 200);
    }

    public function archives($studentId){
        return view('pages.students.live.archives', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student Archives', 'href' => 'javascript:void(0);'],
            ],
            'student' => Student::find($studentId),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
        ]);
    }
}
