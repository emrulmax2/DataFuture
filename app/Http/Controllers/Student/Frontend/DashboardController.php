<?php

namespace App\Http\Controllers\Student\Frontend;

use App\Exports\FeeEligibilityExport;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Assign;
use App\Models\AwardingBody;
use App\Models\ConsentPolicy;
use App\Models\Country;
use App\Models\CourseCreationInstance;
use App\Models\Disability;
use App\Models\DocumentSettings;
use App\Models\ELearningActivitySetting;
use App\Models\Employee;
use App\Models\Ethnicity;
use App\Models\FeeEligibility;
use App\Models\HesaGender;
use App\Models\KinsRelation;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\PlanContent;
use App\Models\PlanContentUpload;
use App\Models\PlansDateList;
use App\Models\PlanTask;
use App\Models\PlanTaskUpload;
use App\Models\ReferralCode;
use App\Models\Religion;
use App\Models\SexIdentifier;
use App\Models\SexualOrientation;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentConsent;
use App\Models\StudentUser;
use App\Models\TermTimeAccommodationType;
use App\Models\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userData = auth('student')->user();
        $countries = Country::all();
        $ethnicities = Ethnicity::all();
        $religions = Religion::all();
        $sexualOrientations = SexualOrientation::all();
        $sexIdentifiers = SexIdentifier::all();
        $genderIdentities = HesaGender::all();
        $studentData = Student::where("student_user_id", $userData->id)->get()->first();
        $studentContact = $studentData->contact;
        $studentOtherDetails = $studentData->other;
        $currentAddress = Address::find($studentContact->term_time_address_id);
        $permanentAddress = Address::find($studentContact->permanent_address_id);
        $terTimeAccomadtionType = TermTimeAccommodationType::all();
        $consentList = ConsentPolicy::all();

        $data = [
            "student_id" => $studentData->id,
            "nationality" => $studentData->nationality_id,
            "permanent_country" => $studentData->country_id,
            "ethnicity" => $studentData->nationality_id,
            "religion" => $studentData->nationality_id,
            "sex_identifier_id" => $studentData->sex_identifier_id,
            "sexualOrientation" => "",
            "current_address" => $currentAddress,
            "permanent_address" => $permanentAddress,
            "consents" => $consentList,
            "term_time_accommodation_type_id" => $studentContact->term_time_accommodation_type_id
        ];

        if($studentData->users->first_login==1):
            return view('pages.students.frontend.index', [
                'title' => 'Student Dashboard - London Churchill College',
                'breadcrumbs' => [],
                'user' => $userData,
                "countries" =>$countries,
                "ethnicities" => $ethnicities,
                "religions" => $religions,
                "sexualOrientations" => $sexualOrientations,
                "sexIdentifiers" => $sexIdentifiers,
                "genderIdentities" => $genderIdentities,
                "studentData" => $data,
                "consents" =>$consentList,
                "termTimeAccomadtionTypes" => $terTimeAccomadtionType
            ]);
        else:
            $student = $studentData = Student::where("student_user_id", auth('student')->user()->id)->get()->first();
            $studentAssigned = Assign::where('student_id',$student->id)->get()->first();
            
            if($studentAssigned)
             $dataBox = $this->moduleList();
            else {
                $dataBox = ["termList" =>[],"data" => [],"currenTerm" => [] ];
            }
            return view('pages.students.frontend.dashboard.index', [
                'title' => 'Live Students - London Churchill College',
                'breadcrumbs' => [
                    ['label' => 'Profile View', 'href' => 'javascript:void(0);'],
                ],
                'student' => $student,
                'allStatuses' => Status::where('type', 'Student')->get(),
                'titles' => Title::where('active', 1)->get(),
                'country' => Country::where('active', 1)->get(),
                'ethnicity' => Ethnicity::where('active', 1)->get(),
                'disability' => Disability::where('active', 1)->get(),
                'relations' => KinsRelation::where('active', 1)->get(),
                'bodies' => AwardingBody::all(),
                'instance' => CourseCreationInstance::all(),
                'feeelegibility' => FeeEligibility::where('active', 1)->get(),
                'sexualOrientation' => SexualOrientation::where('active', 1)->get(),
                'sexid' => SexIdentifier::where('active', 1)->get(),
                'hesaGender' => HesaGender::where('active', 1)->get(),
                'religion' => Religion::where('active', 1)->get(),
                'stdConsentIds' => StudentConsent::where('student_id', $student->id)->where('status', 'Agree')->pluck('consent_policy_id')->toArray(),
                'consent' => ConsentPolicy::all(),
                'ttacom' => TermTimeAccommodationType::where('active', 1)->get(),
                "termList" =>$dataBox["termList"],
                "data" => $dataBox["data"],
                "currenTerm" => $dataBox["currenTerm"],
            ]);
        endif;

    }

    public function profileView(){

            
        
        $student = $studentData = Student::where("student_user_id", auth('student')->user()->id)->get()->first();

        return view('pages.students.frontend.dashboard.profile.index', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Profile View', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'allStatuses' => Status::where('type', 'Student')->get(),
            'titles' => Title::where('active', 1)->get(),
            'country' => Country::where('active', 1)->get(),
            'ethnicity' => Ethnicity::where('active', 1)->get(),
            'disability' => Disability::where('active', 1)->get(),
            'relations' => KinsRelation::where('active', 1)->get(),
            'bodies' => AwardingBody::all(),
            'instance' => CourseCreationInstance::all(),
            'feeelegibility' => FeeEligibility::where('active', 1)->get(),
            'sexualOrientation' => SexualOrientation::where('active', 1)->get(),
            'sexid' => SexIdentifier::where('active', 1)->get(),
            'hesaGender' => HesaGender::where('active', 1)->get(),
            'religion' => Religion::where('active', 1)->get(),
            'stdConsentIds' => StudentConsent::where('student_id', $student->id)->where('status', 'Agree')->pluck('consent_policy_id')->toArray(),
            'consent' => ConsentPolicy::all(),
            'ttacom' => TermTimeAccommodationType::where('active', 1)->get()
        ]);
    }

    protected function moduleList() {

        $userData = StudentUser::find(auth('student')->user()->id);
        $studentData = Student::where("student_user_id",$userData->id)->get()->first();

        $Query = DB::table('plans as plan')
        ->select('plan.*','academic_years.id as academic_year_id','academic_years.name as academic_year_name','terms.id as term_id','term_declarations.name as term_name','terms.term as term','course.name as course_name','module.module_name','venue.name as venue_name','room.name as room_name','group.name as group_name',"user.name as username")
        ->leftJoin('courses as course', 'plan.course_id', 'course.id')
        ->leftJoin('module_creations as module', 'plan.module_creation_id', 'module.id')
        ->leftJoin('instance_terms as terms', 'module.instance_term_id', 'terms.id')
        ->leftJoin('term_declarations', 'term_declarations.id', 'terms.term_declaration_id')
        ->leftJoin('course_creation_instances as course_relation_instances', 'terms.course_creation_instance_id','course_relation_instances.id')
        ->leftJoin('course_creations as course_relation', 'course_relation_instances.course_creation_id','course_relation.id')
        ->leftJoin('academic_years', 'course_relation_instances.academic_year_id','academic_years.id')
        ->leftJoin('venues as venue', 'plan.venue_id', 'venue.id')
        ->leftJoin('rooms as room', 'plan.rooms_id', 'room.id')
        ->leftJoin('groups as group', 'plan.group_id', 'group.id')
        ->leftJoin('users as user', 'plan.tutor_id', 'user.id')
        ->leftJoin('assigns', 'assigns.plan_id', 'plan.id')
        ->where('assigns.student_id', $studentData->id);

        

        $Query = $Query
                 ->orderBy('plan.term_declaration_id','DESC')
                 ->get();

        $data = array();
        $currentTerm = 0;
        if(!empty($Query)):
            $i = 1;
            
            foreach($Query as $list):
                    
                    if($currentTerm==0)
                        $currentTerm = $list->term_id;

                    $termData[$list->term_id] = (object) [ 
                        'id' =>$list->term_id,
                        'name' => $list->term_name,   
                        "total_modules" => !isset($termData[$list->term_id]) ? 1 : $termData[$list->term_id]->total_modules,
                        
                    ];

                    $data[$list->term_id][] = (object) [
                        'id' => $list->id,
                        'sl' => $i,
                        'course' => $list->course_name,
                        'module' => $list->module_name,
                        'group'=> $list->group_name,           
                    ];

                    if(isset($termData[$list->term_id]))  
                        $termData[$list->term_id]->total_modules = count($data[$list->term_id]);
                    else 
                        $termData[$list->term_id] = 1;
                    $i++;
        
            endforeach;
        endif;
        // $request = new Request();

        // $request->merge([
        //     'plan_date' => "13-09-2023",
        //     'id' =>$id,
        // ]);
        // $todaysList = $this->latestList($request);
        // $returnData = json_decode($todaysList->getContent(),true);

        return $dataSet = ["termList" =>$termData,
            "data" => $data,
            "currenTerm" => $currentTerm ];
    }

    public function showCourseContent(Plan $plan) {

        $userData = StudentUser::find(Auth::guard('student')->user()->id);
        //$employee = Employee::where("user_id",$userData->id)->get()->first();

        $tutor = Employee::where("user_id",$plan->tutor->id)->get()->first();
        
        $personalTutor = isset($plan->personalTutor->id) ? Employee::where("user_id",$plan->personalTutor->id)->get()->first() : "";
        
        $planTask = PlanTask::where("plan_id",$plan->id)->get();  
        
        $studentAssign = Assign::where('plan_id', $plan->id)->get();
        $studentListCount = $studentAssign->count();
        // $planParticipant = PlanParticipant::where('plan_id', $plan->id)->get();
        // $participantList = $planParticipant->count();
        $planDates = $planDateList = PlansDateList::where("plan_id",$plan->id)->get();
        $eLearningActivites = ELearningActivitySetting::all();
        $planDateWiseContent = [];
        foreach($planDates as $classDate) {

            $content = PlanContent::where("plans_date_list_id", $classDate->id)->get();

            foreach($content as $singleContent){
                
                $uploads = PlanContentUpload::where("plan_content_id",$singleContent->id)->get();
    
                $planDateWiseContent[$classDate->id] = (object) [
                    "task" => $content,
                    "taskUploads" => $uploads,
                ];
            }
            
        }
        $allPlanTasks = [];

            foreach($planTask as $task){
                $uploads = PlanTaskUpload::where("plan_task_id",$task->id)->get();

                $allPlanTasks[$task->id] = (object) [
                    "task"=> $task,
                    "taskUploads" => $uploads
                ]; 
            }
        
        $moduleCreations = ModuleCreation::find($plan->creations->id);
                    $data = (object) [
                        'id' => $plan->id,
                        'term_name' => $moduleCreations->term->name,
                        'course' => $plan->course->name,
                        
                        'classType' => $plan->creations->class_type,
                        'module' => $plan->creations->module_name,
                        'group'=> $plan->group->name,           
                        'room'=> $plan->room->name,           
                        'venue'=> $plan->venu->name,           
                        'tutor'=> ($tutor) ?? null,           
                        'personalTutor'=> ($personalTutor) ?? null,           
                    ];

                
       
        return view('pages.students.frontend.dashboard.module.view', [
            'title' => 'Attendance - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Attendance', 'href' => 'javascript:void(0);']
            ],
            "plan" => $plan,
            "user" => $userData,
            "employee" => NULL,
            "data" => $data,
            'planTasks' => $allPlanTasks,
            'planDates' => $planDateWiseContent,
            'planDateList' => $planDateList,
            'eLearningActivites' => $eLearningActivites,
            'studentCount' => $studentListCount,
        ]);
    }

    public function planDatelist(Request $request) {
        $planid = (isset($request->planid) && !empty($request->planid) ? $request->planid : 0);
        $dates = (isset($request->dates) && !empty($request->dates) ? date('Y-m-d', strtotime($request->dates)) : '');
        $status = (isset($request->status) && !empty($request->status) ? $request->status : '1');
        
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'date', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = PlansDateList::orderByRaw(implode(',', $sorts));
        if(!empty($planid)): $query->where('plan_id', $planid); endif;
        if(!empty($dates)): $query->where('date', $dates); endif;
        if($status == 2): $query->onlyTrashed(); endif;

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

                $theDay = date("Y-m-d", strtotime($list->date));

                $start_time = date($theDay." ".$list->plan->start_time);
                $start_time = date('h:i A', strtotime($start_time));
                
                $end_day = date($theDay." ".$list->plan->end_time);
                $end_time = date('h:i A', strtotime($end_day));
                if(strtotime(now())> strtotime($end_day)) {
                    $upcommingStatus = "Unknown";
                } else {
                    $upcommingStatus = "Upcomming";
                }
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => (isset($list->plan->virtual_room) && !empty($list->plan->virtual_room) ? 'Virtual - ' : 'Physical - ').$list->name,
                    'date'=> date('l jS M, Y', strtotime($list->date)),
                    'room' => (isset($list->plan->room->name) && !empty($list->plan->room->name) ? $list->plan->room->name : ''),
                    'time' => (isset($list->plan->start_time) && !empty($list->plan->start_time) ? date('H:i', strtotime($list->plan->start_time)) : 'Unknown').' - '.(isset($list->plan->end_time) && !empty($list->plan->end_time) ? date('H:i', strtotime($list->plan->end_time)) : 'Unknown'),
                    'status' => '',
                    'deleted_at' => $list->deleted_at,
                    'tutor_id'=>$list->plan->tutor_id,
                    "start_time" => $start_time,
                    "end_time" => $end_time,
                    "end_date_time" => $end_day,
                    'venue' => $list->plan->venu->name,
                    'virtual_room'=> $list->plan->virtual_room,   
                    'upcomming_status' => $upcommingStatus, 
                    "attendance_information" => ($list->attendanceInformation) ?? null,    
                    "foundAttendances"  => ($list->attendances) ?? null, 
                ];
                $i++;
            endforeach;
        endif;
        
        
        
          


        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
