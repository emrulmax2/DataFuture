<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\TaskList;
use App\Models\ProcessList;
use Illuminate\Http\Request;
use App\Http\Requests\TaskListRequest;
use App\Http\Requests\TaskListUpdateRequest;
use App\Models\TaskListStatus;
use App\Models\TaskListUser;
use App\Models\TaskStatus;
use App\Models\User;
use Google\Service\Tasks\Resource\Tasklists;
use Illuminate\Support\Facades\Storage;

class TaskListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.settings.tasklist.index', [
            'title' => 'Task List - London Churchill College',
            'subtitle' => 'Applicant Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Task List', 'href' => 'javascript:void(0);']
            ],
            'processlists' => ProcessList::all(),
            'taskStatus' => TaskStatus::all(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $processlist = (isset($request->processlist) && $request->processlist > 0 ? $request->processlist : '');

        $query = TaskList::where('id', '!=', 0);
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if(!empty($processlist) && $processlist > 0 ):
            $query->where('process_list_id', $processlist);
        endif;
        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
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
                    $u = 1;
                    $users .= '<div class="flex taskUserLoader" data-taskid="'.$list->id.'">';
                    foreach($list->users as $usr):
                        if($u > 3): break; endif;
                        $photo_url = (isset($usr->user->employee->photo_url) && !empty($usr->user->employee->photo_url) ? $usr->user->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg'));
                        $users .= '<div class="w-10 h-10 image-fit zoom-in '.($u > 1 ? ' -ml-5' : '').'">';
                            $users .= '<img alt="'.(isset($usr->user->employee->full_name) ? $usr->user->employee->full_name : 'Unknown Employee').'" class="rounded-full" src="'.$photo_url.'">';
                        $users .= '</div>';
                        $u++;
                    endforeach;
                    $users .= '</div>';
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
                    'org_email' => $list->org_email,
                    'id_card' => $list->id_card,
                    'image_url' => $list->image_url,
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
            if($request->hasFile('photo')):
                $photo = $request->file('photo');
                $imageName = 'Task_'.$tasklist->id.'_'.time() . '.' . $request->photo->getClientOriginalExtension();
                $path = $photo->storeAs('public/process/'.$request->process_list_id.'/tasks/'.$tasklist->id, $imageName, 'local');
    
                $processUpdate = TaskList::where('id', $tasklist->id)->update([
                    'image' => $imageName,
                    'image_path' => Storage::disk('local')->url($path)
                ]);
            endif;
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
        $taskOldRos = TaskList::find($pl_ID);
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
            'org_email'=> $request->org_email,
            'id_card'=> $request->id_card,
            'external_link' => (isset($request->external_link) ? $request->external_link : '0'),
            'external_link_ref' => (isset($request->external_link) && $request->external_link == 1 && !empty($request->external_link_ref) ? $request->external_link_ref : ''),
            'updated_by' => auth()->user()->id
        ]);

        
        if($request->hasFile('photo')):
            $photo = $request->file('photo');
            $imageName = 'Task_'.$pl_ID.'_'.time() . '.' . $request->photo->getClientOriginalExtension();
            $path = $photo->storeAs('public/process/'.$request->process_list_id.'/tasks/'.$pl_ID, $imageName, 'local');

            if(isset($taskOldRos->image) && !empty($taskOldRos->image)):
                if (Storage::disk('local')->exists('public/process/'.$request->process_list_id.'/tasks/'.$taskOldRos->id.'/'.$taskOldRos->image)):
                    Storage::disk('local')->delete('public/process/'.$request->process_list_id.'/tasks/'.$taskOldRos->id.'/'.$taskOldRos->image);
                endif;
            endif;
            
            $taskListUpdate = TaskList::where('id', $pl_ID)->update([
                'image' => $imageName,
                'image_path' => Storage::disk('local')->url($path)
            ]);

        endif;

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

    public function getAssignedUserList(Request $request){
        $task_id = $request->task_id;
        $task = TaskList::find($task_id);

        $html = '';
        if(isset($task->users) && $task->users->count() > 0):
            foreach($task->users as $tusr):
                $html .= '<tr>';
                    $html .= '<td>';
                        $html .= '<div class="block">';
                            $html .= '<div class="w-10 h-10 intro-x image-fit mr-5 inline-block">';
                                $html .= '<img alt="'.(isset($tusr->user->employee->full_name) ? $tusr->user->employee->full_name : 'Unknown Employee').'" class="rounded-full shadow" src="'.(isset($tusr->user->employee->photo_url) && !empty($tusr->user->employee->photo_url) ? $tusr->user->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'">';
                            $html .= '</div>';
                            $html .= '<div class="inline-block relative" style="top: -5px;">';
                                $html .= '<div class="font-medium whitespace-nowrap uppercase">'.(isset($tusr->user->employee->full_name) ? $tusr->user->employee->full_name : 'Unknown Employee').'</div>';
                                if(isset($tusr->user->employee->employment->employeeJobTitle->name) && !empty($tusr->user->employee->employment->employeeJobTitle->name)):
                                    $html .= '<div class="text-slate-500 text-xs whitespace-nowrap">'.$tusr->user->employee->employment->employeeJobTitle->name.'</div>';
                                endif;
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td>'.(isset($tusr->user->employee->employment->department->name) ? $tusr->user->employee->employment->department->name : '').'</td>';
                    $html .= '<td>'.(isset($tusr->user->employee->employment->employeeWorkType->name) ? $tusr->user->employee->employment->employeeWorkType->name : '').'</td>';
                    $html .= '<td>'.(isset($tusr->user->employee->employment->works_number) ? $tusr->user->employee->employment->works_number : '').'</td>';
                    $html .= '<td>';
                        if(isset($tusr->user->employee->status) && $tusr->user->employee->status == 1):
                            $html .= '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0">Active</span>';
                        elseif(isset($tusr->user->employee->status) && $tusr->user->employee->status == 2):
                            $html .= '<span class="btn inline-flex btn-danger w-auto px-2 text-white py-0 rounded-0">Inactive</span>';
                        endif;
                    $html .= '</td>';
                $html .= '</tr>';
            endforeach;
        else:
            $html .= '<tr>';
                $html .= '<td colspan="5">';
                    $html .= '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert">';
                        $html .= '<i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Assigned user not found';
                    $html .= '</div>';
                $html .= '</td>';
            $html .= '</tr>';
        endif;

        return response()->json(['res' => $html], 200);
    }
}
