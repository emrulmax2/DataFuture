<?php

namespace App\Http\Controllers\NewsUpdates;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsAndEventsStoreRequest;
use App\Models\AcademicYear;
use App\Models\Assign;
use App\Models\Course;
use App\Models\Group;
use App\Models\NewsAndEvent;
use App\Models\NewsAndEventDocument;
use App\Models\NewsAndEventStudent;
use App\Models\Plan;
use App\Models\Semester;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use App\Models\StudentProposedCourse;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class NewsUpdateController extends Controller
{
    public function index(){
        return view('pages.news-update.index', [
            'title' => 'News & Updates - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'News & Updates', 'href' => 'javascript:void(0);']
            ],
        ]);
    }

    public function create(){
        $semesters = Cache::get('semesters', function () {
            $semesters = Semester::all()->sortByDesc("name");
            $semesterData = [];
            foreach ($semesters as $semester):
                $studentProposedCourse = StudentProposedCourse::where('semester_id',$semester->id)->get()->first();
                if(isset($studentProposedCourse->id))
                    $semesterData[] = $semester;
            endforeach;
            return $semesterData;
        });
        $courses = Cache::get('courses', function () {
            return Course::all();
        });
        $statuses = Cache::get('statuses', function () {
            return Status::where('type', 'Student')->get();
        });

        return view('pages.news-update.create', [
            'title' => 'News & Updates - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'News & Updates', 'href' => route('news.updates')],
                ['label' => 'create', 'href' => 'javascript:void(0);']
            ],

            'semesters' => $semesters,
            'courses' => $courses,
            'allStatuses' => $statuses,
            'academicYear' => AcademicYear::all()->sortByDesc('from_date'),
            'terms' => TermDeclaration::all()->sortByDesc('id'),
            'groups' => Group::all(),
        ]);
    }

    public function store(NewsAndEventsStoreRequest $request){
        $title = $request->title;
        $content = $request->content;
        $for_all = (isset($request->for_all_students) && $request->for_all_students > 0 ? $request->for_all_students : 0);
        $student_ids = ($for_all == 0 && isset($request->students) && !empty($request->students) ? $request->students : []);

        $events = NewsAndEvent::create([
            'title' => $title,
            'content' => $content,
            'fol_all' => $for_all,
            'active' => 1,

            'created_by' => auth()->user()->id,
        ]);

        if($events->id):
            if($for_all == 0 && !empty($student_ids)):
                foreach($student_ids as $id):
                    NewsAndEventStudent::create([
                        'news_and_event_id' => $events->id,
                        'student_id' => $id,
                    ]);
                endforeach;
            endif;

            if($request->hasFile('documents')):
                $documents = $request->file('documents');
                foreach($documents as $document):
                    $documentName = time().'_'.$document->getClientOriginalName();
                    $path = $document->storeAs('public/news_events/'.$events->id, $documentName, 's3');

                    NewsAndEventDocument::create([
                        'news_and_event_id' => $events->id,
                        'hard_copy_check' => 0,
                        'doc_type' => $document->getClientOriginalExtension(),
                        'disk_type' => 's3',
                        'path' => Storage::disk('s3')->url($path),
                        'display_file_name' => $documentName,
                        'current_file_name' => $documentName,

                        'created_by' => auth()->user()->id
                    ]);
                endforeach;
            endif;

            return response()->json(['message' => 'News & Event successfully created.', 'red' => route('news.updates')], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try again later.', 'red' => ''], 304);
        endif;
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = NewsAndEvent::with('documents')->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where(function($q) use($queryStr){
                $q->where('title','LIKE','%'.$queryStr.'%')->orWhere('content','LIKE','%'.$queryStr.'%');
            });
        endif;
        if($status == 2):
            $query->onlyTrashed();
        else:
            $query->where('active', $status);
        endif;

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
                $docList = [];
                if(isset($list->documents) && $list->documents->count() > 0):
                    $d = 0;
                    foreach($list->documents as $doc):
                        if(Storage::disk('s3')->exists('public/news_events/'.$list->id.'/'.$doc->current_file_name)):
                            $docList[$d]['name'] = $doc->display_file_name;
                            $docList[$d]['id'] = $doc->id; //Storage::disk('s3')->url('public/news_events/'.$list->id.'/'.$doc->current_file_name);

                            $d++;
                        endif;
                    endforeach;
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'title' => $list->title,
                    'content' => substr(trim($list->content), 0, 80),
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at,
                    'documents' => $docList,
                    'fol_all' => $list->fol_all,
                    'created_by' => isset($list->createdBy->employee->full_name) && !empty($list->createdBy->employee->full_name) ? $list->createdBy->employee->full_name : $list->createdBy->name,
                    'created_at' => (!empty($list->created_at) ? date('jS M, Y h:i A') : '')
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function edit(NewsAndEvent $event){
        $event->load(['students', 'documents']);
        $semesters = Cache::get('semesters', function () {
            $semesters = Semester::all()->sortByDesc("name");
            $semesterData = [];
            foreach ($semesters as $semester):
                $studentProposedCourse = StudentProposedCourse::where('semester_id',$semester->id)->get()->first();
                if(isset($studentProposedCourse->id))
                    $semesterData[] = $semester;
            endforeach;
            return $semesterData;
        });
        $courses = Cache::get('courses', function () {
            return Course::all();
        });
        $statuses = Cache::get('statuses', function () {
            return Status::where('type', 'Student')->get();
        });

        return view('pages.news-update.edit', [
            'title' => 'News & Updates - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'News & Updates', 'href' => route('news.updates')],
                ['label' => 'Edit', 'href' => 'javascript:void(0);']
            ],

            'event' => $event,
            'semesters' => $semesters,
            'courses' => $courses,
            'allStatuses' => $statuses,
            'academicYear' => AcademicYear::all()->sortByDesc('from_date'),
            'terms' => TermDeclaration::all()->sortByDesc('id'),
            'groups' => Group::all(),
        ]);
    }

    public function update(NewsAndEventsStoreRequest $request){
        $event_id = $request->id;
        $title = $request->title;
        $content = $request->content;
        $for_all = (isset($request->for_all_students) && $request->for_all_students > 0 ? $request->for_all_students : 0);
        $student_ids = ($for_all == 0 && isset($request->students) && !empty($request->students) ? $request->students : []);

        $events = NewsAndEvent::where('id', $event_id)->update([
            'title' => $title,
            'content' => $content,
            'fol_all' => $for_all,
            //'active' => 1,

            'updated_by' => auth()->user()->id,
        ]);

        NewsAndEventStudent::where('news_and_event_id', $event_id)->forceDelete();
        if($for_all == 0 && !empty($student_ids)):
            foreach($student_ids as $id):
                NewsAndEventStudent::create([
                    'news_and_event_id' => $event_id,
                    'student_id' => $id,
                ]);
            endforeach;
        endif;

        if($request->hasFile('documents')):
            $documents = $request->file('documents');
            foreach($documents as $document):
                $documentName = time().'_'.$document->getClientOriginalName();
                $path = $document->storeAs('public/news_events/'.$event_id, $documentName, 's3');

                NewsAndEventDocument::create([
                    'news_and_event_id' => $event_id,
                    'hard_copy_check' => 0,
                    'doc_type' => $document->getClientOriginalExtension(),
                    'disk_type' => 's3',
                    'path' => Storage::disk('s3')->url($path),
                    'display_file_name' => $documentName,
                    'current_file_name' => $documentName,

                    'created_by' => auth()->user()->id
                ]);
            endforeach;
        endif;

        return response()->json(['message' => 'News & Event successfully updated.', 'red' => route('news.updates')], 200);
    }

    public function destroy($id){
        $data = NewsAndEvent::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = NewsAndEvent::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $title = NewsAndEvent::find($id);
        $active = (isset($title->active) && $title->active == 1 ? 0 : 1);

        NewsAndEvent::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }

    public function assignedList(Request $request){
        $event_id = (isset($request->event_id) && !empty($request->event_id) ? $request->event_id : 0);
        $student_ids = NewsAndEventStudent::where('news_and_event_id', $event_id)->pluck('student_id')->unique()->toArray();
        $student_ids = (!empty($student_ids) ? $student_ids : [0]);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Student::orderByRaw(implode(',', $sorts))->whereIn('id', $student_ids);

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
                
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'full_time' => (isset($list->activeCR->propose->full_time) && $list->activeCR->propose->full_time > 0) ? $list->activeCR->propose->full_time : 0, 
                    'registration_no' => (!empty($list->registration_no) ? $list->registration_no : $list->application_no),
                    'first_name' => $list->first_name,
                    'last_name' => $list->last_name,
                    'course'=> (isset($list->activeCR->creation->course->name) && !empty($list->activeCR->creation->course->name) ? $list->activeCR->creation->course->name : ''),
                    'semester'=> (isset($list->activeCR->creation->semester->name) && !empty($list->activeCR->creation->semester->name) ? $list->activeCR->creation->semester->name : ''),
                    'status_id'=> (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                    'url' => route('student.show', $list->id),
                    'photo_url' => $list->photo_url,
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function eventDocumentDownload(Request $request){
        $row_id = $request->row_id;

        $theDoc = NewsAndEventDocument::where('id', $row_id)->withTrashed()->get()->first();
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/news_events/'.$theDoc->news_and_event_id.'/'.$theDoc->current_file_name, now()->addMinutes(15));
        return response()->json(['res' => $tmpURL], 200);
    }

    public function deleteEventDocument(Request $request){
        $row_id = $request->row_id;

        NewsAndEventDocument::where('id', $row_id)->delete();
        return response()->json(['message' => 'Record successfully deleted from DB row.', 'red' => ''], 200);
    }

    public function findStudents(Request $request){
        $searchParams = [
            'academic_years' => '',
            'intake_semester' => isset($request->intake_semester) && !empty($request->intake_semester) ? $request->intake_semester : [],
            'attendance_semester' => isset($request->attendance_semester) && !empty($request->attendance_semester) ? $request->attendance_semester : [],
            'course' => isset($request->course) && !empty($request->course) ? $request->course : [],
            'group' => isset($request->group) && !empty($request->group) ? $request->group : [],
            'evening_weekend' => isset($request->evening_weekend) && !empty($request->evening_weekend) ? $request->evening_weekend : '',
            'student_type' => isset($request->student_type) && !empty($request->student_type) ? $request->student_type : [],
            'group_student_status' => isset($request->group_student_status) && !empty($request->group_student_status) ? $request->group_student_status : [],
        ];

        $HTML = '';
        $COUNT = 0;
        $student_ids = $this->callTheStudentListForGroup($searchParams);
        if(!empty($student_ids)):
            $students = Student::whereIn('id', $student_ids)->get();
            if($students->count() > 0):
                $HTML .= '<div class="flex flex-wrap justify-start items-start">';
                    foreach($students as $std):
                        $HTML .= '<div class="singleStudent rounded-sm mr-1 mb-1 inline-flex bg-slate-200 text-primary font-medium pl-2 py-2 leading-none relative" style="padding-right: 30px;">';
                            $HTML .= '<label>'.$std->registration_no.'</label>';
                            $HTML .= '<input type="hidden" name="students[]" value="'.$std->id.'">';
                            $HTML .= '<span class="removeStd bg-danger-soft rounded-sm text-danger cursor-pointer absolute r-0 t-0 w-[25px] h-full inline-flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x" class="lucide lucide-x w-4 h-4"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg></span>';
                        $HTML .= '</div>';

                        $COUNT += 1;
                    endforeach;
                $HTML .= '</div>';
            endif;

            return response()->json(['html' => $HTML, 'count' => $COUNT], 200);
        else:
            return response()->json(['html' => '', 'count' => '0'], 200);
        endif;
    }


    public function callTheStudentListForGroup($searchParams) {
        $academic_years = $searchParams['academic_years'];
        $term_declaration_ids = $searchParams['attendance_semester'];
        $courses = $searchParams['course'];
        $groups = $searchParams['group'];
        $intake_semesters = $searchParams['intake_semester'];
        $group_student_statuses = $searchParams['group_student_status'];
        $student_types = $searchParams['student_type'];
        $evening_weekends = $searchParams['evening_weekend'];
        
        $studentIds = [];


        $QueryInner = StudentCourseRelation::with('creation');
        $QueryInner->where('active','=',1);
        if(!empty($evening_weekends) && ($evening_weekends==0 || $evening_weekends==1))
            $QueryInner->where('full_time',$evening_weekends);
        if(!empty($academic_years) && count($academic_years)>0)
            $QueryInner->where('academic_year_id',$academic_years);
        

            $studentIds =  $QueryInner->whereHas('creation', function($q) use($intake_semesters,$courses){
                    if(!empty($intake_semesters))
                        $q->whereIn('semester_id', $intake_semesters);
                    if(!empty($courses))
                        $q->whereIn('course_id', $courses);
            })->pluck('student_id')->unique()->toArray();

            $studentsListByEveningSemesterAndCourse = $studentIds;

        if(!empty($term_declaration_ids) && count($term_declaration_ids)>0) {

            if(!empty($groups)) {
                $groups = Group::whereIn('name',$groups)->pluck('id')->unique()->toArray();
            }
            $innerQuery = Plan::whereIn('term_declaration_id', $term_declaration_ids);

                if(!empty($groups)) {
                    $innerQuery->whereIn('group_id', $groups);
                }

            $planList = $innerQuery->whereHas('course', function($q) use($courses,$academic_years){
                if(!empty($courses))
                $q->whereIn('course_id', $courses);
                if(!empty($academic_years))
                $q->whereIn('academic_year_id', $academic_years);
                

            })->pluck('id')->unique()->toArray();

            $studentsListByTerm = Assign::whereIn("plan_id",$planList)->pluck('student_id')->unique()->toArray();
            $studentIds = [];
            foreach($studentsListByEveningSemesterAndCourse as $intakeStudent):

            if(in_array($intakeStudent,$studentsListByTerm)) {
                $studentIds[] = $intakeStudent;
            }
            endforeach;
        }

        //this part will use both term and intake and open
        if(!empty($student_types) && count($student_types)>0) {

            $innerQuery = Student::with('courseRelationsList');
            if(!empty($studentIds)) {
                $innerQuery->whereIn('id',$studentIds);
            }
            $studentsListByStudentType = $innerQuery->whereHas('courseRelationsList', function($q) use($student_types){
                $q->whereIn('type', $student_types);
            })->pluck('id')->unique()->toArray();

            $studentIds = $studentsListByStudentType;

        }
        if(!empty($group_student_statuses) && count($group_student_statuses)>0) {

                $innerQuery = Student::whereIn('status_id',$group_student_statuses);
                if(!empty($studentIds)) {
                    $innerQuery->whereIn('id',$studentIds);
                }
                $studentsListByStatus = $innerQuery->pluck('id')->unique()->toArray();

                $studentIds = $studentsListByStatus;
                
        }
            //endof the part

        sort($studentIds);

        return $studentIds;
    }

    
}
