<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\Country;
use App\Models\Course;
use App\Models\CourseBaseDatafutures;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\CourseModule;
use App\Models\Disability;
use App\Models\DisableAllowance;
use App\Models\EquivalentOrLowerQualification;
use App\Models\Ethnicity;
use App\Models\ExchangeProgramme;
use App\Models\FeeEligibility;
use App\Models\FundingCompletion;
use App\Models\FundingLength;
use App\Models\HeapesPopulation;
use App\Models\HesaGender;
use App\Models\HesaQualificationAward;
use App\Models\HesaQualificationSubject;
use App\Models\HighestQualificationOnEntry;
use App\Models\InstanceTerm;
use App\Models\LocationOfStudy;
use App\Models\MajorSourceOfTuitionFee;
use App\Models\ModuleOutcome;
use App\Models\ModuleResult;
use App\Models\NonRegulatedFeeFlag;
use App\Models\Plan;
use App\Models\PreviousProvider;
use App\Models\QualificationTypeIdentifier;
use App\Models\ReasonForEngagementEnding;
use App\Models\ReasonForEndingCourseSession;
use App\Models\Religion;
use App\Models\Semester;
use App\Models\SexIdentifier;
use App\Models\SexualOrientation;
use App\Models\SlcAttendance;
use App\Models\Student;
use App\Models\StudentAward;
use App\Models\StudentCourseRelation;
use App\Models\StudentCourseSessionDatafuture;
use App\Models\StudentDatafuture;
use App\Models\StudentDisability;
use App\Models\StudentModuleInstanceDatafuture;
use App\Models\StudentQualification;
use App\Models\StudentStuloadInformation;
use App\Models\StudentSupportEligibility;
use App\Models\StudentTermStuload;
use App\Models\StudyMode;
use App\Models\SuspensionOfActiveStudy;
use App\Models\TermTimeAccommodationType;
use Google\Service\Datastore\Count;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatafutureController extends Controller
{
    public function index(Student $student){
        $student->load(['other', 'contact', 'qualHigest', 'disability']);
        $course_id = $student->crel->creation->course_id;
        $module_ids = $this->getStudentModules($student->id, $course_id);
        
        $autoStuloads = $this->autoLoadStudentStuloads($student->id, $student->crel->id);
        return view('pages.students.live.datafuture', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Student Documents', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'course_id' => $course_id,
            'student_course_relation_id' => $student->crel->id,
            'course' => Course::find($course_id),
            'modules' => CourseModule::where('active', 1)->where('course_id', $course_id)->orderBy('name', 'ASC')->get(),
            'df_course_fields' => CourseBaseDatafutures::with('field')->whereHas('field', function($q){
                            $q->where('datafuture_field_category_id', 1);
                        })->where('course_id', $course_id)->get(),
            'df_qualification_fields' => CourseBaseDatafutures::with('field')->whereHas('field', function($q){
                            $q->where('datafuture_field_category_id', 2);
                        })->where('course_id', $course_id)->get(),
            'df_modules_fields' => CourseModule::whereIn('id', $module_ids)->orderBy('name', 'ASC')->get(),
            'ethnicity' => Ethnicity::where('active', 1)->orderBy('name', 'ASC')->get(),
            'gender' => HesaGender::where('active', 1)->orderBy('name', 'ASC')->get(),
            'countries' => Country::where('active', 1)->orderBy('name', 'ASC')->get(),
            'religion' => Religion::where('active', 1)->orderBy('name', 'ASC')->get(),
            'sexindtity' => SexIdentifier::where('active', 1)->orderBy('name', 'ASC')->get(),
            'sexort' => SexualOrientation::where('active', 1)->orderBy('name', 'ASC')->get(),
            'ttacom' => TermTimeAccommodationType::where('active', 1)->orderBy('name', 'ASC')->get(),
            'disabilities' => Disability::where('active', 1)->orderBy('name', 'ASC')->get(),
            'semesters' => Semester::orderBy('id', 'DESC')->get(),
            'feeelig' => FeeEligibility::where('active', 1)->orderBy('name', 'ASC')->get(),
            'prefprovider' => PreviousProvider::where('active', 1)->orderBy('name', 'ASC')->get(),
            'highestqoe' => HighestQualificationOnEntry::where('active', 1)->orderBy('name', 'ASC')->get(),
            'qualtypeids' => QualificationTypeIdentifier::where('active', 1)->orderBy('name', 'ASC')->get(),
            'qualtypesubs' => HesaQualificationSubject::where('active', 1)->orderBy('name', 'ASC')->get(),
            'endreasons' => ReasonForEngagementEnding::where('active', 1)->orderBy('name', 'ASC')->get(),
            'venue' => (isset($student->crel->propose->venue->id) && $student->crel->propose->venue->id > 0 ? $student->crel->propose->venue : null),
            'stuloads' => StudentStuloadInformation::where('student_id', $student->id)->where('student_course_relation_id', $student->crel->id)->orderBy('id', 'ASC')->get(),
            'modes' => StudyMode::where('active', 1)->orderBy('name', 'ASC')->get(),
            'rsnscsends' => ReasonForEndingCourseSession::where('active', 1)->orderBy('name', 'ASC')->get(),
            'elqs' => EquivalentOrLowerQualification::where('active', 1)->orderBy('name', 'ASC')->get(),
            'fundcomps' => FundingCompletion::where('active', 1)->orderBy('name', 'ASC')->get(),
            'nonregfees' => NonRegulatedFeeFlag::where('active', 1)->orderBy('name', 'ASC')->get(),
            'fundLengths' => FundingLength::where('active', 1)->orderBy('name', 'ASC')->get(),
            'moduleInstances' => $this->getStuloadModuleInstance($student->id, $student->crel->id),
            'modoutcom' => ModuleOutcome::where('active', 1)->orderBy('name', 'ASC')->get(),
            'modresult' => ModuleResult::where('active', 1)->orderBy('name', 'ASC')->get(),
            'disalls' => DisableAllowance::where('active', 1)->orderBy('name', 'ASC')->get(),
            'exchinds' => ExchangeProgramme::where('active', 1)->orderBy('name', 'ASC')->get(),
            'locsdys' => LocationOfStudy::where('active', 1)->orderBy('name', 'ASC')->get(),
            'mustfees' => MajorSourceOfTuitionFee::where('active', 1)->orderBy('name', 'ASC')->get(),
            'notacts' => SuspensionOfActiveStudy::where('active', 1)->orderBy('name', 'ASC')->get(),
            'sseligs' => StudentSupportEligibility::where('active', 1)->orderBy('name', 'ASC')->get(),
            'quals' => HesaQualificationAward::where('active', 1)->orderBy('name', 'ASC')->get(),
            'heapespops' => HeapesPopulation::where('active', 1)->orderBy('name', 'ASC')->get(),
            
        ]);
    }

    public function store(Student $student, Request $request){
        $course_id = $request->course_id;
        $student_course_relation_id = $request->student_course_relation_id;
        $SCSS = (isset($request->SCS) && !empty($request->SCS) ? $request->SCS : []);

        $existSDDF = StudentDatafuture::where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)->get()->first();
        $stdData = [
            'student_id' => $student->id,
            'student_course_relation_id' => $student_course_relation_id,
            'NUMHUS' => $request->NUMHUS,
            'CARELEAVER' => $request->CARELEAVER,
            'ENTRYQUALAWARDID' => $request->ENTRYQUALAWARDID,
            'ENGENDDATE' => (!empty($request->ENGENDDATE) ? date('Y-m-d', strtotime($request->ENGENDDATE)) : null),
            'RSNENGEND' => $request->RSNENGEND
        ];
        if(isset($existSDDF->id) && $existSDDF->id > 0):
            $stdData['updated_by'] = auth()->user()->id;
            StudentDatafuture::where('id', $existSDDF->id)->where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)->update($stdData);
        else:
            $stdData['created_by'] = auth()->user()->id;
            StudentDatafuture::create($stdData);
        endif;

        if(!empty($SCSS)):
            foreach($SCSS as $SCSID => $SCS):
                $SCSMS = (isset($SCS['SCSM']) && !empty($SCS['SCSM']) ? $SCS['SCSM'] : []);
                $LOADS = (isset($SCS['LOADS']) && !empty($SCS['LOADS']) ? $SCS['LOADS'] : []);

                $SCSDATA = [];
                $SCSDATA['student_id'] = $student->id;
                $SCSDATA['student_course_relation_id'] = $student_course_relation_id;
                $SCSDATA['student_stuload_information_id'] = $SCSID;
                $SCSDATA['ELQ'] = (!empty($SCS['ELQ']) ? $SCS['ELQ'] : null);
                $SCSDATA['FUNDCOMP'] = (!empty($SCS['FUNDCOMP']) ? $SCS['FUNDCOMP'] : null);
                $SCSDATA['FUNDLENGTH'] = (!empty($SCS['FUNDLENGTH']) ? $SCS['FUNDLENGTH'] : null);
                $SCSDATA['NONREGFEE'] = (!empty($SCS['NONREGFEE']) ? $SCS['NONREGFEE'] : null);
                $SCSDATA['FINSUPTYPE'] = (!empty($SCS['FINSUPTYPE']) ? $SCS['FINSUPTYPE'] : null);
                $SCSDATA['DISTANCE'] = (!empty($SCS['DISTANCE']) ? $SCS['DISTANCE'] : null);
                $SCSDATA['STUDYPROPORTION'] = (!empty($SCS['STUDYPROPORTION']) ? $SCS['STUDYPROPORTION'] : 100);

                $rowExist = StudentCourseSessionDatafuture::where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)
                            ->where('student_stuload_information_id', $SCSID)->get()->first();
                if(isset($rowExist->id) && $rowExist->id > 0):
                    $SCSDATA['updated_by'] = auth()->user()->id;
                    StudentCourseSessionDatafuture::where('id', $rowExist->id)->where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)
                            ->where('student_stuload_information_id', $SCSID)->update($SCSDATA);
                else:
                    $SCSDATA['created_by'] = auth()->user()->id;
                    StudentCourseSessionDatafuture::create($SCSDATA);
                endif;

                if(!empty($LOADS)):
                    foreach($LOADS as $instanceTermId => $termDetails):
                        $autoLoad = (isset($termDetails['auto_stuload']) && $termDetails['auto_stuload'] == 1 ? true : false);
                        $loadData = [
                            'student_id' => $student->id,
                            'student_course_relation_id' => $student_course_relation_id,
                            'student_stuload_information_id' => $SCSID,
                            'instance_term_id' => $instanceTermId,
                            'auto_stuload' => $autoLoad,
                        ];
                        if(!$autoLoad):
                            $loadData['student_load'] = (isset($termDetails['student_load']) && $termDetails['student_load'] > 0 ? $termDetails['student_load'] : 0);
                        endif;
                        $existLoad = StudentTermStuload::where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)
                                        ->where('student_stuload_information_id', $SCSID)->where('instance_term_id', $instanceTermId)->get()->first();
                            
                        if(isset($existLoad->id) && $existLoad->id > 0):
                            $loadData['updated_by'] = auth()->user()->id;
                            StudentTermStuload::where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)
                                ->where('student_stuload_information_id', $SCSID)->where('instance_term_id', $instanceTermId)->update($loadData);
                        else:
                            $loadData['created_by'] = auth()->user()->id;
                            StudentTermStuload::create($loadData);
                        endif;
                    endforeach;
                endif;

                if(!empty($SCSMS)):
                    foreach($SCSMS as $MODINSTID => $SCSM):
                        $instnce_term_id = (isset($SCSM['instnce_term_id']) && $SCSM['instnce_term_id'] > 0 ? $SCSM['instnce_term_id'] : null);
                        $course_module_id = (isset($SCSM['MODID']) && $SCSM['MODID'] > 0 ? $SCSM['MODID'] : null);
                        $MODS = [];
                        $MODS['student_id'] = $student->id;
                        $MODS['student_course_relation_id'] = $student_course_relation_id;
                        $MODS['student_stuload_information_id'] = $SCSID;
                        $MODS['instance_term_id'] = $instnce_term_id;
                        $MODS['course_module_id'] = $course_module_id;
                        $MODS['MODULEOUTCOME'] = (!empty($SCSM['MODULEOUTCOME']) ? $SCSM['MODULEOUTCOME'] : null);
                        $MODS['MODULERESULT'] = (!empty($SCSM['MODULERESULT']) ? $SCSM['MODULERESULT'] : null);

                        $rowExist = StudentModuleInstanceDatafuture::where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)
                            ->where('student_stuload_information_id', $SCSID)->where('instance_term_id', $instnce_term_id)->where('course_module_id', $course_module_id)->get()->first();
                        if(isset($rowExist->id) && $rowExist->id > 0):
                            $MODS['updated_by'] = auth()->user()->id;
                            StudentModuleInstanceDatafuture::where('id', $rowExist->id)->where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)
                                    ->where('student_stuload_information_id', $SCSID)->update($MODS);
                        else:
                            $SCSDATA['created_by'] = auth()->user()->id;
                            StudentModuleInstanceDatafuture::create($MODS);
                        endif;
                    endforeach;
                endif;
            endforeach;
        endif;

        return response()->json(['msg' => 'Datafuture missing data successfully updated.'], 200);
    }

    public function autoLoadStudentStuloads($student_id, $student_course_relation_id){
        $student = Student::find($student_id);
        $studentCrel = StudentCourseRelation::find($student_course_relation_id);
        $course_id = (isset($studentCrel->creation->course_id) && $studentCrel->creation->course_id > 0 ? $studentCrel->creation->course_id : null);
        $course_creation_id = $studentCrel->course_creation_id;

        $counts = 0;
        $instances = CourseCreationInstance::where('course_creation_id', $course_creation_id)->orderBy('id', 'ASC')->get();
        if($instances->count() > 0):
            foreach($instances as $instance):
                $existInstance = StudentStuloadInformation::where('student_id', $student_id)->where('student_course_relation_id', $student_course_relation_id)->where('course_creation_instance_id', $instance->id)->withTrashed()->get();
                if($existInstance->count() == 0):
                    $existingRowCount = StudentStuloadInformation::where('student_id', $student_id)->where('student_course_relation_id', $student_course_relation_id)->get()->count();
                    $lastRow = StudentStuloadInformation::where('student_id', $student_id)->where('student_course_relation_id', $student_course_relation_id)->orderBy('id', 'DESC')->get();

                    $priprov_id = null;
                    $qual_type = null;
                    $qual_sub = null;
                    $qual_sit = null;
                    $qualent3_id = null;
                    $sid_number = (isset($lastRow->sid_number) && !empty($lastRow->sid_number) ? $lastRow->sid_number : $this->calculateSidNumber($student->registration_no));
                    $is_education_qualification = (isset($student->other->is_education_qualification) && $student->other->is_education_qualification > 0 ? $student->other->is_education_qualification : 0);
                    if($is_education_qualification == 1):
                        $qualification = StudentQualification::orderBy('id', 'DESC')->get()->first();
                        $priprov_id = (isset($qualification->previous_provider_id) && $qualification->previous_provider_id > 0 ? $qualification->previous_provider_id : null);
                        $qual_type = (isset($qualification->qualification_type_identifier_id) && $qualification->qualification_type_identifier_id > 0 ? $qualification->qualification_type_identifier_id : null);
                        $qual_sub = (isset($qualification->hesa_qualification_subject_id) && $qualification->hesa_qualification_subject_id > 0 ? $qualification->hesa_qualification_subject_id : null);
                        $qual_sit = (isset($qualification->hesa_exam_sitting_venue_id) && $qualification->hesa_exam_sitting_venue_id > 0 ? $qualification->hesa_exam_sitting_venue_id : null);
                        $qualent3_id = (isset($qualification->highest_qualification_on_entry_id) && $qualification->highest_qualification_on_entry_id > 0 ? $qualification->highest_qualification_on_entry_id : null);
                    endif;
                    $disall_id = null;
                    if(isset($student->other->disability_status) && $student->other->disability_status > 0 ? $student->other->disability_status : 0):
                        $studentDis = StudentDisability::where('student_id', $student->id)->orderBy('id', 'DESC')->get()->first();
                        $disall_id = (isset($studentDis->disability_id) && $studentDis->disability_id > 0 ? $studentDis->disability_id : null);
                    endif;
                    $awards = StudentAward::where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)->orderBy('id', 'DESC')->get()->first();
                    $class_id = (isset($awards->qual_award_result_id) && $awards->qual_award_result_id > 0 ? $awards->qual_award_result_id : null);
                    

                    $data = [
                        'student_id' => $student->id,
                        'student_course_relation_id' => $student_course_relation_id,
                        'course_creation_instance_id' => $instance->id,
                        'year_of_the_course' => ($existingRowCount > 0 ? ($existingRowCount + 1) : 1),
                        'auto_stuload' => 1,
                        'student_load' => null,
                        'disall_id' => $disall_id,
                        'exchind_id' => null,
                        'gross_fee' => (isset($instance->fees) && $instance->fees > 0 ? $instance->fees : 0),
                        'locsdy_id' => null,
                        'mode_id' => 1,
                        'mstufee_id' => null,
                        'netfee' => (isset($instance->fees) && $instance->fees > 0 ? $instance->fees : 0),
                        'notact_id' => null,
                        'periodstart' => (isset($instance->start_date) && !empty($instance->start_date) ? date('Y-m-d', strtotime($instance->start_date)) : null),
                        'periodend' => (isset($instance->end_date) && !empty($instance->end_date) ? date('Y-m-d', strtotime($instance->end_date)) : null),
                        'priprov_id' => $priprov_id,
                        'sselig_id' => null,
                        'yearprg' => (isset($student->stuload) && $student->stuload->count() > 0 ? $student->stuload->count() + 1 : 1),
                        'yearstu' => (isset($student->stuload) && $student->stuload->count() > 0 ? $student->stuload->count() + 1 : 1),
                        'qual_id' => null,
                        'heapespop_id' => null,
                        'class_id' => $class_id,
                        'courseaim_id' => $course_id,
                        'genderid_id' => (isset($student->other->hesa_gender_id) && $student->other->hesa_gender_id > 0 ? $student->other->hesa_gender_id : null),
                        'regbody_id' => null,
                        'relblf_id' => (isset($student->other->religion_id) && $student->other->religion_id > 0 ? $student->other->religion_id : null),
                        'rsnend_id' => null,
                        'sexort_id' => (isset($student->other->sexual_orientation_id) && $student->other->sexual_orientation_id > 0 ? $student->other->sexual_orientation_id : null),
                        'ttcid_id' => (isset($student->contact->term_time_accommodation_type_id) && $student->contact->term_time_accommodation_type_id > 0 ? $student->contact->term_time_accommodation_type_id : null),
                        'uhn_number' => (isset($student->uhn_no) && !empty($student->uhn_no) ? $student->uhn_no : null),
                        'sid_number' => $sid_number,
                        'provider_name' => $priprov_id,
                        'qual_type' => $qual_type,
                        'qual_sub' => $qual_sub,
                        'qual_sit' => $qual_sit,
                        'domicile_id' => (isset($student->contact->permanent_country_id) && $student->contact->permanent_country_id > 0 ? $student->contact->permanent_country_id : null),
                        'numhus' => null,
                        'owninst' => $student->registration_no,
                        'comdate' => (isset($studentCrel->course_start_date) && !empty($studentCrel->course_start_date) ? date('Y-m-d', strtotime($studentCrel->course_start_date)) : null),
                        'enddate' => (isset($studentCrel->course_end_date) && !empty($studentCrel->course_end_date) ? date('Y-m-d', strtotime($studentCrel->course_end_date)) : null),
                        'qualent3_id' => $qualent3_id,
                        'reporting_period' => 0,
                        'created_by' => auth()->user()->id,
                    ];

                    $stuload = StudentStuloadInformation::create($data);
                    if($stuload->id):
                        $counts += 1;
                    endif;
                endif;
            endforeach;
        endif;

        return $counts;
    }

    public function getStuloadModuleInstance($student_id, $student_course_relation_id){
        $res = [];
        $stuloads = StudentStuloadInformation::where('student_id', $student_id)->where('student_course_relation_id', $student_course_relation_id)->orderBy('id', 'ASC')->get();
        if($stuloads->count() > 0):
            foreach($stuloads as $stu):
                $instance_id = $stu->course_creation_instance_id;
                $instance = CourseCreationInstance::find($instance_id);
                $stuLoadTotal = 0;
                if(isset($instance->terms) && $instance->terms->count() > 0):
                    foreach($instance->terms as $term):
                        $term_declaration_id = $term->term_declaration_id;
                        $termStart = (isset($term->start_date) && !empty($term->start_date) ? date('Y-m-d', strtotime($term->start_date)) : '');
                        $termEnd = (isset($term->end_date) && !empty($term->end_date) ? date('Y-m-d', strtotime($term->end_date)) : '');

                        $existLoad = StudentTermStuload::where('student_id', $student_id)->where('student_course_relation_id', $student_course_relation_id)
                                    ->where('student_stuload_information_id', $stu->id)->where('instance_term_id', $term->id)->get()->first();
                        $autoLoad = (isset($existLoad->id) && $existLoad->id > 0 ? $existLoad->auto_stuload : 1);
                        $stuload = (isset($existLoad->id) && $existLoad->id > 0 ? $existLoad->student_load : 0);
                        if($autoLoad == 1):
                            $attendanceCodes = SlcAttendance::where('student_id', $student_id)->where('student_course_relation_id', $student_course_relation_id)->where('term_declaration_id', $term_declaration_id)->pluck('attendance_code_id')->unique()->toArray();
                            $stuload = (!empty($attendanceCodes) && in_array(1, $attendanceCodes) && !in_array(6, $attendanceCodes) ? 33 : 0);
                            $stuLoadTotal += $stuload;

                            $loadData = [
                                'student_id' => $student_id,
                                'student_course_relation_id' => $student_course_relation_id,
                                'student_stuload_information_id' => $stu->id,
                                'instance_term_id' => $term->id,
                                'auto_stuload' => $autoLoad,
                                'student_load' => $stuload,
                            ];
                            if(isset($existLoad->id) && $existLoad->id > 0):
                                $loadData['updated_by'] = auth()->user()->id;
                                StudentTermStuload::where('student_id', $student_id)->where('student_course_relation_id', $student_course_relation_id)
                                    ->where('student_stuload_information_id', $stu->id)->where('instance_term_id', $term->id)->update($loadData);
                            else:
                                $loadData['created_by'] = auth()->user()->id;
                                StudentTermStuload::create($loadData);
                            endif;
                        endif;

                        $res[$stu->id][$term->id]['name'] = (isset($term->termDeclaration->name) && !empty($term->termDeclaration->name) ? $term->termDeclaration->name : $term_declaration_id);
                        $res[$stu->id][$term->id]['start'] = $termStart;
                        $res[$stu->id][$term->id]['end'] = $termEnd;
                        $res[$stu->id][$term->id]['auto_stuload'] = $autoLoad;
                        $res[$stu->id][$term->id]['student_load'] = $stuload;

                        $plan_ids = Attendance::where('student_id', $student_id)->whereBetween('attendance_date', [$termStart, $termEnd])->pluck('plan_id')->unique()->toArray();
                        if(!empty($plan_ids)):
                            $plans = Plan::with('attenTerm')->whereIn('id', $plan_ids)->where(function($q){
                                        $q->whereNotIn('class_type', ['Tutorial'])->orWhereNull('class_type');
                                    })->orderBy('id', 'DESC')->get();
                            
                            if($plans->count() > 0):
                                $mod = 1;
                                foreach($plans as $pln):
                                    $theRow = StudentModuleInstanceDatafuture::where('student_id', $student_id)->where('student_course_relation_id', $student_course_relation_id)
                                              ->where('student_stuload_information_id', $stu->id)->where('instance_term_id', $term->id)
                                              ->where('course_module_id', $pln->creations->course_module_id)->get()->first();
                                    $res[$stu->id][$term->id]['modules'][$mod]['MODINSTID'] = $pln->id;
                                    $res[$stu->id][$term->id]['modules'][$mod]['MODINS_MODID'] = $pln->creations->course_module_id;
                                    $res[$stu->id][$term->id]['modules'][$mod]['MODINSTENDDATE'] = $pln->attenTerm->end_date;
                                    $res[$stu->id][$term->id]['modules'][$mod]['MODINSTSTARTDATE'] = $pln->attenTerm->start_date;
                                    $res[$stu->id][$term->id]['modules'][$mod]['MODULEOUTCOME'] = (isset($theRow->MODULEOUTCOME) && !empty($theRow->MODULEOUTCOME) ? $theRow->MODULEOUTCOME : '');
                                    $res[$stu->id][$term->id]['modules'][$mod]['MODULERESULT'] = (isset($theRow->MODULERESULT) && !empty($theRow->MODULERESULT) ? $theRow->MODULERESULT : '');
                                    $mod++;
                                endforeach;
                            endif;
                        endif;
                    endforeach;
                endif;
                $stuLoadTotal = ($stuLoadTotal == 99 ? 100 : $stuLoadTotal);
                StudentStuloadInformation::where('id', $stu->id)->update(['student_load' => $stuLoadTotal]);
            endforeach;
        endif;

        //dd($res);
        return $res;
    }

    public function storeHesaInstance(Student $student, Request $request){
        $course_id = $request->course_id;
        $student_course_relation_id = $request->student_course_relation_id;
        $studentCrel = StudentCourseRelation::find($student_course_relation_id);
        $course_creation_instance_id = (isset($request->course_creation_instance_id) && $request->course_creation_instance_id > 0 ? $request->course_creation_instance_id : 0);
        
        $existInstanceStuload = StudentStuloadInformation::where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)->where('course_creation_instance_id')->withTrashed()->get();
        if($existInstanceStuload->count() > 0):
            foreach($existInstanceStuload as $theStuload):
                if(isset($theStuload->deleted_at) && !empty($theStuload->deleted_at)):
                    StudentStuloadInformation::where('id', $theStuload->id)->withTrashed()->restore();
                endif;
            endforeach;
            return response()->json(['msg' => 'Student Stuload successfully created.'], 200);
        else:
            $instance = CourseCreationInstance::find($course_creation_instance_id);
            $existingRowCount = StudentStuloadInformation::where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)->get()->count();
            $lastRow = StudentStuloadInformation::where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)->orderBy('id', 'DESC')->get();

            $priprov_id = null;
            $qual_type = null;
            $qual_sub = null;
            $qual_sit = null;
            $qualent3_id = null;
            $sid_number = (isset($lastRow->sid_number) && !empty($lastRow->sid_number) ? $lastRow->sid_number : $this->calculateSidNumber($student->registration_no));
            $is_education_qualification = (isset($student->other->is_education_qualification) && $student->other->is_education_qualification > 0 ? $student->other->is_education_qualification : 0);
            if($is_education_qualification == 1):
                $qualification = StudentQualification::orderBy('id', 'DESC')->get()->first();
                $priprov_id = (isset($qualification->previous_provider_id) && $qualification->previous_provider_id > 0 ? $qualification->previous_provider_id : null);
                $qual_type = (isset($qualification->qualification_type_identifier_id) && $qualification->qualification_type_identifier_id > 0 ? $qualification->qualification_type_identifier_id : null);
                $qual_sub = (isset($qualification->hesa_qualification_subject_id) && $qualification->hesa_qualification_subject_id > 0 ? $qualification->hesa_qualification_subject_id : null);
                $qual_sit = (isset($qualification->hesa_exam_sitting_venue_id) && $qualification->hesa_exam_sitting_venue_id > 0 ? $qualification->hesa_exam_sitting_venue_id : null);
                $qualent3_id = (isset($qualification->highest_qualification_on_entry_id) && $qualification->highest_qualification_on_entry_id > 0 ? $qualification->highest_qualification_on_entry_id : null);
            endif;
            $disall_id = null;
            if(isset($student->other->disability_status) && $student->other->disability_status > 0 ? $student->other->disability_status : 0):
                $studentDis = StudentDisability::where('student_id', $student->id)->orderBy('id', 'DESC')->get()->first();
                $disall_id = (isset($studentDis->disability_id) && $studentDis->disability_id > 0 ? $studentDis->disability_id : null);
            endif;
            $awards = StudentAward::where('student_id', $student->id)->where('student_course_relation_id', $student_course_relation_id)->orderBy('id', 'DESC')->get()->first();
            $class_id = (isset($awards->qual_award_result_id) && $awards->qual_award_result_id > 0 ? $awards->qual_award_result_id : null);
            

            $data = [
                'student_id' => $student->id,
                'student_course_relation_id' => $student_course_relation_id,
                'course_creation_instance_id' => $course_creation_instance_id,
                'year_of_the_course' => ($existingRowCount > 0 ? ($existingRowCount + 1) : 1),
                'auto_stuload' => 1,
                'student_load' => null,
                'disall_id' => $disall_id,
                'exchind_id' => null,
                'gross_fee' => (isset($instance->fees) && $instance->fees > 0 ? $instance->fees : 0),
                'locsdy_id' => null,
                'mode_id' => 1,
                'mstufee_id' => null,
                'netfee' => (isset($instance->fees) && $instance->fees > 0 ? $instance->fees : 0),
                'notact_id' => null,
                'periodstart' => (isset($instance->start_date) && !empty($instance->start_date) ? date('Y-m-d', strtotime($instance->start_date)) : null),
                'periodend' => (isset($instance->end_date) && !empty($instance->end_date) ? date('Y-m-d', strtotime($instance->end_date)) : null),
                'priprov_id' => $priprov_id,
                'sselig_id' => null,
                'yearprg' => (isset($student->stuload) && $student->stuload->count() > 0 ? $student->stuload->count() + 1 : 1),
                'yearstu' => (isset($student->stuload) && $student->stuload->count() > 0 ? $student->stuload->count() + 1 : 1),
                'qual_id' => null,
                'heapespop_id' => null,
                'class_id' => $class_id,
                'courseaim_id' => $course_id,
                'genderid_id' => (isset($student->other->hesa_gender_id) && $student->other->hesa_gender_id > 0 ? $student->other->hesa_gender_id : null),
                'regbody_id' => null,
                'relblf_id' => (isset($student->other->religion_id) && $student->other->religion_id > 0 ? $student->other->religion_id : null),
                'rsnend_id' => null,
                'sexort_id' => (isset($student->other->sexual_orientation_id) && $student->other->sexual_orientation_id > 0 ? $student->other->sexual_orientation_id : null),
                'ttcid_id' => (isset($student->contact->term_time_accommodation_type_id) && $student->contact->term_time_accommodation_type_id > 0 ? $student->contact->term_time_accommodation_type_id : null),
                'uhn_number' => (isset($student->uhn_no) && !empty($student->uhn_no) ? $student->uhn_no : null),
                'sid_number' => $sid_number,
                'provider_name' => $priprov_id,
                'qual_type' => $qual_type,
                'qual_sub' => $qual_sub,
                'qual_sit' => $qual_sit,
                'domicile_id' => (isset($student->contact->permanent_country_id) && $student->contact->permanent_country_id > 0 ? $student->contact->permanent_country_id : null),
                'numhus' => null,
                'owninst' => $student->registration_no,
                'comdate' => (isset($studentCrel->course_start_date) && !empty($studentCrel->course_start_date) ? date('Y-m-d', strtotime($studentCrel->course_start_date)) : null),
                'enddate' => (isset($studentCrel->course_end_date) && !empty($studentCrel->course_end_date) ? date('Y-m-d', strtotime($studentCrel->course_end_date)) : null),
                'qualent3_id' => $qualent3_id,
                'reporting_period' => 0,
                'created_by' => auth()->user()->id,
            ];

            $stuload = StudentStuloadInformation::create($data);
            if($stuload->id):
                return response()->json(['msg' => 'Student Stuload successfully created.'], 200);
            else:
                return response()->json(['msg' => 'Something went wrong. Please try again later or contact with the administrator.'], 304);
            endif;
        endif;
    }

    public function getInstances(Student $student, Request $request){
        $semester_id = $request->semester_id;
        $course_id = $request->course_id;

        $html = '';
        $course_creations_ids = CourseCreation::where('course_id', $course_id)->where('semester_id', $semester_id)->pluck('id')->unique()->toArray();
        if(!empty($course_creations_ids)):
            $instances = CourseCreationInstance::whereIn('course_creation_id', $course_creations_ids)->orderBy('id', 'DESC')->get();
            if($instances->count() > 0):
                foreach($instances as $inst):
                    $html .= '<tr>';
                        $html .= '<td>';
                            $html .= '<div class="form-check mr-2">';
                                $html .= '<input id="instance_'.$inst->id.'" class="form-check-input" type="radio" name="course_creation_instance_id" value="'.$inst->id.'">';
                                $html .= '<label class="form-check-label" for="instance_'.$inst->id.'">'.$inst->id.'</label>';
                            $html .= '</div>';
                        $html .= '</td>';
                        $html .= '<td>'.(!empty($inst->start_date) ? date('jS F, Y', strtotime($inst->start_date)) : '').'</td>';
                        $html .= '<td>'.(!empty($inst->end_date) ? date('jS F, Y', strtotime($inst->end_date)) : '').'</td>';
                        $html .= '<td>'.($inst->total_teaching_week > 0 ? $inst->total_teaching_week : '0').'</td>';
                    $html .= '</tr>';
                endforeach;
            else:
                $html .= '<tr><td colspan="4"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Instance not found for the semester.</div></td></tr>';
            endif;
        else:
            $html .= '<tr><td colspan="4"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Course relations not found for the semester.</div></td></tr>';
        endif;

        return response()->json(['html' => $html], 200);
    }

    public function getStudentModules($student_id, $course_id){
        $plan_ids = Assign::where('student_id', $student_id)->pluck('plan_id')->unique()->toArray();
        $module_ids = DB::table('plans as pln')
                ->select('mc.course_module_id')
                ->leftJoin('module_creations as mc', 'pln.module_creation_id', 'mc.id')
                ->leftJoin('course_modules as cm', 'mc.course_module_id', 'cm.id')
                ->whereIn('pln.id', $plan_ids)->where('pln.course_id', $course_id)
                ->where('cm.course_id', $course_id)->where('mc.class_type', 'Theory')
                ->pluck('course_module_id')->unique()->toArray();
        
        return $module_ids;
    }

    function calculateSidNumber($student_reg_no){
        $theNumber = substr($student_reg_no, 5);
        $theYear2Digit = substr($theNumber, 0, 2);
        $theUKPRN = 10030391;
        $theAllocatedID = substr($student_reg_no, 7, 6);
        if(strlen($theAllocatedID) < 6):
            $theAllocatedID = sprintf('%06d', $theAllocatedID);
        elseif(strlen($theAllocatedID) > 6):
            $theAllocatedID = substr($theAllocatedID, -6);
        endif;
        $weight = [1 => 1, 2 => 3, 3 => 7, 4 => 9, 5 => 1, 6 => 3, 7 => 7, 8 => 9, 9 => 1, 10 => 3, 11 => 7, 12 => 9, 13 => 1, 14 => 3, 15 => 7, 16 => 9];
        $theWeightMultiplieds = [];

        $theNumber = $theYear2Digit.$theUKPRN.$theAllocatedID;
        $theAllocatedIDArray = str_split($theNumber);
        $theIncrement = 1;
        foreach($theAllocatedIDArray as $theNum):
            $theWeight = $weight[$theIncrement];
            $theMultipliedValue = $theNum * $theWeight;
            $theWeightMultiplieds[] = $theMultipliedValue;
            $theIncrement++;
        endforeach;

        $theTotalOfMultiplied = 0;
        foreach($theWeightMultiplieds as $theWMV):
            $theTotalOfMultiplied += $theWMV;
        endforeach;
        $theLastDigit = substr($theTotalOfMultiplied, -1);
        $theLastDigit = (int) $theLastDigit;
        $theCheckDigit = ($theLastDigit == 0 ? '0' : (10 - $theLastDigit));

        $theSID = $theNumber.$theCheckDigit;

        return $theSID;
    }

    public function getStuloadInformation(Request $request){
        $stuload_id = $request->stuload_id;
        $stuload = StudentStuloadInformation::find($stuload_id);
        $stuload->periodstart = (isset($stuload->periodstart) && !empty($stuload->periodstart) ? date('d-m-Y', strtotime($stuload->periodstart)) : '');
        $stuload->periodend = (isset($stuload->periodend) && !empty($stuload->periodend) ? date('d-m-Y', strtotime($stuload->periodend)) : '');
        $stuload->comdate = (isset($stuload->comdate) && !empty($stuload->comdate) ? date('d-m-Y', strtotime($stuload->comdate)) : '');
        $stuload->enddate = (isset($stuload->enddate) && !empty($stuload->enddate) ? date('d-m-Y', strtotime($stuload->enddate)) : '');

        return response()->json(['row' => $stuload], 200);
    }

    public function updateStuloadInformation(Student $student, Request $request){
        $data = [
            'disall_id' => (!empty($request->disall_id) ? $request->disall_id : null),
            'exchind_id' => (!empty($request->exchind_id) ? $request->exchind_id : null),
            'gross_fee' => (!empty($request->gross_fee) ? $request->gross_fee : null),
            'locsdy_id' => (!empty($request->locsdy_id) ? $request->locsdy_id : null),
            'mode_id' => (!empty($request->mode_id) ? $request->mode_id : null),
            'mstufee_id' => (!empty($request->mstufee_id) ? $request->mstufee_id : null),
            'netfee' => (!empty($request->netfee) ? $request->netfee : null),
            'notact_id' => (!empty($request->notact_id) ? $request->notact_id : null),
            'periodstart' => (!empty($request->periodstart) ? date('Y-m-d', strtotime($request->periodstart)) : null),
            'periodend' => (!empty($request->periodend) ? date('Y-m-d', strtotime($request->periodend)) : null),
            'priprov_id' => (!empty($request->priprov_id) ? $request->priprov_id : null),
            'sselig_id' => (!empty($request->sselig_id) ? $request->sselig_id : null),
            'yearprg' => (!empty($request->yearprg) ? $request->yearprg : null),
            'yearstu' => (!empty($request->yearstu) ? $request->yearstu : null),
            'qual_id' => (!empty($request->qual_id) ? $request->qual_id : null),
            'heapespop_id' => (!empty($request->heapespop_id) ? $request->heapespop_id : null),
            'comdate' => (!empty($request->comdate) ? date('Y-m-d', strtotime($request->comdate)) : null),
            'enddate' => (!empty($request->enddate) ? date('Y-m-d', strtotime($request->enddate)) : null),
            'updated_by' => auth()->user()->id
        ];
        StudentStuloadInformation::where('student_id', $student->id)->where('id', $request->id)->update($data);

        return response()->json(['msg' => 'Student stuload information successfully updated.'], 200);
    }
}
