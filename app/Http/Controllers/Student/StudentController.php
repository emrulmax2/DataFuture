<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;

use App\Models\Applicant;
use App\Models\ApplicantTemporaryEmail;
use App\Models\AttendanceCode;
use App\Models\AwardingBody;
use App\Models\ComonSmtp;
use App\Models\ConsentPolicy;
use App\Models\Country;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\CourseModule;
use App\Models\Disability;
use App\Models\DocumentSettings;
use App\Models\EmailTemplate;
use App\Models\Ethnicity;
use App\Models\FeeEligibility;
use App\Models\Group;
use App\Models\HesaGender;
use App\Models\InstanceTerm;
use App\Models\KinsRelation;
use App\Models\LetterSet;
use App\Models\ProcessList;
use App\Models\ReferralCode;
use App\Models\Religion;
use App\Models\Semester;
use App\Models\SexIdentifier;
use App\Models\SexualOrientation;
use App\Models\Signatory;
use App\Models\SlcRegistrationStatus;
use App\Models\SmsTemplate;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentConsent;
use App\Models\StudentProposedCourse;
use App\Models\Title;
use App\Models\User;
use App\Models\StudentSms;
use App\Models\StudentTask;
use App\Models\TermTimeAccommodationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


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
            'title' => 'Live Students - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Live', 'href' => 'javascript:void(0);']
            ],
            'semesters' => $semesters,
            'courses' => $courses,
            'allStatuses' => $statuses,
            'academicYear' => AcademicYear::all()->sortByDesc('from_date'),
            'terms' => InstanceTerm::all()->sortByDesc('id'),
            'groups' => Group::all(),
            'modules' => CourseModule::all(),
        ]);
    }

    public function list(Request $request){
        $student_id = isset($request->student_id) && !empty($request->student_id) ? $request->student_id : '';

        $studentParams = isset($request->student) && !empty($request->student) ? $request->student : [];
        $groupParams = isset($request->group) && !empty($request->group) ? $request->group : [];
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
        
        //$Query = Student::orderByRaw(implode(',', $sorts));
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
            if(!empty($academic_year)): $Query->whereIn('spc.academic_year_id', $academic_year); endif;
            if(!empty($intake_semester)): $Query->whereIn('spc.semester_id', $intake_semester); endif;

            if(!empty($evening_weekend)): $Query->whereIn('spc.full_time', $evening_weekend); endif;
            if(!empty($group_student_status)): $Query->whereIn('std.status_id', $group_student_status); endif;
        endif;


        /*$query = Student::orderByRaw(implode(',', $sorts));
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
        endif;*/

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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'application_no' => (empty($list->application_no) ? $list->id : $list->application_no),
                    'first_name' => $list->first_name,
                    'last_name' => $list->last_name,
                    'date_of_birth'=> $list->date_of_birth,
                    'course'=> (isset($list->course_name) && !empty($list->course_name) ? $list->course_name : ''),
                    'semester'=> (isset($list->semester_name) && !empty($list->semester_name) ? $list->semester_name : ''),
                    'gender'=> (isset($list->sexid_name) && !empty($list->sexid_name) ? $list->sexid_name : ''),
                    'status_id'=> (isset($list->status_name) && !empty($list->status_name) ? $list->status_name : ''),
                    'url' => route('student.show', $list->id)
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
            'title' => 'Live Students - LCC Data Future Managment',
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
            'users' => User::all(),
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
            'title' => 'Live Students - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student Course', 'href' => 'javascript:void(0);'],
            ],
            'student' => Student::find($studentId),
            'allStatuses' => Status::where('type', 'Student')->get(),
            'instance' => CourseCreationInstance::all(),
            'feeelegibility' => FeeEligibility::all(),
            'proposedCourse' => StudentProposedCourse::where('student_id', $studentId)->first(),
            "courseCreations" => CourseCreation::all(),
            "academicYears" => AcademicYear::all(),
            "semesters" => Semester::all(),
        ]);
    }

    public function communications($studentId){
        return view('pages.students.live.communication', [
            'title' => 'Live Students - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student Communications', 'href' => 'javascript:void(0);'],
            ],
            'student' => Student::find($studentId),
            'allStatuses' => Status::where('type', 'Student')->get(),
            'smtps' => ComonSmtp::all(),
            'letterSet' => LetterSet::all(),
            'signatory' => Signatory::all(),
            'smsTemplates' => SmsTemplate::all(),
            'emailTemplates' => EmailTemplate::all(),
        ]);
    }

    public function uploads($studentId){
        return view('pages.students.live.uploads', [
            'title' => 'Live Students - LCC Data Future Managment',
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
            'title' => 'Live Students - LCC Data Future Managment',
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
            'title' => 'Live Student - LCC Data Future Managment',
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
        $path = $document->storeAs('public/applicants/'.$applicant_id, $imageName, 'google');
        if(!empty($oldPhoto)):
            if (Storage::disk('google')->exists('public/applicants/'.$applicant_id.'/'.$oldPhoto)):
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
        $courseCreationID = (isset($student->crel->course_creation_id) && $student->crel->course_creation_id > 0 ? $student->crel->course_creation_id : 0);
        $firstCreationInstance = CourseCreationInstance::where('course_creation_id', $courseCreationID)->orderBy('id', 'ASC')->get()->first();

        return view('pages.students.live.slc-history', [
            'title' => 'Live Students - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student SLC History', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'ac_years' => AcademicYear::orderBy('from_date', 'ASC')->get(),
            'active_ac_year' => (isset($firstCreationInstance->academic_year_id) && $firstCreationInstance->academic_year_id > 0 ? $firstCreationInstance->academic_year_id : 0),
            'reg_status' => SlcRegistrationStatus::where('active', 1)->get(),
            'instances' => CourseCreationInstance::where('course_creation_id', $courseCreationID)->orderBy('academic_year_id', 'ASC')->get(),
            'attendanceCodes' => AttendanceCode::where('active', 1)->orderBy('code', 'ASC')->get()
        ]);
    }
}
