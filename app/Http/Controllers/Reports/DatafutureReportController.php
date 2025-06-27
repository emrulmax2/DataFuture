<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Course;
use App\Models\CourseBaseDatafutures;
use App\Models\CourseCreationInstance;
use App\Models\CourseModule;
use App\Models\InstanceTerm;
use App\Models\Plan;
use App\Models\Student;
use App\Models\StudentStuloadInformation;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $course_ids = [$course_id];

        $XML = $this->generateXml($course_ids, $student_ids, $dateRanges);
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

                $course_creation_instance_id = $stuloads->pluck('course_creation_instance_id')->unique()->toArray();
                dd(count($course_creation_instance_id));
            endif;
        endif;

        $XML = $this->generateXml($course_ids, $student_ids, $dateRanges);
        dd($course_ids);
    }

    public function generateXml($course_ids, $student_ids, $dateRanges = []){
        $XML = '';

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
                $COURSE_XML .= (isset($course->name) && !empty($course->name) ? '<COURSETITLE>'.$course_id.'</COURSETITLE>' : '');

                if($dfFields->count() > 0):
                    foreach($dfFields as $dfld):
                        $name = (isset($dfld->field->name) && !empty($dfld->field->name) ? $dfld->field->name : '');
                        $value = (isset($dfld->field_value) && !empty($dfld->field_value) ? trim($dfld->field_value) : '');

                        if($name == 'INITIATIVEID' || $name == 'VALIDFROM' || $name == 'VALIDTO'):
                            $COURSE_INI .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                        elseif($name == 'COURSEREFRNCID' || $name == 'COURSEREFRNCIDTYPE'):
                            $COURSE_REF .= (!empty($name) && !empty($value) ? '<'.$name.'>'.$value.'</'.$name.'>' : '');
                        elseif($name == 'HESAID' || $name == 'ROLETYPE' || $name == 'CRPROPORTION'):
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
        $module_ids = $this->getAllModuleIds($course_ids, $student_ids);
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
        $sessionYears = $this->getAllSessionYears($student_ids, $dateRanges = []);
        if($sessionYears && $sessionYears->count() > 0):
            foreach($sessionYears as $SES):
                $SESYEAR_XML = '';
                $SESYEAR_XML .= (isset($SES->id) && !empty($SES->id) ? '<SESSIONYEARID>'.$SES->id.'</SESSIONYEARID>' : '');
                $SESYEAR_XML .= (isset($SES->firstTerm->termDeclaration->name) && !empty($SES->firstTerm->termDeclaration->name) ? '<OWNSESSIONID>'.$SES->firstTerm->termDeclaration->name.'</OWNSESSIONID>' : '');
                $SESYEAR_XML .= (isset($SES->end_date) && !empty($SES->end_date) && $SES->end_date != '0000-00-00' ? '<SYENDDATE>'.$SES->end_date.'</SYENDDATE>' : '');
                $SESYEAR_XML .= (isset($SES->start_date) && !empty($SES->start_date) && $SES->start_date != '0000-00-00' ? '<SYSTARTDATE>'.$SES->start_date.'</SYSTARTDATE>' : '');
                
                if(!empty($SESYEAR_XML)): $XML .= '<SessionYear>'.$SESYEAR_XML.'</SessionYear>'; endif;
            endforeach;
        endif;
        /* SESSION YEARS XML END */

        /* STUDENT XML START */
        if(!empty($student_ids)):
            foreach($student_ids as $student_id):
                $student_crels = $this->getStudentCourseRelations($student_id, $dateRanges);
                $student = Student::with('other', 'contact', 'qualHigest', 'disability', 'termStatus')->find($student_id);

                if(!empty($student_crels)):
                    foreach($student_crels as $crel):

                    endforeach;
                endif;
            endforeach;
        endif;
        /* STUDENT XML END */
    }

    public function getAllModuleIds($course_ids, $student_ids, $dateRanges = []){
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
                $instance_term = InstanceTerm::whereIn('course_creation_instance_id', $instance_ids)->get();
                $instance_term_ids = $instance_term->pluck('id')->unique()->toArray();
                //$plan_ids = Plan::whereIn()
            endif;
        endif;


        $plan_ids = Assign::whereIn('student_id', $student_ids)->pluck('plan_id')->unique()->toArray();
        $module_ids = DB::table('plans as pln')
                ->select('mc.course_module_id')
                ->leftJoin('module_creations as mc', 'pln.module_creation_id', 'mc.id')
                ->leftJoin('course_modules as cm', 'mc.course_module_id', 'cm.id')
                ->whereIn('pln.id', $plan_ids)->whereIn('pln.course_id', $course_ids)
                ->where('cm.course_id', $course_ids)->where('mc.class_type', 'Theory')
                ->pluck('course_module_id')->unique()->toArray();
        
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
}
