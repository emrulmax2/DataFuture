<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdmissionContactDetailsRequest;
use App\Http\Requests\AdmissionCourseDetailsRequest;
use App\Http\Requests\AdmissionKinDetailsRequest;
use App\Http\Requests\AdmissionPersonalDetailsRequest;
use App\Http\Requests\ApplicantNoteRequest;
use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantContact;
use App\Models\ApplicantDisability;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\Semester;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ApplicantQualification;
use App\Models\ApplicantEmployment;
use App\Models\ApplicantKin;
use App\Models\ApplicantOtherDetail;
use App\Models\ApplicantProposedCourse;
use App\Models\ApplicantTemporaryEmail;
use App\Models\AwardingBody;
use App\Models\Country;
use App\Models\CourseCreationInstance;
use App\Models\Disability;
use App\Models\Ethnicity;
use App\Models\KinsRelation;
use App\Models\Title;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use Mail; 
use Hash;
use App\Mail\ApplicantTempEmailVerification;
use App\Models\ApplicantDocument;
use App\Models\ApplicantDocumentList;
use App\Models\ApplicantNote;
use App\Models\ApplicantTask;
use App\Models\ApplicantTaskDocument;
use App\Models\ApplicantTaskLog;
use App\Models\DocumentSettings;
use App\Models\ProcessList;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Mail as FacadesMail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class AdmissionController extends Controller
{
    public function index(){
        return view('pages.students.admission.index', [
            'title' => 'Admission Management - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => 'javascript:void(0);']
            ],
            'semesters' => Semester::all(),
            'courses' => Course::all(),
            'statuses' => Status::where('type', 'Applicant')->get(),
        ]);
    }

    public function list(Request $request){
        $semesters = (isset($request->semesters) && !empty($request->semesters) ? $request->semesters : []);
        $courses = (isset($request->courses) && !empty($request->courses) ? $request->courses : []);
        $statuses = (isset($request->statuses) && !empty($request->statuses) ? $request->statuses : []);
        $refno = (isset($request->refno) && !empty($request->refno) ? $request->refno : '');
        $firstname = (isset($request->firstname) && !empty($request->firstname) ? $request->firstname : '');
        $lastname = (isset($request->lastname) && !empty($request->lastname) ? $request->lastname : '');
        $dob = (isset($request->dob) && !empty($request->dob) ? date('Y-m-d', strtotime($request->dob)) : '');

        $courseCreationId = [];
        if(!empty($courses)):
            $courseCreations = CourseCreation::whereIn('course_id', $courses)->get();
            if(!$courseCreations->isEmpty()):
                foreach($courseCreations as $cc):
                    $courseCreationId[] = $cc->id;
                endforeach;
            else:
                $courseCreationId[1] = '0';
            endif;
        endif;

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);

        $query = Applicant::orderByRaw(implode(',', $sorts))->whereNotNull('submission_date');
        if(!empty($refno)): $query->where('application_no', $refno); endif;
        if(!empty($firstname)): $query->where('first_name', 'LIKE', '%'.$firstname.'%'); endif;
        if(!empty($lastname)): $query->where('last_name', 'LIKE', '%'.$lastname.'%'); endif;
        if(!empty($dob)): $query->where('date_of_birth', $dob); endif;
        if(!empty($statuses)): $query->whereIn('status_id', $statuses); endif;
        if(!empty($semesters) || !empty($courseCreationId)):
            $query->whereHas('course', function($qs) use($semesters, $courses, $courseCreationId){
                if(!empty($semesters)): $qs->whereIn('semester_id', $semesters); endif;
                if(!empty($courses) && !empty($courseCreationId)): $qs->whereIn('course_creation_id', $courseCreationId); endif;
            });
        endif;

        $total_rows = $query->count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
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
                    'date_of_birth'=> $list->date_of_birth,
                    'course'=> (isset($list->course->creation->course->name) ? $list->course->creation->course->name : ''),
                    'semester'=> (isset($list->course->semester->name) ? $list->course->semester->name : ''),
                    'gender'=> $list->gender,
                    'status_id'=> (isset($list->status->name) ? $list->status->name : ''),
                    'url' => route('admission.show', $list->id),
                    'ccid' => implode(',', $courses).' - '.implode(',', $courseCreationId)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function show($applicantId){
        return view('pages.students.admission.show', [
            'title' => 'Admission Management - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'titles' => Title::all(),
            'country' => Country::all(),
            'ethnicity' => Ethnicity::all(),
            'disability' => Disability::all(),
            'relations' => KinsRelation::all(),
            'bodies' => AwardingBody::all(),
            'users' => User::all(),
            'instance' => CourseCreationInstance::all(),
            'tempEmail' => ApplicantTemporaryEmail::where('applicant_id', $applicantId)->orderBy('id', 'desc')->first(),
            'documents' => DocumentSettings::where('admission', '1')->orderBy('id', 'ASC')->get()
        ]);
    }


    public function updatePersonalDetails(AdmissionPersonalDetailsRequest $request){
        $applicant_id = $request->id;
        $applicantOldRow = Applicant::find($applicant_id);
        $otherDetailsOldRow = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();

        $ethnicity_id = $request->ethnicity_id;
        $disability_status = (isset($request->disability_status) && $request->disability_status > 0 ? $request->disability_status : 0);
        $disability_id = ($disability_status == 1 && isset($request->disability_id) && !empty($request->disability_id) ? $request->disability_id : []);
        $disabilty_allowance = ($disability_status == 1 && !empty($disability_id) && (isset($request->disabilty_allowance) && $request->disabilty_allowance > 0) ? $request->disabilty_allowance : 0);

        $request->request->remove('ethnicity_id');
        $request->request->remove('disability_status');
        $request->request->remove('disability_id');
        $request->request->remove('disabilty_allowance');

        $applicant = Applicant::find($applicant_id);
        $applicant->fill($request->input());
        $changes = $applicant->getDirty();
        $applicant->save();

        if($applicant->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicants';
                $data['field_name'] = $field;
                $data['field_value'] = $applicantOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;
        $request->request->remove('id');

        $otherDetails = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();
        $otherDetails->fill([
            'ethnicity_id' => $ethnicity_id,
            'disability_status' => $disability_status,
            'disability_status' => $disability_status,
            'disabilty_allowance' => $disabilty_allowance,
        ]);
        $changes = $otherDetails->getDirty();
        $otherDetails->save();

        if($otherDetails->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_other_details';
                $data['field_name'] = $field;
                $data['field_value'] = $otherDetailsOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;
        $applicantDisablities = ApplicantDisability::where('applicant_id', $applicant_id)->get();
        $existingIds = [];
        if(!empty($applicantDisablities)):
            foreach($applicantDisablities as $dis):
                $existingIds[] = $dis->disabilitiy_id;
            endforeach;
        endif;
        if($disability_status == 1 && !empty($disability_id)):
            $applicantDisablityDel = ApplicantDisability::where('applicant_id', $applicant_id)->forceDelete();
            foreach($disability_id as $disabilityID):
                $applicantDisabilitiesCr = ApplicantDisability::create([
                    'applicant_id' => $applicant_id,
                    'disabilitiy_id' => $disabilityID,
                    'created_by' => auth()->user()->id,
                ]);
            endforeach;

            $data = [];
            $data['applicant_id'] = $applicant_id;
            $data['table'] = 'applicant_disabilities';
            $data['field_name'] = 'disabilitiy_id';
            $data['field_value'] = implode(',', $existingIds);
            $data['field_new_value'] = implode(',', $disability_id);
            $data['created_by'] = auth()->user()->id;

            ApplicantArchive::create($data);
        else:
            if(!empty($existingIds)):
                $applicantDisablityDel = ApplicantDisability::where('applicant_id', $applicant_id)->forceDelete();
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_disabilities';
                $data['field_name'] = 'disabilitiy_id';
                $data['field_value'] = implode(',', $existingIds);
                $data['field_new_value'] = implode(',', $disability_id);
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endif;
        endif;


        return response()->json(['msg' => 'Personal Data Successfully Updated.'], 200);
    }

    public function updateContactDetails(AdmissionContactDetailsRequest $request){
        $applicant_id = $request->applicant_id;
        $applicant = Applicant::find($applicant_id);
        $contactOldRow = ApplicantContact::find($request->id);
        $email = $request->email;

        $request->request->remove('email');

        $contact = ApplicantContact::find($request->id);
        $contact->fill([
            'home' => $request->phone,
            'mobile' => $request->mobile,
            'address_line_1' => (isset($request->applicant_address_line_1) && !empty($request->applicant_address_line_1) ? $request->applicant_address_line_1 : null),
            'address_line_2' => (isset($request->applicant_address_line_2) && !empty($request->applicant_address_line_2) ? $request->applicant_address_line_2 : null),
            'state' => (isset($request->applicant_address_state) && !empty($request->applicant_address_state) ? $request->applicant_address_state : null),
            'post_code' => (isset($request->applicant_address_postal_zip_code) && !empty($request->applicant_address_postal_zip_code) ? $request->applicant_address_postal_zip_code : null),
            'city' => (isset($request->applicant_address_city) && !empty($request->applicant_address_city) ? $request->applicant_address_city : null),
            'country' => (isset($request->applicant_address_country) && !empty($request->applicant_address_country) ? $request->applicant_address_country : null),
            'updated_by' => auth()->user()->id
        ]);
        $changes = $contact->getDirty();
        $contact->save();

        if($applicant->users->email != $email):
            $tempEmail = ApplicantTemporaryEmail::create([
                'applicant_id' => $applicant_id,
                'email' => $email,
                'status' => 'Pending',
                'created_by' => auth()->user()->id
            ]);
            if($tempEmail):
                $applicantName = $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name;
                $url = route('varify.temp.email', $applicant_id);
                Mail::to($email)->send(new ApplicantTempEmailVerification($applicantName, $applicant->users->email, $email, $url));
            endif;
        endif;

        if($contact->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_contacts';
                $data['field_name'] = $field;
                $data['field_value'] = $contactOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Contact Details Successfully Updated.'], 200);
    }

    public function updateKinDetails(AdmissionKinDetailsRequest $request){
        $applicant_id = $request->applicant_id;
        $kinOldRow = ApplicantKin::find($request->id);

        $kin = ApplicantKin::find($request->id);
        $kin->fill([
            'name' => $request->name,
            'kins_relation_id' => $request->kins_relation_id,
            'mobile' => $request->kins_mobile,
            'email' => (isset($request->kins_email) && !empty($request->kins_email) ? $request->kins_email : null),
            'address_line_1' => (isset($request->kin_address_line_1) && !empty($request->kin_address_line_1) ? $request->kin_address_line_1 : null),
            'address_line_2' => (isset($request->kin_address_line_2) && !empty($request->kin_address_line_2) ? $request->kin_address_line_2 : null),
            'state' => (isset($request->kin_address_state) && !empty($request->kin_address_state) ? $request->kin_address_state : null),
            'post_code' => (isset($request->kin_address_postal_zip_code) && !empty($request->kin_address_postal_zip_code) ? $request->kin_address_postal_zip_code : null),
            'city' => (isset($request->kin_address_city) && !empty($request->kin_address_city) ? $request->kin_address_city : null),
            'country' => (isset($request->kin_address_country) && !empty($request->kin_address_country) ? $request->kin_address_country : null),
            'updated_by' => auth()->user()->id
        ]);
        $changes = $kin->getDirty();
        $kin->save();

        if($kin->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_kin';
                $data['field_name'] = $field;
                $data['field_value'] = $kinOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Next of Kin Details Successfully Updated.'], 200);
    }

    public function updateCourseAndProgrammeDetails(AdmissionCourseDetailsRequest $request){
        $applicant_id = $request->applicant_id;
        $ProposedCourseOldRow = ApplicantProposedCourse::find($request->id);

        $course_creation_id = $request->course_creation_id;
        $courseCreation = CourseCreation::find($course_creation_id);
        $studentLoan = $request->student_loan;
        $studentFinanceEngland = ($studentLoan == 'Student Loan' && isset($request->student_finance_england) && $request->student_finance_england > 0 ? $request->student_finance_england : null);
        $appliedReceivedFund = ($studentLoan == 'Student Loan' && isset($request->applied_received_fund) && $request->applied_received_fund > 0 ? $request->applied_received_fund : null);
        $fundReceipt = ($studentFinanceEngland == 1 && isset($request->fund_receipt) && $request->fund_receipt > 0 ? $request->fund_receipt : null);

        $proposedCourse = ApplicantProposedCourse::find($request->id);
        $proposedCourse->fill([
            'course_creation_id' => $course_creation_id,
            'semester_id' => $courseCreation->semester_id,
            'student_loan' => $studentLoan,
            'student_finance_england' => $studentFinanceEngland,
            'applied_received_fund' => $appliedReceivedFund,
            'fund_receipt' => $fundReceipt,
            'other_funding' => ($studentLoan == 'Others' && isset($request->other_funding) && !empty($request->other_funding) ? $request->other_funding : null),
            'full_time' => (isset($request->full_time) && $request->full_time > 0 ? $request->full_time : 0),
            'updated_by' => auth()->user()->id
        ]);
        $changes = $proposedCourse->getDirty();
        $proposedCourse->save();

        if($proposedCourse->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_proposed_courses';
                $data['field_name'] = $field;
                $data['field_value'] = $ProposedCourseOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Course & Programme Details Successfully Updated.'], 200);
    }

    public function updateQualificationStatus(Request $request){
        $applicant_id = $request->applicant;
        $status = $request->status;
        $otherDetailsOldRow = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();

        
        $otherDetails = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();
        $otherDetails->fill([
            'is_edication_qualification' => $status
        ]);
        $changes = $otherDetails->getDirty();
        $otherDetails->save();

        if($otherDetails->wasChanged() && !empty($changes)):
            if($status == 0){
                $eduQual = ApplicantQualification::where('applicant_id', $applicant_id)->delete();
            }
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_other_details';
                $data['field_name'] = $field;
                $data['field_value'] = $otherDetailsOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Education qualification status Successfully Updated.'], 200);
    }

    public function updateEmploymentStatus(Request $request){
        $applicant_id = $request->applicant;
        $status = $request->status;
        $otherDetailsOldRow = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();

        $otherDetails = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();
        $otherDetails->fill([
            'employment_status' => $status
        ]);
        $changes = $otherDetails->getDirty();
        $otherDetails->save();

        if($otherDetails->wasChanged() && !empty($changes)):
            if($status == 'Unemployed' || $status == 'Contractor' || $status == 'Consultant' || $status == 'Office Holder'){
                $eduQual = ApplicantEmployment::where('applicant_id', $applicant_id)->delete();
            }
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_other_details';
                $data['field_name'] = $field;
                $data['field_value'] = $otherDetailsOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Education qualification status Successfully Updated.'], 200);
    }

    public function admissionProcess($applicantId){
        return view('pages.students.admission.process', [
            'title' => 'Admission Management - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => route('admission.show', $applicantId)],
                ['label' => 'Process', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'process' => ProcessList::where('phase', 'Applicant')->first(),
            'existingTask' => ApplicantTask::where('applicant_id', $applicantId)->pluck('task_list_id')->toArray(),
            'applicantPendingTask' => ApplicantTask::where('applicant_id', $applicantId)->where('status', 'Pending')->get(),
            'applicantCompletedTask' => ApplicantTask::where('applicant_id', $applicantId)->where('status', 'Completed')->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get()
        ]);
    }

    public function admissionStoreProcessTask(Request $request){
        $task_list_ids = (isset($request->task_list_ids) && !empty($request->task_list_ids) ? $request->task_list_ids : []);
        $applicant_id = (isset($request->applicant_id) && $request->applicant_id ? $request->applicant_id : 0);
        if(!empty($task_list_ids) && $applicant_id > 0):
            $existingTaskIds = ApplicantTask::where('applicant_id', $applicant_id)->pluck('task_list_id')->toArray();
            $existingDiff = array_diff($existingTaskIds, $task_list_ids);
            $taskListDiff = array_diff($task_list_ids, $existingTaskIds);

            $numInsert = 0;
            $numDelete = 0;
            if(!empty($taskListDiff)):
                foreach($taskListDiff as $task):
                    $withTrashed = ApplicantTask::where('applicant_id', $applicant_id)->where('task_list_id', $task)->onlyTrashed()->get();
                    if(!empty($withTrashed) && $withTrashed->count() > 0):
                        $restoreTask = ApplicantTask::where('applicant_id', $applicant_id)->where('task_list_id', $task)->withTrashed()->restore();
                    else:
                        $data = [];
                        $data['applicant_id'] = $applicant_id;
                        $data['task_list_id'] = $task;
                        $data['status'] = 'Pending';
                        $data['created_by'] = auth()->user()->id;
                        $insertTask = ApplicantTask::create($data);
                    endif;
                    $numInsert += 1;
                endforeach;
            endif;
            if(!empty($existingDiff)):
                foreach($existingDiff as $task):
                    $deleteTask = ApplicantTask::where('applicant_id', $applicant_id)->where('task_list_id', $task)->delete();
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


    public function admissionUploadTaskDocument(Request $request){
        $applicant_id = $request->applicant_id;
        $applicant_task_id = $request->applicant_task_id;
        $applicantTask = ApplicantTask::find($applicant_task_id);
        $taskName = (isset($applicantTask->task->name) && !empty($applicantTask->task->name) ? $applicantTask->task->name : '');

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/applicants/'.$applicant_id.'/', $imageName);
        $data = [];
        $data['applicant_id'] = $applicant_id;
        $data['hard_copy_check'] = 0;
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = asset('storage/applicants/'.$applicant_id.'/'.$imageName);
        $data['display_file_name'] = (!empty($taskName) ? $taskName : $imageName);
        $data['current_file_name'] = $imageName;
        $data['created_by'] = auth()->user()->id;
        $applicantDoc = ApplicantDocument::create($data);
        if($applicantDoc):
            $applicantTaskDoc = ApplicantTaskDocument::create([
                'applicant_task_id' => $applicant_task_id,
                'applicant_document_id' => $applicantDoc->id,
                'created_by' => auth()->user()->id
            ]);

            $applicantTaskLog = ApplicantTaskLog::create([
                'applicant_tasks_id' => $applicant_task_id,
                'actions' => 'Document',
                'field_name' => '',
                'prev_field_value' => '',
                'current_field_value' => asset('storage/applicants/'.$applicant_id.'/'.$imageName),
                'created_by' => auth()->user()->id
            ]);
        endif;

        return response()->json(['message' => 'Document successfully uploaded.'], 200);
    }

    public function admissionDeleteTask(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        $data = ApplicantTask::where('id', $recordid)->where('applicant_id', $applicant)->delete();
        $applicantTaskLog = ApplicantTaskLog::create([
            'applicant_tasks_id' => $recordid,
            'actions' => 'Delete',
            'field_name' => '',
            'prev_field_value' => '',
            'current_field_value' => 'Item Deleted',
            'created_by' => auth()->user()->id
        ]);
        return response()->json(['message' => 'Data deleted'], 200);
    }

    public function admissionCompletedTask(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;


        $applicantTask = ApplicantTask::where('id', $recordid)->where('applicant_id', $applicant)->update(['status' => 'Completed', 'updated_by' => auth()->user()->id]);
        $applicantTaskLog = ApplicantTaskLog::create([
            'applicant_tasks_id' => $recordid,
            'actions' => 'Status Changed',
            'field_name' => 'status',
            'prev_field_value' => 'Pending',
            'current_field_value' => 'Completed',
            'created_by' => auth()->user()->id
        ]);
        return response()->json(['message' => 'Data deleted'], 200);
    }

    public function admissionPendingTask(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;


        $applicantTask = ApplicantTask::where('id', $recordid)->where('applicant_id', $applicant)->update(['status' => 'Pending', 'updated_by' => auth()->user()->id]);
        $applicantTaskLog = ApplicantTaskLog::create([
            'applicant_tasks_id' => $recordid,
            'actions' => 'Status Changed',
            'field_name' => 'status',
            'prev_field_value' => 'Completed',
            'current_field_value' => 'Pending',
            'created_by' => auth()->user()->id
        ]);
        return response()->json(['message' => 'Data updated'], 200);
    }

    public function admissionArchivedProcessList(Request $request){
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'asc']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ApplicantTask::orderByRaw(implode(',', $sorts))->onlyTrashed();

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

    public function admissionResotreTask(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        $data = ApplicantTask::where('id', $recordid)->where('applicant_id', $applicant)->withTrashed()->restore();
        $applicantTaskLog = ApplicantTaskLog::create([
            'applicant_tasks_id' => $recordid,
            'actions' => 'Restore',
            'field_name' => '',
            'prev_field_value' => '',
            'current_field_value' => 'Item Restored',
            'created_by' => auth()->user()->id
        ]);
        return response()->json(['message' => 'Data Restored'], 200);
    }

    public function admissionShowTaskStatuses(Request $request){
        $applicantTaskId = $request->taskId;
        $applicantTask = ApplicantTask::find($applicantTaskId);
        $taskStatuses = $applicantTask->task->statuses;

        $statusOpt = [];
        if(!empty($taskStatuses)):
            $html = '<label for="upload" class="form-label">Task Result <span class="text-danger">*</span></label>';
            foreach($taskStatuses as $ts):
                $taskStatus = TaskStatus::find($ts->task_status_id);
                $html .= '<div class="form-check mt-2">';
                    $html .= '<input '.($applicantTask->task_status_id == $taskStatus->id ? 'Checked' : '').' id="outc_task-status-'.$taskStatus->id.'" class="form-check-input resultStatus" type="radio" name="result_statuses" value="'.$taskStatus->id.'">';
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

    public function admissionTaskResultUpdate(Request $request){
        $applicant_id = $request->applicant_id;
        $applicant_task_id = $request->applicant_task_id;
        $result_statuses = (isset($request->result_statuses) ? $request->result_statuses : '');
        $applicantTaskOld = ApplicantTask::where('applicant_id', $applicant_id)->where('id', $applicant_task_id)->get()->first();

        if($result_statuses > 0):
            $data = [];
            $data['task_status_id'] = $result_statuses;
            $data['updated_by'] = auth()->user()->id;
            $applicantTask = ApplicantTask::where('applicant_id', $applicant_id)->where('id', $applicant_task_id)->update($data);
            $applicantTaskLog = ApplicantTaskLog::create([
                'applicant_tasks_id' => $applicant_task_id,
                'actions' => 'Task Status',
                'field_name' => 'task_status_id',
                'prev_field_value' => $applicantTaskOld->task_status_id,
                'current_field_value' => $result_statuses,
                'created_by' => auth()->user()->id
            ]);
            return response()->json(['message' => 'Result successfully updated.'], 200);
        else: 
            return response()->json(['message' => 'Error found!'], 422);
        endif;
    }

    public function admissionTaskLogList(Request $request){
        $applicantTaskId = $request->applicantTaskId;
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'desc']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ApplicantTaskLog::where('applicant_tasks_id', $applicantTaskId)->orderByRaw(implode(',', $sorts));

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
                $fieldName = '';
                $prevValue = '';
                $newValue = '';
                if($list->actions == 'Document'):
                    $fieldName = '';
                    $prevValue = '';
                    $newValue = '<a href="'.$list->current_field_value.'" download traget="_blank" class="text-success" style="white-space: normal; word-break: break-all;">'.$list->current_field_value.'</a>';
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

    public function admissionUploads($applicantId){
        return view('pages.students.admission.uploads', [
            'title' => 'Admission Management - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => route('admission.show', $applicantId)],
                ['label' => 'Uploads', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'docSettings' => DocumentSettings::where('admission', '1')->get()
        ]);
    }

    public function AdmissionUploadDocuments(Request $request){
        $applicant_id = $request->applicant_id;
        $document_setting_id = $request->document_setting_id;
        $documentSetting = DocumentSettings::find($document_setting_id);
        $hard_copy_check = $request->hard_copy_check;

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/applicants/'.$applicant_id.'/', $imageName);
        $data = [];
        $data['applicant_id'] = $applicant_id;
        $data['document_setting_id'] = ($document_setting_id > 0 ? $document_setting_id : 0);
        $data['hard_copy_check'] = ($hard_copy_check > 0 ? $hard_copy_check : 0);
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = asset('storage/applicants/'.$applicant_id.'/'.$imageName);
        $data['display_file_name'] = (isset($documentSetting->name) && !empty($documentSetting->name) ? $documentSetting->name : $imageName);
        $data['current_file_name'] = $imageName;
        $data['created_by'] = auth()->user()->id;
        $applicantDoc = ApplicantDocument::create($data);

        return response()->json(['message' => 'Document successfully uploaded.'], 200);
    }

    public function AdmissionUploadList(Request $request){
        $applicantId = (isset($request->applicantId) && !empty($request->applicantId) ? $request->applicantId : 0);
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);

        $query = ApplicantDocument::orderByRaw(implode(',', $sorts))->where('applicant_id', $applicantId);
        if(!empty($queryStr)):
            $query->where('display_file_name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'display_file_name' => (!empty($list->display_file_name) ? $list->display_file_name : 'Unknown'),
                    'hard_copy_check' => $list->hard_copy_check,
                    'doc_type' => strtoupper($list->doc_type),
                    'current_file_name'=> $list->current_file_name,
                    'url' => asset('storage/applicants/'.$list->applicant_id.'/'.$list->current_file_name),
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function AdmissionUploadDestroy(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $data = ApplicantDocument::find($recordid)->delete();
        return response()->json($data);
    }

    public function AdmissionUploadRestore(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $data = ApplicantDocument::where('id', $recordid)->withTrashed()->restore();

        response()->json($data);
    }

    public function admissionNotes($applicantId){
        return view('pages.students.admission.notes', [
            'title' => 'Admission Management - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => route('admission.show', $applicantId)],
                ['label' => 'Notes', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($applicantId),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get()
        ]);
    }

    public function admissionStoreNotes(ApplicantNoteRequest $request){
        $applicant_id = $request->applicant_id;
        $note = ApplicantNote::create([
            'applicant_id'=> $applicant_id,
            'note'=> $request->content,
            'phase'=> 'Admission',
            'created_by' => auth()->user()->id
        ]);
        if($note):
            if($request->hasFile('document')):
                $document = $request->file('document');
                $documentName = time().'_'.$document->getClientOriginalName();
                $path = $document->storeAs('public/applicants/'.$applicant_id.'/', $documentName);

                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['hard_copy_check'] = 0;
                $data['doc_type'] = $document->getClientOriginalExtension();
                $data['path'] = asset('storage/applicants/'.$applicant_id.'/'.$documentName);
                $data['display_file_name'] = $documentName;
                $data['current_file_name'] = $documentName;
                $data['created_by'] = auth()->user()->id;
                $applicantDocument = ApplicantDocument::create($data);

                if($applicantDocument):
                    $noteUpdate = ApplicantNote::where('id', $note->id)->update([
                        'applicant_document_id' => $applicantDocument->id
                    ]);
                endif;
            endif;
            return response()->json(['message' => 'Applicant Note successfully created'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function admissionNotesList(Request $request){
        $applicantId = (isset($request->applicantId) && !empty($request->applicantId) ? $request->applicantId : 0);
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);

        $query = ApplicantNote::orderByRaw(implode(',', $sorts))->where('applicant_id', $applicantId);
        if(!empty($queryStr)):
            $query->where('note','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $docURL = '';
                if(isset($list->applicant_document_id) && isset($list->document)):
                    $docURL = (isset($list->document->current_file_name) && !empty($list->document->current_file_name) ? asset('storage/applicants/'.$list->applicant_id.'/'.$list->document->current_file_name) : '');
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'note' => (strlen(strip_tags($list->note)) > 40 ? substr(strip_tags($list->note), 0, 40).'...' : strip_tags($list->note)),
                    'url' => $docURL,
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function admissionShowNote(Request $request){
        $noteId = $request->noteId;
        $note = ApplicantNote::find($noteId);
        $html = '';
        $btns = '';
        if(!empty($note) && !empty($note->note)):
            $html .= '<div>';
                $html .= $note->note;
            $html .= '</div>';
            if(isset($note->applicant_document_id) && isset($note->document)):
                $docURL = (isset($note->document->current_file_name) && !empty($note->document->current_file_name) ? asset('storage/applicants/'.$note->applicant_id.'/'.$note->document->current_file_name) : '');
                if(!empty($docURL)):
                    $btns .= '<a download href="'.$docURL.'" class="btn btn-primary w-auto inline-flex"><i data-lucide="cloud-lightning" class="w-4 h-4 mr-2"></i>Download Attachment</a>';
                endif;
            endif;
        else:
            $html .= '<div class="alert alert-danger-soft show flex items-start mb-2" role="alert">
                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! No data foudn for this note.
                    </div>';
        endif;

        return response()->json(['message' => $html, 'btns' => $btns], 200);
    }

    public function admissionGetNote(Request $request){
        $noteId = $request->noteId;
        $theNote = ApplicantNote::find($noteId);
        $docURL = '';
        if(isset($theNote->applicant_document_id) && isset($theNote->document)):
            $docURL = (isset($theNote->document->current_file_name) && !empty($theNote->document->current_file_name) ? asset('storage/applicants/'.$theNote->applicant_id.'/'.$theNote->document->current_file_name) : '');
        endif;
        $theNote['docURL'] = $docURL;

        return response()->json(['res' => $theNote], 200);
    }

    public function admissionUpdateNote(ApplicantNoteRequest $request){
        $applicant_id = $request->applicant_id;
        $noteId = $request->id;
        $oleNote = ApplicantNote::find($noteId);
        $applicantDocumentId = (isset($oleNote->applicant_document_id) && $oleNote->applicant_document_id > 0 ? $oleNote->applicant_document_id : 0);

        $note = ApplicantNote::where('id', $noteId)->where('applicant_id', $applicant_id)->Update([
            'applicant_id'=> $applicant_id,
            'note'=> $request->content,
            'phase'=> 'Admission',
            'updated_by' => auth()->user()->id
        ]);
        if($request->hasFile('document')):
            if($applicantDocumentId > 0 && isset($oleNote->document->current_file_name) && !empty($oleNote->document->current_file_name)):
                if (Storage::disk('local')->exists('public/applicants/'.$applicant_id.'/'.$oleNote->document->current_file_name)):
                    Storage::delete('public/applicants/'.$applicant_id.'/'.$oleNote->document->current_file_name);
                endif;

                $ad = ApplicantDocument::where('id', $applicantDocumentId)->forceDelete();
            endif;

            $document = $request->file('document');
            $documentName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/applicants/'.$applicant_id.'/', $documentName);

            $data = [];
            $data['applicant_id'] = $applicant_id;
            $data['hard_copy_check'] = 0;
            $data['doc_type'] = $document->getClientOriginalExtension();
            $data['path'] = asset('storage/applicants/'.$applicant_id.'/'.$documentName);
            $data['display_file_name'] = $documentName;
            $data['current_file_name'] = $documentName;
            $data['created_by'] = auth()->user()->id;
            $applicantDocument = ApplicantDocument::create($data);

            if($applicantDocument):
                $noteUpdate = ApplicantNote::where('id', $noteId)->update([
                    'applicant_document_id' => $applicantDocument->id
                ]);
            endif;
        endif;
        return response()->json(['message' => 'Applicant Note successfully updated'], 200);
    }

    public function admissionDestroyNote(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $applicantNote = ApplicantNote::find($recordid);
        $applicantDocumentID = (isset($applicantNote->applicant_document_id) && $applicantNote->applicant_document_id > 0 ? $applicantNote->applicant_document_id : 0);
        ApplicantNote::find($recordid)->delete();

        if($applicantDocumentID > 0):
            ApplicantDocument::find($applicantDocumentID)->delete();
        endif;

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function admissionRestoreNote(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $data = ApplicantNote::where('id', $recordid)->withTrashed()->restore();
        $applicantNote = ApplicantNote::find($recordid);
        $applicantDocumentID = (isset($applicantNote->applicant_document_id) && $applicantNote->applicant_document_id > 0 ? $applicantNote->applicant_document_id : 0);
        if($applicantDocumentID > 0):
            ApplicantDocument::where('id', $applicantDocumentID)->withTrashed()->restore();
        endif;
        return response()->json(['message' => 'Successfully restored'], 200);
    }
}
