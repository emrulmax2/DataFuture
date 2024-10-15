<?php

namespace App\Http\Controllers;

use App\Exports\StudentEmailIdTaskExport;               
use App\Imports\ResultImport;
use App\Models\Result;
use App\Http\Requests\StoreResultRequest;
use App\Http\Requests\StoreResultSingleRequest;
use App\Http\Requests\UpdateResultRequest;
use App\Imports\ResultImportUpdate;
use App\Models\Assessment;
use App\Models\AssessmentPlan;
use App\Models\Assign;
use App\Models\ELearningActivitySetting;
use App\Models\Employee;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\PlanContent;
use App\Models\PlanContentUpload;
use App\Models\PlansDateList;
use App\Models\PlanTask;
use App\Models\PlanTaskUpload;
use App\Models\ResultsegmentInCoursemodules;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar as FacadesDebugbar;
use DebugBar\DebugBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

use Carbon\Carbon;

class ResultController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AssessmentPlan $assessmentPlan)
    {
        $plan = Plan::find($assessmentPlan->plan_id);
        $moduleCreation = ModuleCreation::find($plan->module_creation_id);
        

  
        $userData = User::find(Auth::user()->id);
        $employee = Employee::where("user_id",$userData->id)->get()->first();

        $tutor = Employee::where("user_id",$plan->tutor->id)->get()->first();
        
        $personalTutor = isset($plan->personalTutor->id) ? Employee::where("user_id",$plan->personalTutor->id)->get()->first() : "";
 
        $studentAssign = Assign::with('student')->where('plan_id', $plan->id)->get();
        $studentListCount = $studentAssign->count();

        $gradeList = ResultsegmentInCoursemodules::with('grade')->where('course_module_base_assesment_id',$assessmentPlan->course_module_base_assesment_id)->get();
        
        $results = Result::where('assessment_plan_id',$assessmentPlan->id)->get();
        $result = [];
        foreach($results as $resultData) {
            if(!isset($result[$resultData->student_id]["count"])){
                $result[$resultData->student_id]["count"] = 0;
            }
            $result[$resultData->student_id] = ["id"=>$resultData->id,"grade" =>$resultData->grade_id, "count"=>++$result[$resultData->student_id]["count"],"created_by"=>$resultData->created_by];
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
                        'tutor'=> ($tutor->full_name) ?? null,           
                        'personalTutor'=> ($personalTutor->full_name) ?? null     
                    ];

        return view('pages.tutor.module.results.view', [
            'title' => 'Attendance - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Attendance', 'href' => 'javascript:void(0);']
            ],
            "plan" => $plan,
            "user" => $userData,
            "employee" => $employee,
            "data" => $data,
            'studentCount' => $studentListCount,
            'assessmentPlan' => $assessmentPlan,
            'plan' => $plan,
            'assignList' => $studentAssign,
            'gradeList' => $gradeList ,
            'result' => ($result) ?? null,
        ]);
    }
    public function downloadStudentListExcel(AssessmentPlan $assessmentPlan) {

        $plan = Plan::with(['course','group','creations','attenTerm'])->where('id',$assessmentPlan->plan_id)->get()->first();
        $studentAssigns = Assign::with('student')->where('plan_id', $plan->id)->get();
        $studentListCount = $studentAssigns->count();

        if($studentAssigns->isNotEmpty()):
            $theCollection = [];
            $theCollection[1][] = 'Serial';
            $theCollection[1][] = 'Registration No';
            $theCollection[1][] = 'Name';
            $theCollection[1][] = 'Status';
            $theCollection[1][] = 'Assessment Type';
            $theCollection[1][] = 'Course';
            $theCollection[1][] = 'Term';
            $theCollection[1][] = 'Module';
            $theCollection[1][] = 'Plan';
            $theCollection[1][] = 'Grade';
            $theCollection[1][] = 'Exam Table Id';
            $theCollection[1][] = 'Total Attemp';
            $theCollection[1][] = 'Published Date';
            $theCollection[1][] = 'Module Code';

            $row = 2;
            $serial = 1;
            foreach($studentAssigns as $assigned_student):
                if($assigned_student):
                    $student = $assigned_student->student;

                    if($studentListCount):
                        /* Excel Data Array */
                        $theCollection[$row][] = $serial++;
                        $theCollection[$row][] = $student->registration_no;
                        $theCollection[$row][] = $student->full_name;
                        $theCollection[$row][] = $student->status->name;
                        $theCollection[$row][] = $assessmentPlan->courseModuleBase->type->name;
                        $theCollection[$row][] = $plan->course->name;
                        $theCollection[$row][] = $plan->attenTerm->name;
                        $theCollection[$row][] = $plan->creations->module->name;
                        $theCollection[$row][] = $plan->id;
                        $theCollection[$row][] = "";
                        $theCollection[$row][] = "";
                        $theCollection[$row][] = "";
                        $theCollection[$row][] = $assessmentPlan->published_at;
                        $theCollection[$row][] = $plan->creations->code;
                        $row++;
                    endif;
                endif;
            endforeach;

            return Excel::download(new StudentEmailIdTaskExport($theCollection), 'result_sample_'.$plan->creations->module->name.'_download.xlsx');
        else:
            return response()->json(['msg' => 'Error Found!'], 422);
        endif;
    }

    public function downloadStudentResultExcel(AssessmentPlan $assessmentPlan) {
        $plan = Plan::with(['course','group','creations','attenTerm'])->where('id',$assessmentPlan->plan_id)->get()->first();
        $studentAssigns = Assign::with('student')->where('plan_id', $plan->id)->get();
        $studentListCount = $studentAssigns->count();
        
        if($studentAssigns->isNotEmpty()):
            $theCollection = [];
            $theCollection[1][] = 'Serial';
            $theCollection[1][] = 'Registration No';
            $theCollection[1][] = 'Name';
            $theCollection[1][] = 'Status';
            $theCollection[1][] = 'Assessment Type';
            $theCollection[1][] = 'Course';
            $theCollection[1][] = 'Term';
            $theCollection[1][] = 'Module';
            $theCollection[1][] = 'Plan';
            $theCollection[1][] = 'Grade';
            $theCollection[1][] = 'Exam Table Id';
            $theCollection[1][] = 'Total Attemped';
            $theCollection[1][] = 'Published Date';
            $theCollection[1][] = 'Module Code';

            $row = 2;
            $serial = 1;
            foreach($studentAssigns as $assigned_student):
                if($assigned_student):

                    $student = $assigned_student->student;
                    $result = Result::with('grade')
                                ->where("assessment_plan_id",$assessmentPlan->id)
                                ->where('student_id',$student->id)
                                ->latest()
                                ->get();
                    $countResult = $result->count();
                    $result = $result->first();
                    
                    if($studentListCount):
                        /* Excel Data Array */
                        $theCollection[$row][] = $serial++;
                        $theCollection[$row][] = $student->registration_no;
                        $theCollection[$row][] = $student->full_name;
                        $theCollection[$row][] = $student->status->name;
                        $theCollection[$row][] = $assessmentPlan->courseModuleBase->type->name;
                        $theCollection[$row][] = $plan->course->name;
                        $theCollection[$row][] = $plan->attenTerm->name;
                        $theCollection[$row][] = $plan->creations->module->name;
                        $theCollection[$row][] = $plan->id;
                        $theCollection[$row][] = ($result) ? $result->grade->code : "";
                        $theCollection[$row][] = ($result) ? $result->id : "";
                        $theCollection[$row][] = ($countResult) ? $countResult : "";
                        $theCollection[$row][] = $assessmentPlan->published_at;
                        $theCollection[$row][] = $plan->creations->code;
                        $row++;
                    endif;
                    
                endif;
            endforeach;

            return Excel::download(new StudentEmailIdTaskExport($theCollection), 'result_'.$plan->creations->module->name.'_download.xlsx');
        else:
            return response()->json(['msg' => 'Error Found!'], 422);
        endif;
    }

    public function uploadStudentExcel(Request $request) {
        $file = $request->file('file');
        $uploadType = $request->input("upload_type");
        if($uploadType=="update")
            Excel::import(new ResultImportUpdate($request->input("assessment_plan_id")),$file);
        else
            Excel::import(new ResultImport($request->input("assessment_plan_id")),$file);

        return back()->with('success', 'Data Uploaded');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreResultRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreResultRequest $request)
    {
        $gradeList = $request->input('grade_id');
        foreach($gradeList as $grade) {
            if($grade==null) {
                return response()->json(['message' => 'Grade field required',"errors"=>["grade_id[]"=>"This field is required."]], 422);
            }
        }
        if(is_array($request->input('grade_id')))
        {
            $grade_id = $request->input('grade_id');
            $plan_id = $request->input('plan_id');
            $assessment_plan_id = $request->input('assessment_plan_id');
            $student_id = $request->input('student_id');
            $published_at = $request->input('published_at');
            $created_by = $request->input('created_by');

            for($count = 0; $count < count($grade_id); $count++)
            {
                $data = array(
                        'grade_id' => $grade_id[$count],
                        'assessment_plan_id'  => $assessment_plan_id[$count],
                        'student_id'  => $student_id[$count],
                        'plan_id' =>$plan_id[$count],
                        'published_at'  => date("Y-m-d H:i:s",strtotime($published_at[$count])),
                        'created_by'  => $created_by[$count],
                    );

                $insert_schedule[] = $data; 
            }
            //dd($insert_schedule);
            //DB::table('users')
            return Result::insert($insert_schedule);

        } else {
            $result = new Result();
            $result->fill($request->all());
            $result->save();

            if($result->id)
                return response()->json(['message' => 'Result successfully created.',"data"=>['result'=>$result]], 200);
            else
                return response()->json(['message' => 'Result could not be saved'], 302);
        }
        
    }
    public function storeSingle(StoreResultSingleRequest $request)
    {
            $date = $request->input('published_at');
            $time = $request->input('published_time');
            $request->flashOnly(['published_at', 'published_time']);
            $request->merge(["published_at"=> date('Y-m-d',strtotime($date))." ".$time]);

            $result = new Result();
            $result->fill($request->except(['published_time']));
            $result->save();

            if($result->id)
                return response()->json(['message' => 'Result successfully created.',"data"=>['result'=>$result]], 200);
            else
                return response()->json(['message' => 'Result could not be saved'], 302);
        
        
    }
    public function resubmit(Request $request)
    {
        $assessmentPlan = AssessmentPlan::find($request->input('assessment_plan_id'));
        $request->merge(['published_at'=>$assessmentPlan->resubmission_at]);
        $result = new Result();
        $result->fill($request->all());
        $result->save();

        $results = Result::with('grade')->where('student_id',$result->student_id)->where('assessment_plan_id',$assessmentPlan->id)->orderBy('id','DESC')->get();
        $result = $results->first();
        $countResult = $results->count();

        if($result->id)
            return response()->json(['message' => 'Result successfully created.',"data"=>["id"=>$result->id,'count'=>$countResult,'student_id'=>$result->student_id]], 200);
        else
            return response()->json(['message' => 'Result could not be saved'], 302);

    }
    public function resultByAssessmentAndStudent(AssessmentPlan $assessmentPlan, Student $student)
    {

        $results = Result::with('grade')->where('student_id',$student->id)->where('assessment_plan_id',$assessmentPlan->id)->orderBy('id','DESC')->get();
        $result = $results->first();
        $countResult = $results->count();

        if($result->id)
            return response()->json(['message' => 'Result found.',"data"=>["id"=>$result->id,'count'=>$countResult,'results'=> $results,'student_id'=>$result->student_id]], 200);
        else
            return response()->json(['message' => 'Result could not be saved'], 302);

    }

    
    public function restore($id) {

        $data = Result::where('id', $id)->withTrashed()->restore();

        return response()->json($data);
    }

  
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function show(Result $result)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function edit(Result $result)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateResultRequest  $request
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateResultRequest $request, Result $result)
    {
        //
      
        $request->merge(["updated_by"=>Auth::id()]);
        $date = $request->input('published_at');
        $time = $request->input('published_time');
        $request->flashOnly(['published_at', 'published_time']);
        $request->merge(["published_at"=> date('Y-m-d',strtotime($date))." ".$time]);
        $result->fill($request->except(['id','published_time']));
        $result->save();

        if($result->wasChanged()) {

            return response()->json(['message' => 'Result successfully updated.'], 200);
        } else 
            return response()->json(['message' => 'Result Couldn\'t updated.'], 422);
    }

    
    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateResultRequest  $request
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function updateBulk(UpdateResultRequest $request)
    {
        
            $grade_id = $request->input('grade_id');
            $plan_id = $request->input('plan_id');
            $term_declaration_id = $request->input('term_declaration_id');
            $student_id = $request->input('student_id');
            $published_at = $request->input('published_at');
            $created_at = $request->input('created_at');
            $id = $request->input('id');

            for($count = 0; $count < count($grade_id); $count++)
            {
                


                if($id[$count] == null) {

                    $data = [
                        'grade_id' => $grade_id[$count],
                        'term_declaration_id'  => $term_declaration_id[$count],
                        'student_id'  => $student_id[$count],
                        'plan_id' =>$plan_id[$count],
                        'published_at'  => date("Y-m-d H:i:s",strtotime($published_at[$count])),
                        'created_at'  => ($created_at[$count]!=null) ? date("Y-m-d H:i:s",strtotime($created_at[$count])) : date("Y-m-d H:i:s"),
                        'created_by'  => auth()->user()->id,

                    ];
                    $resultCreate = Result::create($data);
                   
                } else {
                    //(isset($result->createdBy->employee->full_name) ? $result->createdBy->employee->full_name: $result->createdBy->name) 
                    
                    

                    $ResultOldRow = Result::find($id[$count]);
                    $result = Result::find($id[$count]);
                    
                    // Normalize the datetime values
                    $publishedAt = Carbon::parse($published_at[$count])->format('Y-m-d H:i:s');
                    $currentPublishedAt = $result->published_at ? Carbon::parse($result->published_at)->format('Y-m-d H:i:s'): null;

                    // Assign the normalized value to the model
                    if ($currentPublishedAt != $publishedAt) {
                        $result->published_at = $publishedAt;
                    }
                    // Normalize the datetime values for created_at
                    $createdAt = Carbon::parse($created_at[$count])->format('Y-m-d H:i:s');
                    $currentCreatedAt = $result->created_at ? Carbon::parse($result->created_at)->format('Y-m-d H:i:s') : null;

                    // Assign the normalized value to the model if they are different
                    if ($currentCreatedAt != $createdAt) {
                        $result->created_at = $createdAt;
                    }
                    
                    $result->grade_id = $grade_id[$count];
                    $result->term_declaration_id  = $term_declaration_id[$count];
                    $result->student_id  = $student_id[$count];
                    $result->plan_id = $plan_id[$count];

                    $changes = $result->getDirty();

                    if(!empty($changes)) {

                        FacadesDebugbar::info($changes);
                        $result->updated_by = auth()->user()->id;
                        
                        $result->save();

                        if($result->wasChanged() && !empty($changes)):
                            foreach($changes as $field => $value):
                                $data = [];
                                $data['student_id'] = $result->student_id;
                                $data['table'] = 'results';
                                $data['field_name'] = $field;
                                $data['field_value'] = $ResultOldRow->$field;
                                $data['field_new_value'] = $value;
                                $data['created_by'] = auth()->user()->id;
                                StudentArchive::create($data);
                            endforeach;
                        endif;
                    }

                    
                }
                
            }
            
            if($result->id)
                return response()->json(['message' => 'Result successfully updated.',"data"=>['result'=>$result]], 200);
            else if($resultCreate->id)
                return response()->json(['message' => 'Result successfully created.',"data"=>['result'=>$resultCreate]], 200);
            else
                return response()->json(['message' => 'Result could not be saved'], 302);

        //}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function destroy(Result $result)
    {
        
        $result->delete();
    }

    public function destroyByAssessmentPlan(AssessmentPlan $assessmentPlan)
    {
        $resultList = Result::where('assessment_plan_id',$assessmentPlan->id)->get();

        foreach($resultList as $result) {

            $result->delete();
        }
        
    }


    
}
