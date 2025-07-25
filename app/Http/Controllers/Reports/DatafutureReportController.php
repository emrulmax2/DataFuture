<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseBaseDatafutures;
use App\Models\CourseCreationInstance;
use App\Models\CourseModule;
use App\Models\InstanceTerm;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\ReasonForEndingCourseSession;
use App\Models\SessionStatus;
use App\Models\Student;
use App\Models\StudentAttendanceTermStatus;
use App\Models\StudentAward;
use App\Models\StudentCourseRelation;
use App\Models\StudentModuleInstanceDatafuture;
use App\Models\StudentProposedCourse;
use App\Models\StudentStuloadInformation;
use App\Models\StudentTermStuload;
use App\Models\StudyMode;
use App\Models\TermDeclaration;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use XMLWriter;

class DatafutureReportController extends Controller
{
    public function getSingleStudentXml(Request $request){
        $student_id = $request->student_id;
        $course_id = $request->course_id;
        $student_course_relation_id = $request->student_course_relation_id;

        $term_declaration_ids = (isset($request->term_declaration_id) && !empty($request->term_declaration_id) ? $request->term_declaration_id : []);
        $from_date = (isset($request->from_date) && !empty($request->from_date) ? date('Y-m-d', strtotime($request->from_date)) : '');
        $to_date = (isset($request->to_date) && !empty($request->to_date) ? date('Y-m-d', strtotime($request->to_date)) : '');

        $dateRanges = [];
        if(!empty($term_declaration_ids)):
            $i = 1;
            foreach($term_declaration_ids as $id):
                $term = TermDeclaration::find($id);
                if((isset($term->start_date) && !empty($term->start_date)) && (isset($term->end_date) && !empty($term->end_date))):
                    $dateRanges[$i]['start'] = date('Y-m-d', strtotime($term->start_date));
                    $dateRanges[$i]['end'] = date('Y-m-d', strtotime($term->end_date));
                    $i++;
                endif;
            endforeach;
        elseif(!empty($from_date) && !empty($to_date)):
            $dateRanges[1]['start'] = date('Y-m-d', strtotime($from_date));
            $dateRanges[1]['end'] = date('Y-m-d', strtotime($to_date));
        endif;

        $student_ids = [$student_id];
        $course_ids = [];
        if(!empty($dateRanges)):
            $whereRaw = "";
            foreach($dateRanges as $date):
                $FROM_DATE = $date['start'];
                $TO_DATE = $date['end'];
                $whereRaw .= (!empty($whereRaw) ? " OR " : '');
                $whereRaw .= " (
                    (('$FROM_DATE' BETWEEN periodstart AND periodend) OR ('$TO_DATE' BETWEEN periodstart AND periodend)) 
                    OR 
                    ((periodstart BETWEEN '$FROM_DATE' AND '$TO_DATE') OR (periodend BETWEEN '$FROM_DATE' AND '$TO_DATE'))
                ) ";
            endforeach;
            $stuloads = StudentStuloadInformation::whereRaw("(".$whereRaw.")")->where('student_id', $student_id)->orderBy('student_id', 'ASC')->get();

            if($stuloads->count() > 0):
                $student_course_relation_ids = $stuloads->pluck('student_course_relation_id')->unique()->toArray();
                $course_ids = DB::table('student_course_relations as scr')
                            ->select('cc.course_id')
                            ->leftJoin('course_creations as cc', 'cc.id', 'scr.course_creation_id')
                            ->whereIn('scr.id', $student_course_relation_ids)
                            ->get()->pluck('course_id')->unique()->toArray();
            endif;
        endif;

