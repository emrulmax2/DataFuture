<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndividualModulCreationRequest;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Models\ModuleCreation;
use App\Models\InstanceTerm;
use App\Models\CourseModule;
use App\Models\ModuleLevel;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseModuleBaseAssesment;
use App\Models\ELearningActivitySetting;
use App\Models\PlanTask;
use App\Models\Semester;
use App\Models\TermDeclaration;
use App\Models\TermType;
use Illuminate\Support\Facades\DB;

class TermModuleCreationController extends Controller
{
    public function index()
    {
        return view('pages.course-management.module-creations.index', [
            // Opts this screen into the redesigned module shell.
            'layout' => 'course-top-menu',
            'title' => 'Term Module Creations - London Churchill College',
            'subtitle' => 'Term Module Creations',
            'cmPageTitle' => 'Term Module Creations',
            'cmBackUrl' => route('course.management'),
            'cmBackLabel' => 'Back to Course Management',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => route('course.management')],
                ['label' => 'Term Modules', 'href' => 'javascript:void(0);']
            ],

            'semesters' => Semester::orderBy('name', 'ASC')->get(),
            'courses' => Course::all(),
            'academic_years' =>AcademicYear::orderBy('to_date','desc')->get(),
            'termDeclaration' => TermDeclaration::all(),
            'termType' => TermType::all(),
            'terms' => InstanceTerm::all(),
        ]);
    }

    public function list(Request $request){
        //$courses = (isset($request->courses) && !empty($request->courses) ? $request->courses : 0);
        $instance_term = (isset($request->instance_term) && $request->instance_term > 0 ? $request->instance_term : 0);

        $query = DB::table('instance_terms as it')
                    ->select('it.*', 'cci.course_creation_id', 'cc.course_id', 'cc.semester_id', 'c.name as course_name', 's.name as semester_name', 'td.name as term_dec_name', 'tt.name as term_type_name')
                    ->leftJoin('term_declarations as td', 'it.term_declaration_id', '=', 'td.id')
                    ->leftJoin('term_types as tt', 'it.term_type_id', '=', 'tt.id')
                    ->leftJoin('course_creation_instances as cci', 'it.course_creation_instance_id', '=', 'cci.id')
                    ->leftJoin('course_creations as cc', 'cci.course_creation_id', '=', 'cc.id')
                    ->leftJoin('courses as c', 'cc.course_id', '=', 'c.id')
                    ->leftJoin('semesters as s', 'cc.semester_id', '=', 's.id');
        // if($courses > 0):
        //     $query->where('c.id', '=', $courses);
        // endif;
        if($instance_term > 0):
            $query->where('it.id', '=', $instance_term);
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        // 1, not '' — an empty string reaches Tabulator as NaN and breaks the pager.
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : 1;

        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);
        
        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();
        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $instanceTermId = $list->id;
                $moduleCreations = ModuleCreation::where('instance_term_id', $instanceTermId)->get();
                $moduleCreationsCount = $moduleCreations->count();
                $moduleCreationIds = $moduleCreations->pluck('id')->toArray();
                $planTasks = PlanTask::whereIn('module_creation_id',$moduleCreationIds)->get()->count();
                
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'course_creation_instance_id' => $list->course_creation_instance_id,
                    'course_creation_id' => $list->course_creation_id,
                    'course_id' => $list->course_id,
                    'course_name' => $list->course_name,
                    'term_dec_name' => (isset($list->term_dec_name) && !empty($list->term_dec_name) ? $list->term_dec_name : ''),
                    'term_type' => (isset($list->term_type_name) && !empty($list->term_type_name) ? $list->term_type_name : ''),
                    'start_date' => (isset($list->start_date) && !empty($list->start_date) && $list->start_date != '0000-00-00' ? date('jS F, Y', strtotime($list->start_date)) : ''),
                    'end_date' => (isset($list->end_date) && !empty($list->end_date) && $list->end_date != '0000-00-00' ? date('jS F, Y', strtotime($list->end_date)) : ''),
                    'modules_count' => $moduleCreationsCount,
                    'planTasks_count' => isset($planTasks) ? $planTasks : 0,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'total' => $total_rows, 'data' => $data]);
    }

    public function add($instanceTermId, $courseId){
        return view('pages.course-management.module-creations.add', [
            // Opts this screen into the redesigned module shell.
            'layout' => 'course-top-menu',
            'title' => 'Add Term Module Creations - London Churchill College',
            'subtitle' => 'Add Term Module Creations',
            'cmPageTitle' => 'Add Term Module Creations',
            'cmBackUrl' => route('term.module.creation'),
            'cmBackLabel' => 'Back to Term Modules',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => route('course.management')],
                ['label' => 'Term Modules', 'href' => route('term.module.creation')],
                ['label' => 'Add', 'href' => 'javascript:void(0);']
            ],
            'instanceTerm' => InstanceTerm::find($instanceTermId),
            'modules' => CourseModule::where('course_id', $courseId)->where('active', 1)->orderBy('name','ASC')->get(),
            'instanceTermId' => $instanceTermId,
            'courseId' => $courseId,
        ]);
    }

    public function store(Request $request){
        $moduleid = $request->moduleid;
        $instanceTermId = $request->instanceTermId;
        $courseId = $request->courseId;
        
        if(!empty($moduleid)):
            foreach($moduleid as $mod):
                $courseModule = CourseModule::find($mod);
                $data = [];
                $data['instance_term_id'] = $instanceTermId;
                $data['course_module_id'] = $mod;
                $data['module_level_id'] = (isset($courseModule->module_level_id) && $courseModule->module_level_id > 0 ? $courseModule->module_level_id : null);
                $data['module_name'] = (isset($courseModule->name) && !empty($courseModule->name) ? $courseModule->name : null);
                $data['code'] = (isset($courseModule->code) && !empty($courseModule->code) ? $courseModule->code : null);
                $data['status'] = (isset($courseModule->status) && !empty($courseModule->status) ? $courseModule->status : 'optional');
                $data['credit_value'] = (isset($courseModule->credit_value) && !empty($courseModule->credit_value) ? $courseModule->credit_value : '');
                $data['unit_value'] = (isset($courseModule->unit_value) && !empty($courseModule->unit_value) ? $courseModule->unit_value : '');
                $data['class_type'] = (isset($courseModule->class_type) && !empty($courseModule->class_type) ? $courseModule->class_type : null);
                $data['created_by'] = auth()->user()->id;


                $moduleCreation = ModuleCreation::create($data);

                $eLearningActivitys = ELearningActivitySetting::all();
                foreach($eLearningActivitys as $eLearningActivity) :
                    $planTask = new PlanTask();
                    $planTask->name = $eLearningActivity->category;
                    $planTask->description = $eLearningActivity->category;
                    $planTask->category = $eLearningActivity->category;
                    $planTask->module_creation_id = $moduleCreation->id;
                    $planTask->logo = $eLearningActivity->logo;
                    $planTask->days_reminder = $eLearningActivity->days_reminder;
                    $planTask->is_mandatory = $eLearningActivity->is_mandatory;
                    $planTask->e_learning_activity_setting_id = $eLearningActivity->id;
                    $planTask->created_by = auth()->user()->id;
                    $planTask->save();
                endforeach;
            endforeach;
            return response()->json(['message' => 'Selected modules successfully inserted. Click <a class="text-success font-medium" href="'.route('term.module.creation.module.details', $instanceTermId).'"><u>here</u></a> to redirect the details page.', 'red' => route('term.module.creation.module.details', $instanceTermId)], 200);
        else:
            return response()->json(['message' => 'Moudes can not be empty. Please select at least one moudle from available module.'], 422);
        endif;
    }
    public function updatePlanTask($id) {
        
        $moduleCreations = ModuleCreation::where('instance_term_id',$id)->get();
        $eLearningActivitys = ELearningActivitySetting::all();
        if($moduleCreations->count()>0):
        foreach($moduleCreations as $moduleCreation):
            foreach($eLearningActivitys as $eLearningActivity) :
                $planTask = PlanTask::where("module_creation_id",$moduleCreation->id)
                            ->where('e_learning_activity_setting_id', $eLearningActivity->id)
                            ->get()
                            ->first();
                if(!$planTask)
                $planTask = new PlanTask();

                $planTask->name = $eLearningActivity->category;
                $planTask->description = $eLearningActivity->category;
                $planTask->category = $eLearningActivity->category;
                $planTask->module_creation_id = $moduleCreation->id;
                $planTask->logo = $eLearningActivity->logo;
                $planTask->days_reminder = $eLearningActivity->days_reminder;
                $planTask->is_mandatory = $eLearningActivity->is_mandatory;
                $planTask->e_learning_activity_setting_id = $eLearningActivity->id;
                $planTask->created_by = auth()->user()->id;
                $planTask->save();
            endforeach;
        endforeach;
            return response()->json(['message' => 'Successfully regenerated'], 200);
        else:
            return response()->json(['message' => 'Couldn\'t regenerated'], 422);
        
        endif;
    }
    public function moduleDetails($instanceTermId){
        return view('pages.course-management.module-creations.add-details', [
            // Opts this screen into the redesigned module shell.
            'layout' => 'course-top-menu',
            'title' => 'Term Module Details - London Churchill College',
            'subtitle' => 'Term Module Details',
            'cmPageTitle' => 'Term Module Details',
            'cmBackUrl' => route('term.module.creation'),
            'cmBackLabel' => 'Back to Term Modules',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => route('course.management')],
                ['label' => 'Term Modules', 'href' => route('term.module.creation')],
                ['label' => 'Module Details', 'href' => 'javascript:void(0);']
            ],
            'instanceTerm' => InstanceTerm::find($instanceTermId),
            'moduleCreations' => ModuleCreation::where('instance_term_id', $instanceTermId)->orderBy('id')->get(),
        ]);
    }

    public function show($instanceTermId){
        $instance_term = InstanceTerm::find($instanceTermId);
        $courseId = $instance_term->instance->creation->course_id;
        $termRow = DB::table('instance_terms as it')
                    ->select('it.*', 'cci.course_creation_id', 'cc.course_id', 'cc.semester_id', 'c.name as course_name', 's.name as semester_name', 'td.name as term_name')
                    ->leftJoin('course_creation_instances as cci', 'it.course_creation_instance_id', '=', 'cci.id')
                    ->leftJoin('course_creations as cc', 'cci.course_creation_id', '=', 'cc.id')
                    ->leftJoin('courses as c', 'cc.course_id', '=', 'c.id')
                    ->leftJoin('semesters as s', 'cc.semester_id', '=', 's.id')
                    ->leftJoin('term_declarations as td', 'it.term_declaration_id', '=', 'td.id')
                    ->where('it.id', $instanceTermId)
                    ->first();
        return view('pages.course-management.module-creations.show', [
            // Opts this screen into the redesigned module shell.
            'layout' => 'course-top-menu',
            'title' => 'Module Creation Details - London Churchill College',
            'subtitle' => 'Module Creation Details',
            'cmPageTitle' => 'Module Creation Details',
            'cmBackUrl' => route('term.module.creation'),
            'cmBackLabel' => 'Back to Term Modules',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => route('course.management')],
                ['label' => 'Term Modules', 'href' => route('term.module.creation')],
                ['label' => 'Details', 'href' => 'javascript:void(0);']
            ],
            'term' => $termRow,
            'course' => Course::find($courseId),
            'modules' => CourseModule::where('course_id', $courseId)->where('active', 1)->get(),
            'existing_modules' => ModuleCreation::where('instance_term_id', $instanceTermId)->pluck('course_module_id')->unique()->toArray()
        ]);
    }

    public function moduleList(Request $request){
        $terminstanceid = (isset($request->terminstanceid) && $request->terminstanceid > 0 ? $request->terminstanceid : 0);
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && !empty($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ModuleCreation::where('instance_term_id', $terminstanceid)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            // Grouped: AND binds tighter than OR, so ungrouped orWheres would
            // escape the instance_term_id filter and return other terms' modules.
            $query->where(function($q) use ($queryStr){
                $q->where('module_name','LIKE','%'.$queryStr.'%')
                  ->orWhere('code','LIKE','%'.$queryStr.'%')
                  ->orWhere('status','LIKE','%'.$queryStr.'%')
                  ->orWhere('moodle_enrollment_key','LIKE','%'.$queryStr.'%');
            });
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        // 1, not '' — an empty string reaches Tabulator as NaN and breaks the pager.
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : 1;

        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);
        
        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();
        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'module_name' => $list->module_name,
                    'code' => $list->code,
                    'status' => $list->status,
                    'credit_value' => $list->credit_value,
                    'unit_value' => $list->unit_value,
                    'moodle_enrollment_key' => $list->moodle_enrollment_key,
                    'submission_date' => $list->submission_date,
                    'class_type' => $list->class_type,
                    'course_module_id' => $list->course_module_id,
                    'module_level_id' => $list->module_level_id,
                    //'module_level' => $list->level->name,
                    'assessment_count' => $list->asses->count(),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'total' => $total_rows, 'data' => $data]);
    }

    public function moduleViewAssessments(Request $request){
        $moduleCreationId = $request->moduleCreationId;
        $moduleCreation = ModuleCreation::find($moduleCreationId);
        $baseAssessments = CourseModuleBaseAssesment::where('course_module_id', $moduleCreation->course_module_id)->get();

        // Which of the module's base assessments are already attached.
        $attached = Assessment::where('module_creation_id', $moduleCreationId)
            ->pluck('course_module_base_assesment_id')
            ->all();

        $rows = $baseAssessments->map(function ($ass) use ($attached) {
            return [
                'id' => $ass->id,
                'name' => $ass->assesment_name,
                'code' => $ass->assesment_code,
                'on' => in_array($ass->id, $attached),
            ];
        })->all();

        // Markup moved out of this controller into a Blade partial so it shares
        // the module's styling; the response shape is unchanged.
        $html = view('pages.course-management.partials.assessment-toggles', [
            'assessRows' => $rows,
            'assessOwnerId' => $moduleCreationId,
        ])->render();

        return response()->json(['html' => $html, 'moduleName' => $moduleCreation->module_name], 200);
    }

    public function moduleAddAssessments(Request $request){
        $moduleCreationId = $request->moduleCreationId;
        $moduleCreation = ModuleCreation::find($moduleCreationId);
        $baseAssessments = CourseModuleBaseAssesment::where('course_module_id', $moduleCreation->course_module_id)->get();

        // Nothing is attached yet on this path, so every row starts off.
        $rows = $baseAssessments->map(function ($ass) {
            return [
                'id' => $ass->id,
                'name' => $ass->assesment_name,
                'code' => $ass->assesment_code,
                'on' => false,
            ];
        })->all();

        $html = view('pages.course-management.partials.assessment-toggles', [
            'assessRows' => $rows,
            'assessOwnerId' => $moduleCreationId,
        ])->render();

        return response()->json(['html' => $html, 'moduleName' => $moduleCreation->module_name], 200);
    }

    public function edit($id){
        $data = ModuleCreation::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(Request $request){
        $MC_ID = $request->id;
        $request->request->add(['updated_by' => auth()->user()->id]);

        $moduleCreation = ModuleCreation::find($MC_ID);
        $moduleCreation->fill($request->all());
        $moduleCreation->save();
        
        if($moduleCreation->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }
    }


    public function getModulesBaseAssessments(Request $request){
        $course_module_id = $request->course_module_id;
        $baseAssessments = CourseModuleBaseAssesment::where('course_module_id', $course_module_id)->get();

        $rows = $baseAssessments->map(function ($ass) {
            return [
                'id' => $ass->id,
                'name' => $ass->assesment_name,
                'code' => $ass->assesment_code,
                'on' => false,
            ];
        })->all();

        // Same partial as the other two assessment lists; this flow just wires
        // its checkboxes to a different hook class.
        $html = view('pages.course-management.partials.assessment-toggles', [
            'assessRows' => $rows,
            'assessOwnerId' => $course_module_id,
            'assessClass' => 'cmb_assessment_indv',
            'assessIdPrefix' => 'cmb_assessment_indv',
        ])->render();

        return response()->json(['html' => $html], 200);
    }

    public function storeIndividually(IndividualModulCreationRequest $request){
        $course_module_id = $request->course_module_id;
        $instance_term_id = $request->instance_term_id;
        $course_id = $request->course_id;
        $cmb_assessment = (isset($request->cmb_assessment) && !empty($request->cmb_assessment) ? $request->cmb_assessment : []);

        $courseModule = CourseModule::find($course_module_id);
        $data = [];
        $data['instance_term_id'] = $instance_term_id;
        $data['course_module_id'] = $course_module_id;
        $data['module_level_id'] = (isset($courseModule->module_level_id) && $courseModule->module_level_id > 0 ? $courseModule->module_level_id : null);
        $data['module_name'] = (isset($courseModule->name) && !empty($courseModule->name) ? $courseModule->name : null);
        $data['code'] = (isset($courseModule->code) && !empty($courseModule->code) ? $courseModule->code : null);
        $data['status'] = (isset($courseModule->status) && !empty($courseModule->status) ? $courseModule->status : 'optional');
        $data['credit_value'] = (isset($courseModule->credit_value) && !empty($courseModule->credit_value) ? $courseModule->credit_value : '');
        $data['unit_value'] = (isset($courseModule->unit_value) && !empty($courseModule->unit_value) ? $courseModule->unit_value : '');
        $data['class_type'] = (isset($courseModule->class_type) && !empty($courseModule->class_type) ? $courseModule->class_type : null);
        $data['created_by'] = auth()->user()->id;
        $moduleCreation = ModuleCreation::create($data);

        if($moduleCreation):
            $eLearningActivitys = ELearningActivitySetting::all();
            foreach($eLearningActivitys as $eLearningActivity) :
                $planTask = new PlanTask();
                $planTask->name = $eLearningActivity->category;
                $planTask->description = $eLearningActivity->category;
                $planTask->category = $eLearningActivity->category;
                $planTask->module_creation_id = $moduleCreation->id;
                $planTask->logo = $eLearningActivity->logo;
                $planTask->days_reminder = $eLearningActivity->days_reminder;
                $planTask->is_mandatory = $eLearningActivity->is_mandatory;
                $planTask->e_learning_activity_setting_id = $eLearningActivity->id;
                $planTask->created_by = auth()->user()->id;
                $planTask->save();
            endforeach;

            if(!empty($cmb_assessment)):
                foreach($cmb_assessment as $assementID):
                    $moduleBaseAssessment = CourseModuleBaseAssesment::find($assementID);
    
                    $data = [];
                    $data['module_creation_id'] = $moduleCreation->id;
                    $data['course_module_base_assesment_id'] = $assementID;
                    $data['assessment_name'] = $moduleBaseAssessment->assesment_name;
                    $data['assessment_code'] = $moduleBaseAssessment->assesment_code;
                    $data['created_by'] = auth()->user()->id;
                    
                    Assessment::create($data);
                endforeach;
            endif;
        endif;
        return response()->json(['res' => 'Modul creation successfully completed.'], 200);
    }

}
