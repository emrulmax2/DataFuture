<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentWorkPlacementHourRequest;
use App\Models\Assign;
use App\Models\Company;
use App\Models\CompanySupervisor;
use App\Models\LearningHours;
use App\Models\LevelHours;
use App\Models\StudentWorkPlacement;
use App\Models\WorkplacementSetting;
use App\Models\WorkplacementSettingType;
use Illuminate\Http\Request;

class WorkPlacementController extends Controller
{
    public function getSupervisorByCompany(Request $request){
        $company_id = $request->theCompany;
        $res = '<option value="">Please Select</option>';

        $supervisors = CompanySupervisor::where('company_id', $company_id)->orderBy('name', 'ASC')->get();
        if($supervisors->count() > 0):
            foreach($supervisors as $sup):
                $res .= '<option value="'.$sup->id.'">'.$sup->name.'</option>';
            endforeach;
        endif;

        return response()->json(['res' => $res], 200);
    }

    public function getWpLearningHours(Request $request){
        $level_hours_id = $request->theLevelHours;
        $learning_hours = LearningHours::where('level_hours_id', $level_hours_id)->orderBy('name', 'ASC')->get();

        return response()->json([
            'learning_hours' => $learning_hours
        ], 200);


    }

    public function getWpLearningHour(Request $request){
        $learning_hours_id = $request->theLearningHour;
        $learning_hour = LearningHours::find($learning_hours_id);

        return response()->json([
            'learning_hour' => $learning_hour
        ], 200);


    }
    public function getWpSettingType(Request $request){
        $wp_setting_id = $request->theWpSetting;
        $wp_setting_types = WorkplacementSettingType::where('workplacement_setting_id', $wp_setting_id)->orderBy('type', 'ASC')->get();

        return response()->json([
            'wp_setting_types' => $wp_setting_types
        ], 200);
    }

    public function getCompanySupervisor(Request $request){
        $company_id = $request->theCompany;
        $supervisors = CompanySupervisor::where('company_id', $company_id)->orderBy('name', 'ASC')->get();

        return response()->json([
            'supervisors' => $supervisors
        ], 200);
    }

