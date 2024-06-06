<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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
use App\Models\CourseModule;
use App\Models\Disability;
use App\Models\DocumentSettings;
use App\Models\EmailTemplate;
use App\Models\EmailVerificationCode;
use App\Models\Ethnicity;
use App\Models\FeeEligibility;
use App\Models\Grade;
use App\Models\Group;
use App\Models\HesaGender;
use App\Models\InstanceTerm;
use App\Models\KinsRelation;
use App\Models\LetterSet;
use App\Models\MobileVerificationCode;
use App\Models\ModuleCreation;
use App\Models\Option;
use App\Models\Plan;
use App\Models\ProcessList;
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
use App\Models\StudentConsent;
use App\Models\StudentContact;
use App\Models\StudentCourseRelation;
use App\Models\StudentDocument;
use App\Models\StudentProposedCourse;
use App\Models\Title;
use App\Models\User;
use App\Models\StudentSms;
use App\Models\StudentTask;
use App\Models\StudentWorkPlacement;
use App\Models\TermDeclaration;
use App\Models\TermTimeAccommodationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;


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

        $Query = DB::table('students as std')
                    ->select('std.*', 'sts.name as status_name', 'scn.id as scn_id', 'scr.id as scr_id', 'scr.course_creation_id', 'cc.course_id', 'cc.semester_id', 'cr.name as course_name', 'sm.name as semester_name', 'si.name as sexid_name')
                    ->leftJoin('statuses as sts', 'std.status_id', 'sts.id')
                    ->leftJoin('student_contacts as scn', 'std.id', 'scn.student_id')
                    ->leftJoin('student_users as su', 'std.student_user_id', 'su.id')
                    ->leftJoin('student_course_relations as scr', function($join){
                            $join->on('std.id', '=', 'scr.student_id');
                            $join->on('scr.active', '=', DB::raw(1));
                    })
                    ->leftJoin('student_awarding_body_details as sabd', 'scr.id', 'sabd.student_course_relation_id')
                    ->leftJoin('course_creations as cc', 'cc.id', 'scr.course_creation_id')
                    ->leftJoin('courses as cr', 'cr.id', 'cc.course_id')
                    ->leftJoin('semesters as sm', 'sm.id', 'cc.semester_id')
                    ->leftJoin('student_proposed_courses as spc', 'spc.student_course_relation_id', 'scr.id')
                    ->leftJoin('sex_identifiers as si', 'std.sex_identifier_id', 'si.id');
        
        if(!empty($student_id)): $Query->where('registration_no', $student_id); endif;
        if($studentSearch):
            foreach($studentParams as $field => $value):
                $$field = (isset($value) && !empty($value) ? ($field == 'student_dob' ? date('Y-m-d', strtotime($value)) :$value) : '');
            endforeach;
            if(!empty($student_name)): $Query->where('std.first_name','LIKE','%'.$student_name.'%'); endif;
            if(!empty($student_name)): $Query->where('std.last_name','LIKE','%'.$student_name.'%'); endif;
            if(!empty($student_dob)): $Query->where('std.date_of_birth', $student_dob); endif;
            if(!empty($student_post_code)): $Query->where('scn.term_time_post_code', $student_post_code); endif;
            if(!empty($student_email)): $Query->where('su.email', $student_email); endif;
            if(!empty($student_mobile)): $Query->where('scn.mobile', $student_mobile); endif;
            if(!empty($student_uhn)): $Query->where('std.uhn_no', $student_uhn); endif;
            if(!empty($student_ssn)): $Query->where('std.ssn_no', $student_ssn); endif;
            if(!empty($student_abr)): $Query->where('sabd.reference', $student_abr); endif;
            if(!empty($student_status)): $Query->whereIn('std.status_id', $student_status); endif;
        endif;

        if($groupSearch):
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
        endif;

        $total_rows = $Query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $Query = $Query->orderByRaw(implode(',', $sorts))->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                if ($list->photo !== null && Storage::disk('local')->exists('public/applicants/'.$list->applicant_id.'/'.$list->photo)) {
                    $photo_url = Storage::disk('local')->url('public/applicants/'.$list->applicant_id.'/'.$list->photo);
                } else {
                    $photo_url = asset('build/assets/images/user_avatar.png');
                }
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'registration_no' => (!empty($list->registration_no) ? $list->registration_no : $list->application_no),
                    'first_name' => $list->first_name,
                    'last_name' => $list->last_name,
                    'date_of_birth'=> (!empty($list->date_of_birth) ? date('d-m-Y', strtotime($list->date_of_birth)) : '') ,
                    'course'=> (isset($list->course_name) && !empty($list->course_name) ? $list->course_name : ''),
                    'semester'=> (isset($list->semester_name) && !empty($list->semester_name) ? $list->semester_name : ''),
                    'gender'=> (isset($list->sexid_name) && !empty($list->sexid_name) ? $list->sexid_name : ''),
                    'status_id'=> (isset($list->status_name) && !empty($list->status_name) ? $list->status_name : ''),
                    'url' => route('student.show', $list->id),
                    'photo_url' => $photo_url
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
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
        ]);
    }

    public function courseDetails($studentId){
        return view('pages.students.live.course', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student Course', 'href' => 'javascript:void(0);'],
            ],
            'student' => Student::find($studentId),
            'allStatuses' => Status::where('type', 'Student')->get(),
            'instance' => CourseCreationInstance::all(),
            'feeelegibility' => FeeEligibility::all(),
            'proposedCourse' => StudentProposedCourse::where('student_id', $studentId)->first(),
            "courses" => Course::orderBy('name', 'ASC')->get(),
            "academicYears" => AcademicYear::orderBy('from_date', 'DESC')->get(),
            "semesters" => Semester::orderBy('id', 'DESC')->get(),
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
            'docSettings' => DocumentSettings::where('live', '1')->get()
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
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get()
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

            'processGroup' => $processGroup
        ]);
    }

    public function UploadStudentPhoto(Request $request){
        $applicant_id = $request->applicant_id;
        $student_id = $request->student_id;
        $applicantOldRow = Student::where('id', $student_id)->first();
        $oldPhoto = (isset($applicantOldRow->photo) && !empty($applicantOldRow->photo) ? $applicantOldRow->photo : '');

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/applicants/'.$applicant_id, $imageName, 'local');
        if(!empty($oldPhoto)):
            if (Storage::disk('local')->exists('public/applicants/'.$applicant_id.'/'.$oldPhoto)):
                Storage::delete('public/applicants/'.$applicant_id.'/'.$oldPhoto);
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
            'lastAssigns' => Assign::where('student_id', $studentId)->orderBy('id', 'desc')->get()->first()
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
                    if(isset($attendance)) {

                        $attendanceInformation =AttendanceInformation::with(["tutor","planDate"])->where("plans_date_list_id",$list->id)->get()->first();
                        if(isset($attendanceInformation))
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
        // endforeach;

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
            "attendanceFeedStatus" =>$attendanceFeedStatus
        ]);
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
                    
                    $data[$list->term_id][$list->module_name."-".$list->module_code] = [
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
                    $planDetails[$list->term_id][$list->module_name."-".$list->module_code] = Plan::with(["tutor","personalTutor"])->where('id',$list->plan_id)->get()->first();
                    

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
            "attendanceFeedStatus" =>$attendanceFeedStatus
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
            'placement' => StudentWorkPlacement::all()
        ]);
    }

    public function studentDocumentDownload(Request $request){ 
        $row_id = $request->row_id;

        $studentDoc = StudentDocument::find($row_id);
        $applicant_id = $studentDoc->student->applicant_id;
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/applicants/'.$applicant_id.'/'.$studentDoc->current_file_name, now()->addMinutes(5));
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
}
