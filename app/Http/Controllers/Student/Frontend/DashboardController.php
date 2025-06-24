<?php

namespace App\Http\Controllers\Student\Frontend;

use App\Exports\FeeEligibilityExport;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Assign;
use App\Models\AttendanceExcuseDay;
use App\Models\AwardingBody;
use App\Models\ConsentPolicy;
use App\Models\Country;
use App\Models\CourseCreationInstance;
use App\Models\CourseCreationVenue;
use App\Models\Disability;
use App\Models\DocumentSettings;
use App\Models\ELearningActivitySetting;
use App\Models\Employee;
use App\Models\Ethnicity;
use App\Models\FeeEligibility;
use App\Models\FormsTable;
use App\Models\HesaGender;
use App\Models\KinsRelation;
use App\Models\LevelHours;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\PlanContent;
use App\Models\PlanContentUpload;
use App\Models\PlansDateList;
use App\Models\PlanTask;
use App\Models\PlanTaskUpload;
use App\Models\ReferralCode;
use App\Models\Religion;
use App\Models\Room;
use App\Models\SexIdentifier;
use App\Models\SexualOrientation;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentConsent;
use App\Models\StudentProposedCourse;
use App\Models\StudentUser;
use App\Models\StudentWorkPlacement;
use App\Models\TermTimeAccommodationType;
use App\Models\Title;
use App\Models\User;
use App\Models\Venue;
use App\Models\WorkplacementDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userData = auth('student')->user();
        $countries = Country::where('active', 1)->get();
        $ethnicities = Ethnicity::where('active', 1)->get();
        $religions = Religion::where('active', 1)->get();
        $sexualOrientations = SexualOrientation::where('active', 1)->get();
        $sexIdentifiers = SexIdentifier::where('active', 1)->get();
        $genderIdentities = HesaGender::where('active', 1)->get();
        $studentData = Student::where("student_user_id", $userData->id)->get()->first();
        $studentContact = $studentData->contact;
        $studentOtherDetails = $studentData->other;
        $currentAddress = Address::find($studentContact->term_time_address_id);
        $permanentAddress = Address::find($studentContact->permanent_address_id);
        $terTimeAccomadtionType = TermTimeAccommodationType::where('active', 1)->get();
        $consentList = ConsentPolicy::all();
        $data = [
            "student_id" => $studentData->id,
            "nationality" => $studentData->nationality_id,
            "permanent_country" => $studentData->country_id,
            "ethnicity" => isset($studentOtherDetails->ethnicity->id) ? $studentOtherDetails->ethnicity->id : "" ,
            "religion" => isset($studentOtherDetails->religion->id) ? $studentOtherDetails->religion->id : "" ,
            "sex_identifier_id" => $studentData->sex_identifier_id,
            "sexualOrientation" => isset($studentOtherDetails->sexori->id) ? $studentOtherDetails->sexori->id : "",
            'hesa_gender_id' => $studentOtherDetails->hesa_gender_id,
            "current_address" => $currentAddress,
            "permanent_address" => $permanentAddress,
            "consents" => $consentList,
            "term_time_accommodation_type_id" => $studentContact->term_time_accommodation_type_id
        ];

        if($studentData->users->first_login==1 && !$studentData->users->isImpersonated()):
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
            $DoItOnline = FormsTable::all();
            if($studentAssigned)
             $dataBox = $this->moduleList();
            else {
                $dataBox = ["termList" =>[],"data" => [],"currenTerm" => [] ];
            }

            $allData = $dataBox["data"];
            $currenTerm = $dataBox["currenTerm"];
           
            if(isset($allData) && !empty($allData))
            foreach($allData[$currenTerm] as $key => $data):
               foreach($data->plan_dates as $dateData):
                $upcommingDate = strtotime(date("Y-m-d",strtotime($dateData->date)));
                $currentDate = strtotime(date("Y-m-d"));
                $hr_date = date('F jS, Y',$upcommingDate);
                $dateWiseClassList[date("Y-m-d",strtotime($dateData->date))][] = (object) [
                    "module" => $data->module,
                    "classType" => $data->classType,
                    "hr_date" =>$hr_date,
                    "hr_time" => $data->start_time."-".$data->end_time,
                    "venue_room" => $data->venue->name.", ".$data->room->name,
                    "virtual_room" => $data->virtual_room,
                ];
                    
               endforeach;
            endforeach;
            if(isset($dateWiseClassList))
                uksort($dateWiseClassList, function($a, $b) {
                    return strtotime($a) - strtotime($b);
                });
            else
            $dateWiseClassList = [];

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
                "doItOnline" => $DoItOnline,
                "datewiseClasses" => $dateWiseClassList,
            ]);
        endif;

    }

    public function profileView() {
        
        $student = $studentData = Student::with('crel', 'course')->where("student_user_id", auth('student')->user()->id)->get()->first();
        $courseRelationCreation = $student->crel->creation;
        $studentCourseAvailability = $courseRelationCreation->availability;
        $courseCreationQualificationData = $courseRelationCreation->qualification;
        $currentCourse = StudentProposedCourse::with('venue')->where('student_id',$student->id)
                        ->where('course_creation_id',$courseRelationCreation->id)
                        ->where('student_course_relation_id',$student->crel->id)
                        ->get()
                        ->first();
        $CourseCreationVenue = CourseCreationVenue::where('course_creation_id',$courseRelationCreation->id)->where('venue_id', $currentCourse->venue_id)->get()->first();
        $dateWiseClassList = $this->upcommingClass($student->id);

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
            'ttacom' => TermTimeAccommodationType::where('active', 1)->get(),
            "courseQualification" =>$courseCreationQualificationData,
            "slcCode" =>(!empty($CourseCreationVenue)) ? $CourseCreationVenue->slc_code : "UNKNOWN",
            "venue" =>(!empty($CourseCreationVenue)) ? $currentCourse->venue->name : "",
            'studentCourseAvailability' => $studentCourseAvailability,
            "datewiseClasses" => $dateWiseClassList,
        ]);

    }

    protected function moduleList() {

        $userData = StudentUser::find(auth('student')->user()->id);
        $studentData = Student::where("student_user_id",$userData->id)->get()->first();

        $Query = DB::table('plans as plan')
        ->select('plan.*','academic_years.id as academic_year_id','academic_years.name as academic_year_name','terms.id as term_id','term_declarations.name as term_name','terms.term as term','course.name as course_name','module.module_name','module.class_type as module_class_type','venue.name as venue_name','room.name as room_name','group.name as group_name',"user.name as username")
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
        //->where('plan.parent_id', 0);

        

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
                        //PlansDateList::
                    $termData[$list->term_id] = (object) [ 
                        'id' =>$list->term_id,
                        'name' => $list->term_name,   
                        "total_modules" => !isset($termData[$list->term_id]) ? 1 : $termData[$list->term_id]->total_modules,
                        
                    ];
                    $tutor = User::with('employee')->where("id",$list->tutor_id)->get()->first();
                    $pTutor = User::with('employee')->where("id",$list->personal_tutor_id)->get()->first();

                    $getClassDatesForStudent =  PlansDateList::where('plan_id',$list->id)->get();
                    
                    $start_time = date("Y-m-d ".$list->start_time);
                    $start_time = date('h:i A', strtotime($start_time));
                    
                    $end_time = date("Y-m-d ".$list->end_time);
                    $end_time = date('h:i A', strtotime($end_time));

                    $tutorial = Plan::where('parent_id', $list->id)->where('class_type', 'Tutorial')->get()->first();
                    $has_tutorial = (isset($tutorial->id) && $tutorial->id > 0 ? true : false); 
                    $data[$list->term_id][] = (object) [
                        'id' => $list->id,
                        'sl' => $i,
                        'parent_id' => $list->parent_id,
                        'course' => $list->course_name,
                        'tutor_photo' => isset($tutor->employee->photo_url) ? $tutor->employee->photo_url : "",
                        'personal_tutor_photo' => isset($pTutor->employee->photo_url) ? $pTutor->employee->photo_url : "",
                        'classType' => ($list->class_type!="")  ? $list->class_type : $list->module_class_type,
                        'module' => $list->module_name,
                        'group'=> $list->group_name,
                        'venue' =>Venue::find($list->venue_id),           
                        'room' =>Room::find($list->rooms_id),   
                        'virtual_room' =>$list->virtual_room,   
                        'plan_dates' => $getClassDatesForStudent,
                        'start_time' =>$start_time,           
                        'end_time' =>$end_time, 
                        'has_tutorial' => $has_tutorial,
                        'p_tutor_photo' => isset($tutorial->personalTutor->employee->photo_url) ? $tutorial->personalTutor->employee->photo_url : ""              
                    ];
                    
                    if(isset($termData[$list->term_id]))  
                        $termData[$list->term_id]->total_modules = count($data[$list->term_id]);
                    else 
                        $termData[$list->term_id] = 1;
                    $i++;
        
            endforeach;
        endif;

        usort($data[$currentTerm], fn($a, $b) => strcmp($a->module, $b->module));

        return $dataSet = ["termList" =>$termData,
            "data" => $data,
            "currenTerm" => $currentTerm ];
    }

    protected function upcommingClass($student_id) {
        $studentAssigned = Assign::where('student_id',$student_id)->get()->first();
        if($studentAssigned)
        $dataBox = $this->moduleList();
       else {
           $dataBox = ["termList" =>[],"data" => [],"currenTerm" => [] ];
       }

       $allData = $dataBox["data"];
       $currenTerm = $dataBox["currenTerm"];
       //dd($allData[$currenTerm]);
       if(isset($allData) && !empty($allData))
       foreach($allData[$currenTerm] as $key => $data):
          foreach($data->plan_dates as $dateData):
           $upcommingDate = strtotime(date("Y-m-d",strtotime($dateData->date)));
           $currentDate = strtotime(date("Y-m-d"));
           $hr_date = date('F jS, Y',$upcommingDate);
           $dateWiseClassList[date("Y-m-d",strtotime($dateData->date))][] = (object) [
               "module" => $data->module,
               "classType" => $data->classType,
               "hr_date" =>$hr_date,
               "hr_time" => $data->start_time."-".$data->end_time,
               "venue_room" => $data->venue->name.", ".$data->room->name,
           ];
               
          endforeach;
       endforeach;
       if(isset($dateWiseClassList))
           uksort($dateWiseClassList, function($a, $b) {
               return strtotime($a) - strtotime($b);
           });
       else
          $dateWiseClassList = [];

       return $dateWiseClassList;

    }
    public function showCourseContent(Plan $plan) {

        $userData = StudentUser::find(Auth::guard('student')->user()->id);
        //$employee = Employee::where("user_id",$userData->id)->get()->first();

        $tutor = isset($plan->tutor) ? Employee::where("user_id",$plan->tutor->id)->get()->first() : null;
        
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
                        
                        'classType' => ($plan->class_type!="")  ? $plan->class_type : $moduleCreations->class_type,
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

    public function attendanceExcuse(){
        $student = Student::with('crel', 'course')->where("student_user_id", auth('student')->user()->id)->get()->first();
        $dateWiseClassList = $this->upcommingClass($student->id);

        return view('pages.students.frontend.dashboard.profile.excuse', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Attendance Excuse', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'datewiseClasses' => $dateWiseClassList,
            'pastDateList' => $this->getAbsentExcuseDateList($student->id),
            'futureDateList' => $this->getFutureExcuseDateList($student->id),
        ]);
    }

    public function getAbsentExcuseDateList($student_id){
        $planids = Assign::where('student_id', $student_id)->where(function($q){
            $q->where('attendance', 1)->orWhereNull('attendance');
        })->pluck('plan_id')->unique()->toArray();

        $today = date('Y-m-d');
        $list = [];
        if(!empty($planids) && count($planids) > 0):
            foreach($planids as $plan_id):
                $plan = Plan::find($plan_id);
                $planDateList = PlansDateList::where('plan_id', $plan_id)->where('feed_given', 1)->whereHas('attendances', function($q) use($student_id, $plan_id){
                    $q->where('attendance_feed_status_id', 4)->where('student_id', $student_id)->where('plan_id', $plan_id);
                })->where('date', '<', $today)->orderBy('date', 'DESC')->get();
                if($planDateList->count() > 0):
                    $list[$plan_id]['module'] = $plan->creations->module_name;
                    $i = 1;
                    foreach($planDateList as $pdl):
                        $existExcuse = AttendanceExcuseDay::with('excuse')->where('plan_id', $plan_id)->where('plans_date_list_id', $pdl->id)->where('active', 1)
                                       ->whereHas('excuse', function($q) use($student_id){
                                            $q->where('student_id', $student_id);
                                       })->orderBy('attendance_excuse_id', 'DESC')->get()->first();
                        $status = (isset($existExcuse->excuse->status) ? $existExcuse->excuse->status : '');
                        $statusLabel = (isset($existExcuse->excuse->status_label) ? $existExcuse->excuse->status_label : '');
                        if($status != 2):
                            $list[$plan_id]['date_lists'][$i]['id'] = $pdl->id;
                            $list[$plan_id]['date_lists'][$i]['dates'] = date('jS F, Y', strtotime($pdl->date));
                            $list[$plan_id]['date_lists'][$i]['status'] = $status;
                            $list[$plan_id]['date_lists'][$i]['status_label'] = $statusLabel;
                        endif;

                        $i++;
                    endforeach;
                endif;
            endforeach;
        endif;
        
        return $list;
    }

    public function getFutureExcuseDateList($student_id){
        $planids = Assign::where('student_id', $student_id)->where(function($q){
            $q->where('attendance', 1)->orWhereNull('attendance');
        })->pluck('plan_id')->unique()->toArray();

        $today = date('Y-m-d');
        $list = [];
        if(!empty($planids) && count($planids) > 0):
            foreach($planids as $plan_id):
                $plan = Plan::find($plan_id);
                $planDateList = PlansDateList::where('plan_id', $plan_id)->where('status', 'Scheduled')->where('date', '>', $today)->orderBy('date', 'DESC')->get();
                if($planDateList->count() > 0):
                    $list[$plan_id]['module'] = $plan->creations->module_name;
                    $i = 1;
                    foreach($planDateList as $pdl):
                        $existExcuse = AttendanceExcuseDay::with('excuse')->where('plan_id', $plan_id)->where('plans_date_list_id', $pdl->id)->where('active', 1)
                                        ->whereHas('excuse', function($q) use($student_id){
                                            $q->where('student_id', $student_id);
                                        })->orderBy('attendance_excuse_id', 'DESC')->get()->first();
                        $status = (isset($existExcuse->excuse->status) ? $existExcuse->excuse->status : '');
                        $statusLabel = (isset($existExcuse->excuse->status_label) ? $existExcuse->excuse->status_label : '');
                        if($status != 2):
                            $list[$plan_id]['date_lists'][$i]['id'] = $pdl->id;
                            $list[$plan_id]['date_lists'][$i]['dates'] = date('jS F, Y', strtotime($pdl->date));
                            $list[$plan_id]['date_lists'][$i]['status'] = $status;
                            $list[$plan_id]['date_lists'][$i]['status_label'] = $statusLabel;
                        endif;

                        $i++;
                    endforeach;
                endif;
            endforeach;
        endif;

        return $list;
    }

    public function awardingBodyUpdateStatus(Request $request){
        $student_id = $request->student_id;
        $student_crel_id = $request->student_crel_id;
        $row_id = (isset($request->id) && $request->id > 0 ? (int) $request->id : 0);
        $status = (isset($request->status) && !empty($request->status) ? $request->status : '');
        $remarks = (isset($request->remarks) && !empty($request->remarks) ? $request->remarks : '');
        $status = ($status == 'Reset' ? null : $status);

        $existRow = StudentAwardingBodyDetails::find($row_id);
        
        $data = [];
        $data['registration_document_verified'] = $status;
        $data['student_id'] = $student_id;
        if($remarks!="")
            $data['remarks'] = $remarks;

        if($row_id > 0 && !empty($existRow)):
            $data['updated_by'] = auth('student')->user()->id;

            $awardingBody = StudentAwardingBodyDetails::find($row_id);
            $awardingBody->fill($data);
            $changes = $awardingBody->getDirty();
            $awardingBody->save();

            if($awardingBody->wasChanged() && !empty($changes)):
                foreach($changes as $field => $value):
                    $data = [];
                    $data['student_id'] = $student_id;
                    $data['table'] = 'student_awarding_body_details';
                    $data['field_name'] = $field;
                    $data['field_value'] = $existRow->$field;
                    $data['field_new_value'] = $value;
                    $data['created_by'] = auth('student')->user()->id;

                    StudentArchive::create($data);
                endforeach;
            endif;
        else:
            $data['student_course_relation_id'] = $student_crel_id;
            $data['created_by'] = auth('student')->user()->id;

            StudentAwardingBodyDetails::create($data);
        endif;

        return response()->json(['msg' => 'Student awarding body Successfully Updated.'], 200);
    }


    public function workplacement(){
        $student_user_id = auth('student')->user()->id;
        $student = Student::with('crel', 'course')->where("student_user_id", $student_user_id)->get()->first();

        $dateWiseClassList = $this->upcommingClass($student->id);

        $courseStartDate = (isset($student->crel->course_start_date) && !empty($student->crel->course_start_date) ? date('Y-m-d', strtotime($student->crel->course_start_date)) : date('Y-m-d', strtotime($student->crel->creation->available->course_start_date)) );
        $courseId = $student->crel->creation->course_id;

        $workPlacementDetails = WorkplacementDetails::with('level_hours')->where('course_id', $courseId)
                                ->where(function($q) use($courseStartDate){
                                    $q->whereNull('end_date')->where('start_date', '<=', $courseStartDate);
                                })->orWhere(function($q) use($courseStartDate){
                                    $q->whereNotNull('end_date')->where('start_date', '<=', $courseStartDate)->where('end_date', '>=', $courseStartDate);
                                })->orderBy('id', 'DESC')->get()->first();

        $total_hours_calculations = [];
        $confirmed_hours = [];
        if($workPlacementDetails && $workPlacementDetails->id):
            $total_hours_calculations = LevelHours::with('learning_hours')->where('workplacement_details_id', $workPlacementDetails->id)->get();
            if($total_hours_calculations->count() > 0):
                foreach($total_hours_calculations as $lavelHour):
                    if(isset($lavelHour->learning_hours) && $lavelHour->learning_hours->count() > 0):
                        foreach($lavelHour->learning_hours as $learningHour):
                            $confirmedHours = StudentWorkPlacement::where('workplacement_details_id', $workPlacementDetails->id)->where('level_hours_id', $lavelHour->id)
                                                        ->where('learning_hours_id', $learningHour->id)->where('status', 'Confirmed')->sum('hours');
                            $confirmed_hours[$learningHour->id]['lavel_hours'] = $lavelHour->name;
                            $confirmed_hours[$learningHour->id]['learning_hours'] = $learningHour->name;
                            $confirmed_hours[$learningHour->id]['confirmed_hours'] = ($confirmedHours > 0 ? $confirmedHours.' Hours' : '');

                        endforeach;
                    endif;
                endforeach;
            endif;
        endif;

        return view('pages.students.frontend.dashboard.profile.workplacement', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Workplacement Details', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'datewiseClasses' => $dateWiseClassList,

            'workplacement_details' => $workPlacementDetails,
            'total_hours_calculations' => $total_hours_calculations ?? [],
            'confirmed_hours' => $confirmed_hours
        ]);
    }
}