        $XMLDATA = $this->generateXml($course_ids, $student_ids, $dateRanges);
        if(!empty($XMLDATA)):
            $XMLDATA = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $XMLDATA);
            $XML = new XMLWriter();
            $XML->openMemory();
            $XML->startDocument('1.0', 'UTF-8');
                $XML->writeRaw($XMLDATA);
            $XML->endDocument();

            $HEADERS = [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="Data_Future.xml"',
            ];
            $response = new Response($XML->outputMemory(), 200, $HEADERS);

            return $response;
        else:
            return response()->json(['msg' => 'Data not found!'], 304);
        endif;
    }

    public function getMultipleStudentXml(Request $request){
        $term_declaration_ids = (isset($request->term_declaration_id) && !empty($request->term_declaration_id) ? $request->term_declaration_id : []);
        $from_date = (isset($request->from_date) && !empty($request->from_date) ? date('Y-m-d', strtotime($request->from_date)) : '');
        $to_date = (isset($request->to_date) && !empty($request->to_date) ? date('Y-m-d', strtotime($request->to_date)) : '');

        $dateRanges = [];
        if(!empty($term_declaration_ids)):
            $i = 1;
            foreach($term_declaration_ids as $id):
                $term = TermDeclaration::find($id);
                if((isset($term->start_date) && !empty($term->start_date)) && (isset($term->end_date) && !empty($term->end_date))):
                    $dateRanges[$i]['start'] = date('Y-m-d', strtotime($term->start_date));
                    $dateRanges[$i]['end'] = date('Y-m-d', strtotime($term->end_date));
                    $i++;
                endif;
            endforeach;
        elseif(!empty($from_date) && !empty($to_date)):
            $dateRanges[1]['start'] = date('Y-m-d', strtotime($from_date));
            $dateRanges[1]['end'] = date('Y-m-d', strtotime($to_date));
        endif;

        $student_ids = [];
        $course_ids = [];
        //DB::enableQueryLog();
        if(!empty($dateRanges)):
            $whereRaw = "";
            foreach($dateRanges as $date):
                $FROM_DATE = $date['start'];
                $TO_DATE = $date['end'];
                $whereRaw .= (!empty($whereRaw) ? " OR " : '');
                $whereRaw .= " (
                    (('$FROM_DATE' BETWEEN periodstart AND periodend) OR ('$TO_DATE' BETWEEN periodstart AND periodend)) 
                    OR 
                    ((periodstart BETWEEN '$FROM_DATE' AND '$TO_DATE') OR (periodend BETWEEN '$FROM_DATE' AND '$TO_DATE'))
                ) ";
            endforeach;
            $stuloads = StudentStuloadInformation::whereRaw("(".$whereRaw.")")->orderBy('student_id', 'ASC')->get();
            //dd(DB::getQueryLog());

            if($stuloads->count() > 0):
                $student_ids = $stuloads->pluck('student_id')->unique()->toArray();
                $student_course_relation_ids = $stuloads->pluck('student_course_relation_id')->unique()->toArray();
                $course_ids = DB::table('student_course_relations as scr')
                            ->select('cc.course_id')
                            ->leftJoin('course_creations as cc', 'cc.id', 'scr.course_creation_id')
                            ->whereIn('scr.id', $student_course_relation_ids)
                            ->get()->pluck('course_id')->unique()->toArray();
            endif;
        endif;

        $XMLDATA = $this->generateXml($course_ids, $student_ids, $dateRanges);
        if(!empty($XMLDATA)):
            $XMLDATA = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $XMLDATA);
            $XML = new XMLWriter();
            $XML->openMemory();
            $XML->startDocument('1.0', 'UTF-8');
                $XML->writeRaw($XMLDATA);
            $XML->endDocument();

            $HEADERS = [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="Data_Future.xml"',
            ];
            $response = new Response($XML->outputMemory(), 200, $HEADERS);

            return $response;
        else:
            return response()->json(['msg' => 'Data not found!'], 304);
        endif;
    }

    public function generateXml($course_ids, $student_ids, $dateRanges = []){
        $XML = '';
        $VENUE_IDS = [];

        /* Course XML START */
        if(!empty($course_ids)):
            foreach($course_ids as $course_id):
                $course = Course::find($course_id);
                $dfFields = CourseBaseDatafutures::with('field')->whereHas('field', function($q){
                                $q->where('datafuture_field_category_id', 1);
                            })->where('course_id', $course_id)->get();

                $COURSE_XML = '';
                $COURSE_INI = '';
                $COURSE_REF = '';
                $COURSE_ROL = '';

                $COURSE_XML .= '<COURSEID>'.$course_id.'</COURSEID>';
                $COURSE_XML .= (isset($course->name) && !empty($course->name) ? '<COURSETITLE>'.$course->name.'</COURSETITLE>' : '');

                if($dfFields->count() > 0):
                    foreach($dfFields as $dfld):
                        $name = (isset($dfld->field->name) && !empty($dfld->field->name) ? $dfld->field->name : '');
                        $value = (isset($dfld->field_value) && !empty($dfld->field_value) ? trim($dfld->field_value) : '');

                        if($name == 'INITIATIVEID' || $name == 'VALIDFROM' || $name == 'VALIDTO'):
                            $COURSE_INI .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                        elseif($name == 'COURSEREFRNCID' || $name == 'COURSEREFRNCIDTYPE'):
                            $COURSE_REF .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                        elseif($name == 'COURSEROLEHESAID' || $name == 'ROLETYPE' || $name == 'CRPROPORTION'):
                            $COURSE_ROL .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                        else:
                            $COURSE_XML .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                        endif;
                    endforeach;
                endif;

                if(!empty($COURSE_INI)): $COURSE_XML .= '<CourseInitiative>'.$COURSE_INI.'</CourseInitiative>'; endif;
                if(!empty($COURSE_REF)): $COURSE_XML .= '<CourseReference>'.$COURSE_REF.'</CourseReference>'; endif;
                if(!empty($COURSE_ROL)): $COURSE_XML .= '<CourseRole>'.$COURSE_ROL.'</CourseRole>'; endif;

                if(!empty($COURSE_XML)): $XML .= '<Course>'.$COURSE_XML.'</Course>'; endif;
            endforeach;
        endif;
        /* Course XML END */

        /* MODULES XML START */
        $module_ids = $this->getAllModuleIds($course_ids, $student_ids, $dateRanges);
        if(!empty($module_ids)):
            $modules = CourseModule::with('df')->whereIn('id', $module_ids)->orderBy('name', 'ASC')->get();
            if(!empty($modules)):
                foreach($modules as $module):
                    $MODULE_XML = '';
                    $MODULE_CST = '';
                    $MODULE_SUB = '';

                    $MODULE_XML .= (isset($module->id) && !empty($module->id) ? '<MODID>'.$module->id.'</MODID>' : '');
                    $MODULE_XML .= (isset($module->name) && !empty($module->name) ? '<MTITLE>'.$module->name.'</MTITLE>' : '');

                    if(isset($module->df) && $module->df->count() > 0):
                        foreach($module->df as $dfld):
                            $name = (isset($dfld->field->name) && !empty($dfld->field->name) ? $dfld->field->name : '');
                            $value = (isset($dfld->field_value) && !empty($dfld->field_value) ? trim($dfld->field_value) : '');

                            if($name == 'COSTCN' ||  $name == 'COSTCNPROPORTION'):
                                $MODULE_CST .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                            elseif($name == 'MODSBJ' ||  $name == 'MODPROPORTION'):
                                $MODULE_SUB .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                            else:
                                $MODULE_XML .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                            endif;
                        endforeach;
                    endif;

                    if(!empty($MODULE_CST)): $MODULE_XML .= '<ModuleCostCentre>'.$MODULE_CST.'</ModuleCostCentre>'; endif;
                    if(!empty($MODULE_SUB)): $MODULE_XML .= '<ModuleSubject>'.$MODULE_SUB.'</ModuleSubject>'; endif;

                    if(!empty($MODULE_XML)): $XML .= '<Module>'.$MODULE_XML.'</Module>'; endif;
                endforeach;
            endif;
        endif;
        /* MODULES XML END */

        /* QUALIFICATIONS XML START */
        if(!empty($course_ids)):
            foreach($course_ids as $course_id):
                $course = Course::find($course_id);
                $dfFields = CourseBaseDatafutures::with('field')->whereHas('field', function($q){
                                $q->where('datafuture_field_category_id', 2);
                            })->where('course_id', $course_id)->get();
                
                $QUALIF_XML = '';
                $QUALIF_ROL = '';
                $QUALIF_SUB = '';

                if($dfFields->count() > 0):
                    foreach($dfFields as $dfld):
                        $name = (isset($dfld->field->name) && !empty($dfld->field->name) ? $dfld->field->name : '');
                        $value = (isset($dfld->field_value) && !empty($dfld->field_value) ? trim($dfld->field_value) : '');

                        if($name == 'AWARDINGBODYID'):
                            $QUALIF_ROL .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                        elseif($name == 'QUALSUBJECT' || $name == 'QUALPROPORTION'):
                            $QUALIF_SUB .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                        else:
                            $QUALIF_XML .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                        endif;
                    endforeach;
                endif;

                if(!empty($QUALIF_ROL)): $QUALIF_XML .= '<AwardingBodyRole>'.$QUALIF_ROL.'</AwardingBodyRole>'; endif;
                if(!empty($QUALIF_SUB)): $QUALIF_XML .= '<QualificationSubject>'.$QUALIF_SUB.'</QualificationSubject>'; endif;

                if(!empty($QUALIF_XML)): $XML .= '<Qualification>'.$QUALIF_XML.'</Qualification>'; endif;
            endforeach;
        endif;
        /* QUALIFICATIONS XML END */

        /* SESSION YEARS XML START */
        $sessionYears = $this->getAllSessionYears($student_ids, $dateRanges);
        if($sessionYears && $sessionYears->count() > 0):
            foreach($sessionYears as $SES):
                $SESYEAR_XML = '';
                $SESYEAR_XML .= (isset($SES->id) && !empty($SES->id) ? '<SESSIONYEARID>'.$SES->id.'</SESSIONYEARID>' : '');
                $SESYEAR_XML .= (isset($SES->firstTerm->termDeclaration->name) && !empty($SES->firstTerm->termDeclaration->name) ? '<OWNSESSIONID>'.$SES->firstTerm->termDeclaration->name.'</OWNSESSIONID>' : '');
                $SESYEAR_XML .= (isset($SES->end_date) && !empty($SES->end_date) && $SES->end_date != '0000-00-00' ? '<SYENDDATE>'.date('Y-m-d', strtotime($SES->end_date)).'</SYENDDATE>' : '');
                $SESYEAR_XML .= (isset($SES->start_date) && !empty($SES->start_date) && $SES->start_date != '0000-00-00' ? '<SYSTARTDATE>'.date('Y-m-d', strtotime($SES->start_date)).'</SYSTARTDATE>' : '');
                
                if(!empty($SESYEAR_XML)): $XML .= '<SessionYear>'.$SESYEAR_XML.'</SessionYear>'; endif;
            endforeach;
        endif;
        /* SESSION YEARS XML END */

        /* STUDENT XML START */
        if(!empty($student_ids)):
            foreach($student_ids as $student_id):
                $student_crels = $this->getStudentCourseRelations($student_id, $dateRanges);
                $STUDENT = Student::with('other', 'contact', 'qualHigest', 'disability', 'termStatus')->find($student_id);

                if(!empty($student_crels)):
                    foreach($student_crels as $CRELID):
                        $STUDENT_CREL = StudentCourseRelation::with('creation', 'creation.available')->find($CRELID);
                        if(isset($STUDENT_CREL->propose->venue_id) && $STUDENT_CREL->propose->venue_id > 0):
                            $VENUE_IDS[] = $STUDENT_CREL->propose->venue_id;
                        endif;
                        $STUDENT_COURSE_ID = (isset($STUDENT_CREL->creation->course_id) && $STUDENT_CREL->creation->course_id > 0 ? $STUDENT_CREL->creation->course_id : 0);
                        $DF_QUAL_FIELDS = CourseBaseDatafutures::with('field')->whereHas('field', function($q){
                                            $q->where('datafuture_field_category_id', 2);
                                        })->where('course_id', $STUDENT_COURSE_ID)->get();

                        $Student_XML = '';
                        $StudentRoot_XML = '';
                        $Disability_XML = '';
                        $Engagement_XML = '';
                        $EngagementRoot_XML = '';
                        $EntryProfile_XML = '';
                        $EntryProfileRoot_XML = '';
                        $EntryQualificationAward_XML = '';
                        $Leaver_XML = '';
                        $QualificationAwarded_XML = '';
                        $QualificationAwardedRoot_XML = '';
                        $StudentCourseSession_XML = '';

                        /* STUDENT XML START */
                            $StudentRoot_XML .= (isset($STUDENT->laststuload->sid_number) && !empty($STUDENT->laststuload->sid_number) ? '<SID>'.$STUDENT->laststuload->sid_number.'</SID>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->date_of_birth) && !empty($STUDENT->date_of_birth) ? '<BIRTHDTE>'.date('Y-m-d', strtotime($STUDENT->date_of_birth)).'</BIRTHDTE>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->other->ethnicity->df_code) && !empty($STUDENT->other->ethnicity->df_code) ? '<ETHNIC>'.$STUDENT->other->ethnicity->df_code.'</ETHNIC>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->first_name) && !empty($STUDENT->first_name) ? '<FNAMES>'.$STUDENT->first_name.'</FNAMES>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->other->gender->df_code) && !empty($STUDENT->other->gender->df_code) ? '<GENDERID>'.$STUDENT->other->gender->df_code.'</GENDERID>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->nation->df_code) && !empty($STUDENT->nation->df_code) ? '<NATION>'.$STUDENT->nation->df_code.'</NATION>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->registration_no) && !empty($STUDENT->registration_no) ? '<OWNSTU>'.$STUDENT->registration_no.'</OWNSTU>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->other->religion->df_code) && !empty($STUDENT->other->religion->df_code) ? '<RELIGION>'.$STUDENT->other->religion->df_code.'</RELIGION>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->sexid->df_code) && !empty($STUDENT->sexid->df_code) ? '<SEXID>'.$STUDENT->sexid->df_code.'</SEXID>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->other->sexori->df_code) && !empty($STUDENT->other->sexori->df_code) ? '<SEXORT>'.$STUDENT->other->sexori->df_code.'</SEXORT>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->ssn_no) && !empty($STUDENT->ssn_no) ? '<SSN>'.$STUDENT->ssn_no.'</SSN>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->last_name) && !empty($STUDENT->last_name) ? '<SURNAME>'.$STUDENT->last_name.'</SURNAME>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->contact->ttacom->df_code) && !empty($STUDENT->contact->ttacom->df_code) ? '<TTACCOM>'.$STUDENT->contact->ttacom->df_code.'</TTACCOM>' : '');
                            $StudentRoot_XML .= (isset($STUDENT->contact->term_time_post_code) && !empty($STUDENT->contact->term_time_post_code) ? '<TTPCODE>'.$STUDENT->contact->term_time_post_code.'</TTPCODE>' : '');

                            /* DISABILITY XML START */
                            if(isset($STUDENT->other->disability_status) && $STUDENT->other->disability_status == 1 && isset($STUDENT->disability) && $STUDENT->disability->count() > 0):
                                $Disability_XML .= '<Disability>';
                                    foreach($STUDENT->disability as $disability):
                                        $Disability_XML .= (isset($disability->disabilities->df_code) && !empty($disability->disabilities->df_code) ? '<DISABILITY>'.$disability->disabilities->df_code.'</DISABILITY>' : '');
                                    endforeach;
                                $Disability_XML .= '</Disability>';
                            else:
                                $Disability_XML .= '<Disability><DISABILITY>95</DISABILITY></Disability>';
                            endif;
                            /* DISABILITY XML END */

                            /* ENGAGEMENT XML START */
                            $ENGEXPECTEDENDDATE = (isset($STUDENT_CREL->course_end_date) && !empty($STUDENT_CREL->course_end_date) ? date('Y-m-d', strtotime($STUDENT_CREL->course_end_date)) : (isset($STUDENT_CREL->creation->available->course_end_date) && !empty($STUDENT_CREL->creation->available->course_end_date) ? date('Y-m-d', strtotime($STUDENT_CREL->creation->available->course_end_date)) : ''));
                            $ENGSTARTDATE = (isset($STUDENT_CREL->course_start_date) && !empty($STUDENT_CREL->course_start_date) ? date('Y-m-d', strtotime($STUDENT_CREL->course_start_date)) : (isset($STUDENT_CREL->creation->available->course_start_date) && !empty($STUDENT_CREL->creation->available->course_start_date) ? date('Y-m-d', strtotime($STUDENT_CREL->creation->available->course_start_date)) : ''));
                            $EngagementRoot_XML .= (isset($STUDENT->df->NUMHUS) && !empty($STUDENT->df->NUMHUS) ? '<NUMHUS>'.$STUDENT->df->NUMHUS.'</NUMHUS>' : '<NUMHUS>1</NUMHUS>');
                            $EngagementRoot_XML .= (!empty($ENGEXPECTEDENDDATE) ? '<ENGEXPECTEDENDDATE>'.$ENGEXPECTEDENDDATE.'</ENGEXPECTEDENDDATE>' : '');
                            $EngagementRoot_XML .= (!empty($ENGSTARTDATE) ? '<ENGSTARTDATE>'.$ENGSTARTDATE.'</ENGSTARTDATE>' : '');
                            $EngagementRoot_XML .= (isset($STUDENT_CREL->creation->semester->name) && !empty($STUDENT_CREL->creation->semester->name) ? '<OWNENGID>'.$STUDENT_CREL->creation->semester->name.'</OWNENGID>' : '');
                            $EngagementRoot_XML .= (isset($STUDENT_CREL->feeeligibility->elegibility->df_code) && !empty($STUDENT_CREL->feeeligibility->elegibility->df_code) ? '<FEEELIG>'.$STUDENT_CREL->feeeligibility->elegibility->df_code.'</FEEELIG>' : '');
                            
                                /* ENTRY PROFILE XML START */
                                $EntryProfileRoot_XML .= (isset($STUDENT->df->CARELEAVER) && !empty($STUDENT->df->CARELEAVER) ? '<CARELEAVER>'.$STUDENT->df->CARELEAVER.'</CARELEAVER>' : '');
                                $EntryProfileRoot_XML .= (isset($STUDENT->contact->pcountry->df_code) && !empty($STUDENT->contact->pcountry->df_code) ? '<PERMADDCOUNTRY>'.$STUDENT->contact->pcountry->df_code.'</PERMADDCOUNTRY>' : '');
                                $EntryProfileRoot_XML .= (isset($STUDENT->contact->permanent_post_code) && !empty($STUDENT->contact->permanent_post_code) ? '<PERMADDPOSTCODE>'.$STUDENT->contact->permanent_post_code.'</PERMADDPOSTCODE>' : '');
                                $EntryProfileRoot_XML .= (isset($STUDENT->other->is_education_qualification) && $STUDENT->other->is_education_qualification == 1 && isset($STUDENT->qualHigest->previous_providers->df_code) && !empty($STUDENT->qualHigest->previous_providers->df_code) ? '<PREVIOUSPROVIDER>'.$STUDENT->qualHigest->previous_providers->df_code.'</PREVIOUSPROVIDER>' : '');
                                //$EntryProfileRoot_XML .= (isset($STUDENT->other->religion->df_code) && !empty($STUDENT->other->religion->df_code) && $STUDENT->other->religion->df_code != '' ? '<RELIGIOUSBGROUND>'.$STUDENT->other->religion->df_code.'</RELIGIOUSBGROUND>' : '');
                                $EntryProfileRoot_XML .= (isset($STUDENT->other->is_education_qualification) && $STUDENT->other->is_education_qualification == 1 && isset($STUDENT->qualHigest->highest_qualification_on_entries->df_code) && !empty($STUDENT->qualHigest->highest_qualification_on_entries->df_code) ? '<HIGHESTQOE>'.$STUDENT->qualHigest->highest_qualification_on_entries->df_code.'</HIGHESTQOE>' : '');

                                    /* ENTRY QUALIFICATION AWARD XML START */
                                    $EntryQualificationAward_XML .= (isset($STUDENT->other->is_education_qualification) && $STUDENT->other->is_education_qualification == 1 && isset($STUDENT->qualHigest->qualification->name) && !empty($STUDENT->qualHigest->qualification->name) ? '<ENTRYQUALAWARDID>'.$STUDENT->qualHigest->qualification->name.'</ENTRYQUALAWARDID>' : '');
                                    $EntryQualificationAward_XML .= (isset($STUDENT->other->is_education_qualification) && $STUDENT->other->is_education_qualification == 1 && isset($STUDENT->qualHigest->grade->df_code) && !empty($STUDENT->qualHigest->grade->df_code) ? '<ENTRYQUALAWARDRESULT>'.$STUDENT->qualHigest->grade->df_code.'</ENTRYQUALAWARDRESULT>' : '');
                                    $EntryQualificationAward_XML .= (isset($STUDENT->other->is_education_qualification) && $STUDENT->other->is_education_qualification == 1 && isset($STUDENT->qualHigest->qualification_type_identifiers->df_code) && !empty($STUDENT->qualHigest->qualification_type_identifiers->df_code) ? '<QUALTYPEID>'.$STUDENT->qualHigest->qualification_type_identifiers->df_code.'</QUALTYPEID>' : '');
                                    $EntryQualificationAward_XML .= (isset($STUDENT->other->is_education_qualification) && $STUDENT->other->is_education_qualification == 1 && isset($STUDENT->qualHigest->degree_award_date) && !empty($STUDENT->qualHigest->degree_award_date) ? '<QUALYEAR>'.date('Y', strtotime($STUDENT->qualHigest->degree_award_date)).'</QUALYEAR>' : '');
                                    
                                    if(isset($STUDENT->other->is_education_qualification) && $STUDENT->other->is_education_qualification == 1 && isset($STUDENT->qualHigest->hesa_qualification_subjects->df_code) && !empty($STUDENT->qualHigest->hesa_qualification_subjects->df_code)):
                                        $EntryQualificationAward_XML .= '<EntryQualificationSubject>';
                                            $EntryQualificationAward_XML .= '<SUBJECTID>'.$STUDENT->qualHigest->hesa_qualification_subjects->df_code.'</SUBJECTID>';
                                        $EntryQualificationAward_XML .= '</EntryQualificationSubject>';
                                    endif;
                                    /* ENTRY QUALIFICATION AWARD XML END */
                                
                                if(!empty($EntryProfileRoot_XML) || !empty($EntryQualificationAward_XML)):
                                    $EntryProfile_XML .= '<EntryProfile>';
                                        $EntryProfile_XML .= (!empty($EntryProfileRoot_XML) ? $EntryProfileRoot_XML : '');
                                        if(!empty($EntryQualificationAward_XML)):
                                            $EntryProfile_XML .= '<EntryQualificationAward>';
                                                $EntryProfile_XML .= $EntryQualificationAward_XML;
                                            $EntryProfile_XML .= '</EntryQualificationAward>';
                                        endif;
                                    $EntryProfile_XML .= '</EntryProfile>';
                                endif;
                                /* ENTRY PROFILE XML END */

                                /* LEAVER XML START */
                                $ENGENDDATE = '';
                                $RSNENGEND = '';
                                $QUALRESULT = '';
                                if(isset($STUDENT_CREL->active) && $STUDENT_CREL->active == 1):
                                    $endStatuses = [21, 26, 27, 31, 42];
                                    $student_status_id = (isset($STUDENT->status_id) && $STUDENT->status_id > 0 ? $STUDENT->status_id : '');
                                    $termStatusId = (isset($STUDENT->termStatus->status_id) && !empty($STUDENT->termStatus->status_id) ? $STUDENT->termStatus->status_id : '');

                                    if($student_status_id == $termStatusId && in_array($student_status_id, $endStatuses)):
                                        $ENGENDDATE = (isset($STUDENT->termStatus->status_end_date) && !empty($STUDENT->termStatus->status_end_date) ? date('Y-m-d', strtotime($STUDENT->termStatus->status_end_date)) : '');
                                        $RSNENGEND = (isset($STUDENT->termStatus->reason->df_code) && !empty($STUDENT->termStatus->reason->df_code) ? $STUDENT->termStatus->reason->df_code : '');
                                        $QUALRESULT = (isset($STUDENT->termStatus->other_academic_qualification_id) && !empty($STUDENT->termStatus->other_academic_qualification_id) ? $STUDENT->termStatus->other_academic_qualification_id : '');
                                    endif;

                                    if(!empty($ENGENDDATE) || !empty($RSNENGEND)):
                                        $Leaver_XML .= '<Leaver>';
                                            $Leaver_XML .= (!empty($ENGENDDATE) ? '<ENGENDDATE>'.$ENGENDDATE.'</ENGENDDATE>' : '');
                                            $Leaver_XML .= (!empty($RSNENGEND) ? '<RSNENGEND>'.$RSNENGEND.'</RSNENGEND>' : '');
                                        $Leaver_XML .= '</Leaver>';
                                    endif;
                                else:
                                    $Leaver_XML .= '<Leaver>';
                                        $Leaver_XML .= '<ENGENDDATE>'.$ENGENDDATE.'</ENGENDDATE>';
                                        $Leaver_XML .= '<RSNENGEND>'.$RSNENGEND.'</RSNENGEND>';
                                    $Leaver_XML .= '</Leaver>';
                                endif;
                                
                                /* LEAVER XML END */

                                /* QUALIFICATION AWARDED START */
                                $QUALID = '';
                                if(!empty($DF_QUAL_FIELDS) && $DF_QUAL_FIELDS->count() > 0):
                                    foreach($DF_QUAL_FIELDS as $qf):
                                        if(isset($qf->field->name) && $qf->field->name == 'QUALID'):
                                            $QUALID = (isset($qf->field_value) && !empty($qf->field_value) ? trim($qf->field_value) : '');
                                        endif;
                                    endforeach;
                                endif;
                                $STUDENT_AWARD = StudentAward::where('student_id', $STUDENT->id)->where('student_course_relation_id', $CRELID)->orderBy('id', 'DESC')->get()->first();

                                $QualificationAwardedRoot_XML .= (isset($STUDENT_AWARD->qual_award_type) && !empty($STUDENT_AWARD->qual_award_type) ? '<QUALAWARDID>'.$STUDENT_AWARD->qual_award_type.'</QUALAWARDID>' : '');
                                $QualificationAwardedRoot_XML .= (!empty($QUALID) && isset($STUDENT_AWARD->qual_award_type) && !empty($STUDENT_AWARD->qual_award_type) ? '<QUALID>'.$QUALID.'</QUALID>' : '');
                                $QualificationAwardedRoot_XML .= (isset($STUDENT_AWARD->qual->df_code) && !empty($STUDENT_AWARD->qual->df_code) ? '<QUALAWARDRESULT>'.$STUDENT_AWARD->qual->df_code.'</QUALAWARDRESULT>' : '');
                                
                                if(!empty($QualificationAwardedRoot_XML)):
                                    $QualificationAwarded_XML .= '<QualificationAwarded>'.$QualificationAwardedRoot_XML.'</QualificationAwarded>';
                                endif;
                                /* QUALIFICATION AWARDED END */

                                /* COURSE SESSION START */
                                $STULOADS = $this->getStudentCourseSessions($STUDENT->id, $CRELID, $dateRanges);
                                $S = 1;
                                if($STULOADS && $STULOADS->count()):
                                    foreach($STULOADS as $STU):
                                        $modules = $this->getStudentModuleInstances($STU->id, $STUDENT->id, $STUDENT_COURSE_ID, $dateRanges);
                                        if($modules && $modules->count() > 0):
                                            $instanceStart = (isset($STU->instance->start_date) && !empty($STU->instance->start_date) ? date('Y-m-d', strtotime($STU->instance->start_date)) : '');
                                            $instanceEnd = (isset($STU->instance->end_date) && !empty($STU->instance->end_date) ? date('Y-m-d', strtotime($STU->instance->end_date)) : '');
                                            $hesaEndDate = (isset($STU->enddate) && !empty($STU->enddate) ? date('Y-m-d', strtotime($STU->enddate)) : '');
                                            $periodEndDate = (isset($STU->periodend) && !empty($STU->periodend) && $STU->periodend != '0000-00-00' ? date('Y-m-d', strtotime($STU->periodend)) : '');
                                            $periodStartDate = (isset($STU->periodstart) && !empty($STU->periodstart) && $STU->periodstart != '0000-00-00' ? date('Y-m-d', strtotime($STU->periodstart)) : '');

                                            //$SCSMODE = (isset($STU->mode_id) && $STU->mode_id > 0 ? $STU->mode_id : '');
                                            $SCSMODE = (isset($STUDENT->other->mode->df_code) && $STUDENT->other->mode->df_code > 0 ? $STUDENT->other->mode->df_code : '01');
                                            $SCSEXPECTEDENDDATE = $instanceEnd;
                                            $SCSENDDATE = $hesaEndDate;
                                            if(!empty($ENGENDDATE) && ($ENGENDDATE > $periodStartDate &&  $ENGENDDATE < $periodEndDate) && $ENGENDDATE < $instanceEnd):
                                                $SCSENDDATE = $ENGENDDATE;
                                                //$SCSMODE = (!empty($SCSMODE) ? 2 : $SCSMODE);
                                            elseif(empty($hesaEndDate) && (!empty($SCSEXPECTEDENDDATE) && $SCSEXPECTEDENDDATE < date('Y-m-d'))):
                                                $SCSENDDATE = $SCSEXPECTEDENDDATE;
                                                //$SCSMODE = (!empty($SCSMODE) ? 4 : $SCSMODE);
                                            endif;

                                            $RSNSCSEND_ID = '';
                                            $RSNSCSEND = '';
                                            if(($hesaEndDate == '' && $instanceEnd <= date('Y-m-d')) || ($hesaEndDate != '' && $hesaEndDate == $instanceEnd) || ($hesaEndDate != '' && $hesaEndDate > $instanceEnd && $instanceEnd <= date('Y-m-d'))):
                                                $RSNSCSEND_ID = 4;
                                            elseif($hesaEndDate != '' && $hesaEndDate > $instanceStart && $hesaEndDate < $instanceEnd):
                                                $RSNSCSEND_ID = 2;
                                            else:
                                                $RSNSCSEND = '';
                                            endif;
                                            $RSNSCSEND_ID = (isset($STU->df->RSNSCSEND) && !empty($STU->df->RSNSCSEND) ? $STU->df->RSNSCSEND : $RSNSCSEND_ID);
                                            $RSNSCSEND_ROW = ($RSNSCSEND_ID > 0 ? ReasonForEndingCourseSession::find($RSNSCSEND_ID) : []);
                                            $RSNSCSEND = (isset($RSNSCSEND_ROW->df_code) && !empty($RSNSCSEND_ROW->df_code) ? $RSNSCSEND_ROW->df_code : '');

                                            $FUNDCOMP = (!empty($periodEndDate) && $periodEndDate < date('Y-m-d') ? 1 : (!empty($periodStartDate) && $periodStartDate <= date('Y-m-d') && !empty($periodEndDate) && $periodEndDate > date('Y-m-d') ? 2 : 3));
                                            $FUNDLENGTH = 3;

                                            $REFPERIOD_INC = '01'; //($S < 10 ? '0'.$S : $S);
                                            $STULOAD = ($STU->student_load && $STU->student_load > 0 ? ($STU->student_load == 99 ? '100' : $STU->student_load) : '');

                                            $COURSE_SESS_XML = '';
                                            $COURSE_SESS_XML .= (isset($STU->course_creation_instance_id) && !empty($STU->course_creation_instance_id) ? '<SCSESSIONID>'.$STU->course_creation_instance_id.'</SCSESSIONID>' : '');
                                            $COURSE_SESS_XML .= (isset($STU->courseaim_id) && !empty($STU->courseaim_id) ? '<COURSEID>'.$STU->courseaim_id.'</COURSEID>' : '');
                                            $COURSE_SESS_XML .= (isset($STU->gross_fee) && !empty($STU->gross_fee) ? '<INVOICEFEEAMOUNT>'.$STU->gross_fee.'</INVOICEFEEAMOUNT>' : '');
                                            $COURSE_SESS_XML .= '<INVOICEHESAID>'.(isset($STU->df->INVOICEHESAID) && !empty($STU->df->INVOICEHESAID) ? $STU->df->INVOICEHESAID : '5026').'</INVOICEHESAID>';
                                            //$COURSE_SESS_XML .= (!empty($SCSEXPECTEDENDDATE) ? '<SCSEXPECTEDENDDATE>'.$SCSEXPECTEDENDDATE.'</SCSEXPECTEDENDDATE>' : '');
                                            $COURSE_SESS_XML .= ($SCSENDDATE != '' ? '<SCSENDDATE>'.$SCSENDDATE.'</SCSENDDATE>' : '');
                                            $COURSE_SESS_XML .= (isset($STU->netfee) && $STU->netfee > 0 ? '<SCSFEEAMOUNT>'.$STU->netfee.'</SCSFEEAMOUNT>' : '');
                                            $COURSE_SESS_XML .= (!empty($SCSMODE) ? '<SCSMODE>'.$SCSMODE.'</SCSMODE>' : '');
                                            $COURSE_SESS_XML .= (isset($STU->periodstart) && !empty($STU->periodstart) && $STU->periodstart != '0000-00-00' ? '<SCSSTARTDATE>'.$STU->periodstart.'</SCSSTARTDATE>' : '');
                                            $COURSE_SESS_XML .= (isset($STU->course_creation_instance_id) && !empty($STU->course_creation_instance_id) ? '<SESSIONYEARID>'.$STU->course_creation_instance_id.'</SESSIONYEARID>' : '');
                                            $COURSE_SESS_XML .= '<STULOAD> </STULOAD>';
                                            $COURSE_SESS_XML .= (isset($STU->yearprg) && $STU->yearprg > 0 ? '<YEARPRG>'.$STU->yearprg.'</YEARPRG>' : '');
                                            $COURSE_SESS_XML .= (!empty($RSNSCSEND) ? '<RSNSCSEND>'.$RSNSCSEND.'</RSNSCSEND>' : '');

                                            $FUND_MON_XML = '';
                                            $FUND_MON_XML .= (isset($STU->df->elq->df_code) && !empty($STU->df->elq->df_code) ? '<ELQ>'.$STU->df->elq->df_code.'</ELQ>' : '');
                                            $FUND_MON_XML .= (isset($STU->df->fundcomp->df_code) && !empty($STU->df->fundcomp->df_code) ? '<FUNDCOMP>'.$STU->df->fundcomp->df_code.'</FUNDCOMP>' : '');
                                            $FUND_MON_XML .= (isset($STU->df->fundLength->df_code) && !empty($STU->df->fundLength->df_code) ? '<FUNDLENGTH>'.$STU->df->fundLength->df_code.'</FUNDLENGTH>' : '');
                                            $FUND_MON_XML .= (isset($STU->df->nonregfee->df_code) && !empty($STU->df->nonregfee->df_code) ? '<NONREGFEE>'.$STU->df->nonregfee->df_code.'</NONREGFEE>' : '');
                                            if(!empty($FUND_MON_XML)):
                                                $COURSE_SESS_XML .= '<FundingAndMonitoring>'.$FUND_MON_XML.'</FundingAndMonitoring>';
                                            endif;

                                            $MOD_INST_XML = '';
                                            if($modules && !empty($modules)):
                                                foreach($modules as $module):
                                                    $modDF = StudentModuleInstanceDatafuture::where('student_id', $STUDENT->id)->where('student_course_relation_id', $CRELID)
                                                            ->where('student_stuload_information_id', $STU->id)->where('instance_term_id', $module->instance_term_id)
                                                            ->where('course_module_id', $module->creations->course_module_id)->get()->first();
                                                    $MOD_INST_XML .= '<ModuleInstance>';
                                                        $MOD_INST_XML .= (isset($module->id) && !empty($module->id) ? '<MODINSTID>'.$module->id.'</MODINSTID>' : '');
                                                        $MOD_INST_XML .= (isset($module->creations->course_module_id) && !empty($module->creations->course_module_id) ? '<MODID>'.$module->creations->course_module_id.'</MODID>' : '');
                                                        $MOD_INST_XML .= (isset($module->attenTerm->end_date) && !empty($module->attenTerm->end_date) && $module->attenTerm->end_date != '0000-00-00' ? '<MODINSTENDDATE>'.date('Y-m-d', strtotime($module->attenTerm->end_date)).'</MODINSTENDDATE>' : '');
                                                        $MOD_INST_XML .= (isset($module->attenTerm->start_date) && !empty($module->attenTerm->start_date) && $module->attenTerm->start_date != '0000-00-00' ? '<MODINSTSTARTDATE>'.date('Y-m-d', strtotime($module->attenTerm->start_date)).'</MODINSTSTARTDATE>' : '');
                                                        $MOD_INST_XML .= (isset($modDF->moduleoutcome->df_code) && !empty($modDF->moduleoutcome->df_code) ? '<MODULEOUTCOME>'.$modDF->moduleoutcome->df_code.'</MODULEOUTCOME>' : '');
                                                        $MOD_INST_XML .= (isset($modDF->moduleresult->df_code) && !empty($modDF->moduleresult->df_code) ? '<MODULERESULT>'.$modDF->moduleresult->df_code.'</MODULERESULT>' : '');
                                                    $MOD_INST_XML .= '</ModuleInstance>';
                                                endforeach;
                                            endif;
                                            $COURSE_SESS_XML .= (!empty($MOD_INST_XML) ? $MOD_INST_XML : '');

                                            $REF_PRD_XML = '';
                                            $STU_RPSTULOAD = $this->getStudentPrStuload($STU->id, $STUDENT->id, $STUDENT_COURSE_ID, $dateRanges);
                                            $RPSTULOAD = ($STU_RPSTULOAD > 0 ? ($STU_RPSTULOAD == 99 ? '100' : $STU_RPSTULOAD) : '');
                                            $REF_PRD_XML .= (isset($REFPERIOD_INC) && !empty($REFPERIOD_INC) ? '<REFPERIOD>'.$REFPERIOD_INC.'</REFPERIOD>' : '');
                                            $REF_PRD_XML .= (isset($STU->instance->year->from_date) && !empty($STU->instance->year->from_date) ? '<YEAR>'.date('Y', strtotime($STU->instance->year->from_date)).'</YEAR>' : '');
                                            $REF_PRD_XML .= (!empty($RPSTULOAD) ? '<RPSTULOAD>'.$RPSTULOAD.'</RPSTULOAD>' : '');
                                            $COURSE_SESS_XML .= (!empty($REF_PRD_XML) ? '<ReferencePeriodStudentLoad>'.$REF_PRD_XML.'</ReferencePeriodStudentLoad>' : '');

                                            $CRS_SES_STS_XML = '';
                                            $SESSIONSTATUES = $this->getStuloadSessionStatuses($STUDENT->id, $CRELID);
                                            if(isset($SESSIONSTATUES[$STU->id]) && !empty($SESSIONSTATUES[$STU->id])):
                                                foreach($SESSIONSTATUES[$STU->id] as $TERMDECID => $CSTS):
                                                    $CRS_SES_STS_XML .= '<SessionStatus>';
                                                        $CRS_SES_STS_XML .= (isset($CSTS['STATUSVALIDFROM']) && !empty($CSTS['STATUSVALIDFROM']) ? '<STATUSVALIDFROM>'.$CSTS['STATUSVALIDFROM'].'</STATUSVALIDFROM>' : '');
                                                        if(isset($CSTS['STATUSCHANGEDTO']) && $CSTS['STATUSCHANGEDTO'] > 0):
                                                            $DBSESSIONSTATUS = SessionStatus::find($CSTS['STATUSCHANGEDTO']);
                                                            $CRS_SES_STS_XML .= (isset($DBSESSIONSTATUS->df_code) && !empty($DBSESSIONSTATUS->df_code) ? '<STATUSCHANGEDTO>'.$DBSESSIONSTATUS->df_code.'</STATUSCHANGEDTO>' : '');
                                                        endif;
                                                    $CRS_SES_STS_XML .= '</SessionStatus>';
                                                endforeach;
                                            endif;
                                            $COURSE_SESS_XML .= (!empty($CRS_SES_STS_XML) ? $CRS_SES_STS_XML : '');

                                            if(isset($STU->df->FINSUPTYPE) && !empty($STU->df->FINSUPTYPE)):
                                                $COURSE_SESS_XML .= '<StudentFinancialSupport>';
                                                    $COURSE_SESS_XML .= '<FINSUPTYPE>'.$STU->df->FINSUPTYPE.'</FINSUPTYPE>';
                                                $COURSE_SESS_XML .= '</StudentFinancialSupport>';
                                            endif;

                                            $STD_LOC_XML = '';
                                            $STD_LOC_XML .= (isset($STU->studentCR->propose->venue->name) && !empty($STU->studentCR->propose->venue->name) ? '<STUDYLOCID>'.$STU->studentCR->propose->venue->name.'</STUDYLOCID>' : '');
                                            $STD_LOC_XML .= (isset($STU->df->STUDYPROPORTION) && !empty($STU->df->STUDYPROPORTION) ? '<STUDYPROPORTION>'.$STU->df->STUDYPROPORTION.'</STUDYPROPORTION>' : '<STUDYPROPORTION>100</STUDYPROPORTION>');
                                            $STD_LOC_XML .= (isset($STU->studentCR->propose->venue->idnumber) && !empty($STU->studentCR->propose->venue->idnumber) ? '<VENUEID>'.$STU->studentCR->propose->venue->idnumber.'</VENUEID>' : '');
                                            $COURSE_SESS_XML .= (!empty($STD_LOC_XML) ? '<StudyLocation>'.$STD_LOC_XML.'</StudyLocation>' : '');

                                            if(!empty($COURSE_SESS_XML)):
                                                $StudentCourseSession_XML .= '<StudentCourseSession>';
                                                    $StudentCourseSession_XML .= $COURSE_SESS_XML;
                                                $StudentCourseSession_XML .= '</StudentCourseSession>';
                                            endif;

                                            $S++;
                                        endif;
                                    endforeach;
                                endif;
                                /* COURSE SESSION END */

                            if(!empty($EngagementRoot_XML) || !empty($EntryProfile_XML) || !empty($StudentCourseSession_XML) || !empty($Leaver_XML) || !empty($QualificationAwarded_XML)):
                                $Engagement_XML .= '<Engagement>';
                                    $Engagement_XML .= (!empty($EngagementRoot_XML) ? $EngagementRoot_XML : '');
                                    $Engagement_XML .= (!empty($EntryProfile_XML) ? $EntryProfile_XML : '');
                                    $Engagement_XML .= (!empty($Leaver_XML) ? $Leaver_XML : '');
                                    $Engagement_XML .= (!empty($QualificationAwarded_XML) ? $QualificationAwarded_XML : '');
                                    $Engagement_XML .= (!empty($StudentCourseSession_XML) ? $StudentCourseSession_XML : '');
                                $Engagement_XML .= '</Engagement>';
                            endif;
                            /* ENGAGEMENT XML END */
                        /* STUDENT XML END */

                        if(!empty($StudentRoot_XML) || !empty($Disability_XML) || !empty($Engagement_XML)):
                            $Student_XML .= '<Student>';
                                $Student_XML .= (!empty($StudentRoot_XML) ? $StudentRoot_XML : '');
                                $Student_XML .= (!empty($Disability_XML) ? $Disability_XML : '');
                                $Student_XML .= (!empty($Engagement_XML) ? $Engagement_XML : '');
                            $Student_XML .= '</Student>';
                        endif;
                        $XML .= (!empty($Student_XML) ? $Student_XML : '');
                    endforeach;
                endif;
            endforeach;
        endif;
        /* STUDENT XML END */

        if(!empty($VENUE_IDS)):
            $VENUE_IDS = array_unique($VENUE_IDS);
            $venues = Venue::whereIn('id', $VENUE_IDS)->get();
            if($venues->count() > 0):
                foreach($venues as $venue):
                    $XML .= '<Venue>';
                        $XML .= (isset($venue->idnumber) && !empty($venue->idnumber) ? '<VENUEID>'.$venue->idnumber.'</VENUEID>' : '');
                        $XML .= (isset($venue->id) && !empty($venue->id) ? '<OWNVENUEID>'.$venue->id.'</OWNVENUEID>' : '');
                        $XML .= (isset($venue->postcode) && !empty($venue->postcode) ? '<POSTCODE>'.$venue->postcode.'</POSTCODE>' : '');
                        $XML .= (isset($venue->name) && !empty($venue->name) ? '<VENUENAME>'.$venue->name.'</VENUENAME>' : '');
                        $XML .= (isset($venue->ukprn) && !empty($venue->ukprn) ? '<VENUEUKPRN>'.$venue->ukprn.'</VENUEUKPRN>' : '');
                    $XML .= '</Venue>';
                endforeach;
            endif;
        endif;

        if(!empty($XML)):
            $XML = '<DataFutures>'.$XML.'</DataFutures>';
        endif;

        return $XML;
    }

    public function getAllModuleIds($course_ids, $student_ids, $dateRanges = []){
        $plan_ids = [];
        $module_ids = [];

        foreach($student_ids as $student_id):
            if(!empty($dateRanges)):
                $whereRaw = "";
                foreach($dateRanges as $date):
                    $FROM_DATE = $date['start'];
                    $TO_DATE = $date['end'];
                    $whereRaw .= (!empty($whereRaw) ? " OR " : '');
                    $whereRaw .= " (
                        (('$FROM_DATE' BETWEEN periodstart AND periodend) OR ('$TO_DATE' BETWEEN periodstart AND periodend)) 
                        OR 
                        ((periodstart BETWEEN '$FROM_DATE' AND '$TO_DATE') OR (periodend BETWEEN '$FROM_DATE' AND '$TO_DATE'))
                    ) ";
                endforeach;
                $stuloads = StudentStuloadInformation::whereRaw("(".$whereRaw.")")->where('student_id', $student_id)->orderBy('student_id', 'ASC')->get();

                if($stuloads->count() > 0):
                    foreach($stuloads as $stu):
                        $instance_id = $stu->course_creation_instance_id;
                        $instance = CourseCreationInstance::find($instance_id);
                        if(isset($instance->terms) && $instance->terms->count() > 0):
                            foreach($instance->terms as $term):
                                $termStart = (isset($term->start_date) && !empty($term->start_date) ? date('Y-m-d', strtotime($term->start_date)) : '');
                                $termEnd = (isset($term->end_date) && !empty($term->end_date) ? date('Y-m-d', strtotime($term->end_date)) : '');

                                $student_plan_ids = Attendance::where('student_id', $student_id)->whereBetween('attendance_date', [$termStart, $termEnd])->pluck('plan_id')->unique()->toArray();
                                $plan_ids = array_merge($plan_ids, $student_plan_ids);
                            endforeach;
                        endif;
                    endforeach;
                endif;
            endif;
        endforeach;

        // if(!empty($dateRanges)):
        //     $whereRaw = "";
        //     foreach($dateRanges as $date):
        //         $FROM_DATE = $date['start'];
        //         $TO_DATE = $date['end'];
        //         $whereRaw .= (!empty($whereRaw) ? " OR " : '');
        //         $whereRaw .= " (
        //             (('$FROM_DATE' BETWEEN periodstart AND periodend) OR ('$TO_DATE' BETWEEN periodstart AND periodend)) 
        //             OR 
        //             ((periodstart BETWEEN '$FROM_DATE' AND '$TO_DATE') OR (periodend BETWEEN '$FROM_DATE' AND '$TO_DATE'))
        //         ) ";
        //     endforeach;
        //     $stuloads = StudentStuloadInformation::whereRaw("(".$whereRaw.")")->whereIn('student_id', $student_ids)->orderBy('student_id', 'ASC')->get();

        //     if($stuloads->count() > 0):
        //         $instance_ids = $stuloads->pluck('course_creation_instance_id')->unique()->toArray();
        //         $instance_term_ids = InstanceTerm::whereIn('course_creation_instance_id', $instance_ids)->pluck('id')->unique()->toArray();
        //         $plan_ids = Plan::whereIn('instance_term_id', $instance_term_ids)->whereIn('course_id', $course_ids)->whereHas('assign', function($q) use($student_ids){
        //                         $q->whereIn('student_id', $student_ids);
        //                     })->pluck('id')->unique()->toArray();
        //         return($student_ids);
        //     endif;
        // endif;

        if(!empty($plan_ids)):
            $plan_ids = array_unique($plan_ids);
            $module_creation_ids = Plan::whereIn('id', $plan_ids)->where(function($q){
                            $q->whereNotIn('class_type', ['Tutorial', 'Seminar', 'Practical'])->orWhereNull('class_type');
                        })->whereDoesntHave('creations', function($q){
                            $q->where('module_name', 'LIKE', '%GROUP TUTORIAL (QCF)%')->orWhere('module_name', 'LIKE', '%Group Tutorial (RQF)%')
                                    ->orWhere('module_name', 'LIKE', '%GROUP TUTORIAL (RQF)%')->orWhere('module_name', 'LIKE', '%PERSONAL TUTORIAL%')
                                    ->orWhere('module_name', 'LIKE', '%PERSONAL TUTORIAL (QCF)%')->orWhere('module_name', 'LIKE', '%Personal Tutorial (RQF)%')
                                    ->orWhere('module_name', 'LIKE', '%PERSONAL TUTORIAL (RQF)%');
                        })->pluck('module_creation_id')->unique()->toArray();
            $module_ids = (!empty($module_creation_ids) ? ModuleCreation::whereIn('id', $module_creation_ids)->pluck('course_module_id')->unique()->toArray() : []);
        endif;
        
        return $module_ids;
    }

    public function getAllSessionYears($student_ids, $dateRanges = []){
        if(!empty($dateRanges)):
            $whereRaw = "";
            foreach($dateRanges as $date):
                $FROM_DATE = $date['start'];
                $TO_DATE = $date['end'];
                $whereRaw .= (!empty($whereRaw) ? " OR " : '');
                $whereRaw .= " (
                    (('$FROM_DATE' BETWEEN periodstart AND periodend) OR ('$TO_DATE' BETWEEN periodstart AND periodend)) 
                    OR 
                    ((periodstart BETWEEN '$FROM_DATE' AND '$TO_DATE') OR (periodend BETWEEN '$FROM_DATE' AND '$TO_DATE'))
                ) ";
            endforeach;
            $stuloads = StudentStuloadInformation::whereRaw("(".$whereRaw.")")->whereIn('student_id', $student_ids)->orderBy('student_id', 'ASC')->get();

            if($stuloads->count() > 0):
                $instance_ids = $stuloads->pluck('course_creation_instance_id')->unique()->toArray();
                return CourseCreationInstance::whereIn('id', $instance_ids)->orderBy('id', 'ASC')->get();
            endif;
        endif;

        return false;
    }

    public function getStudentCourseRelations($student_id, $dateRanges = []){
        if(!empty($dateRanges)):
            $whereRaw = "";
            foreach($dateRanges as $date):
                $FROM_DATE = $date['start'];
                $TO_DATE = $date['end'];
                $whereRaw .= (!empty($whereRaw) ? " OR " : '');
                $whereRaw .= " (
                    (('$FROM_DATE' BETWEEN periodstart AND periodend) OR ('$TO_DATE' BETWEEN periodstart AND periodend)) 
                    OR 
                    ((periodstart BETWEEN '$FROM_DATE' AND '$TO_DATE') OR (periodend BETWEEN '$FROM_DATE' AND '$TO_DATE'))
                ) ";
            endforeach;
            return StudentStuloadInformation::whereRaw("(".$whereRaw.")")->where('student_id', $student_id)
                        ->orderBy('student_course_relation_id', 'ASC')->get()->pluck('student_course_relation_id')->unique()->toArray();
        endif;

        return [];
    }

    public function getStudentCourseSessions($student_id, $student_course_relation_id, $dateRanges = []){
        if(!empty($dateRanges)):
            $whereRaw = "";
            foreach($dateRanges as $date):
                $FROM_DATE = $date['start'];
                $TO_DATE = $date['end'];
                $whereRaw .= (!empty($whereRaw) ? " OR " : '');
                $whereRaw .= " (
                    (('$FROM_DATE' BETWEEN periodstart AND periodend) OR ('$TO_DATE' BETWEEN periodstart AND periodend)) 
                    OR 
                    ((periodstart BETWEEN '$FROM_DATE' AND '$TO_DATE') OR (periodend BETWEEN '$FROM_DATE' AND '$TO_DATE'))
                ) ";
            endforeach;
            return StudentStuloadInformation::whereRaw("(".$whereRaw.")")->where('student_course_relation_id', $student_course_relation_id)->where('student_id', $student_id)->orderBy('id', 'ASC')->get();
        endif;

        return false;
    }

    public function getStudentModuleInstances($stuload_id, $student_id, $course_id, $dateRanges = []){
        $stuload = StudentStuloadInformation::where('student_id', $student_id)->where('id', $stuload_id)->orderBy('student_id', 'ASC')->get()->first();
        $plan_ids = [];

        $instance_id = $stuload->course_creation_instance_id;
        $instance = CourseCreationInstance::find($instance_id);
        if(isset($instance->terms) && $instance->terms->count() > 0):
            foreach($instance->terms as $term):
                $termStart = (isset($term->start_date) && !empty($term->start_date) ? date('Y-m-d', strtotime($term->start_date)) : '');
                $termEnd = (isset($term->end_date) && !empty($term->end_date) ? date('Y-m-d', strtotime($term->end_date)) : '');

                if($this->isTermInRange($dateRanges, $termStart, $termEnd)):
                    $student_plan_ids = Attendance::where('student_id', $student_id)->whereBetween('attendance_date', [$termStart, $termEnd])->pluck('plan_id')->unique()->toArray();
                    $plan_ids = array_merge($plan_ids, $student_plan_ids);
                endif;
            endforeach;
        endif;
        
        if(!empty($plan_ids)):
            return Plan::whereIn('id', $plan_ids)->where('course_id', $course_id)->where(function($q){
                        $q->whereNotIn('class_type', ['Tutorial', 'Seminar', 'Practical'])->orWhereNull('class_type');
                    })->whereDoesntHave('creations', function($q){
                        $q->where('module_name', 'LIKE', '%GROUP TUTORIAL (QCF)%')->orWhere('module_name', 'LIKE', '%Group Tutorial (RQF)%')
                                ->orWhere('module_name', 'LIKE', '%GROUP TUTORIAL (RQF)%')->orWhere('module_name', 'LIKE', '%PERSONAL TUTORIAL%')
                                ->orWhere('module_name', 'LIKE', '%PERSONAL TUTORIAL (QCF)%')->orWhere('module_name', 'LIKE', '%Personal Tutorial (RQF)%')
                                ->orWhere('module_name', 'LIKE', '%PERSONAL TUTORIAL (RQF)%');
                    })->whereHas('assign', function($q) use($student_id){
                        $q->where('student_id', $student_id);
                    })->orderBy('id', 'DESC')->get();
        else:
            return false;
        endif;
        
        return $plans;
    }

    public function isTermInRange($ranges, $termStart, $termEnd){
        $inRange = false;
        if(!empty($ranges) && !empty($termStart) && !empty($termEnd)):
            $inRangeCount = 0;
            foreach($ranges as $range):
                $start = (isset($range['start']) && !empty($range['start']) ? $range['start'] : '');
                $end = (isset($range['end']) && !empty($range['end']) ? $range['end'] : '');

                $inRangeCount += (!empty($start) && !empty($end) && ($termStart >= $start && $termStart <= $end) ? 1 : 0);
            endforeach;
            $inRange = ($inRangeCount > 0 ? true : $inRange);
        endif;

        return $inRange;
    }

    public function getStuloadSessionStatuses($student_id, $student_course_relation_id){
        $res = [];
        $stuloads = StudentStuloadInformation::where('student_id', $student_id)->where('student_course_relation_id', $student_course_relation_id)->orderBy('id', 'ASC')->get();
        if($stuloads->count() > 0):
            $suspendedContinued = false;
            foreach($stuloads as $stu):
                $instance_id = $stu->course_creation_instance_id;
                $instance = CourseCreationInstance::find($instance_id);
                if(isset($instance->terms) && $instance->terms->count() > 0):
                    $suspendedFound = false;
                    $termCount = 1;
                    foreach($instance->terms as $term):
                        $term_declaration_id = $term->term_declaration_id;
                        $termDeclaration = (isset($term->termDeclaration) && !empty($term->termDeclaration) ? $term->termDeclaration : []);

                        $stdAttenTermStatus = StudentAttendanceTermStatus::where('term_declaration_id', $term_declaration_id)->where('student_id', $student_id)->orderBy('id', 'DESC')->get()->first();
                        $lastAttendance = Attendance::where('student_id', $student_id)->whereHas('plan', function($q) use($term_declaration_id){
                                    $q->where('term_declaration_id', $term_declaration_id);
                                })->whereHas('feed', function($q){
                                    $q->where('attendance_count', 1);
                                })->orderBy('attendance_date', 'DESC')->get()->first();
                        if($suspendedFound || ($suspendedContinued && $termCount == 1)):
                            if(isset($stdAttenTermStatus->status_id) && $stdAttenTermStatus->status_id > 0 && in_array($stdAttenTermStatus->status_id, [17, 27, 30, 31, 33, 36])):
                                $res[$stu->id][$term_declaration_id]['STATUSVALIDFROM'] = (isset($lastAttendance->attendance_date) && !empty($lastAttendance->attendance_date) ? date('Y-m-d', strtotime($lastAttendance->attendance_date)) : '');
                                $res[$stu->id][$term_declaration_id]['STATUSCHANGEDTO'] = 2;
                                //$res[$stu->id][$term_declaration_id]['CONTINUED'] = ($suspendedContinued ? 1 : 0);
                                $suspendedContinued = ($instance->terms->count() == $termCount ? true : false);
                            else:
                                $termDeclaration = TermDeclaration::find($term_declaration_id);

                                $res[$stu->id][$term_declaration_id]['STATUSVALIDFROM'] = (isset($termDeclaration->start_date) && !empty($termDeclaration->start_date) ? date('Y-m-d', strtotime($termDeclaration->start_date)) : '');
                                $res[$stu->id][$term_declaration_id]['STATUSCHANGEDTO'] = 1;
                                //$res[$stu->id][$term_declaration_id]['CONTINUED'] = ($suspendedContinued ? 1 : 0);
                                $suspendedContinued = ($suspendedContinued ? false : $suspendedContinued);
                            endif;
                        else:
                            if(isset($stdAttenTermStatus->status_id) && $stdAttenTermStatus->status_id > 0 && in_array($stdAttenTermStatus->status_id, [17, 27, 30, 31, 33, 36])):
                                $suspendedFound = true;
                                $suspendedContinued = ($instance->terms->count() == $termCount ? true : false);

                                $res[$stu->id][$term_declaration_id]['STATUSVALIDFROM'] = (isset($lastAttendance->attendance_date) && !empty($lastAttendance->attendance_date) ? date('Y-m-d', strtotime($lastAttendance->attendance_date)) : '');
                                $res[$stu->id][$term_declaration_id]['STATUSCHANGEDTO'] = 2;
                                //$res[$stu->id][$term_declaration_id]['CONTINUED'] = ($suspendedContinued ? 1 : 0).$instance->terms->count().$termCount;
                            endif;
                        endif;
                        $termCount++;
                    endforeach;
                endif;
            endforeach;
        endif;

        //dd($res);
        return $res;
    }


    public function getStudentPrStuload($stuload_id, $student_id, $course_id, $dateRanges = []){
        $stuload = StudentStuloadInformation::find($stuload_id);
        $instance_id = $stuload->course_creation_instance_id;
        $instance = CourseCreationInstance::find($instance_id);
        $stuLoadTotal = 0;

        if(isset($instance->terms) && $instance->terms->count() > 0):
            foreach($instance->terms as $term):
                $termStart = (isset($term->start_date) && !empty($term->start_date) ? date('Y-m-d', strtotime($term->start_date)) : '');
                $termEnd = (isset($term->end_date) && !empty($term->end_date) ? date('Y-m-d', strtotime($term->end_date)) : '');

                if($this->isTermInRange($dateRanges, $termStart, $termEnd)):
                    $termStuload = StudentTermStuload::where('student_id', $student_id)->where('student_course_relation_id', $stuload->student_course_relation_id)
                                ->where('student_stuload_information_id', $stuload->id)->where('instance_term_id', $term->id)->get()->first();
                    if(isset($termStuload->student_load) && $termStuload->student_load > 0):
                        $stuLoadTotal += $termStuload->student_load;
                    endif;
                endif;
            endforeach;
        endif;

        return $stuLoadTotal;
    }
}
