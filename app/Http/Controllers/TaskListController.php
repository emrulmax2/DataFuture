<?php

namespace App\Http\Controllers;

use App\Models\TaskList;
use App\Models\ProcessList;
use Illuminate\Http\Request;
use App\Http\Requests\TaskListRequest;
use App\Http\Requests\TaskListUpdateRequest;
use App\Models\TaskListStatus;
use App\Models\TaskListUser;
use App\Models\TaskStatus;
use App\Models\User;

class TaskListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/tasklist/index', [
            'title' => 'Task List - LCC Data Future Managment',
            'breadcrumbs' => [['label' => 'Task List', 'href' => 'javascript:void(0);']],
            'processlists' => ProcessList::all(),
            'taskStatus' => TaskStatus::all(),
            'users' => User::all()
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $processlist = (isset($request->processlist) && $request->processlist > 0 ? $request->processlist : '');

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $query = TaskList::where('id', '!=', 0);
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if(!empty($processlist) && $processlist > 0 ):
            $query->where('process_list_id', $processlist);
        endif;
        $total_rows = $query->count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = TaskList::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if(!empty($processlist) && $processlist > 0 ):
            $query->where('process_list_id', $processlist);
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;
        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();
        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $users = '';
                if(isset($list->users) && !empty($list->users)):
                    foreach($list->users as $usr):
                        $users .= '<span class="btn inline-flex btn-secondary w-auto text-left px-1 ml-0 mr-1 py-0 mb-1 rounded-0">'.$usr->user->name.'</span>';
                    endforeach;
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'processlist' => $list->processlist->name,
                    'name' => $list->name,
                    'short_description' => $list->short_description,
                    'external_link_ref' => $list->external_link_ref,
                    'interview' => $list->interview,
                    'upload' => $list->upload,
                    'external_link' => ($list->external_link == 1 ? 'Yes' : 'No'),
                    'status' => $list->status,
                    'user' => $users,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TaskListRequest $request){
        $assigned_users = $request->assigned_users;
        $status = $request->status;
        $task_statuses = (isset($request->task_statuses) && !empty($request->task_statuses) ? $request->task_statuses : []);

        $request->request->remove('assigned_users');
        $request->request->remove('task_statuses');

        $request->request->add(['created_by' => auth()->user()->id]);
        $tasklist = TaskList::create($request->all());

        if($tasklist):
            if(!empty($assigned_users)):
                foreach($assigned_users as $user):
                    TaskListUser::create([
                        'task_list_id' => $tasklist->id,
                        'user_id' => $user,
                        'created_by' => auth()->user()->id,
                    ]);
                endforeach;
            endif;
            if($status == 'Yes' && !empty($task_statuses)):
                foreach($task_statuses as $statuses):
                    TaskListStatus::create([
                        'task_list_id' => $tasklist->id,
                        'task_status_id' => $statuses,
                        'created_by' => auth()->user()->id,
                    ]);
                endforeach;
            endif;
        endif;
        
        return response()->json(['message' => 'Data successfully inserted'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TaskList  $taskList
     * @return \Illuminate\Http\Response
     */
    public function show(TaskList $taskList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TaskList  $taskList
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        $data = TaskList::with(['users', 'statuses'])->find($id);
        

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TaskList  $taskList
     * @return \Illuminate\Http\Response
     */
    public function update(TaskListRequest $request){
        $pl_ID = $request->id;
        $assigned_users = $request->assigned_users;
        $status = $request->status;
        $task_statuses = (isset($request->task_statuses) && !empty($request->task_statuses) ? $request->task_statuses : []);

        $taskDF = TaskList::where('id', $pl_ID)->update([
            'process_list_id'=> $request->process_list_id,
            'name'=> $request->name,
            'short_description'=> $request->short_description,
            'interview'=> $request->interview,
            'status'=> $request->status,
            'upload'=> $request->upload,
            'external_link' => (isset($request->external_link) ? $request->external_link : '0'),
            'external_link_ref' => (isset($request->external_link) && $request->external_link == 1 && !empty($request->external_link_ref) ? $request->external_link_ref : ''),
            'updated_by' => auth()->user()->id
        ]);

        if(!empty($assigned_users)):
            TaskListUser::where('task_list_id', $pl_ID)->forceDelete();
            foreach($assigned_users as $user):
                TaskListUser::create([
                    'task_list_id' => $pl_ID,
                    'user_id' => $user,
                    'updated_by' => auth()->user()->id,
                ]);
            endforeach;
        else:
            TaskListUser::where('task_list_id', $pl_ID)->forceDelete();
        endif;
        if($status == 'Yes' && !empty($task_statuses)):
            TaskListStatus::where('task_list_id', $pl_ID)->forceDelete();
            foreach($task_statuses as $statuses):
                TaskListStatus::create([
                    'task_list_id' => $pl_ID,
                    'task_status_id' => $statuses,
                    'updated_by' => auth()->user()->id,
                ]);
            endforeach;
        else: 
            TaskListStatus::where('task_list_id', $pl_ID)->forceDelete();
        endif;


        if($taskDF){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TaskList  $taskList
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        $data = TaskList::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = TaskList::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
