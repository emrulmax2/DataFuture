<?php

namespace App\Http\Controllers;

use App\Models\StudentDocumentRequestForm;
use App\Http\Requests\StoreStudentDocumentRequestFormRequest;
use App\Http\Requests\UpdateStudentDocumentRequestFormRequest;
use App\Models\Assign;
use App\Models\FormsTable;
use App\Models\LetterSet;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\Room;
use App\Models\Student;
use App\Models\StudentOrder;
use App\Models\StudentTask;
use App\Models\StudentUser;
use App\Models\TermDeclaration;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentDocumentRequestFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       
        // Fetch all student document request forms
        $studentDocumentRequestForms = StudentDocumentRequestForm::all();

        $student = $studentData = Student::where("student_user_id", auth('student')->user()->id)->get()->first();
        $studentOrderList = StudentOrder::with('studentOrderItems','studentOrderItems.student','studentOrderItems.letterSet')->where('student_id',  $student->id)->get();
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
        $letter_sets = LetterSet::where('status',1)->where('document_request',1)->get();

        // Return the view with the data
        return view('pages.students.frontend.document_requests.index',[
                'title' => 'Live Students - London Churchill College',
                'breadcrumbs' => [
                    ['label' => 'Profile View', 'href' => route('students.dashboard')],
                    ['label' => 'Document / ID Card Replacement request', 'href' => 'javascript:void(0);'],
                ],
                'student' => $student,
                "termList" =>$dataBox["termList"],
                "data" => $dataBox["data"],
                "currenTerm" => $dataBox["currenTerm"],
                "doItOnline" => $DoItOnline,
                "datewiseClasses" => $dateWiseClassList,
                "letter_sets" => $letter_sets,
                "term_declarations" => TermDeclaration::orderBy('id','DESC')->get(), 
                'latestTermInfo' => TermDeclaration::orderBy('id','DESC')->get()->first(),
                'studentOrderList' => $studentOrderList,
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentDocumentRequestFormRequest $request)
    {


        $studentDocumentRequestForm = new StudentDocumentRequestForm();
        $studentDocumentRequestForm->student_id = $request->student_id;
        $studentDocumentRequestForm->term_declaration_id = $request->term_declaration_id;
        $studentDocumentRequestForm->letter_set_id = $request->letter_set_id;
        $studentDocumentRequestForm->name = !isset($request->name) ? LetterSet::where('id',$request->letter_set_id)->get()->first()->letter_title : $request->name;
        $studentDocumentRequestForm->description = $request->description;
        $studentDocumentRequestForm->service_type = $request->service_type;
        $studentDocumentRequestForm->status = 'Pending';
        $studentDocumentRequestForm->email_status = 'Pending';
        $studentDocumentRequestForm->student_consent = 1;
        $studentDocumentRequestForm->created_by = auth('student')->user()->id;

        if ($studentDocumentRequestForm->save()) {
            
            $data['student_id'] = $request->student_id;
            $data['task_list_id'] = 20; // Document Request Task
            $data['student_document_request_form_id'] = $studentDocumentRequestForm->id;
            $data['status'] = "Pending";
            $data['created_by'] = 1;

            StudentTask::create($data);
            return response()->json([
                'status' => 'success',
                'message' => 'Your Order has been submitted successfully. Please check order history.',
                'data' => $studentDocumentRequestForm,
            ]);

        } else {

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit document request form.',
            ],400);

        }
    }


    public function list(Request $request){
        


        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        $student = Student::where("student_user_id", auth('student')->user()->id)->get()->first();

        if(isset($request->search) && !empty($request->search)):
            $search = $request->search;
            $query = StudentDocumentRequestForm::with('letterSet')->orderByRaw(implode(',', $sorts))->where('student_id', $student->id)
            ->where(function ($query) use ($search) {
                // $query->where('description', 'LIKE', "%$search%")
                //       ->orWhere('service_type', 'LIKE', "%$search%")
                //       ->orWhere('status', 'LIKE', "%$search%")
                $query->where('term_declaration_id', $search);
            });
        else:
            $query = StudentDocumentRequestForm::with('letterSet')->orderByRaw(implode(',', $sorts))->where('student_id', $student->id);
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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'student_id' => $list->student_id,
                    'name' => (isset($list->letterSet->letter_title) && !empty($list->letterSet->letter_title) ? $list->letterSet->letter_title : ''),
                    'description' => (isset($list->description) && !empty($list->description) ? $list->description : ''),
                    'service_type' => (isset($list->service_type) && !empty($list->service_type) ? $list->service_type : ''),
                    'email_status' => (isset($list->email_status) && !empty($list->email_status) ? $list->email_status : ''),
                    'status' => (isset($list->status) && !empty($list->status) ? $list->status : ''),
                    //'updated_by' => (isset($list->updatedBy->name) && !empty($list->updatedBy->name) ? $list->updatedBy->name : ''),

                    // 'updated_at' => $list->updated_at,
                    // 'created_at_human' => $list->created_at->diffForHumans(),
                    // 'updated_at_human' => $list->updated_at->diffForHumans(),
                    // 'deleted_at_human' => (isset($list->deleted_at) && !empty($list->deleted_at) ? $list->deleted_at->diffForHumans() : ''),

                    'updated_at'=> (!isset($list->updated_at) ? $list->created_at_human : $list->updated_at_human),

                ];
                $i++;
            endforeach;
        endif;
        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function products(Request $request)
    {
        $letter_sets = LetterSet::where('status',1)->where('document_request',1)->get();
        $student = Student::where("student_user_id", auth('student')->user()->id)->get()->first();
        $studentOrderList = StudentOrder::where('student_id',  $student->id)->where('status','Pending')->get();
        
        // $studentDocumentRequestForm = StudentDocumentRequestForm::where('student_id', $student->id)
        //     ->where('status', 'Pending')
        //     ->get();

        $countPendingOrders = $studentOrderList->count(); 
        return view('pages.students.frontend.document_requests.products',[
                'title' => 'Live Students - London Churchill College',
                'breadcrumbs' => [
                    ['label' => 'Profile View', 'href' => route('students.dashboard')],
                    ['label' => 'Document / ID Card Replacement request', 'href' => 'javascript:void(0);'],
                ],
                'student' => $student,
                "letter_sets" => $letter_sets,
                "term_declarations" => TermDeclaration::orderBy('id','DESC')->get(), 
                'latestTermInfo' => TermDeclaration::orderBy('id','DESC')->get()->first(),
                'current_term_id' => $student->assigned_terms->first(),
                'countPendingOrders' => $countPendingOrders,
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(StudentDocumentRequestForm $studentDocumentRequestForm)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentDocumentRequestForm $studentDocumentRequestForm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentDocumentRequestFormRequest $request, StudentDocumentRequestForm $studentDocumentRequestForm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentDocumentRequestForm $studentDocumentRequestForm)
    {
        //
    }
}
