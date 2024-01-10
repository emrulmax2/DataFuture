<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\ApplicantTask;
use App\Models\Student;
use App\Models\StudentTask;
use App\Models\TaskList;
use App\Models\TaskListUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendingTaskManagerController extends Controller
{
    public function index()
    {
        $userData = \Auth::guard('web')->user();
        return view('pages.users.staffs.task.index', [
            'title' => 'User Task Manager - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Task Manager', 'href' => 'javascript:void(0);'],
            ],
            'user' => $userData,
            'mytasks' => $this->getUserPendingTask()
        ]);
    }

    public function getUserPendingTask(){
        $res = [];
        $assignedTaskIds = TaskListUser::where('user_id', auth()->user()->id)->pluck('task_list_id')->unique()->toArray();

        if(!empty($assignedTaskIds)):
            $assignedTasks = TaskList::whereIn('id', $assignedTaskIds)->orderBy('name', 'ASC')->get();
            if(!empty($assignedTasks)):
                foreach($assignedTasks as $atsk):
                    $aplPendingTask = ApplicantTask::where('task_list_id', $atsk->id)->where('status', 'Pending')->get();
                    $stdPendingTask = StudentTask::where('task_list_id', $atsk->id)->where('status', 'Pending')->get();
                    if($aplPendingTask->count() > 0 || $stdPendingTask->count() > 0):
                        $res[$atsk->id] = $atsk;
                        $res[$atsk->id]['pending_task'] = $aplPendingTask->count() + $stdPendingTask->count();
                    endif;
                endforeach;
            endif;
        endif;

        return $res;
    }

    public function show($id){
        return view('pages.users.staffs.task.details', [
            'title' => 'User Task Manager - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Task Manager', 'href' => route('task.manager')],
                ['label' => 'Details', 'href' => 'javascript:void(0);'],
            ],
            'task' => TaskList::find($id)
        ]);
    }

    public function list(Request $request){
        $querystr = isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '';
        $task_id = isset($request->task_id) && $request->task_id > 0 ? $request->task_id : 0;
        $task = TaskList::find($task_id);
        $phase = (isset($task->processlist->phase) && !empty($task->processlist->phase) ? $task->processlist->phase : 'Live');

        if($phase == 'Applicant'):
            $applicant_ids = ApplicantTask::where('task_list_id', $task_id)->where('status', 'Pending')->pluck('applicant_id')->unique()->toArray();
            $Query = Applicant::whereIn('id', $applicant_ids);
            if(!empty($querystr)):
                $Query->where('first_name', 'LIKE', '%'.$querystr.'%');
                $Query->orWhere('last_name', 'LIKE', '%'.$querystr.'%');
            endif;

            $total_rows = $Query->count();
            $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
            $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
            $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
            
            $limit = $perpage;
            $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

            $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
            $sorts = [];
            foreach($sorters as $sort):
                $sorts[] = $sort['field'].' '.$sort['dir'];
            endforeach;

            $Query = $Query->orderByRaw(implode(',', $sorts))->skip($offset)
                ->take($limit)
                ->get();

            $data = array();

            if(!empty($Query)):
                $i = 1;
                foreach($Query as $list):
                    $data[] = [
                        'id' => $list->id,
                        'sl' => $i,
                        'application_no' => (empty($list->application_no) ? $list->id : $list->application_no),
                        'first_name' => $list->first_name,
                        'last_name' => $list->last_name,
                        'date_of_birth'=> (isset($list->date_of_birth) && !empty($list->date_of_birth) ? date('d-m-Y', strtotime($list->date_of_birth)) : ''),
                        'course'=> (isset($list->course->creation->course->name) && !empty($list->course->creation->course->name) ? $list->course->creation->course->name : ''),
                        'semester'=> (isset($list->course->creation->semester->name) && !empty($list->course->creation->semester->name) ? $list->course->creation->semester->name : ''),
                        'sex_identifier_id'=> (isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : ''),
                        'status_id'=> (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                        'url' => route('admission.process', $list->id)
                    ];
                    $i++;
                endforeach;
            endif;
        else:
            $student_ids = StudentTask::where('task_list_id', $task_id)->where('status', 'Pending')->pluck('student_id')->unique()->toArray();

            $Query = Student::whereIn('id', $student_ids);
            if(!empty($querystr)):
                $Query->where('first_name', 'LIKE', '%'.$querystr.'%');
                $Query->where('last_name', 'LIKE', '%'.$querystr.'%');
            endif;

            $total_rows = $Query->count();
            $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
            $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
            $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
            
            $limit = $perpage;
            $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

            $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
            $sorts = [];
            foreach($sorters as $sort):
                $sorts[] = $sort['field'].' '.$sort['dir'];
            endforeach;

            $Query = $Query->orderByRaw(implode(',', $sorts))->skip($offset)
                ->take($limit)
                ->get();

            $data = array();

            if(!empty($Query)):
                $i = 1;
                foreach($Query as $list):
                    $data[] = [
                        'id' => $list->id,
                        'sl' => $i,
                        'application_no' => (empty($list->application_no) ? $list->id : $list->application_no),
                        'first_name' => $list->first_name,
                        'last_name' => $list->last_name,
                        'date_of_birth'=> (isset($list->date_of_birth) && !empty($list->date_of_birth) ? date('d-m-Y', strtotime($list->date_of_birth)) : ''),
                        'course'=> (isset($list->course->creation->course->name) && !empty($list->course->creation->course->name) ? $list->course->creation->course->name : ''),
                        'semester'=> (isset($list->course->creation->semester->name) && !empty($list->course->creation->semester->name) ? $list->course->creation->semester->name : ''),
                        'sex_identifier_id'=> (isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : ''),
                        'status_id'=> (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                        'url' => route('student.process', $list->id)
                    ];
                    $i++;
                endforeach;
            endif;
        endif;

        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
