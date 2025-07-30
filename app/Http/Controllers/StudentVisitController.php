<?php

namespace App\Http\Controllers;

use App\Models\StudentVisit;
use App\Http\Requests\StoreStudentVisitRequest;
use App\Http\Requests\UpdateStudentVisitRequest;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\Status;
use App\Models\Student;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentVisitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function list(Request $request)
    {
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        if(isset($request->query_search) && !empty($request->query_search)):
            $search = $request->query_search;
            $query = StudentVisit::where('student_id', $request->student_id)
            ->where(function ($query) use ($search) {
                $query->where('visit_notes', 'LIKE', "%$search%")
                       ->orWhere('visit_type', 'LIKE', "%$search%")
                       ->orWhere('visit_duration', 'LIKE', "%$search%")
                ->where('term_declaration_id', $search);
            });
        else:
            $query = StudentVisit::orderByRaw(implode(',', $sorts))->where('student_id', $request->student_id);
        endif;

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
            $i = $offset+1;
            foreach($Query as $list):
                $moduleName = '';
                if(isset($list->plan_id) && !empty($list->plan_id)):
                    $planSet = Plan::with('creations','creations.module')->where('id', $list->plan_id)->first();
                    $moduleName = $planSet ? $planSet->creations->module->name.' - '. $planSet->creations->module->code . ($planSet->class_type ? ' ['. $planSet->class_type. ']' : '') : '';
                endif;

                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'student_id' => $list->student_id,
                    'visit_type' => $list->visit_type,
                    'visit_date' => $list->visit_date ? $list->visit_date : '',
                    'attendance_deleted_by' => isset($list->attendance_deleted_by) ? $list->attendanceDeletedBy->employee->full_name : '',
                    'visit_duration' => $list->visit_duration,
                    'visit_notes' => $list->visit_notes ? $list->visit_notes : '',
                    'created_by' => $list->createdBy && $list->createdBy->employee ? $list->createdBy->employee->full_name : '',
                    'updated_by' => $list->updatedBy && $list->updatedBy->employee ? $list->updatedBy->employee->full_name : '',
                    'deleted_at' => $list->deleted_at ? $list->deleted_at : null,
                    'module_name' => $moduleName,
                    'plan_id' => isset($list->plan_id) ? $list->plan_id : null,
                ];
                $i++;
            endforeach;
        endif;
        
        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentVisitRequest $request)
    {
        $request->merge([
            'created_by' => auth()->user()->id,
        ]);

        
        if(isset($request->visit_type) && $request->visit_type == 'academic'):

            
            $givenDate = Carbon::parse($request->visit_date)->format('Y-m-d');

                    $newPlanDate = new PlansDateList();
                    $newPlanDate->date = $givenDate;
                    $newPlanDate->plan_id = $request->plan_id;
                    $newPlanDate->name = "Visit";
                    $newPlanDate->feed_given = 1;
                    $newPlanDate->created_by = auth()->user()->id;
                    $newPlanDate->status = "Completed";
                    $newPlanDate->save();
                    
                    
                    $plan_id =  $request->plan_id;


                    $attendanceCreated = Attendance::create([
                        'plans_date_list_id' =>$newPlanDate->id,
                        'attendance_date' =>$givenDate,
                        'attendance_captured_at' => Carbon::now()->format('Y-m-d'),
                        'plan_id'=>$plan_id,
                        'attendance_feed_status_id'=>1,
                        'student_id'=> $request->student_id,
                        'created_by' => auth()->user()->id,
                        'note'   => "Student Visit",
                    ]);

                    $request->merge([
                        'attendance_id' => $attendanceCreated->id,
                    ]);
        endif;

        StudentVisit::create($request->all());
        return response()->json(['message' => 'Visit created successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(StudentVisit $studentVisit)
    {
        $data = [
            'id' => $studentVisit->id,
            'visit_type' => $studentVisit->visit_type,
            'visit_date' => $studentVisit->visit_date,
            'visit_duration' => $studentVisit->visit_duration,
            'visit_notes' => $studentVisit->visit_notes,
            'terms' => isset($studentVisit->termDeclaration) ? $studentVisit->termDeclaration->name : '',
            'modules' => isset($studentVisit->plan) ? $studentVisit->plan->creations->module->name . ' - ' . $studentVisit->plan->creations->module->code . ($studentVisit->plan->class_type ? ' [' . $studentVisit->plan->class_type . ']' : '') : '',
            'attendance_id' => $studentVisit->attendance_id,
            'attendance_deleted_by' => $studentVisit->attendanceDeletedBy ? $studentVisit->attendanceDeletedBy->employee->full_name : '',
            'created_by' => $studentVisit->createdBy ? $studentVisit->createdBy->employee->full_name : '',
            'updated_by' => $studentVisit->updatedBy ? $studentVisit->updatedBy->employee->full_name : '',
            'deleted_at' => $studentVisit->deleted_at ? $studentVisit->deleted_at : '',
        ];
        return response()->json($data);
                                    
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentVisit $studentVisit)
    {
        return response()->json($studentVisit);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentVisitRequest $request, StudentVisit $studentVisit)
    {
        
        
        $studentVisit->update($request->all());
        $studentVisit->updated_by = auth()->user()->id;
        $studentVisit->save();

        return response()->json(['message' => 'Visit updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentVisit $studentVisit)
    {
        if($studentVisit->attendance_id):
            $attendance = Attendance::find($studentVisit->attendance_id);
            if($attendance):
                $attendance->delete();
            endif;
            
            $studentVisit->attendance_deleted_by = auth()->user()->id;
            $studentVisit->save();
        endif;
        $studentVisit->delete();
        return response()->json(['message' => 'Visit deleted successfully']);
    }


    public function restore($id)
    {
        $studentVisit = StudentVisit::withTrashed()->findOrFail($id);
        $studentVisit->restore();

         if($studentVisit->attendance_id):
            $attendance = Attendance::withTrashed()->findOrFail($studentVisit->attendance_id);
            if($attendance):
                $attendance->restore();
            endif;
            
            $studentVisit->attendance_deleted_by = null;
            $studentVisit->updated_by = auth()->user()->id;
            $studentVisit->save();
        endif;
        return response()->json(['message' => 'Visit restored successfully']);
    }


    public function showModulesByTerm(TermDeclaration $term, Student $student)
    {
        $plans = Assign::where('student_id', $student->id)->pluck('plan_id')->toArray(); 
        $dataList = Plan::whereIn('id', $plans)->orderBy('term_declaration_id', 'DESC')->get();
        $moduleCreationList = [];
        $i = 0;
        if(!empty($dataList)):
            foreach($dataList as $data):
                if(!empty($data->module_creation_id)):
                    $module = ModuleCreation::with('module')->where('id', $data->module_creation_id)->first();
                    if($term->id && $data->term_declaration_id == $term->id):

                            if (
                                strpos(strtoupper($module->module->name), 'PERSONAL TUTORIAL') === false &&
                                strpos(strtoupper($module->module->name), 'GROUP TUTORIAL') === false
                            ) {
                                $moduleCreationList[] = [ "value"=>$data->id, "text"=> $module->module->name.'-'.$module->code.' ['.$data->class_type.']' ];
                            }
                    endif;
                endif;
            endforeach;
        endif;



        return response()->json($moduleCreationList);
    }
}
