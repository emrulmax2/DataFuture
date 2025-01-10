<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ArrayCollectionExport;
use App\Exports\Reports\StudentDataReportBySelectionExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Agent;
use App\Models\AgentReferralCode;
use App\Models\Assign;
use App\Models\Course;
use App\Models\CourseCreationVenue;
use App\Models\Employee;
use App\Models\Group;
use App\Models\Plan;
use App\Models\Semester;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use App\Models\StudentProposedCourse;
use App\Models\TermDeclaration;
use Barryvdh\Debugbar\Facades\Debugbar;
use DebugBar\DebugBar as DebugBarDebugBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StudentDataReportController extends Controller
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
        
        return view('pages.reports.data.index', [
            'title' => 'Student Data Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Student Data Reports', 'href' => 'javascript:void(0);']
            ],
            'semesters' => $semesters,
            'courses' => $courses,
            'allStatuses' => $statuses,
            'academicYear' => AcademicYear::all()->sortByDesc('from_date'),
            'terms' => TermDeclaration::all()->sortByDesc('id'),
            'groups' => Group::all(),
        ]);
    }


    public function totalCount(Request $request) {

        //parse_str($request->form_data, $form);

        $groupParams = isset($request->group) && !empty($request->group) ? $request->group : [];

        $sorts = [];
        
        $Query = Student::orderBy('id','desc');
        $itemSelected = false;
        foreach($groupParams as $field => $value):
            $$field = (isset($value) && !empty($value) ? $value : '');

            if($$field!='') {
                $itemSelected = true;
            }
        endforeach;
        if($itemSelected==true) {
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
                if(isset($evening_weekend))
                    $myRequest->request->add(['evening_weekends' => $evening_weekend]);

                $studentsIds = $this->callTheStudentListForGroup($myRequest);
                
            if(!empty($studentsIds)): 
                $Query->whereIn('id', $studentsIds); 
            else:
                $Query->whereIn('id', [0]); 
            endif;
            
            $total_rows = $Query->count();

            $Query = $Query->get();

    
            return response()->json(['all_rows' => $total_rows, 'student_ids'=>$studentsIds],200);

        } else {
            return response()->json(['all_rows' => 0, 'student_ids'=>[]],302);
        }

    }

    protected function callTheStudentListForGroup(Request $request) {
        

        $academic_years = $request->academic_years;
        $term_declaration_ids = $request->term_declaration_ids;
        $courses = $request->courses;
        $groups = $request->groups;
        $intake_semesters = $request->intake_semesters;
        $group_student_statuses = $request->group_student_statuses;
        $student_types = $request->student_types;
        $evening_weekends = $request->evening_weekends;
        
        $studentIds = [];


        $QueryInner = StudentCourseRelation::with('activeCreation');
        $QueryInner->where('active','=',1);
        if(!empty($evening_weekends) && ($evening_weekends==0 || $evening_weekends==1))
            $QueryInner->where('full_time',$evening_weekends);
        if(!empty($academic_years) && count($academic_years)>0)
            $QueryInner->where('academic_year_id',$academic_years);
        

            $studentIds =  $QueryInner->whereHas('activeCreation', function($q) use($intake_semesters,$courses){
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

            $innerQuery = Student::with('crel');
            if(!empty($studentIds)) {
                $innerQuery->whereIn('id',$studentIds);
            }
            $studentsListByStudentType = $innerQuery->whereHas('crel', function($q) use($student_types){
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


    public function excelDownload(Request $request)
    {         
        $studentIds = explode(",",$request->studentIds);
        $studentDataSet = $request->Student;

        $StudentOtherDetailData =  $request->StudentOtherDetail;
        
        $StudentCourseRelationData  = $request->StudentCourseRelation;
        $StudentProposedCourseData  = $request->StudentProposedCourse;
        $StudentAttendanceTermStatusData  = $request->StudentAttendanceTermStatus;
        $StudentAwardingBodyDetailsData  = $request->StudentAwardingBodyDetails;
        $StudentProofOfIdData  = $request->StudentProofOfId;
        $StudentContactData  = $request->StudentContact;
        $StudentKinData  = $request->StudentKin;
        $StudentQualificationData  = $request->StudentQualification;
        $AgentReferralCodeData  = $request->AgentReferralCode;
        
        

        $StudentData = Student::with('other','crel','termStatus','termStatusLatest','course','award','nation','contact','kin','disability','quals','status','ProofOfIdLatest','qualHigest','qualHigest.previous_providers','qualHigest.qualification_type_identifiers')->whereIn('id',$studentIds)->get();

        

        $theCollection = [];
        $i=1;
        $j=0;

        $theCollection[$i][$j++] = "Regestration No";
        $theCollection[$i][$j++] = "Student Data ID";
        $theCollection[$i][$j++] = "Status";
        
        if(!empty($studentDataSet))
        foreach($studentDataSet as $key =>$value):
            if($key=="full_name"){
                $theCollection[$i][$j++] = "Title";
                $theCollection[$i][$j++] = "First Name";
                $theCollection[$i][$j++] = "Last Name";
            }elseif($key=="ssn_no"){
                $theCollection[$i][$j++] = "SSN";

            }elseif($key=="uhn_no"){

                $theCollection[$i][$j++] = "UHN No ";
            }else  
                $theCollection[$i][$j++] = str_replace('Id','',ucwords(str_replace('_',' ', $key)));
            
        endforeach; 


        if(!empty($StudentOtherDetailData))
        foreach($StudentOtherDetailData as $key =>$value):
           
            $theCollection[$i][$j++] = str_replace('Id','',ucwords(str_replace('_',' ', $key)));
        endforeach; 

        if(!empty($StudentCourseRelationData))
        foreach($StudentCourseRelationData as $key =>$value):
            $theCollection[$i][$j++] = str_replace('Id','',ucwords(str_replace('_',' ', $key)));
        endforeach; 

        if(!empty($StudentProposedCourseData))
        foreach($StudentProposedCourseData as $key =>$value):
            if($key=="full_time"){
                $theCollection[$i][$j++] = "Evening/Weekend";
            }else
            $theCollection[$i][$j++] = str_replace('Id','',ucwords(str_replace('_',' ', $key)));
        endforeach; 
        
        if(!empty($StudentAttendanceTermStatusData))
        foreach($StudentAttendanceTermStatusData as $key =>$value):
            
            $theCollection[$i][$j++] = str_replace('Id','',ucwords(str_replace('_',' ', $key)));
            
        endforeach; 

        if(!empty($StudentAwardingBodyDetailsData))
        foreach($StudentAwardingBodyDetailsData as $key =>$value):
            
            $theCollection[$i][$j++] = str_replace('Id','',ucwords(str_replace('_',' ', $key)));
        endforeach; 

        if(!empty($StudentProofOfIdData))
        foreach($StudentProofOfIdData as $key =>$value):
            
            $theCollection[$i][$j++] = ucwords(str_replace('_',' ', $key));
        endforeach; 
        
        if(!empty($StudentContactData))
        foreach($StudentContactData as $key =>$value):
            if($key=="term_time_address_id"){
                $theCollection[$i][$j++] = "Term Address Line 1";
                $theCollection[$i][$j++] = "Term Address Line 2";
                $theCollection[$i][$j++] = "Term State";
                $theCollection[$i][$j++] = "Term Post Code";
                $theCollection[$i][$j++] = "Term City";
                $theCollection[$i][$j++] = "Term Country";
            }elseif($key=="permanent_address_id"){
                $theCollection[$i][$j++] = "Permanent Address Line 1";
                $theCollection[$i][$j++] = "Permanent Address Line 2";
                $theCollection[$i][$j++] = "Permanent State";
                $theCollection[$i][$j++] = "Permanent Post Code";
                $theCollection[$i][$j++] = "Permanent City";
                $theCollection[$i][$j++] = "Permanent Country";
            }else
                $theCollection[$i][$j++] = str_replace('Id','',ucwords(str_replace('_',' ', $key)));
        endforeach; 

        if(!empty($StudentKinData))
        foreach($StudentKinData as $key =>$value):
            if($key=="address_id"){
                $theCollection[$i][$j++] = "Kin Address Line 1";
                $theCollection[$i][$j++] = "Kin Address Line 2";
                $theCollection[$i][$j++] = "Kin State";
                $theCollection[$i][$j++] = "Kin Post Code";
                $theCollection[$i][$j++] = "Kin City";
                $theCollection[$i][$j++] = "Kin Country";
            } else
                $theCollection[$i][$j++] = str_replace('Id','',ucwords(str_replace('_',' ', $key)));
        endforeach; 
        
        
        if(!empty($StudentQualificationData))
        foreach($StudentQualificationData as $key =>$value):
            if($key=="highest_qualification_on_Entry"){
                $theCollection[$i][$j++] = "Highest Qualification on Entry (QUALENT3)";
            } else if($key=="qualification_details") {
                $theCollection[$i][$j++] = "Prev. Qualification Awarding Body";
                $theCollection[$i][$j++] = "Prev. Provider Name";
                $theCollection[$i][$j++] = "Prev. Qualification Higest Academic Degree";
                $theCollection[$i][$j++] = "Prev. Qualification Type";
                $theCollection[$i][$j++] = "Prev. Qualification Subjects";
                $theCollection[$i][$j++] = "Prev. Qualification Result";
                $theCollection[$i][$j++] = "Prev. Qualification Award Date";
            }else 
                $theCollection[$i][$j++] = str_replace('Id','',ucwords(str_replace('_',' ', $key)));
        endforeach; 

        if(!empty($AgentReferralCodeData))
        foreach($AgentReferralCodeData as $key =>$value):
            
            $theCollection[$i][$j++] = ucwords(str_replace('_',' ', $key));
        endforeach; 

        $returnData = [];

        $row = 2;

        if(!empty($StudentData)):
            
            foreach($StudentData as $student):
                $j=0;

                $theCollection[$row][$j++] = $student->registration_no;
                $theCollection[$row][$j++] = $student->id;
                $theCollection[$row][$j++] = $student->status->name;
                
                if(!empty($studentDataSet))
                    foreach($studentDataSet as $key =>$value):
                        if(strpos( $key, '_id') !== false) {
                            $rel = key($value);
                            $theCollection[$row][$j++] = (isset($student->$rel)) ? $student->$rel->name : ""; 
                        }else {
                            
                            switch ($key) {
                                case "full_name":

                                    $theCollection[$row][$j++] = (isset($student->title->name)) ? $student->title->name : "";  
                                    $theCollection[$row][$j++] = $student->first_name;  
                                    $theCollection[$row][$j++] = $student->last_name;  
                                    break;
                                case 'DF_SID_Number':
                                    $theCollection[$row][$j++] = $student->df_sid_number;  
                                    break;
                                default:
                                    $key = strtolower($key);
                                    $theCollection[$row][$j++] = $student->$key;  
                              }
                        }
                    endforeach; 


                    if(!empty($StudentOtherDetailData))
                    foreach($StudentOtherDetailData as $key =>$value):
                        if(strpos( $key, '_id') !== false) {
                            $rel = key($value);
                            $theCollection[$row][$j++] = (isset($student->other->$rel)) ?$student->other->$rel->name : "";
                        }else {
                            $theCollection[$row][$j++] = (isset($student->other)) ? $student->other->$key: "";
                        }

                    endforeach; 
            
                    if(!empty($StudentCourseRelationData))
                    foreach($StudentCourseRelationData as $key =>$value):
                        if(strpos( $key, '_id') !== false) {
                            $rel = key($value);
                            $theCollection[$row][$j++] = (isset($student->crel->$rel)) ?$student->crel->$rel->name : "";
                        }else {

                            switch ($key) {
                                case "course_start_date":

                                    $theCollection[$row][$j++] = (!empty($student->crel->$key)) ? $student->crel->$key : $student->crel->creation->available->course_start_date; 
                                    
                                  break;
                                case "course_end_date":

                                    $theCollection[$row][$j++] = (!empty($student->crel->$key)) ? $student->crel->$key : $student->crel->creation->available->course_end_date; 
                                  
                                  break;
                                case 'awarding_body':
                                    $theCollection[$row][$j++] = (isset($student->crel->creation->course->body->name) ? $student->crel->creation->course->body->name : 'Unknown');
                                  break;
                                default:
                                    $theCollection[$row][$j++] = (isset($student->crel)) ? $student->crel->$key : "";
                            }
                            
                        }

                    endforeach; 
            
                    if(!empty($StudentProposedCourseData))
                    foreach($StudentProposedCourseData as $key =>$value):
                        if(strpos( $key, '_id') !== false) {
                            $rel = key($value);
                            $theCollection[$row][$j++] = (isset($student->course->$rel)) ?$student->course->$rel->name : ""; $student->course->$rel->name;
                        }else {
                            
                            switch ($key) {
                                case "full_time":

                                    $theCollection[$row][$j++] = (($student->course->$key)==1) ? 'Yes' : 'No'; 
                                    
                                  break;
                                case "SLC_course_code":

                                    $currentCourse = StudentProposedCourse::where('student_id',$student->id)
                                    ->where('student_course_relation_id',$student->crel->id)
                                    ->get()
                                    ->first();
                                    
                                    $CourseCreationVenue = CourseCreationVenue::where('course_creation_id',$currentCourse->course_creation_id)->where('venue_id', $currentCourse->venue_id)->get()->first();
                                    
                                    $theCollection[$row][$j++] = $CourseCreationVenue->slc_code; 

                                  break;
                                
                                default:
                                    $key = strtolower($key);
                                    $theCollection[$row][$j++] = (isset($student->course)) ? $student->course->$key : "";
                            }
                        }
                    endforeach; 
            
                    if(!empty($StudentAttendanceTermStatusData))
                    foreach($StudentAttendanceTermStatusData as $key =>$value):
                        if(strpos( $key, '_id') !== false) {
                            
                            $rel = key($value);
                            $theCollection[$row][$j++] = (isset($student->termStatusLatest->$rel)) ?$student->termStatusLatest->$rel->name : "";
                        }else {
                            // if($student->id == 16968) {
                            //     dd($student->termStatus);
                            // }
                            $theCollection[$row][$j++] = (isset($student->termStatusLatest)) ? $student->termStatusLatest->$key : "";
                        }
                    endforeach; 
            
                    if(!empty($StudentAwardingBodyDetailsData))
                    foreach($StudentAwardingBodyDetailsData as $key =>$value):
                        if(strpos( $key, '_id') !== false) {
                            $rel = key($value);
                            
                            $theCollection[$row][$j++] = (isset($student->crel->abody->$rel)) ? $student->crel->abody->$rel->name : "";
                        }else {
                                
                            switch ($key) {
                                case "awarding_body_reference":

                                    $theCollection[$row][$j++] = (isset($student->crel->abody)) ? $student->crel->abody->reference : "";
                                    
                                  break;
                                
                                default:
                                    $key = strtolower($key);
                                    $theCollection[$row][$j++] = (isset($student->crel->abody)) ? $student->crel->abody->$key : "";
                            }

                            

                        }
                    endforeach; 

                    if(!empty($StudentProofOfIdData))
                    foreach($StudentProofOfIdData as $key =>$value):
                        $theCollection[$row][$j++] = (isset($student->ProofOfIdLatest)) ? $student->ProofOfIdLatest->$key : "";
                    endforeach; 
                    
                    if(!empty($StudentContactData))
                    foreach($StudentContactData as $key =>$value):
                        
                            switch ($key) {
                                case "term_time_address_id":

                                    $theCollection[$row][$j++] = (isset($student->contact->termaddress)) ? $student->contact->termaddress->address_line_1 : "";;
                                    $theCollection[$row][$j++] = (isset($student->contact->termaddress)) ? $student->contact->termaddress->address_line_2 : "";;
                                    $theCollection[$row][$j++] = (isset($student->contact->termaddress)) ? $student->contact->termaddress->state : "";
                                    $theCollection[$row][$j++] = (isset($student->contact->termaddress)) ? $student->contact->termaddress->post_code : "";
                                    $theCollection[$row][$j++] = (isset($student->contact->termaddress)) ? $student->contact->termaddress->city : "";
                                    $theCollection[$row][$j++] = (isset($student->contact->termaddress)) ? $student->contact->termaddress->country : "";
                                    
                                  break;

                                case "permanent_address_id":

                                    $theCollection[$row][$j++] = (isset($student->contact->permaddress)) ? $student->contact->permaddress->address_line_1 : "";;
                                    $theCollection[$row][$j++] = (isset($student->contact->permaddress)) ? $student->contact->permaddress->address_line_2 : "";;
                                    $theCollection[$row][$j++] = (isset($student->contact->permaddress)) ? $student->contact->permaddress->state : "";
                                    $theCollection[$row][$j++] = (isset($student->contact->permaddress)) ? $student->contact->permaddress->post_code : "";
                                    $theCollection[$row][$j++] = (isset($student->contact->permaddress)) ? $student->contact->permaddress->city : "";
                                    $theCollection[$row][$j++] = (isset($student->contact->permaddress)) ? $student->contact->permaddress->country : "";
                                    
                                  break;
                                
                                default:
                                    $key = strtolower($key);
                                    $theCollection[$row][$j++] = (isset($student->contact)) ? $student->contact->$key : "";
                            }

                    endforeach; 

                    if(!empty($StudentKinData))
                    foreach($StudentKinData as $key =>$value):

                        switch ($key) {
                            case "kins_relation_id":
                                $rel = key($value);
                                $theCollection[$row][$j++] = (isset($student->kin->$rel)) ? $student->kin->$rel->name : "";
                                break;

                            case "address_id":
                                $theCollection[$row][$j++] = (isset($student->kin->address)) ? $student->kin->address->address_line_1 : "";;
                                $theCollection[$row][$j++] = (isset($student->kin->address)) ? $student->kin->address->address_line_2 : "";;
                                $theCollection[$row][$j++] = (isset($student->kin->address)) ? $student->kin->address->state : "";
                                $theCollection[$row][$j++] = (isset($student->kin->address)) ? $student->kin->address->post_code : "";
                                $theCollection[$row][$j++] = (isset($student->kin->address)) ? $student->kin->address->city : "";
                                $theCollection[$row][$j++] = (isset($student->kin->address)) ? $student->kin->address->country : "";
                                break;

                            default:
                                $key = strtolower($key);
                                $theCollection[$row][$j++] = (isset($student->kin)) ? $student->kin->$key : "";
                        }
                    endforeach;
                            
                    if(!empty($StudentQualificationData))
                    foreach($StudentQualificationData as $key =>$value):
                        
                        switch ($key) {
                            case "highest_qualification_on_Entry":
                                $rel = key($value);
                                $theCollection[$row][$j++] = (isset($student->qualHigest->highest_qualification_on_entries)) ? $student->qualHigest->highest_qualification_on_entries->name : "";
                                break;

                            case "qualification_details":

                                $theCollection[$row][$j++] = (isset($student->qualHigest->awarding_body)) ? $student->qualHigest->awarding_body : "";
                                $theCollection[$row][$j++] = (isset($student->qualHigest->previous_providers)) ? $student->qualHigest->previous_providers->name : "";
                                $theCollection[$row][$j++] = (isset($student->qualHigest->highest_academic)) ? $student->qualHigest->highest_academic : "";
                                $theCollection[$row][$j++] = (isset($student->qualHigest->qualification_type_identifiers)) ? $student->qualHigest->qualification_type_identifiers->name : "";
                                $theCollection[$row][$j++] = (isset($student->qualHigest->subjects)) ? $student->qualHigest->subjects : "";
                                $theCollection[$row][$j++] = (isset($student->qualHigest->result)) ? $student->qualHigest->result : "";
                                $theCollection[$row][$j++] = (isset($student->qualHigest->degree_award_date)) ? $student->qualHigest->degree_award_date : "";
                                break;

                            default:
                                $key = strtolower($key);
                                $theCollection[$row][$j++] = (isset($student->qualHigest)) ? $student->qualHigest->$key : "";
                        }
                    endforeach; 

                    if(!empty($AgentReferralCodeData))
                    foreach($AgentReferralCodeData as $key =>$value):
                        
                        switch ($key) {
                            
                            case "referral_name":

                                $theCollection[$row][$j++] = isset($student->referral_info) ? $student->referral_info->full_name : "";

                            default:
                                $key = strtolower($key);
                                $theCollection[$row][$j++] = (isset($student->qualHigest)) ? $student->qualHigest->$key : "";
                        }
                        
                    endforeach; 
                        
                $row++;
            endforeach;
        endif;

        //dd($theCollection);

        return Excel::download(new ArrayCollectionExport($theCollection), 'student_data_report.xlsx');
                
        //return Excel::download(new StudentDataReportBySelectionExport($returnData), 'student_data_report.xlsx');
    }
}
