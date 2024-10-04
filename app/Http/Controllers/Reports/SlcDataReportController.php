<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceCode;
use App\Models\CourseCreationVenue;
use App\Models\Semester;
use App\Models\SlcAttendance;
use App\Models\SlcCoc;
use App\Models\SlcRegistration;
use App\Models\SlcRegistrationStatus;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use App\Models\StudentProposedCourse;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SlcDataReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {

        return view('pages.reports.slc.index', [
            'title' => 'SLC REPORT Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'SLC Reports', 'href' => 'javascript:void(0);']
            ],
            'attendanceCodes' => AttendanceCode::orderBy('id', 'DESC')->get(),
            'termDeclarations' => TermDeclaration::orderBy('id', 'DESC')->get(),
            'academicYears' => AcademicYear::orderBy('id', 'DESC')->get(),
            'slcRegistrationStatuses' => SlcRegistrationStatus::orderBy('name', 'ASC')->get(),
        ]);
    }

    public function SLCAttendanceExcelDownload(Request $request)
    {         
        
        $dates = (isset($request->date_range) && !empty($request->date_range) ? explode(' - ', $request->date_range) : []);
        $attendance_code_id = (isset($request->attendance_code_id) && !empty($request->attendance_code_id) ? $request->attendance_code_id : '');
        $attendance_year = (isset($request->attendance_year) && !empty($request->attendance_year) ? $request->attendance_year : '');
        $session_term = (isset($request->session_term) && !empty($request->session_term) ? $request->session_term : []);
        $from_date = isset($dates[0]) && !empty($dates[0]) ? date('Y-m-d', strtotime($dates[0])) : date('Y-m-d');
        $to_date = isset($dates[1]) && !empty($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : date('Y-m-d');

        

        $queryInner = SlcAttendance::with('student',
            'student.termStatus',
            'student.status',
            'registration',
            'registration.slcAgreement',
            'registration.slcAgreement.installments',
            'code',
            'term',
            'crel',
            'crel.abody',
            'crel.creation',
            'crel.creation.course',
            'crel.creation.semester',
            'crel.propose'
        )->whereBetween('confirmation_date',[$from_date, $to_date]);


        if($attendance_code_id)
            $queryInner->where('attendance_code_id',$attendance_code_id);
        if($attendance_year)
            $queryInner->where('attendance_year',$attendance_year);
        if($session_term)
            $queryInner->whereIn('session_term',$session_term);


        $StudentSLCData = $queryInner->orderBy('id', 'DESC')->get();



        
        $theCollection = [];
        $i=1;
        $j=0;
        $theCollection[$i][$j++] = "Regestration No";
        $theCollection[$i][$j++] = "SSN";
        $theCollection[$i][$j++] = "Name";
        $theCollection[$i][$j++] = "Date Of Birth";
        $theCollection[$i][$j++] = "Course";
        $theCollection[$i][$j++] = "Semester";
        $theCollection[$i][$j++] = "Class Startdate";
        $theCollection[$i][$j++] = "Class Enddate";
        $theCollection[$i][$j++] = "Status";
        $theCollection[$i][$j++] = "Status Change Date";
        $theCollection[$i][$j++] = "Awarding Body";
        $theCollection[$i][$j++] = "SLC Course Code";
        $theCollection[$i][$j++] = "Attendance Year";
        $theCollection[$i][$j++] = "Code";
        $theCollection[$i][$j++] = "Term";
        $theCollection[$i][$j++] = "Claim Amount";
        $theCollection[$i][$j++] = "Confirmation Date";
        

        $row = 2;
        if(!empty($StudentSLCData)):
            
            foreach($StudentSLCData as $slc):
                $slc_crel = $slc->crel;
                $j=0;
                $student = $slc->student; 

                $theCollection[$row][$j++] = $student->registration_no;
                $theCollection[$row][$j++] = $student->ssn_no ?? '';
                $theCollection[$row][$j++] = $student->full_name;  
                $theCollection[$row][$j++] = $student->date_of_birth;  
                
                $theCollection[$row][$j++] = (isset($slc_crel->creation->course->name)  && !empty($slc_crel->creation->course->name)) ? $slc_crel->creation->course->name : "";  
                $theCollection[$row][$j++] = (isset($slc_crel->creation->semester->name) && !empty($slc_crel->creation->semester->name)) ? $slc_crel->creation->semester->name : "";  
                $theCollection[$row][$j++] = (isset($slc_crel->course_start_date) && !empty($slc_crel->course_start_date)) ? $slc_crel->course_start_date : '' ;  
                $theCollection[$row][$j++] = (isset($slc_crel->course_end_date) && !empty($slc_crel->course_end_date)) ? $slc_crel->course_end_date : '' ;  
                
                $theCollection[$row][$j++] = isset($student->status) ? $student->status->name : ""; 
                $theCollection[$row][$j++] = (isset($student->termStatus->status_change_date)&& !empty($slc_crel->course_end_date)) ? $student->termStatus->status_change_date : ''; 
                $theCollection[$row][$j++] = ((isset($slc_crel->abody->reference)&& !empty($slc_crel->abody->reference)) ? $slc_crel->abody->reference : '');

                
                $theCollection[$row][$j++] = (isset($slc_crel->propose)&& !empty($slc_crel->propose)) ?  $slc_crel->propose->slc_code : ''; 

                $theCollection[$row][$j++] = (isset($slc->attendance_year)&& !empty($slc->attendance_year)) ? $slc->attendance_year. " Year" : ''; 
                $theCollection[$row][$j++] = (isset($slc->code->code)&& !empty($slc->code->code)) ? $slc->code->code : ''; 
                $theCollection[$row][$j++] = (isset($slc->session_term)&& !empty($slc->session_term)) ? 'Term '.$slc->session_term : '';  
                $claimAmount = 0;
                if(isset($slc->registration->slcAgreement))
                foreach($slc->registration->slcAgreement as $agreement):
                    if(isset($agreement->installments) && $agreement->installments->count() > 0):
                        foreach($agreement->installments as $inst):
                            $claimAmount += $inst->amount;
                        endforeach;
                    endif;
                endforeach;
                $theCollection[$row][$j++] = (isset($claimAmount)&& !empty($claimAmount)) ? $claimAmount :'';  
                
                $theCollection[$row][$j++] = (isset($slc->confirmation_date)&& !empty($slc->confirmation_date)) ? $slc->confirmation_date :'';
                $row++;
            endforeach;

        endif;

        
        return Excel::download(new ArrayCollectionExport($theCollection), 'slc_attendace_report.xlsx');
                
       
    }
    public function SlcRegistrationHistoryExcelDownload(Request $request)
    {         
        
        $dates = (isset($request->date_range) && !empty($request->date_range) ? explode(' - ', $request->date_range) : []);
        
        $academic_year_id = (isset($request->academic_year_id) && !empty($request->academic_year_id) ? $request->academic_year_id : '');
        
        $registration_year = (isset($request->registration_year) && !empty($request->registration_year) ? $request->registration_year : '');
        
        $slc_registration_status_id = (isset($request->slc_registration_status_id) && !empty($request->slc_registration_status_id) ? $request->slc_registration_status_id : '');
        
        $from_date = isset($dates[0]) && !empty($dates[0]) ? date('Y-m-d', strtotime($dates[0])) : date('Y-m-d');
        $to_date = isset($dates[1]) && !empty($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : date('Y-m-d');

        $queryInner = SlcRegistration::with('student','student.termStatus',
            'student.status',
            'regStatus',
            'year',
            'cocs',
            'crel',
            'crel.abody',
            'crel.creation',
            'crel.creation.course',
            'crel.creation.semester',
            'crel.propose'
        )->whereBetween('confirmation_date',[$from_date, $to_date]);

        if($academic_year_id)
            $queryInner->where('academic_year_id',$academic_year_id);

        if($registration_year)
            $queryInner->where('registration_year',$registration_year);

        if($slc_registration_status_id)
            $queryInner->where('slc_registration_status_id',$slc_registration_status_id);

        $StudentSLCData = $queryInner->orderBy('id', 'DESC')->get();
        
        $theCollection = [];
        $i=1;
        $j=0;
        $theCollection[$i][$j++] = "Regestration No";
        $theCollection[$i][$j++] = "SSN";
        $theCollection[$i][$j++] = "Name";
        $theCollection[$i][$j++] = "Date Of Birth";
        $theCollection[$i][$j++] = "Course";
        $theCollection[$i][$j++] = "Semester";
        $theCollection[$i][$j++] = "Class Startdate";
        $theCollection[$i][$j++] = "Class Enddate";
        $theCollection[$i][$j++] = "Status";
        $theCollection[$i][$j++] = "Status Change Date";
        $theCollection[$i][$j++] = "Awarding Body";
        $theCollection[$i][$j++] = "SLC Course Code";
        $theCollection[$i][$j++] = "Registration Status";
        $theCollection[$i][$j++] = "Academic Year";
        $theCollection[$i][$j++] = "Registration Year";
        $theCollection[$i][$j++] = "Confirmation Date";
        
        $row = 2;
        if(!empty($StudentSLCData)):
            
            foreach($StudentSLCData as $slc):
                $slc_crel = $slc->crel;
                $j=0;
                $student = $slc->student; 
                $theCollection[$row][$j++] = $student->registration_no;
                $theCollection[$row][$j++] = $student->ssn_no ?? '';
                $theCollection[$row][$j++] = $student->full_name;  
                $theCollection[$row][$j++] = $student->date_of_birth;  
                
                $theCollection[$row][$j++] = (isset($slc_crel->creation->course->name)  && !empty($slc_crel->creation->course->name)) ? $slc_crel->creation->course->name : "";  
                $theCollection[$row][$j++] = (isset($slc_crel->creation->semester->name) && !empty($slc_crel->creation->semester->name)) ? $slc_crel->creation->semester->name : "";  
                $theCollection[$row][$j++] = (isset($slc_crel->course_start_date) && !empty($slc_crel->course_start_date)) ? $slc_crel->course_start_date : '' ;  
                $theCollection[$row][$j++] = (isset($slc_crel->course_end_date) && !empty($slc_crel->course_end_date)) ? $slc_crel->course_end_date : '' ;  
                
                $theCollection[$row][$j++] = isset($student->status) ? $student->status->name : ""; 
                $theCollection[$row][$j++] = (isset($student->termStatus->status_change_date)&& !empty($slc_crel->course_end_date)) ? $student->termStatus->status_change_date : ''; 
                $theCollection[$row][$j++] = ((isset($slc_crel->abody->reference)&& !empty($slc_crel->abody->reference)) ? $slc_crel->abody->reference : '');

                
                $theCollection[$row][$j++] = (isset($slc_crel->propose)&& !empty($slc_crel->propose)) ?  $slc_crel->propose->slc_code : ''; 

                $theCollection[$row][$j++] = (isset($slc->regStatus)&& !empty($slc->regStatus)) ? $slc->regStatus->name : ''; 
                $theCollection[$row][$j++] = (isset($slc->year->name)&& !empty($slc->year->name)) ? $slc->year->name : ''; 
                $theCollection[$row][$j++] = (isset($slc->registration_year)&& !empty($slc->registration_year)) ? "Year ".$slc->registration_year : ''; 
                $theCollection[$row][$j++] = (isset($slc->confirmation_date)&& !empty($slc->confirmation_date)) ? $slc->confirmation_date :''; 
                
                
                $row++;
            endforeach;
        endif;

        
        return Excel::download(new ArrayCollectionExport($theCollection), 'slc_attendace_report.xlsx');
                
       
    }

    public function SlcCocHistoryExcelDownload(Request $request)
    {         
        
        $dates = (isset($request->date_range) && !empty($request->date_range) ? explode(' - ', $request->date_range) : []);
        $coc_type = (isset($request->coc_type) && !empty($request->coc_type) ? $request->coc_type : '');
        $actioned = (isset($request->actioned) && !empty($request->actioned) ? $request->actioned : '');
        $from_date = isset($dates[0]) && !empty($dates[0]) ? date('Y-m-d', strtotime($dates[0])) : date('Y-m-d');
        $to_date = isset($dates[1]) && !empty($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : date('Y-m-d');

        

        $queryInner = SlcCoc::with('student','student.termStatus',
                        'student.status',
                        'crel',
                        'crel.abody',
                        'crel.creation',
                        'crel.creation.course',
                        'crel.creation.semester',
                        'crel.propose'
                    )->whereBetween('confirmation_date',[$from_date, $to_date]);

        if($coc_type)
            $queryInner->where('coc_type',$coc_type);
        if($actioned)
            $queryInner->where('actioned',$actioned);


        $StudentSLCData = $queryInner->orderBy('id', 'DESC')->get();



        
        $theCollection = [];
        $i=1;
        $j=0;
        $theCollection[$i][$j++] = "Regestration No";
        $theCollection[$i][$j++] = "SSN";
        $theCollection[$i][$j++] = "Name";
        $theCollection[$i][$j++] = "Date Of Birth";
        $theCollection[$i][$j++] = "Course";
        $theCollection[$i][$j++] = "Semester";
        $theCollection[$i][$j++] = "Class Startdate";
        $theCollection[$i][$j++] = "Class Enddate";
        $theCollection[$i][$j++] = "Status";
        $theCollection[$i][$j++] = "Status Change Date";
        $theCollection[$i][$j++] = "Awarding Body";
        $theCollection[$i][$j++] = "SLC Course Code";
        $theCollection[$i][$j++] = "Type Of COC";
        $theCollection[$i][$j++] = "Actioned";
        $theCollection[$i][$j++] = "Confirmation Date";
        

        $row = 2;
        if(!empty($StudentSLCData)):
            
            foreach($StudentSLCData as $slc):
                $slc_crel = $slc->crel;
                $j=0;
                $student = $slc->student; 

                $theCollection[$row][$j++] = $student->registration_no;
                $theCollection[$row][$j++] = $student->ssn_no ?? '';
                $theCollection[$row][$j++] = $student->full_name;  
                $theCollection[$row][$j++] = $student->date_of_birth;  
                
                $theCollection[$row][$j++] = (isset($slc_crel->creation->course->name)  && !empty($slc_crel->creation->course->name)) ? $slc_crel->creation->course->name : "";  
                $theCollection[$row][$j++] = (isset($slc_crel->creation->semester->name) && !empty($slc_crel->creation->semester->name)) ? $slc_crel->creation->semester->name : "";  
                $theCollection[$row][$j++] = (isset($slc_crel->course_start_date) && !empty($slc_crel->course_start_date)) ? $slc_crel->course_start_date : '' ;  
                $theCollection[$row][$j++] = (isset($slc_crel->course_end_date) && !empty($slc_crel->course_end_date)) ? $slc_crel->course_end_date : '' ;  
                
                $theCollection[$row][$j++] = isset($student->status) ? $student->status->name : ""; 
                $theCollection[$row][$j++] = (isset($student->termStatus->status_change_date)&& !empty($slc_crel->course_end_date)) ? $student->termStatus->status_change_date : ''; 
                $theCollection[$row][$j++] = ((isset($slc_crel->abody->reference)&& !empty($slc_crel->abody->reference)) ? $slc_crel->abody->reference : '');

                $theCollection[$row][$j++] = (isset($slc_crel->propose)&& !empty($slc_crel->propose)) ?  $slc_crel->propose->slc_code : ''; 

                $theCollection[$row][$j++] = (isset($slc->coc_type)&& !empty($slc->coc_type)) ? $slc->coc_type : ''; 
                $theCollection[$row][$j++] = (isset($slc->actioned)&& !empty($slc->actioned)) ? $slc->actioned : ''; 
                $theCollection[$row][$j++] = (isset($slc->confirmation_date)&& !empty($slc->confirmation_date)) ? $slc->confirmation_date :''; 
                $row++;
            endforeach;
        endif;

        
        return Excel::download(new ArrayCollectionExport($theCollection), 'slc_coc_history_report.xlsx');
                
       
    }
}
