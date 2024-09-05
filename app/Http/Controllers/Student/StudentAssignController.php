<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Exports\ArrayCollectionExport;
use App\Models\Plan;
use Maatwebsite\Excel\Facades\Excel;

class StudentAssignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function list(Request $request){
        $planid = (isset($request->planid) && !empty($request->planid) ? $request->planid : 0);
        //$dates = (isset($request->dates) && !empty($request->dates) ? date('Y-m-d', strtotime($request->dates)) : '');
        $status = (isset($request->status) && !empty($request->status) ? $request->status : '1');
        
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Assign::orderByRaw(implode(',', $sorts));
        if(!empty($planid)): $query->where('plan_id', $planid); endif;
        //if(!empty($dates)): $query->where('date', $dates); endif;
        if($status == 2): $query->onlyTrashed(); endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();
               
        $data = array();
        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                if(isset($list->student)) {
                    $list2 = $list->student;
                    $list2Other = $list2->other;
                    $data[] = [
                        'id' => $list->id,
                        'sl' => $i,
                        'disability' =>  (isset($list2Other->disability_status) && $list2Other->disability_status > 0 ? $list2Other->disability_status : 0),
                        'registration_no' => (!empty($list2->registration_no) ? $list2->registration_no : $list2->application_no),
                        'first_name' => $list2->first_name,
                        'last_name' => $list2->last_name,
                        'status_id'=> (isset($list2->status->name) && !empty($list2->status->name) ? $list2->status->name : ''),
                        'url' => route('student.show', $list2->id),
                        'photo_url' => $list2->photo_url,
                        'deleted_at' => $list->deleted_at,
                        'student_id' => $list->student_id,
                        'evening_and_weekend' => (isset($list2->activeCR->propose->full_time) && $list2->activeCR->propose->full_time > 0 ? $list2->activeCR->propose->full_time : 0)
                    ];
                    $i++;
                }
            endforeach;
        endif;
        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
    

    public function exportStudentList(Request $request){
        $plan_id = $request->plan_id;
        $plan = Plan::find($plan_id);

        $student_ids = (isset($request->ids) && !empty($request->ids) ? $request->ids : []);
        $moduleName = (isset($plan->creations->module_name) && !empty($plan->creations->module_name) ? $plan->creations->module_name : '' );

        $theCollection = [];
        $theCollection[1][0] = 'Registration No';
        $theCollection[1][1] = 'First Name';
        $theCollection[1][2] = 'Last Name';
        $theCollection[1][3] = 'Mobile';
        $theCollection[1][4] = 'Personal Email';
        $theCollection[1][5] = 'Org Email';
        $theCollection[1][6] = 'Status';

        $row = 2;
        if(!empty($student_ids)):
            $students = Student::whereIn('id', $student_ids)->orderBy('registration_no', 'ASC')->get();
            if(!empty($students)):
                foreach($students as $student):
                    $theCollection[$row][0] = $student->registration_no;
                    $theCollection[$row][1] = $student->first_name;
                    $theCollection[$row][2] = $student->last_name;
                    $theCollection[$row][3] = (isset($student->contact->mobile) && !empty($student->contact->mobile) ? $student->contact->mobile : '');
                    $theCollection[$row][4] = (isset($student->contact->personal_email) && !empty($student->contact->personal_email) ? $student->contact->personal_email : '');
                    $theCollection[$row][5] = (isset($student->contact->institutional_email) && !empty($student->contact->institutional_email) ? $student->contact->institutional_email : '');
                    $theCollection[$row][6] = (isset($student->status->name) && !empty($student->status->name) ? $student->status->name : '');

                    $row += 1;
                endforeach;
            endif;
        endif;

        $fileName = (!empty($moduleName) ? str_replace(' ', '_', $moduleName).'_student_lists.xlsx' : 'student_lists.xlsx');
        return Excel::download(new ArrayCollectionExport($theCollection), $fileName);
    }
}
