<?php

namespace App\Http\Controllers\Staff;

use App\Exports\StudentEmailIdTaskExport;
use App\Http\Controllers\Controller;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\Applicant;
use App\Models\ApplicantTask;
use App\Models\ComonSmtp;
use App\Models\LetterSet;
use App\Models\ProcessList;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentContact;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Models\StudentUser;
use App\Models\TaskList;
use App\Models\TaskListUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $phase = (isset($request->phase) && !empty($request->phase) ? $request->phase : 'Live');

        $task = TaskList::find($task_id);

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
                    $theApplicantTask = ApplicantTask::where('task_list_id', $task_id)->where('applicant_id', $list->id)->where('status', 'Pending')->orderBy('id', 'DESC')->get()->first();
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
                        'url' => route('admission.process', $list->id),
                        'task_id' => $task_id,
                        'task_created' => (isset($theApplicantTask->created_at) && !empty($theApplicantTask->created_at) ? date('jS M, Y', strtotime($theApplicantTask->created_at)) : ''),
                        'task_status' => (isset($theApplicantTask->status) && !empty($theApplicantTask->status) ? $theApplicantTask->status : ''),
                        'ids' => '',
                        'phase' => $phase
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
                    $theStudentTask = StudentTask::where('task_list_id', $task_id)->where('student_id', $list->id)->where('status', 'Pending')->orderBy('id', 'DESC')->get()->first();
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
                        'url' => route('student.process', $list->id),
                        'task_id' => $task_id,
                        'task_created' => (isset($theStudentTask->created_at) && !empty($theStudentTask->created_at) ? date('jS M, Y', strtotime($theStudentTask->created_at)) : ''),
                        'task_status' => (isset($theStudentTask->status) && !empty($theStudentTask->status) ? $theStudentTask->status : ''),
                        'ids' => $list->id,
                        'phase' => $phase
                    ];
                    $i++;
                endforeach;
            endif;
        endif;

        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function allTasks(){
        return view('pages.users.staffs.task.all-task', [
            'title' => 'User Task Manager - LCC Data Future Managment',
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
                        $aplPendingTask = ApplicantTask::where('task_list_id', $atsk->id)->where('status', 'Pending')->get();
                        $stdPendingTask = StudentTask::where('task_list_id', $atsk->id)->where('status', 'Pending')->get();
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
                    
                    if($studentUserEmail != $orgEmail):
                        $studentContact = $studentContactOld = StudentContact::find($student->contact->id);
                        $studentContact->fill([
                            'personal_email' => $studentUserEmail, 
                            'personal_email_verification' => $studentUserEmailVerifiedA
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
                        $theCollection[$row][] = $student->first_name;
                        $theCollection[$row][] = $student->last_name;
                        $theCollection[$row][] = $orgEmail;
                        $theCollection[$row][] = $newPassword;

                        $row++;
                    endif;
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
                $mailTo[] = $studentUserEmail;
                if(isset($student->contact->personal_email) && !empty($student->contact->personal_email)):
                    $mailTo[] = $student->contact->personal_email;
                endif;
                $mailTo[] = 'limon@churchill.ac';

                $theStudentTask = StudentTask::where('task_list_id', 5)->where('student_id', $student->id)->where('status', 'Pending')->get()->first();
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
        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>ID CARD</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{background-image: url(https://datafuture2.lcc.ac.uk/limon/id_card_bg.jpg); background-repeat: no-repeat; background-position: center top; background-color: #1a2c44;}
                                @page{margin: 0;}
                            </style>';
            $PDFHTML .= '</head>';
            $PDFHTML .= '<body>';
            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = 'Student_ID_Card.pdf';
        $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper(array(0, 0, 240, 324), 'portrait')
            ->setWarnings(false);
        return $pdf->download($fileName);
    }
}
