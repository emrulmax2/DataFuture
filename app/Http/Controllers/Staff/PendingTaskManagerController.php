<?php

namespace App\Http\Controllers\Staff;

use App\Exports\ArrayCollectionExport;
use App\Exports\StudentEmailIdTaskExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\InterviewerUnlockDirectRequest;
use App\Http\Requests\TaskCanceledReasonRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantDocument;
use App\Models\ApplicantInterview;
use App\Models\ApplicantTask;
use App\Models\ApplicantTaskDocument;
use App\Models\ApplicantTaskLog;
use App\Models\ApplicantViewUnlock;
use App\Models\ComonSmtp;
use App\Models\LetterSet;
use App\Models\ProcessList;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentContact;
use App\Models\StudentDocument;
use App\Models\StudentTask;
use App\Models\StudentTaskDocument;
use App\Models\StudentTaskLog;
use App\Models\StudentUser;
use App\Models\TaskList;
use App\Models\TaskListUser;
use App\Models\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class PendingTaskManagerController extends Controller
{
    public function index()
    {
        $userData = \Auth::guard('web')->user();
        
        return view('pages.users.staffs.task.index', [
            'title' => 'User Task Manager - London Churchill College',
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
                    $aplPendingTask = ApplicantTask::where('task_list_id', $atsk->id)->whereIn('status', ['Pending', 'In Progress'])->get();
                    $stdPendingTask = StudentTask::where('task_list_id', $atsk->id)->whereIn('status', ['Pending', 'In Progress'])->get();
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
            'title' => 'User Task Manager - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Task Manager', 'href' => route('task.manager')],
                ['label' => 'Details', 'href' => 'javascript:void(0);'],
            ],
            'task' => TaskList::find($id)
        ]);
    }

    public function list(Request $request){
        $status = isset($request->status) && !empty($request->status) ? $request->status : 'Pending';
        $task_id = isset($request->task_id) && $request->task_id > 0 ? $request->task_id : 0;
        $phase = (isset($request->phase) && !empty($request->phase) ? $request->phase : 'Live');

        $task = TaskList::find($task_id);

        if($phase == 'Applicant'):
            $applicant_ids = ApplicantTask::where('task_list_id', $task_id)->where('status', $status)->pluck('applicant_id')->unique()->toArray();
            $Query = Applicant::whereIn('id', $applicant_ids);

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
                    $theApplicantTask = ApplicantTask::where('task_list_id', $task_id)->where('applicant_id', $list->id)->where('status', $status)->orderBy('id', 'DESC')->get()->first();
                    $createOrUpdate = '';
                    $createOrUpdateBy = '';
                    $status = (isset($theApplicantTask->status) && !empty($theApplicantTask->status) ? $theApplicantTask->status : '');
                    if($status != 'Pending'):
                        $createOrUpdateBy = (isset($theApplicantTask->updatedBy->employee->full_name) && !empty($theApplicantTask->updatedBy->employee->full_name) ? $theApplicantTask->updatedBy->employee->full_name : '');
                        $createOrUpdate = (isset($theApplicantTask->updated_at) && !empty($theApplicantTask->updated_at) ? date('jS M, Y', strtotime($theApplicantTask->updated_at)) : '');
                    else:
                        $createOrUpdate = (isset($theApplicantTask->created_at) && !empty($theApplicantTask->created_at) ? date('jS M, Y', strtotime($theApplicantTask->created_at)) : '');
                    endif;
                    $interviewDetails = [];
                    if($task->interview == 'Yes' && ($status == 'In Progress' || $status == 'Completed')):
                        $interview = ApplicantInterview::where('applicant_id', $list->id)->where('applicant_task_id', $theApplicantTask->id)->orderBy('id', 'DESC')->get()->first();
                        if(isset($interview->id) && $interview->id > 0):
                            $interviewDetails['interview_id'] = (isset($interview->id) && $interview->id > 0 ? $interview->id : 0);
                            $interviewDetails['date'] = (isset($interview->interview_date) && !empty($interview->interview_date) ? date('jS M, Y', strtotime($interview->interview_date)) : '');
                            $interviewDetails['time'] = (isset($interview->start_time) && !empty($interview->start_time) ? date('H:i a', strtotime($interview->start_time)) : '00:00');
                            $interviewDetails['time'] .= (isset($interview->end_time) && !empty($interview->end_time) ? ' - '.date('H:i a', strtotime($interview->end_time)) : ' - 00:00');
                            $interviewDetails['interviewer'] = (isset($interview->user->employee->full_name) && !empty($interview->user->employee->full_name) ? $interview->user->employee->full_name : 'Unknown');
                            $interviewDetails['result'] = (isset($interview->interview_result) && !empty($interview->interview_result) ? $interview->interview_result : '');
                        endif;
                    endif;

                    $taskDownloads = '';
                    if(isset($theApplicantTask->documents) && !empty($theApplicantTask->documents)):
                        $taskDownloads .= '<div class="flex">';
                            foreach($theApplicantTask->documents as $tdoc):
                                if($tdoc->doc_type == 'jpg' || $tdoc->doc_type == 'jpeg' || $tdoc->doc_type == 'png' || $tdoc->doc_type == 'gif'):
                                    if(isset($tdoc->current_file_name) && !empty($tdoc->current_file_name) && isset($tdoc->id) && $tdoc->id > 0):
                                        $taskDownloads .= '<a data-phase="'.$phase.'" data-id="'.$tdoc->id.'" class="downloadTaskDoc w-6 h-6 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="javascript:void(0);">';
                                            $taskDownloads .= '<i data-lucide="image" class="w-4 h-4 text-primary"></i>';
                                        $taskDownloads .= '</a>';
                                    endif;
                                else: 
                                    if(isset($tdoc->current_file_name) && !empty($tdoc->current_file_name) && isset($tdoc->id) && $tdoc->id > 0):
                                        $taskDownloads .= '<a data-phase="'.$phase.'" data-id="'.$tdoc->id.'" class="downloadTaskDoc w-6 h-6 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="javascript:void(0);">';
                                            $taskDownloads .= '<i data-lucide="file-text" class="w-4 h-4 text-primary"></i>';
                                        $taskDownloads .= '</a>';
                                    endif;
                                endif;
                            endforeach;
                        $taskDownloads .= '</div>';
                    endif;
                    $data[] = [
                        'id' => $list->id,
                        'sl' => $i,
                        'application_no' => (empty($list->application_no) ? $list->id : $list->application_no),
                        'first_name' => $list->first_name,
                        'last_name' => $list->last_name,
                        'date_of_birth'=> 'N/A',
                        'course'=> (isset($list->course->creation->course->name) && !empty($list->course->creation->course->name) ? $list->course->creation->course->name : ''),
                        'semester'=> (isset($list->course->creation->semester->name) && !empty($list->course->creation->semester->name) ? $list->course->creation->semester->name : ''),
                        'sex_identifier_id'=> (isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : ''),
                        'status_id'=> (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                        'url' => route('admission.show', $list->id),
                        'task_id' => $task_id,
                        'task_created_by' => $createOrUpdateBy,
                        'task_created' => $createOrUpdate,
                        'task_status' => $status,
                        'ids' => $list->id,
                        'phase' => $phase,
                        'canceled_reason' => ($status == 'Canceled' && isset($theApplicantTask->canceled_reason) && !empty($theApplicantTask->canceled_reason) ? $theApplicantTask->canceled_reason : ''),
                        'interview' => $interviewDetails,
                        'has_task_status' => ($task->interview != 'Yes' && isset($theApplicantTask->task->status) && !empty($theApplicantTask->task->status) ? $theApplicantTask->task->status : 'No'),
                        'has_task_upload' => ($task->interview != 'Yes' && isset($theApplicantTask->task->upload) && !empty($theApplicantTask->task->upload) ? $theApplicantTask->task->upload : 'No'),
                        'outcome' => ($task->interview != 'Yes' && isset($theApplicantTask->task_status_id) && isset($theApplicantTask->applicatnTaskStatus->name) && !empty($theApplicantTask->applicatnTaskStatus->name) ? $theApplicantTask->applicatnTaskStatus->name : ''),
                        'is_completable' => ($task->interview != 'Yes' &&  ($theApplicantTask->task->status == 'No' || ($theApplicantTask->task->status == 'Yes' && $theApplicantTask->task_status_id > 0)) && ($theApplicantTask->task->upload == 'No' || ($theApplicantTask->task->upload == 'Yes' && $theApplicantTask->documents->count() > 0)) ? 1 : 0),
                        'downloads' => $taskDownloads
                    ];
                    $i++;
                endforeach;
            endif;
        else:
            $student_ids = StudentTask::where('task_list_id', $task_id)->where('status', $status)->pluck('student_id')->unique()->toArray();

            $Query = Student::whereIn('id', $student_ids);

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
                    $theStudentTask = StudentTask::where('task_list_id', $task_id)->where('student_id', $list->id)->where('status', $status)->orderBy('id', 'DESC')->get()->first();
                    $createOrUpdate = '';
                    $createOrUpdateBy = '';
                    $status = (isset($theStudentTask->status) && !empty($theStudentTask->status) ? $theStudentTask->status : '');
                    if($status != 'Pending'):
                        $createOrUpdateBy = (isset($theStudentTask->updatedBy->employee->full_name) && !empty($theStudentTask->updatedBy->employee->full_name) ? $theStudentTask->updatedBy->employee->full_name : '');
                        $createOrUpdate = (isset($theStudentTask->updated_at) && !empty($theStudentTask->updated_at) ? date('jS M, Y', strtotime($theStudentTask->updated_at)) : '');
                    else:
                        $createOrUpdate = (isset($theStudentTask->created_at) && !empty($theStudentTask->created_at) ? date('jS M, Y', strtotime($theStudentTask->created_at)) : '');
                    endif;

                    $taskDownloads = '';
                    if(isset($theStudentTask->documents) && !empty($theStudentTask->documents)):
                        $taskDownloads .= '<div class="flex">';
                            foreach($theStudentTask->documents as $tdoc):
                                if($tdoc->doc_type == 'jpg' || $tdoc->doc_type == 'jpeg' || $tdoc->doc_type == 'png' || $tdoc->doc_type == 'gif'):
                                    if(isset($tdoc->current_file_name) && !empty($tdoc->current_file_name) && isset($tdoc->id) && $tdoc->id > 0):
                                        $taskDownloads .= '<a data-phase="'.$phase.'" data-id="'.$tdoc->id.'" class="downloadTaskDoc w-6 h-6 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="javascript:void(0);">';
                                            $taskDownloads .= '<i data-lucide="image" class="w-4 h-4 text-primary"></i>';
                                        $taskDownloads .= '</a>';
                                    endif;
                                else: 
                                    if(isset($tdoc->current_file_name) && !empty($tdoc->current_file_name) && isset($tdoc->id) && $tdoc->id > 0):
                                        $taskDownloads .= '<a data-phase="'.$phase.'" data-id="'.$tdoc->id.'" class="downloadTaskDoc w-6 h-6 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="javascript:void(0);">';
                                            $taskDownloads .= '<i data-lucide="file-text" class="w-4 h-4 text-primary"></i>';
                                        $taskDownloads .= '</a>';
                                    endif;
                                endif;
                            endforeach;
                        $taskDownloads .= '</div>';
                    endif;
                    $data[] = [
                        'id' => $list->id,
                        'sl' => $i,
                        'registration_no' => (empty($list->registration_no) ? $list->id : $list->registration_no),
                        'first_name' => $list->first_name,
                        'last_name' => $list->last_name,
                        'date_of_birth'=> (isset($list->date_of_birth) && !empty($list->date_of_birth) ? date('d-m-Y', strtotime($list->date_of_birth)) : ''),
                        'course'=> (isset($list->course->creation->course->name) && !empty($list->course->creation->course->name) ? $list->course->creation->course->name : ''),
                        'semester'=> (isset($list->course->creation->semester->name) && !empty($list->course->creation->semester->name) ? $list->course->creation->semester->name : ''),
                        'sex_identifier_id'=> (isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : ''),
                        'status_id'=> (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                        'url' => route('student.show', $list->id),
                        'task_id' => $task_id,
                        'task_created_by' => $createOrUpdateBy,
                        'task_created' => $createOrUpdate,
                        'task_status' => $status,
                        'ids' => $list->id,
                        'phase' => $phase,
                        'canceled_reason' => ($status == 'Canceled' && isset($theStudentTask->canceled_reason) && !empty($theStudentTask->canceled_reason) ? $theStudentTask->canceled_reason : ''),
                        'interview' => [],
                        'has_task_status' => ($task->interview != 'Yes' && isset($theStudentTask->task->status) && !empty($theStudentTask->task->status) ? $theStudentTask->task->status : 'No'),
                        'has_task_upload' => ($task->interview != 'Yes' && isset($theStudentTask->task->upload) && !empty($theStudentTask->task->upload) ? $theStudentTask->task->status : 'No'),
                        'outcome' => ($task->interview != 'Yes' && isset($theStudentTask->task_status_id) && isset($theStudentTask->studentTaskStatus->name) && !empty($theStudentTask->studentTaskStatus->name) ? $theStudentTask->studentTaskStatus->name : ''),
                        'is_completable' => ($task->interview != 'Yes' &&  ($theStudentTask->task->status == 'No' || ($theStudentTask->task->status == 'Yes' && $theStudentTask->task_status_id > 0)) && ($theStudentTask->task->upload == 'No' || ($theStudentTask->task->upload == 'Yes' && $theStudentTask->documents->count() > 0)) ? 1 : 0),
                        'downloads' => $taskDownloads
                    ];
                    $i++;
                endforeach;
            endif;
        endif;

        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function allTasks(){
        return view('pages.users.staffs.task.all-task', [
            'title' => 'User Task Manager - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Task Manager', 'href' => route('task.manager')],
                ['label' => 'All Task', 'href' => 'javascript:void(0);'],
            ],
            'processTasks' => $this->getAllPendingProcessTasks()
        ]);
    }

    public function getAllPendingProcessTasks(){
        $result = [];
        $allProcess = ProcessList::orderBy('name', 'ASC')->get();

        if(!empty($allProcess)):
            foreach($allProcess as $theProcess):
                $processTasks = TaskList::where('process_list_id', $theProcess->id)->orderBy('name', 'ASC')->get();
                if(!empty($processTasks) && $processTasks->count() > 0):
                    $outstanding_tasks = 0;
                    foreach($processTasks as $atsk):
                        $aplPendingTask = ApplicantTask::where('task_list_id', $atsk->id)->whereIn('status', ['Pending', 'In Progress'])->get();
                        $stdPendingTask = StudentTask::where('task_list_id', $atsk->id)->whereIn('status', ['Pending', 'In Progress'])->get();
                        if($aplPendingTask->count() > 0 || $stdPendingTask->count() > 0):
                            $result[$theProcess->id]['tasks'][$atsk->id] = $atsk;
                            $result[$theProcess->id]['tasks'][$atsk->id]['pending_task'] = $aplPendingTask->count() + $stdPendingTask->count();
                            $outstanding_tasks += $aplPendingTask->count();
                            $outstanding_tasks += $stdPendingTask->count();
                        endif;
                    endforeach;
                    if($outstanding_tasks > 0):
                        $result[$theProcess->id]['name'] = $theProcess->name;
                        $result[$theProcess->id]['outstanding_tasks'] = $outstanding_tasks;
                    endif;
                endif;
            endforeach;
        endif;

        return $result;
    }

    public function downloadTaskStudentEmailListExcel(Request $request){
        $ids = $request->ids;

        if(!empty($ids)):
            $theCollection = [];
            $theCollection[1][] = 'Student ID';
            $theCollection[1][] = 'First Name';
            $theCollection[1][] = 'Last Name';
            $theCollection[1][] = 'Email Address';
            $theCollection[1][] = 'Password';

            $row = 2;
            foreach($ids as $id):
                if($id > 0):
                    $student = Student::find($id);
                    $studentUserEmail = $student->users->email;
                    $studentUserEmailVerifiedA = (isset($student->users->email_verified_at) && !empty($student->users->email_verified_at) ? 1 : 0);
                    $orgEmail = strtolower($student->registration_no).'@lcc.ac.uk';
                    $newPassword = date('Ymd', strtotime($student->date_of_birth)).strtolower($student->last_name);
                    
                    /*if($studentUserEmail != $orgEmail):*/
                        $studentContact = $studentContactOld = StudentContact::find($student->contact->id);
                        $studentContact->fill([
                            //'personal_email' => $studentUserEmail, 
                            //'personal_email_verification' => $studentUserEmailVerifiedA,
                            'institutional_email' => $orgEmail, 
                            'institutional_email_verification' => 1,
                        ]);
                        $changes = $studentContact->getDirty();
                        $studentContact->save();
                        if($studentContact->wasChanged() && !empty($changes)):
                            foreach($changes as $field => $value):
                                $data = [];
                                $data['student_id'] = $id;
                                $data['table'] = 'student_contacts';
                                $data['field_name'] = $field;
                                $data['field_value'] = $studentContactOld->$field;
                                $data['field_new_value'] = $value;
                                $data['created_by'] = auth()->user()->id;
                
                                StudentArchive::create($data);
                            endforeach;
                        endif;

                        $studentUser = $studentUserOld = StudentUser::find($student->users->id);
                        $studentUser->fill([
                            'email' => $orgEmail,
                            'password' => Hash::make($newPassword)
                        ]);
                        $changes = $studentUser->getDirty();
                        $studentUser->save();
                        if($studentUser->wasChanged() && !empty($changes)):
                            foreach($changes as $field => $value):
                                $data = [];
                                $data['student_id'] = $id;
                                $data['table'] = 'student_users';
                                $data['field_name'] = $field;
                                $data['field_value'] = $studentUserOld->$field;
                                $data['field_new_value'] = $value;
                                $data['created_by'] = auth()->user()->id;
                
                                StudentArchive::create($data);
                            endforeach;
                        endif;

                        /* Excel Data Array */
                        $theCollection[$row][] = $student->registration_no;
                        $theCollection[$row][] = $student->first_name;
                        $theCollection[$row][] = $student->last_name;
                        $theCollection[$row][] = $orgEmail;
                        $theCollection[$row][] = $newPassword;

                        $row++;
                    /*endif;*/
                endif;
            endforeach;

            return Excel::download(new StudentEmailIdTaskExport($theCollection), 'New_Student_Email_Id_Create_Task.xlsx');
        else:
            return response()->json(['msg' => 'Error Found!'], 422);
        endif;
    }

    public function completeTaskStudentEmailTask(Request $request){
        $ids = $request->ids;
        if(!empty($ids)){
            $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
            $configuration = [
                'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                
                'from_email'    => 'no-reply@lcc.ac.uk',
                'from_name'    =>  'London Churchill College',
            ];
            $letterSet = LetterSet::find(116);

            foreach($ids as $id):
                $student = Student::find($id);
                $orgEmail = strtolower($student->registration_no).'@lcc.ac.uk';
                $studentUserEmail = $student->users->email;
                $mailTo = [];
                $mailTo[] = $studentUserEmail;
                if(isset($student->contact->personal_email) && !empty($student->contact->personal_email)):
                    $mailTo[] = $student->contact->personal_email;
                endif;
                //$mailTo[] = 'limon@churchill.ac';

                $theEmailTask = TaskList::where('org_email', 'Yes')->orderBy('id', 'DESC')->get()->first();
                $theStudentTask = StudentTask::where('task_list_id', $theEmailTask->id)->where('student_id', $student->id)->where('status', 'Pending')->get()->first();
                if($orgEmail == $studentUserEmail && (isset($theStudentTask->id) && $theStudentTask->id > 0)):
                    $updateStudentTask = StudentTask::where('id', $theStudentTask->id)->where('student_id', $student->id)->update(['status' => 'Completed', 'updated_by' => auth()->user()->id]);
                    $studentTaskLog = StudentTaskLog::create([
                        'student_tasks_id' => $theStudentTask->id,
                        'actions' => 'Status Changed',
                        'field_name' => 'status',
                        'prev_field_value' => 'Pending',
                        'current_field_value' => 'Completed',
                        'created_by' => auth()->user()->id
                    ]);

                    if(isset($letterSet->id) && $letterSet->id > 0 && !empty($letterSet->description)):
                        $subject = 'Welcome to London Churchill College';
                        $MSGBODY = $letterSet->description;

                        UserMailerJob::dispatch($configuration, $mailTo, new CommunicationSendMail($subject, $MSGBODY, []));
                    endif;
                endif;
            endforeach;
        }else{
            return response()->json(['msg' => 'Error Found!'], 422);
        }
    }

    public function downloadIdCard(Request $request){
        $student_id = $request->student_id;
        $task_id = $request->task_id;

        $student = Student::find($student_id);
        if ($student->photo !== null && Storage::disk('local')->exists('public/students/'.$student->id.'/'.$student->photo)) {
            $photoURL = url('storage/students/'.$student->id.'/'.$student->photo);
        } else {
            $photoURL = asset('build/assets/images/user_avatar.png');
        }

        $PDFHTML = '';
        $PDFHTML .= '<div class="printBtns">';
            $PDFHTML .= '<button data-id="'.$student->registration_no.'" id="thePrintBtn_'.$student->registration_no.'" class="btn btn-success text-white thePrintBtn"><i data-lucide="download-cloud" class="w-4 h-4 mr-2"></i> Download '.$student->registration_no.'</button>';
        $PDFHTML .= '</div>';
        $PDFHTML .= '<div class="theIDCard" id="theIDCard_'.$student->registration_no.'" style="background-image: url('.asset('build/assets/images/id_card_bg_new.jpg').');">';
            $PDFHTML .= '<div class="profilePicWrap">';
                $PDFHTML .= '<span style="background-image: url(\''.$photoURL.'\')">';
                    //$PDFHTML .= '<img src="'.$student->photo_url.'" alt=""/>';
                $PDFHTML .= '</span>';
            $PDFHTML .= '</div>';
            $PDFHTML .= '<div class="profileInfWrap">';
                $PDFHTML .= '<h2 class="uppercase firstName">'.$student->first_name.'</h2>';
                $PDFHTML .= '<h2 class="uppercase firstName">'.$student->last_name.'</h2>';
            $PDFHTML .= '</div>';
            $PDFHTML .= '<div class="profileIdentificationWrap">';
                $PDFHTML .= '<p class="registrationNo">'.$student->registration_no.'</p>';
                $PDFHTML .= '<p class="expireDate">Exp Date: '.(isset($student->crel->creation->availability[0]->course_end_date) && !empty($student->crel->creation->availability[0]->course_end_date) ? date('F Y', strtotime($student->crel->creation->availability[0]->course_end_date)) : '').'</p>';
            $PDFHTML .= '</div>';
            $PDFHTML .= '<div class="qrcodeCol">';
                $PDFHTML .= QrCode::format('svg')->size(106)->generate($student->registration_no);
            $PDFHTML .= '</div>';
        $PDFHTML .= '</div>';

        return response()->json(['id' => $student->registration_no, 'res' => $PDFHTML], 200);
    }

    public function updateTaskStatus(Request $request){
        $student_ids = $request->student_ids;
        $task_id = $request->task_id;
        $status = $request->status;
        $phase = $request->phase;

        foreach($student_ids as $student_id):
            if($phase == 'Applicant'):
                $taskOldRow = ApplicantTask::where('applicant_id', $student_id)->where('task_list_id', $task_id)->get()->first();

                if($taskOldRow->status != $status):
                    $studentTask = ApplicantTask::where('task_list_id', $task_id)->where('applicant_id', $student_id)->update(['status' => $status, 'canceled_reason' => null, 'updated_by' => auth()->user()->id]);
                    $studentTaskLog = ApplicantTaskLog::create([
                        'applicant_tasks_id' => $taskOldRow->id,
                        'actions' => 'Status Changed',
                        'field_name' => 'status',
                        'prev_field_value' => $taskOldRow->status,
                        'current_field_value' => $status,
                        'created_by' => auth()->user()->id
                    ]);
                    
                    $applicantRow = Applicant::find($student_id);
                    $pendingTask = ApplicantTask::where('applicant_id', $student_id)->whereIn('status', ['Pending', 'In Progress'])->get();
                    if($pendingTask->count() == 0 && $applicantRow->status_id < 4):
                        $applicantData['status_id'] = 4;
                        Applicant::where('id', $student_id)->update($applicantData);
                        $statusRow = Status::find(4);
                        if(isset($statusRow->letter_set_id) && $statusRow->letter_set_id > 0):
                            $this->sendLetterOnStatusChanged($student_id, 4);
                        elseif(isset($statusRow->email_template_id) && $statusRow->email_template_id > 0):
                            $this->sendEmailOnStatusChanged($student_id, 4);
                        endif;
            
                        $data = [];
                        $data['applicant_id'] = $student_id;
                        $data['table'] = 'applicants';
                        $data['field_name'] = 'status_id';
                        $data['field_value'] = $applicantRow->status_id;
                        $data['field_new_value'] = '4';
                        $data['created_by'] = auth()->user()->id;
            
                        ApplicantArchive::create($data);
                    endif;
                endif;
            else:
                $taskOldRow = StudentTask::where('student_id', $student_id)->where('task_list_id', $task_id)->get()->first();

                if($taskOldRow->status != $status):
                    $studentTask = StudentTask::where('task_list_id', $task_id)->where('student_id', $student_id)->update(['status' => $status, 'canceled_reason' => null, 'updated_by' => auth()->user()->id]);
                    $studentTaskLog = StudentTaskLog::create([
                        'student_tasks_id' => $taskOldRow->id,
                        'actions' => 'Status Changed',
                        'field_name' => 'status',
                        'prev_field_value' => $taskOldRow->status,
                        'current_field_value' => $status,
                        'created_by' => auth()->user()->id
                    ]);
                endif;
            endif;
        endforeach;

        return response()->json(['res' => 'Selected student task status successfully updated.'], 200);
    }

    public function canceledTask(TaskCanceledReasonRequest $request){
        $canceled_reason = $request->canceled_reason;
        $phase = (isset($request->phase) && !empty($request->phase) ? $request->phase : 'Live');
        $task_id = (isset($request->task_id) && !empty($request->task_id) ? $request->task_id : 0);
        $ids = (isset($request->ids) && !empty($request->ids) ? explode(',', $request->ids) : []);

        if(!empty($ids) && $task_id > 0):
            foreach($ids as $id):
                if($phase == 'Applicant'):
                    $taskOldRow = ApplicantTask::where('applicant_id', $id)->where('task_list_id', $task_id)->get()->first();
    
                    $studentTask = ApplicantTask::where('task_list_id', $task_id)->where('applicant_id', $id)->update(['status' => 'Canceled', 'canceled_reason' => $canceled_reason, 'updated_by' => auth()->user()->id]);
                    $studentTaskLog = ApplicantTaskLog::create([
                        'applicant_tasks_id' => $taskOldRow->id,
                        'actions' => 'Status Changed',
                        'field_name' => 'status',
                        'prev_field_value' => $taskOldRow->status,
                        'current_field_value' => 'Canceled',
                        'created_by' => auth()->user()->id
                    ]);
                else:
                    $taskOldRow = StudentTask::where('student_id', $id)->where('task_list_id', $task_id)->get()->first();
    
                    $studentTask = StudentTask::where('task_list_id', $task_id)->where('student_id', $id)->update(['status' => 'Canceled', 'canceled_reason' => $canceled_reason, 'updated_by' => auth()->user()->id]);
                    $studentTaskLog = StudentTaskLog::create([
                        'student_tasks_id' => $taskOldRow->id,
                        'actions' => 'Status Changed',
                        'field_name' => 'status',
                        'prev_field_value' => $taskOldRow->status,
                        'current_field_value' => 'Canceled',
                        'created_by' => auth()->user()->id
                    ]);
                endif;
            endforeach;
        endif;

        return response()->json(['res' => 'Selected student task status successfully updated.'], 200);
    }

    public function downloadTaskStudentListExcel(Request $request){
        $ids = $request->ids;
        $task_id = $request->task_id;
        $phase = $request->phase;
        $task = TaskList::find($task_id);

        if(!empty($ids)):
            $theCollection = [];
            $theCollection[1][] = ($phase == 'Applicant' ? 'Ref. No' : 'Reg. No');
            $theCollection[1][] = 'First Name';
            $theCollection[1][] = 'Last Name';
            $theCollection[1][] = 'Email Address';
            $theCollection[1][] = 'Date of Birth';
            $theCollection[1][] = 'Course';
            $theCollection[1][] = 'Semester';
            $theCollection[1][] = 'Status';

            $row = 2;
            foreach($ids as $id):
                if($phase == 'Applicant'):
                    $applicant = Applicant::find($id);
                    $applicantUserEmail = $applicant->users->email;
                    
                    /* Excel Data Array */
                    $theCollection[$row][] = $applicant->application_no;
                    $theCollection[$row][] = $applicant->first_name;
                    $theCollection[$row][] = $applicant->last_name;
                    $theCollection[$row][] = $applicantUserEmail;
                    $theCollection[$row][] = (isset($applicant->date_of_birth) && !empty($applicant->date_of_birth) ? date('d-m-Y', strtotime($applicant->date_of_birth)) : '');
                    $theCollection[$row][] = (isset($applicant->course->creation->course->name) && !empty($applicant->course->creation->course->name) ? $applicant->course->creation->course->name : '');
                    $theCollection[$row][] = (isset($applicant->course->creation->semester->name) && !empty($applicant->course->creation->semester->name) ? $applicant->course->creation->semester->name : '');
                    $theCollection[$row][] = (isset($applicant->status->name) && !empty($applicant->status->name) ? $applicant->status->name : '');
                else:
                    $student = Student::find($id);
                    $studentUserEmail = $student->users->email;
                    
                    /* Excel Data Array */
                    $theCollection[$row][] = $student->registration_no;
                    $theCollection[$row][] = $student->first_name;
                    $theCollection[$row][] = $student->last_name;
                    $theCollection[$row][] = $studentUserEmail;
                    $theCollection[$row][] = (isset($student->date_of_birth) && !empty($student->date_of_birth) ? date('d-m-Y', strtotime($student->date_of_birth)) : '');
                    $theCollection[$row][] = (isset($student->crel->creation->course->name) && !empty($student->crel->creation->course->name) ? $student->crel->creation->course->name : '');
                    $theCollection[$row][] = (isset($student->crel->creation->semester->name) && !empty($student->crel->creation->semester->name) ? $student->crel->creation->semester->name : '');
                    $theCollection[$row][] = (isset($student->status->name) && !empty($student->status->name) ? $student->status->name : '');
                endif;
                $row++;
            endforeach;

            return Excel::download(new ArrayCollectionExport($theCollection), 'Task_Student_List.xlsx');
        else:
            return response()->json(['msg' => 'Error Found!'], 422);
        endif;
    }

    public function uploadTaskDocument(Request $request){
        $student_id = $request->student_id;
        $task_id = $request->task_id;
        $phase = $request->phase;
        $display_file_name = $request->display_file_name;


        $thePerson = ($phase == 'Applicant' ? Applicant::find($student_id) : Student::find($student_id));
        $applicantId = ($phase == 'Applicant' ? $thePerson->id : $thePerson->applicant_id);

        $theTask = ($phase == 'Applicant' ? ApplicantTask::where('applicant_id', $student_id)->where('task_list_id', $task_id)->get()->first() : StudentTask::where('student_id', $student_id)->where('task_list_id', $task_id)->get()->first());
        $taskName = (isset($theTask->task->name) && !empty($theTask->task->name) ? $theTask->task->name : '');

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/applicants/'.$applicantId, $imageName, 's3');

        $data = [];
        if($phase == 'Applicant'):
            $data['applicant_id'] = $student_id;
        else:
            $data['student_id'] = $student_id;
        endif;
        $data['hard_copy_check'] = (isset($request->hard_copy_check) && $request->hard_copy_check > 0 ? $request->hard_copy_check : 0);
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = Storage::disk('s3')->url($path);
        $data['display_file_name'] = (!empty($display_file_name) ? $display_file_name.' - '.$taskName : (!empty($taskName) ? $taskName : $imageName));
        $data['current_file_name'] = $imageName;
        $data['created_by'] = auth()->user()->id;
        if($phase == 'Applicant'):
            $theDoc = ApplicantDocument::create($data);
        else:
            $theDoc = StudentDocument::create($data);
        endif;

        if($theDoc->id):
            if($phase == 'Applicant'):
                $studentTaskDoc = ApplicantTaskDocument::create([
                    'applicant_task_id' => $theTask->id,
                    'applicant_document_id' => $theDoc->id,
                    'created_by' => auth()->user()->id
                ]);

                $applicantTaskLog = ApplicantTaskLog::create([
                    'applicant_tasks_id' => $theTask->id,
                    'actions' => 'Document',
                    'field_name' => '',
                    'prev_field_value' => '',
                    'current_field_value' => $theDoc->id,
                    'created_by' => auth()->user()->id
                ]);
            else:
                $studentTaskDoc = StudentTaskDocument::create([
                    'student_id' => $student_id,
                    'student_task_id' => $theTask->id,
                    'student_document_id' => $theDoc->id,
                    'created_by' => auth()->user()->id
                ]);

                $studentTaskLog = StudentTaskLog::create([
                    'student_tasks_id' => $theTask->id,
                    'actions' => 'Document',
                    'field_name' => '',
                    'prev_field_value' => '',
                    'current_field_value' => $theDoc->id,
                    'created_by' => auth()->user()->id
                ]);
            endif;
        endif;

        return response()->json(['message' => 'Document successfully uploaded.'], 200);
    }

    public function taskOutcomeStatuses(Request $request){
        $phase = $request->phase;
        $taskid = $request->taskid;
        $studentid = $request->studentid;

        $theTask = ($phase == 'Applicant' ? ApplicantTask::where('applicant_id', $studentid)->where('task_list_id', $taskid)->get()->first() : StudentTask::where('student_id', $studentid)->where('task_list_id', $taskid)->get()->first());
        $taskStatuses = $theTask->task->statuses;

        $statusOpt = [];
        if(!empty($taskStatuses)):
            $html = '<label for="upload" class="form-label">Task Result <span class="text-danger">*</span></label>';
            foreach($taskStatuses as $ts):
                $taskStatus = TaskStatus::find($ts->task_status_id);
                $html .= '<div class="form-check mt-2">';
                    $html .= '<input '.($theTask->task_status_id == $taskStatus->id ? 'Checked' : '').' id="outc_task-status-'.$taskStatus->id.'" class="form-check-input resultStatus" type="radio" name="result_statuses" value="'.$taskStatus->id.'">';
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
    

    public function updateTaskOutcome(Request $request){
        $student_id = $request->student_id;
        $task_id = $request->task_id;
        $phase = $request->phase;
        $result_statuses = (isset($request->result_statuses) ? $request->result_statuses : '');

        $theOldTask = ($phase == 'Applicant' ? ApplicantTask::where('applicant_id', $student_id)->where('task_list_id', $task_id)->get()->first() : StudentTask::where('student_id', $student_id)->where('task_list_id', $task_id)->get()->first());

        if($result_statuses > 0):
            $data = [];
            $data['task_status_id'] = $result_statuses;
            $data['updated_by'] = auth()->user()->id;
            if($phase == 'Applicant'):
                $studentTask = ApplicantTask::where('applicant_id', $student_id)->where('task_list_id', $task_id)->update($data);
                $studentTaskLog = ApplicantTaskLog::create([
                    'applicant_tasks_id' => $theOldTask->id,
                    'actions' => 'Task Status',
                    'field_name' => 'task_status_id',
                    'prev_field_value' => $theOldTask->task_status_id,
                    'current_field_value' => $result_statuses,
                    'created_by' => auth()->user()->id
                ]);
            else:
                $studentTask = StudentTask::where('student_id', $student_id)->where('task_list_id', $task_id)->update($data);
                $studentTaskLog = StudentTaskLog::create([
                    'student_tasks_id' => $theOldTask->id,
                    'actions' => 'Task Status',
                    'field_name' => 'task_status_id',
                    'prev_field_value' => $theOldTask->task_status_id,
                    'current_field_value' => $result_statuses,
                    'created_by' => auth()->user()->id
                ]);
            endif;
            return response()->json(['message' => 'Result successfully updated.'], 200);
        else: 
            return response()->json(['message' => 'Error found!'], 422);
        endif;
    }

    public function documentDownload(Request $request){ 
        $phase = $request->phase;
        $row_id = $request->id;

        $theDoc = ($phase == 'Applicant' ? ApplicantDocument::find($row_id) : StudentDocument::find($row_id));
        $applicant_id = ($phase == 'Applicant' ? $theDoc->applicant_id : $theDoc->student->applicant_id);
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/applicants/'.$applicant_id.'/'.$theDoc->current_file_name, now()->addMinutes(5));
        return response()->json(['res' => $tmpURL], 200);
    }
}
