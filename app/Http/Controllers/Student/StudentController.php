<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;

use App\Models\Applicant;
use App\Models\ApplicantTemporaryEmail;
use App\Models\AwardingBody;
use App\Models\ComonSmtp;
use App\Models\ConsentPolicy;
use App\Models\Country;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\Disability;
use App\Models\DocumentSettings;
use App\Models\EmailTemplate;
use App\Models\Ethnicity;
use App\Models\FeeEligibility;
use App\Models\HesaGender;
use App\Models\KinsRelation;
use App\Models\LetterSet;
use App\Models\ProcessList;
use App\Models\ReferralCode;
use App\Models\Religion;
use App\Models\Semester;
use App\Models\SexualOrientation;
use App\Models\Signatory;
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

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);

        $query = Student::orderByRaw(implode(',', $sorts));
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
                    'first_name' => $list->first_name,
                    'last_name' => $list->last_name,
                    'date_of_birth'=> $list->date_of_birth,
                    'course'=> (isset($list->course->creation->course->name) ? $list->course->creation->course->name : ''),
                    'semester'=> (isset($list->course->semester->name) ? $list->course->semester->name : ''),
                    'gender'=> $list->gender,
                    'status_id'=> (isset($list->status->name) ? $list->status->name : ''),
                    'url' => route('student.show', $list->id),
                    'ccid' => implode(',', $courses).' - '.implode(',', $courseCreationId)
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
            'hesaGender' => HesaGender::where('active', 1)->get(),
            'religion' => Religion::where('active', 1)->get(),
            'stdConsentIds' => StudentConsent::where('student_id', $studentId)->where('status', 'Agree')->pluck('consent_policy_id')->toArray(),
            'consent' => ConsentPolicy::all(),
            'referral' => $referral,
            'ttacom' => TermTimeAccommodationType::where('active', 1)->get()
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
            'proposedCourse' => StudentProposedCourse::where('student_id', $studentId)->first()
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
}
