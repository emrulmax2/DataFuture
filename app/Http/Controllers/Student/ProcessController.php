<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessAndExcuseUpdateRequest;
use App\Models\Attendance;
use App\Models\AttendanceExcuse;
use App\Models\AttendanceExcuseDay;
use App\Models\AttendanceFeedStatus;
use App\Models\Plan;
use App\Models\ProcessList;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentDocument;
use App\Models\StudentTask;
use App\Models\StudentTaskDocument;
use App\Models\StudentTaskLog;
use App\Models\TaskList;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcessController extends Controller
{
    public function storeProcessTask(Request $request){
        $task_list_ids = (isset($request->task_list_ids) && !empty($request->task_list_ids) ? $request->task_list_ids : []);
        $student_id = (isset($request->student_id) && $request->student_id ? $request->student_id : 0);
        $studentRow = Student::find($student_id);

        if(!empty($task_list_ids) && $student_id > 0):
            $existingTaskIds = StudentTask::where('student_id', $student_id)->pluck('task_list_id')->toArray();
            $existingDiff = array_diff($existingTaskIds, $task_list_ids);
            $taskListDiff = array_diff($task_list_ids, $existingTaskIds);

            $numInsert = 0;
            $numDelete = 0;
            if(!empty($taskListDiff)):
                foreach($taskListDiff as $task):
                    $withTrashed = StudentTask::where('student_id', $student_id)->where('task_list_id', $task)->onlyTrashed()->get();
                    if(!empty($withTrashed) && $withTrashed->count() > 0):
                        $restoreTask = StudentTask::where('student_id', $student_id)->where('task_list_id', $task)->withTrashed()->restore();
                    else:
                        $data = [];
                        $data['student_id'] = $student_id;
                        $data['task_list_id'] = $task;
                        $data['status'] = 'Pending';
                        $data['created_by'] = auth()->user()->id;
                        $insertTask = StudentTask::create($data);
                    endif;
                    $numInsert += 1;
                endforeach;
            endif;
            if(!empty($existingDiff)):
                foreach($existingDiff as $task):
                    $deleteTask = StudentTask::where('student_id', $student_id)->where('task_list_id', $task)->delete();
                    $numDelete += 1;
                endforeach;
            endif;

            
            if($numInsert > 0):
                $message = 'Task list '.$numInsert.' item success fully inserted.';
                $message .= ($numDelete > 0 ? ' Previously inserted '.$numDelete.' item deleted.' : '');
            else:
                $message = 'No new task selected. ';
                $message .= ($numDelete > 0 ? ' Previously inserted '.$numDelete.' item deleted.' : '');
            endif;
            return response()->json(['message' => $message], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later or contact administrator.'], 422);
        endif;
    }


    public function uploadTaskDocument(Request $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $studentApplicantId = $student->applicant_id;
        $student_task_id = $request->student_task_id;
        $studentTask = StudentTask::find($student_task_id);
        $taskName = (isset($studentTask->task->name) && !empty($studentTask->task->name) ? $studentTask->task->name : '');

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/students/'.$student_id, $imageName, 's3');
        $data = [];
        $data['student_id'] = $student_id;
        $data['hard_copy_check'] = 0;
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = Storage::disk('s3')->url($path);
        $data['display_file_name'] = (!empty($taskName) ? $taskName : $imageName);
        $data['current_file_name'] = $imageName;
        $data['created_by'] = auth()->user()->id;
        $studentDoc = StudentDocument::create($data);

        if($studentDoc):
            $studentTaskDoc = StudentTaskDocument::create([
                'student_id' => $student_id,
                'student_task_id' => $student_task_id,
                'student_document_id' => $studentDoc->id,
                'created_by' => auth()->user()->id
            ]);

            $studentTaskLog = StudentTaskLog::create([
                'student_tasks_id' => $student_task_id,
                'actions' => 'Document',
                'field_name' => '',
                'prev_field_value' => '',
                'current_field_value' => $studentDoc->id,
                'created_by' => auth()->user()->id
            ]);
        endif;

        return response()->json(['message' => 'Document successfully uploaded.'], 200);
    }

    public function deleteTask(Request $request){
        $student = $request->student;
        $recordid = $request->recordid;

        $data = StudentTask::where('id', $recordid)->where('student_id', $student)->delete();
        $studentTaskLog = StudentTaskLog::create([
            'student_tasks_id' => $recordid,
            'actions' => 'Delete',
            'field_name' => '',
            'prev_field_value' => '',
            'current_field_value' => 'Item Deleted',
            'created_by' => auth()->user()->id
        ]);
        return response()->json(['message' => 'Data deleted'], 200);
    }

    public function completedTask(Request $request){
        $student = $request->student;
        $recordid = $request->recordid;
        $studentRow = Student::find($student);

        $studentTask = StudentTask::where('id', $recordid)->where('student_id', $student)->update(['status' => 'Completed', 'updated_by' => auth()->user()->id]);
        $studentTaskLog = StudentTaskLog::create([
            'student_tasks_id' => $recordid,
            'actions' => 'Status Changed',
            'field_name' => 'status',
            'prev_field_value' => 'Pending',
            'current_field_value' => 'Completed',
            'created_by' => auth()->user()->id
        ]);
        return response()->json(['message' => 'Data deleted'], 200);
    }

    public function pendingTask(Request $request){
        $student = $request->student;
        $recordid = $request->recordid;
        $studentRow = Student::find($student);


        $studentTask = StudentTask::where('id', $recordid)->where('student_id', $student)->update(['status' => 'Pending', 'updated_by' => auth()->user()->id]);
        $studentTaskLog = StudentTaskLog::create([
            'student_tasks_id' => $recordid,
            'actions' => 'Status Changed',
            'field_name' => 'status',
            'prev_field_value' => 'Completed',
            'current_field_value' => 'Pending',
            'created_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Data updated'], 200);
    }

    public function archivedProcessList(Request $request) {
        $studentId = (isset($request->studentId) && $request->studentId > 0 ? $request->studentId : 0);
        $processId = (isset($request->processId) && $request->processId > 0 ? $request->processId : 0);

        $processList = ProcessList::where('id', $processId)->where('phase', 'Live')->orderBy('id', 'ASC')->get();
        $taskIds = [];
        if(!empty($processList)):
            foreach($processList as $prl):
                foreach($prl->tasks as $tsk):
                    $taskIds[] = $tsk->id;
                endforeach;
            endforeach;
        endif;


        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentTask::where('student_id', $studentId);
        if(!empty($taskIds)):
            $query->whereIn('task_list_id', $taskIds);
        else:
            $query->where('task_list_id', '0');
        endif;
        $query->orderByRaw(implode(',', $sorts))->onlyTrashed();

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $total_rows = $query->count();
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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->task->name,
                    'desc' => isset($list->task->short_description) && !empty($list->task->short_description) ? $list->task->short_description : '',
                    'deleted_at' => (!empty($list->deleted_at) ? date('d-m-Y H:i:s', strtotime($list->deleted_at)) : '')
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function resotreTask(Request $request){
        $student = $request->student;
        $recordid = $request->recordid;

        $data = StudentTask::where('id', $recordid)->where('student_id', $student)->withTrashed()->restore();
        $studentTaskLog = StudentTaskLog::create([
            'student_tasks_id' => $recordid,
            'actions' => 'Restore',
            'field_name' => '',
            'prev_field_value' => '',
            'current_field_value' => 'Item Restored',
            'created_by' => auth()->user()->id
        ]);
        return response()->json(['message' => 'Data Restored'], 200);
    }

    public function showTaskStatuses(Request $request){
        $studentTaskId = $request->taskId;
        $studentTask = StudentTask::find($studentTaskId);
        $taskStatuses = $studentTask->task->statuses;

        $statusOpt = [];
        if(!empty($taskStatuses)):
            $html = '<label for="upload" class="form-label">Task Result <span class="text-danger">*</span></label>';
            foreach($taskStatuses as $ts):
                $taskStatus = TaskStatus::find($ts->task_status_id);
                $html .= '<div class="form-check mt-2">';
                    $html .= '<input '.($studentTask->task_status_id == $taskStatus->id ? 'Checked' : '').' id="outc_task-status-'.$taskStatus->id.'" class="form-check-input resultStatus" type="radio" name="result_statuses" value="'.$taskStatus->id.'">';
                    $html .= '<label class="form-check-label" for="outc_task-status-'.$taskStatus->id.'">'.$taskStatus->name.'</label>';
                $html .= '</div>';
            endforeach;
            $statusOpt['suc'] = 1;
            $statusOpt['res'] = $html;
        else:
            $statusOpt['suc'] = 2;
            $statusOpt['res'] = '<div class="alert alert-pending-soft show flex items-start mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> No status found for this task.</div>';
        endif;

        return response()->json(['message' => $statusOpt], 200);
    }

    public function taskResultUpdate(Request $request){
        $student_id = $request->student_id;
        $student_task_id = $request->student_task_id;
        $result_statuses = (isset($request->result_statuses) ? $request->result_statuses : '');
        $studentTaskOld = StudentTask::where('student_id', $student_id)->where('id', $student_task_id)->get()->first();

        if($result_statuses > 0):
            $data = [];
            $data['task_status_id'] = $result_statuses;
            $data['updated_by'] = auth()->user()->id;
            $studentTask = StudentTask::where('student_id', $student_id)->where('id', $student_task_id)->update($data);
            $studentTaskLog = StudentTaskLog::create([
                'student_tasks_id' => $student_task_id,
                'actions' => 'Task Status',
                'field_name' => 'task_status_id',
                'prev_field_value' => $studentTaskOld->task_status_id,
                'current_field_value' => $result_statuses,
                'created_by' => auth()->user()->id
            ]);
            return response()->json(['message' => 'Result successfully updated.'], 200);
        else: 
            return response()->json(['message' => 'Error found!'], 422);
        endif;
    }

    public function taskLogList(Request $request){
        $studentTaskId = $request->studentTaskId;
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'desc']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentTaskLog::where('student_tasks_id', $studentTaskId)->orderByRaw(implode(',', $sorts));

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
                $fieldName = '';
                $prevValue = '';
                $newValue = '';
                if($list->actions == 'Document'):
                    $fieldName = '';
                    $prevValue = '';
                    if(!empty($list->current_field_value) && !preg_match("/[a-z]/i", $list->current_field_value)):
                        $stdDocument = StudentDocument::find($list->current_field_value);
                        $newValue = '<a data-id="'.$list->current_field_value.'" href="javascript:void(0);" class="text-success downloadDoc" style="white-space: normal; word-break: break-all;">'.$stdDocument->current_file_name.'</a>';
                    else:
                        $newValue = 'Not Available';
                    endif;
                elseif($list->actions == 'Restore'):
                    $fieldName = '';
                    $prevValue = '';
                    $newValue = $list->current_field_value;
                elseif($list->actions == 'Delete'):
                    $fieldName = '';
                    $prevValue = '';
                    $newValue = $list->current_field_value;
                elseif($list->actions == 'Task Status'):
                    $prevStatus = (!empty($list->prev_field_value) && $list->prev_field_value > 0 ? TaskStatus::find($list->prev_field_value)->name : '');
                    $newStatus = (!empty($list->current_field_value) && $list->current_field_value > 0 ? TaskStatus::find($list->current_field_value)->name : '');
                    $fieldName = $list->field_name;
                    $prevValue = $prevStatus;
                    $newValue = $newStatus;
                else:
                    $fieldName = $list->field_name;
                    $prevValue = $list->prev_field_value;
                    $newValue = $list->current_field_value;
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'actions' => $list->actions,
                    'field_name' => $fieldName,
                    'prev_field_value' => $prevValue,
                    'current_field_value' => $newValue,
                    'created_at' => (!empty($list->created_at) ? date('d-m-Y H:i:s', strtotime($list->created_at)) : ''),
                    'created_by' => ($list->created_by > 0 ? User::find($list->created_by)->name : '')
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function processTaskUserList(Request $request){
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

    public function processTaskViewExcuse(Request $request){
        $student_task_id = $request->student_task_id;
        $excuse = AttendanceExcuse::with('student', 'days', 'documents')->where('student_task_id', $student_task_id)->get()->first();
        $student_id = $excuse->student_id;

        $HTML = '';
        if(isset($excuse->id) && $excuse->id > 0):
            $HTML .= '<div class="grid grid-cols-12 gap-4">';
                $HTML .= '<div class="col-span-12 sm:col-span-4 text-slate-500 font-medium">Student ID</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-8 text-slate-500 font-medium">'.$excuse->student->registration_no.'</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-4 text-slate-500 font-medium">Name</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-8 text-slate-500 font-medium">'.$excuse->student->full_name.'</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-4 text-slate-500 font-medium">Dates</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-8 text-slate-500 font-medium">';
                    if(isset($excuse->days) && $excuse->days->count() > 0):
                        $plan_ids = $excuse->days->pluck('plan_id')->unique()->toArray();
                        foreach($plan_ids as $plan_id):
                            $plan = Plan::find($plan_id);
                            $excuseDays = AttendanceExcuseDay::where('plan_id', $plan_id)->where('attendance_excuse_id', $excuse->id)->orderBy('id', 'ASC')->get();
                            $HTML .= '<div class="futureCheckedList excuseCheckedList mb-4">';
                                $HTML .= '<label class="font-medium underline inline-flex items-start moduleLabel"><i data-lucide="check-circle" class="w-4 h-4 mr-2 text-success"></i>'.$plan->creations->module_name.'</label>';
                                foreach($excuseDays as $day):
                                    $HTML .= '<div class="form-check items-start mt-2 pl-5">';
                                        $HTML .= '<input '.($day->active == 1 ? 'Checked' : 0).' name="days[]" value="'.$day->id.'" id="excuse_days_'.$plan_id.'_'.$day->id.'" class="form-check-input" type="checkbox">';
                                        $HTML .= '<label class="form-check-label" for="excuse_days_'.$plan_id.'_'.$day->id.'">';
                                            $HTML .= (isset($day->plandate->date) && !empty($day->plandate->date) ? date('jS F, Y', strtotime($day->plandate->date)) : 'Undefined Date');
                                        $HTML .= '</label>';
                                    $HTML .= '</div>';
                                endforeach;
                            $HTML .= '</div>';
                        endforeach;
                    endif;
                $HTML .= '</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-4 text-slate-500 font-medium">Reason</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-8 text-slate-500 font-medium">'.$excuse->reason.'</div>';
                if(isset($excuse->documents) && $excuse->documents->count() > 0):
                    $HTML .= '<div class="col-span-12 sm:col-span-4 text-slate-500 font-medium">Documents</div>';
                    $HTML .= '<div class="col-span-12 sm:col-span-8">';
                        $HTML .= '<ul class="m-0">';
                        foreach($excuse->documents as $docx):
                            if ($docx->current_file_name != null && Storage::disk('s3')->exists('public/students/'.$student_id.'/'.$docx->current_file_name)):
                                $HTML .= '<li class="mb-1 text-primary flex items-center"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>';
                                    $HTML .= '<a target="_blank" href="'.Storage::disk('s3')->temporaryUrl('public/students/'.$student_id.'/'.$docx->current_file_name, now()->addMinutes(60)).'">'.$docx->display_file_name.'</a>';
                                $HTML .= '</li>';
                            endif;
                        endforeach;
                        $HTML .= '</ul>';
                    $HTML .= '</div>';
                endif;
                $HTML .= '<div class="col-span-12 sm:col-span-4 text-slate-500 font-medium">Requested At</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-8 text-slate-500 font-medium">'.date('jS F, Y h:i A', strtotime($excuse->created_at)).'</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-4 text-slate-500 font-medium">Attendance Type <span class="text-danger">*</span></div>';
                $HTML .= '<div class="col-span-12 sm:col-span-8 text-slate-500 font-medium">';
                    $HTML .= '<select class="form-control w-full" name="attendance_types">';
                        $HTML .= '<option value="">Please Select</option>';
                        $HTML .= '<option '.($excuse->attendance_types == 'E' ? 'Selected' : '').' value="E">E (Authorised Absent)</option>';
                        $HTML .= '<option '.($excuse->attendance_types == 'M' ? 'Selected' : '').' value="M">M (Absent For Medical Reason)</option>';
                        $HTML .= '<option '.($excuse->attendance_types == 'M' ? 'Selected' : '').' value="H">H (Exceptional Event)</option>';
                    $HTML .= '</select>';
                    $HTML .= '<div class="acc__input-error error-attendance_types text-danger mt-2"></div>';
                $HTML .= '</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-4 text-slate-500 font-medium">Action <span class="text-danger">*</span></div>';
                $HTML .= '<div class="col-span-12 sm:col-span-8 text-slate-500 font-medium">';
                    $HTML .= '<select class="form-control w-full" name="status">';
                        $HTML .= '<option value="">Please Select</option>';
                        $HTML .= '<option '.($excuse->status == 0 ? 'Selected' : '').' value="0">Pending</option>';
                        $HTML .= '<option '.($excuse->status == 1 ? 'Selected' : '').' value="1">Reviewed & Rejected</option>';
                        $HTML .= '<option '.($excuse->status == 2 ? 'Selected' : '').' value="2">Reviewed & Approved</option>';
                    $HTML .= '</select>';
                    $HTML .= '<div class="acc__input-error error-status text-danger mt-2"></div>';
                $HTML .= '</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-4 text-slate-500 font-medium">Remarks</div>';
                $HTML .= '<div class="col-span-12 sm:col-span-8 text-slate-500 font-medium">';
                    $HTML .= '<textarea class="form-control w-full" name="remarks" rows="3">'.(isset($excuse->remarks) ? $excuse->remarks : '').'</textarea>';
                $HTML .= '</div>';
            $HTML .= '</div>';
        else:
            $HTML .= '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Excuse not found.</div>';
        endif;

        return response()->json(['htm' => $HTML, 'excuse' => $excuse->id], 200);
    }

    public function updateProcessTaskAndExcuse(ProcessAndExcuseUpdateRequest $request){
        $student_id = $request->student_id;
        $student_task_id = $request->student_task_id;
        $attendance_excuse_id = $request->attendance_excuse_id;

        $attendance_types = (isset($request->attendance_types) && !empty($request->attendance_types) ? $request->attendance_types : 'E');
        $status = $request->status;
        $remarks = $request->remarks;
        $days = (isset($request->days) && !empty($request->days) ? $request->days : []);
        $oldDays = AttendanceExcuseDay::where('attendance_excuse_id', $attendance_excuse_id)->pluck('id')->unique()->toArray();
        $deActiveDays = array_diff($oldDays, $days);

        $excuseData = [];
        $excuseData['attendance_types'] = $attendance_types;
        $excuseData['remarks'] = (!empty($remarks) ? $remarks : null);
        $excuseData['status'] = (!empty($days) && count($days) > 0 ? (!empty($status) ? $status : 0) : 1);
        $excuseData['actioned_by'] = auth()->user()->id;
        $excuseData['actioned_at'] = date('Y-m-d H:i:s');
        AttendanceExcuse::where('id', $attendance_excuse_id)->where('student_task_id', $student_task_id)->update($excuseData);

        if(!empty($days) && count($days) > 0):
            AttendanceExcuseDay::whereIn('id', $days)->update(['active' => 1]);

            $attendanceStatus = AttendanceFeedStatus::where('code', $attendance_types)->get()->first();
            $feedStatusId = (isset($attendanceStatus->id) && $attendanceStatus->id > 0 ? $attendanceStatus->id : 6);
            foreach($days as $day):
                $excuseDay = AttendanceExcuseDay::find($day);
                if(isset($excuseDay->id) && $excuseDay->id > 0):
                    Attendance::where('plans_date_list_id', $excuseDay->plans_date_list_id)->where('plan_id', $excuseDay->plan_id)
                        ->where('student_id', $student_id)->update(['attendance_feed_status_id' => $feedStatusId]);
                endif;
            endforeach;
        endif;
        if(!empty($deActiveDays) && count($deActiveDays) > 0):
            AttendanceExcuseDay::whereIn('id', $deActiveDays)->update(['active' => 0]);
        endif;

        StudentTask::where('id', $student_task_id)->where('student_id', $student_id)->update(['status' => 'Completed', 'updated_by' => auth()->user()->id]);

        return response()->json(['message' => 'Excuse successfully reviewd and updated.'], 200);
    }
}