    public function storeHour(StudentWorkPlacementHourRequest $request){
        $student_id = $request->student_id;

        $workPlacement = StudentWorkPlacement::create([
            'student_id' => $student_id,
            'company_id' => $request->company_id,
            'company_supervisor_id' => $request->company_supervisor_id,
            'start_date' => (isset($request->start_date) && !empty($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null),
            'end_date' => (isset($request->end_date) && !empty($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null),
            'hours' => $request->hours,
            'contract_type' => $request->contract_type,

            'created_by' => auth()->user()->id
        ]);

        return response()->json(['res' => 'Success'], 200);
    }
    public function wpStoreHour(StudentWorkPlacementHourRequest $request){
        $student_id = $request->student_id;

        $workPlacement = StudentWorkPlacement::create([
            'assign_module_list_id' => (isset($request->assign_module_list_id) && !empty($request->assign_module_list_id) ? $request->assign_module_list_id : null),
            'learning_hours_id' => (isset($request->learning_hours_id) && !empty($request->learning_hours_id) ? $request->learning_hours_id : null),
            'level_hours_id' => (isset($request->level_hours_id) && !empty($request->level_hours_id) ? $request->level_hours_id : null),
            'workplacement_details_id' => (isset($request->workplacement_details_id) && !empty($request->workplacement_details_id) ? $request->workplacement_details_id : null),
            'workplacement_setting_id' => (isset($request->workplacement_setting_id) && !empty($request->workplacement_setting_id) ? $request->workplacement_setting_id : null),
            'workplacement_setting_type_id' => (isset($request->workplacement_setting_type_id) && !empty($request->workplacement_setting_type_id) ? $request->workplacement_setting_type_id : null),
            'student_id' => $student_id,
            'company_id' => $request->company_id,
            'company_supervisor_id' => $request->company_supervisor_id,
            'start_date' => (isset($request->start_date) && !empty($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null),
            'end_date' => (isset($request->end_date) && !empty($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null),
            'hours' => $request->hours,
            'contract_type' => $request->contract_type,

            'created_by' => auth()->user()->id
        ]);

        return response()->json([
            'status' => 'Success',
            'message' => 'Student work placement hour created successfully!',
        ], 200);
    }

    public function hourList(Request $request){
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $student_id = (isset($request->student_id) && $request->student_id > 0 ? $request->student_id : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentWorkPlacement::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
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
                    'company' => (isset($list->company->name) && !empty($list->company->name) ? $list->company->name : ''),
                    'supervisor' => (isset($list->supervisor->name) && !empty($list->supervisor->name) ? $list->supervisor->name : ''),
                    'start_date' => (isset($list->start_date) && !empty($list->start_date) ? date('jS M, Y', strtotime($list->start_date)) : ''),
                    'end_date' => (isset($list->end_date) && !empty($list->end_date) ? date('jS M, Y', strtotime($list->end_date)) : ''),
                    'hours' => $list->hours,
                    'contract_type' => $list->contract_type,

                    'created_by'=> (isset($list->user->employee->full_name) && !empty($list->user->employee->full_name) ? $list->user->employee->full_name : 'Unknown Employee'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS M, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
    public function wpHourList(Request $request){
        $status = (isset($request->status) && !empty($request->status) ? $request->status : 'All');
        $student_id = (isset($request->student_id) && $request->student_id > 0 ? $request->student_id : 0);
        $module_id = (isset($request->module_id) && $request->module_id > 0 ? $request->module_id : 0);
        $level_hours_id = (isset($request->level_hours_id) && $request->level_hours_id > 0 ? $request->level_hours_id : 0);
        $learning_hours_id = (isset($request->learning_hours_id) && $request->learning_hours_id > 0 ? $request->learning_hours_id : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentWorkPlacement::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if($module_id > 0): $query->where('assign_module_list_id', $module_id); endif;
        if($level_hours_id > 0): $query->where('level_hours_id', $level_hours_id); endif;
        if($learning_hours_id > 0): $query->where('learning_hours_id', $learning_hours_id); endif;
        if($status == 'Archived'):
            $query->onlyTrashed();
        elseif($status != 'Archived' && $status != 'All'):
            $query->where('status', $status);
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();
        $completedHours = 0;
        $pendingHours = 0;
        $rejectedHours = 0;

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                if($list->status == 'Confirmed'):
                    $completedHours += $list->hours;
                elseif($list->status == 'Pending'):
                    $pendingHours += $list->hours;
                elseif($list->status == 'Rejected'):
                    $rejectedHours += $list->hours;
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'company' => (isset($list->company->name) && !empty($list->company->name) ? $list->company->name : ''),
                    'supervisor' => (isset($list->supervisor->name) && !empty($list->supervisor->name) ? $list->supervisor->name : ''),
                    'start_date' => (isset($list->start_date) && !empty($list->start_date) ? date('jS M, Y', strtotime($list->start_date)) : ''),
                    'end_date' => (isset($list->end_date) && !empty($list->end_date) ? date('jS M, Y', strtotime($list->end_date)) : ''),
                    'hours' => $list->hours,
                    'contract_type' => $list->contract_type,
                    'status' => $list->status,
                    'level_hours' => (isset($list->level_hours->name) && !empty($list->level_hours->name) ? $list->level_hours->name : ''),
                    'learning_hours' => (isset($list->learning_hours->name) && !empty($list->learning_hours->name) ? $list->learning_hours->name : ''),
                    'workplacement_setting' => (isset($list->workplacement_setting->name) && !empty($list->workplacement_setting->name) ? $list->workplacement_setting->name : ''),
                    'workplacement_setting_type' => (isset($list->workplacement_setting_type->type) && !empty($list->workplacement_setting_type->type) ? $list->workplacement_setting_type->type : ''),
                    'created_by'=> (isset($list->user->employee->full_name) && !empty($list->user->employee->full_name) ? $list->user->employee->full_name : 'Unknown Employee'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS M, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at,
                    'module_name' => (isset($list->module->module_name) && !empty($list->module->module_name) ? $list->module->module_name : ''),

                    'can_edit' => (isset(auth()->user()->priv()['placement_edit']) && auth()->user()->priv()['placement_edit'] == 1 ? 1 : 0),
                    'can_delete' => (isset(auth()->user()->priv()['placement_delete']) && auth()->user()->priv()['placement_delete'] == 1 ? 1 : 0)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json([
            'last_page' => $last_page, 
            'data' => $data,
            'completed_hours' => $completedHours.' Hours',
            'pending_hours' => $pendingHours.' Hours',
            'rejected_hours' => $rejectedHours.' Hours'
        ]);
    }

    public function editHour($id){
        $workplacement = StudentWorkPlacement::find($id);
        $company_id = $workplacement->company_id;
        $supervisor_id = $workplacement->company_supervisor_id;

        $supervisor_html = '<option value="">Please Select</option>';

        $supervisors = CompanySupervisor::where('company_id', $company_id)->orderBy('name', 'ASC')->get();
        if($supervisors->count() > 0):
            foreach($supervisors as $sup):
                $supervisor_html .= '<option '.($supervisor_id == $sup->id ? 'selected' : '').' value="'.$sup->id.'">'.$sup->name.'</option>';
            endforeach;
        endif;
        $workplacement['supervisor_html'] = $supervisor_html;

        return response()->json(['res' => $workplacement], 200);
    }
    public function editWpHour($id){
        $workplacement = StudentWorkPlacement::find($id);
        $company_id = $workplacement->company_id;
        $supervisor_id = $workplacement->company_supervisor_id;
        $level_hours_id = $workplacement->level_hours_id;
        $learning_hours_id = $workplacement->learning_hours_id;
        $workplacement_setting_id = $workplacement->workplacement_setting_id;
        $workplacement_setting_type_id = $workplacement->workplacement_setting_type_id;
        $asign_module_list_id = $workplacement->assign_module_list_id;

        $companies = Company::where('active', 1)->orderBy('name', 'ASC')->get();
        $supervisors = CompanySupervisor::where('company_id', $company_id)->orderBy('name', 'ASC')->get();
        $level_hours = LevelHours::where('workplacement_details_id', $workplacement->workplacement_details_id)->orderBy('name', 'ASC')->get();
        $learning_hours = LearningHours::where('level_hours_id', $workplacement->level_hours_id)->orderBy('name', 'ASC')->get();
        $workplacement_settings = WorkplacementSetting::where('id', $workplacement->workplacement_setting_id)->orderBy('name', 'ASC')->get();
        $workplacement_setting_types = WorkplacementSettingType::where('id', $workplacement->workplacement_setting_type_id)->get();
        $assign_module_lists = Assign::where('student_id', $workplacement->student_id)
                                ->with(['plan.creations' => function($query) {
                                    $query->select('id', 'module_name');
                                }])
                                ->get()->pluck('plan.creations')->unique('id')->values();    
        $learning_hour = LearningHours::find($learning_hours_id);

        return response()->json([
            'res' => $workplacement, 
            'level_hours' => $level_hours,
            'level_hours_id' => $level_hours_id,
            'learning_hours' => $learning_hours,
            'learning_hours_id' => $learning_hours_id,
            'workplacement_settings' => $workplacement_settings,
            'workplacement_setting_id' => $workplacement_setting_id,
            'workplacement_setting_types' => $workplacement_setting_types,
            'workplacement_setting_type_id' => $workplacement_setting_type_id,
            'companies' => $companies,
            'company_id' => $company_id,
            'supervisors' => $supervisors,
            'supervisor_id' => $supervisor_id,
            'assign_module_lists' => $assign_module_lists, 
            'asign_module_list_id' => $asign_module_list_id,
            'module_required' => (isset($learning_hour->module_required) && $learning_hour->module_required > 0 ? $learning_hour->module_required : 0)
        ], 200);
    }

    public function updateHour(StudentWorkPlacementHourRequest $request){
        $student_id = $request->student_id;
        $id = $request->id;

        $workPlacement = StudentWorkPlacement::where('id', $id)->update([
            'company_id' => $request->company_id,
            'company_supervisor_id' => $request->company_supervisor_id,
            'start_date' => (isset($request->start_date) && !empty($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null),
            'end_date' => (isset($request->end_date) && !empty($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null),
            'hours' => $request->hours,
            'contract_type' => $request->contract_type,

            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['res' => 'Success'], 200);
    }

    public function updateWpHour(StudentWorkPlacementHourRequest $request){
        $student_id = $request->student_id;
        $id = $request->id;

        $workPlacement = StudentWorkPlacement::findOrFail($id);
        $total_completed_hours = StudentWorkPlacement::where('student_id', $student_id)->where('status', 'Confirmed')->sum('hours');

        $workPlacement->update([
            'assign_module_list_id' => (isset($request->assign_module_list_id) && !empty($request->assign_module_list_id) ? $request->assign_module_list_id : null),
            'learning_hours_id' => (isset($request->learning_hours_id) && !empty($request->learning_hours_id) ? $request->learning_hours_id : null),
            'level_hours_id' => (isset($request->level_hours_id) && !empty($request->level_hours_id) ? $request->level_hours_id : null),
            'workplacement_details_id' => (isset($request->workplacement_details_id) && !empty($request->workplacement_details_id) ? $request->workplacement_details_id : null),
            'workplacement_setting_id' => (isset($request->workplacement_setting_id) && !empty($request->workplacement_setting_id) ? $request->workplacement_setting_id : null),
            'workplacement_setting_type_id' => (isset($request->workplacement_setting_type_id) && !empty($request->workplacement_setting_type_id) ? $request->workplacement_setting_type_id : null),
            'company_id' => $request->company_id,
            'company_supervisor_id' => $request->company_supervisor_id,
            'start_date' => (isset($request->start_date) && !empty($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null),
            'end_date' => (isset($request->end_date) && !empty($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null),
            'hours' => $request->hours,
            'contract_type' => $request->contract_type,
            'status' => (isset($request->status) && !empty($request->status) ? $request->status : 'Pending'),

            'updated_by' => auth()->user()->id
        ]);

        return response()->json([
            'status' => 'Success',
            'message' => 'Student work placement hour updated successfully!',
            'data' => $workPlacement,
            'total_completed_hours' => $total_completed_hours
        ], 200);
    }

    public function destroyHour($id) {
        $studentWorkPlacement = StudentWorkPlacement::find($id)->delete();
        return response()->json(['res' => 'Success'], 200);
    }

    public function restoreHour(Request $request) {
        $data = StudentWorkPlacement::where('id', $request->row_id)->withTrashed()->restore();

        return response()->json(['res' => 'Success'], 200);
    }
}
