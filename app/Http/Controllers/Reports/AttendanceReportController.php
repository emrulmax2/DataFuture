<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assign;
use App\Models\Course;
use App\Models\Group;
use App\Models\Plan;
use App\Models\Semester;
use App\Models\Status;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Exports\ArrayCollectionExport;
use App\Models\ComonSmtp;
use App\Models\CourseCreation;
use App\Models\EmailTemplate;
use App\Models\LetterSet;
use App\Models\Signatory;
use App\Models\SmsTemplate;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{
    public function index(){

        return view('pages.reports.attendance.index', [
            'title' => 'Attendance Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Attendance Reports', 'href' => 'javascript:void(0);']
            ],
            'semesters' => Semester::all()->sortByDesc("name"),
            'courses' => Course::all(),
            'allStatuses' => Status::where('type', 'Student')->get(),
            'academicYear' => AcademicYear::all()->sortByDesc('from_date'),
            'terms' => TermDeclaration::all()->sortByDesc('id'),
            'groups' => Group::all(),

            'smsTemplates' => SmsTemplate::where('live', 1)->where('status', 1)->orderBy('sms_title', 'ASC')->get(),
            'emailTemplates' => EmailTemplate::where('live', 1)->where('status', 1)->orderBy('email_title', 'ASC')->get(),
            'letterSet' => LetterSet::where('live', 1)->where('status', 1)->orderBy('letter_title', 'ASC')->get(),
            'smtps' => ComonSmtp::orderBy('smtp_user', 'ASC')->get(),
            'signatory' => Signatory::orderBy('signatory_name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        parse_str($request->form_data, $form);
        $params = isset($form['params']) && !empty($form['params']) ? $form['params'] : [];
        $intake_semester = (isset($params['intake_semester']) && !empty($params['intake_semester']) ? $params['intake_semester'] : []);
        $courseCreations = (!empty($intake_semester) ? CourseCreation::whereIn('semester_id', $intake_semester)->pluck('id')->unique()->toArray() : []);
        $attendance_semester = (isset($params['attendance_semester']) && !empty($params['attendance_semester']) ? $params['attendance_semester'] : []);
        $course = (isset($params['course']) && !empty($params['course']) ? $params['course'] : []);
        $group = (isset($params['group']) && !empty($params['group']) ? $params['group'] : []);
        $groupsIDList = Group::select('id')->whereIn('name', $group);
        if(!empty($attendance_semester) && count($attendance_semester) > 0): $groupsIDList = $groupsIDList->whereIn('term_declaration_id', $attendance_semester); endif;
        if(!empty($course) && count($course) > 0): $groupsIDList = $groupsIDList->whereIn('course_id', $course); endif;
        $groupsIDList = $groupsIDList->groupBy('id')->get()->pluck('id')->unique()->toArray();
        
        $evening_weekend = (isset($params['evening_weekend']) && !empty($params['evening_weekend']) ? [$params['evening_weekend']] : [0, 1]);
        $group_student_status = (isset($params['group_student_status']) && !empty($params['group_student_status']) ? $params['group_student_status'] : []);
        $attendance_percentage = (isset($params['attendance_percentage']) && $params['attendance_percentage'] != '' ? $params['attendance_percentage'] * 1 : 101);

        $plans = Plan::orderBy('id', 'asc');
        /*if(!empty($intake_semester)):
            $plans->whereHas('cCreation', function($q) use($intake_semester){
                $q->whereIn('semester_id', $intake_semester);
            });
        endif;*/
        if(!empty($attendance_semester)): $plans->whereIn('term_declaration_id', $attendance_semester); endif;
        if(!empty($course)): $plans->whereIn('course_id', $course); endif;
        if(!empty($groupsIDList)): $plans->whereIn('group_id', $groupsIDList); endif;
        $plan_ids = $plans->pluck('id')->unique()->toArray();

        $assign_student_ids = Assign::whereIn('plan_id', $plan_ids)->whereHas('student', function($q) use($group_student_status, $courseCreations){
            if(!empty($group_student_status)):
                $q->whereIn('status_id', $group_student_status);
            endif;
            if(!empty($courseCreations)):
                $q->whereHas('activeCR', function($sq) use($courseCreations){
                    $sq->whereIn('course_creation_id', $courseCreations);
                });
            endif;
        })->pluck('student_id')->unique()->toArray();

        $query = DB::table('attendances as atn')
                    ->select(
                        'std.id', 'std.photo', 'std.first_name', 'std.last_name', 'std.registration_no', 'std.ssn_no',
                        'sts.name as status', 'stc.institutional_email', 'stc.mobile', 'sabd.reference',
                        'scr.id as course_relation_id', 'grp.name as group_name', 'cc.semester_id', 'sm.name as semester_name',
                        'cc.course_id', 'cr.name as course_name',


                        DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                    )
                    ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                    ->leftJoin('groups as grp', 'pln.group_id', 'grp.id')
                    ->leftJoin('students as std', 'atn.student_id', 'std.id')
                    ->leftJoin('student_course_relations as scr', function($j){
                        $j->on('scr.student_id', '=', 'std.id');
                        $j->on('scr.active', DB::raw(1));
                        $j->on('scr.id', DB::raw('(SELECT MAX(scrr.id) FROM student_course_relations as scrr WHERE scrr.student_id = std.id AND scrr.active = 1)'));
                    })
                    ->leftJoin('statuses as sts', 'std.status_id', 'sts.id')
                    ->leftJoin('student_contacts as stc', 'stc.student_id', 'std.id')
                    ->leftJoin('student_awarding_body_details as sabd', function($j){
                        $j->on('sabd.student_id', '=', 'std.id');
                        $j->on('sabd.student_course_relation_id', '=', 'scr.id');
                    })
                    ->leftJoin('course_creations as cc', 'scr.course_creation_id', 'cc.id')
                    ->leftJoin('semesters as sm', 'cc.semester_id', 'sm.id')
                    ->leftJoin('courses as cr', 'cc.course_id', 'cr.id')
                    ->whereIn('atn.plan_id', $plan_ids)
                    ->whereIsNull('atn.deleted_at')
                    ->where('pln.course_id', DB::raw('cc.course_id'));
        if(!empty($assign_student_ids)):
            $query->whereIn('atn.student_id', $assign_student_ids);
        endif;
        $query->groupBy('atn.student_id');
        if($attendance_percentage == 0 || $attendance_percentage > 0):
            $query->havingRaw('percentage_withexcuse < '.$attendance_percentage.' OR round(percentage_withexcuse) = 0');
        endif;
        $sql = $query->toSql();


        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

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
                if ($list->photo !== null && Storage::disk('local')->exists('public/students/'.$list->id.'/'.$list->photo)) {
                    $photo_url = Storage::disk('local')->url('public/students/'.$list->id.'/'.$list->photo);
                } else {
                    $photo_url = asset('build/assets/images/user_avatar.png');
                }
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'photo_url' => $photo_url,
                    'registration_no' => $list->registration_no,
                    'name' => $list->first_name.' '.$list->last_name,
                    'semester' => (isset($list->semester_name) && !empty($list->semester_name) ? $list->semester_name : ''),
                    'course' => (isset($list->course_name) && !empty($list->course_name) ? $list->course_name : ''),
                    'ssn' => $list->ssn_no,
                    'aw_body_ref' => $list->reference,
                    'status' => $list->status,
                    'mobile' => $list->mobile,
                    'institutional_email' => $list->institutional_email,
                    'group' => $list->group_name,
                    'P' => $list->P,
                    'O' => $list->O,
                    'A' => $list->A,
                    'E' => $list->E,
                    'M' => $list->M,
                    'H' => $list->H,
                    'L' => $list->L,
                    'LE' => $list->LE,
                    'TC' => $list->TOTAL,
                    'w_excuse' => (isset($list->percentage_withexcuse) && !empty($list->percentage_withexcuse) ? number_format($list->percentage_withexcuse, 2).'%' : '0%'),
                    'wo_excuse' => (isset($list->percentage_withoutexcuse) && !empty($list->percentage_withoutexcuse) ? number_format($list->percentage_withoutexcuse, 2).'%' : '0%'),
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows, 'a' => $sql]);
    }


    public function excelDownload(Request $request){
        parse_str($request->form_data, $form);
        $params = isset($form['params']) && !empty($form['params']) ? $form['params'] : [];
        //$academic_year = (isset($params['academic_year']) && !empty($params['academic_year']) ? $params['academic_year'] : []);
        $intake_semester = (isset($params['intake_semester']) && !empty($params['intake_semester']) ? $params['intake_semester'] : []);
        $courseCreations = (!empty($intake_semester) ? CourseCreation::whereIn('semester_id', $intake_semester)->pluck('id')->unique()->toArray() : []);
        $attendance_semester = (isset($params['attendance_semester']) && !empty($params['attendance_semester']) ? $params['attendance_semester'] : []);
        $course = (isset($params['course']) && !empty($params['course']) ? $params['course'] : []);
        $group = (isset($params['group']) && !empty($params['group']) ? $params['group'] : []);
        $groupsIDList = Group::select('id')->whereIn('name', $group);
        if(!empty($attendance_semester) && count($attendance_semester) > 0): $groupsIDList = $groupsIDList->whereIn('term_declaration_id', $attendance_semester); endif;
        if(!empty($course) && count($course) > 0): $groupsIDList = $groupsIDList->whereIn('course_id', $course); endif;
        $groupsIDList = $groupsIDList->groupBy('id')->get()->pluck('id')->unique()->toArray();
        
        $evening_weekend = (isset($params['evening_weekend']) && !empty($params['evening_weekend']) ? [$params['evening_weekend']] : [0, 1]);
        $group_student_status = (isset($params['group_student_status']) && !empty($params['group_student_status']) ? $params['group_student_status'] : []);
        $attendance_percentage = (isset($params['attendance_percentage']) && $params['attendance_percentage'] != '' ? $params['attendance_percentage'] * 1 : 101);

        $plans = Plan::orderBy('id', 'asc');
        /*if(!empty($intake_semester)):
            $plans->whereHas('cCreation', function($q) use($intake_semester){
                $q->whereIn('semester_id', $intake_semester);
            });
        endif;*/
        if(!empty($attendance_semester)): $plans->whereIn('term_declaration_id', $attendance_semester); endif;
        if(!empty($course)): $plans->whereIn('course_id', $course); endif;
        if(!empty($groupsIDList)): $plans->whereIn('group_id', $groupsIDList); endif;
        $plan_ids = $plans->pluck('id')->unique()->toArray();

        /*$assign_student_ids = Assign::whereIn('plan_id', $plan_ids)->whereHas('student', function($q) use($group_student_status){
            if(!empty($group_student_status)):
                $q->whereIn('status_id', $group_student_status);
            endif;
        })->pluck('student_id')->unique()->toArray();*/
        $assign_student_ids = Assign::whereIn('plan_id', $plan_ids)->whereHas('student', function($q) use($group_student_status, $courseCreations){
            if(!empty($group_student_status)):
                $q->whereIn('status_id', $group_student_status);
            endif;
            if(!empty($courseCreations)):
                $q->whereHas('activeCR', function($sq) use($courseCreations){
                    $sq->whereIn('course_creation_id', $courseCreations);
                });
            endif;
        })->pluck('student_id')->unique()->toArray();

        $query = DB::table('attendances as atn')
                    ->select(
                        'std.id', 'std.photo', 'std.first_name', 'std.last_name', 'std.date_of_birth', 'std.registration_no', 'std.ssn_no',
                        'sts.name as status', 'stc.institutional_email', 'stc.mobile', 'sabd.reference',
                        'scr.id as course_relation_id', 'grp.name as group_name', 'cc.semester_id', 'sm.name as semester_name',
                        'cc.course_id', 'cr.name as course_name',


                        DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                    )
                    ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                    ->leftJoin('groups as grp', 'pln.group_id', 'grp.id')
                    ->leftJoin('students as std', 'atn.student_id', 'std.id')
                    ->leftJoin('student_course_relations as scr', function($j){
                        $j->on('scr.student_id', '=', 'std.id');
                        $j->on('scr.active', DB::raw(1));
                        $j->on('scr.id', DB::raw('(SELECT MAX(scrr.id) FROM student_course_relations as scrr WHERE scrr.student_id = std.id AND scrr.active = 1)'));
                    })
                    ->leftJoin('statuses as sts', 'std.status_id', 'sts.id')
                    ->leftJoin('student_contacts as stc', 'stc.student_id', 'std.id')
                    ->leftJoin('student_awarding_body_details as sabd', function($j){
                        $j->on('sabd.student_id', '=', 'std.id');
                        $j->on('sabd.student_course_relation_id', '=', 'scr.id');
                    })
                    ->leftJoin('course_creations as cc', 'scr.course_creation_id', 'cc.id')
                    ->leftJoin('semesters as sm', 'cc.semester_id', 'sm.id')
                    ->leftJoin('courses as cr', 'cc.course_id', 'cr.id')
                    ->whereIn('atn.plan_id', $plan_ids)
                    ->whereIsNull('atn.deleted_at')
                    ->where('pln.course_id', DB::raw('cc.course_id'));
        if(!empty($assign_student_ids)):
            $query->whereIn('atn.student_id', $assign_student_ids);
        endif;
        $query->groupBy('atn.student_id');
        if($attendance_percentage == 0 || $attendance_percentage > 0):
            $query->havingRaw('percentage_withexcuse < '.$attendance_percentage.' OR round(percentage_withexcuse) = 0');
        endif;
        $Query = $query->get();

        $theCollection = [];
        $theCollection[1][0] = 'ID';
        $theCollection[1][1] = 'Reg No';
        $theCollection[1][2] = 'Name';
        $theCollection[1][3] = 'Date of Birth';
        $theCollection[1][4] = 'Semester';
        $theCollection[1][5] = 'Course';
        $theCollection[1][6] = 'SSN';
        $theCollection[1][7] = 'Awarding Body Ref';
        $theCollection[1][8] = 'Status';
        $theCollection[1][9] = 'Mobile';
        $theCollection[1][10] = 'Email';
        $theCollection[1][11] = 'Group';
        $theCollection[1][12] = 'P';
        $theCollection[1][13] = 'O';
        $theCollection[1][14] = 'A';
        $theCollection[1][15] = 'E';
        $theCollection[1][16] = 'M';
        $theCollection[1][17] = 'H';
        $theCollection[1][18] = 'L';
        $theCollection[1][19] = 'LE';
        $theCollection[1][20] = 'TC';
        $theCollection[1][21] = 'Percentage(%)';
        $theCollection[1][22] = 'Percentage W/O Excused(%)';

        $row = 2;
        if(!empty($Query)):
            foreach($Query as $list):
                $theCollection[$row][0] = $list->id;
                $theCollection[$row][1] = $list->registration_no;
                $theCollection[$row][2] = $list->first_name.' '.$list->last_name;
                $theCollection[$row][3] = (isset($list->date_of_birth) && !empty($list->date_of_birth) ? date('d-m-Y', strtotime($list->date_of_birth)) : '');
                $theCollection[$row][4] = (isset($list->semester_name) && !empty($list->semester_name) ? $list->semester_name : '');
                $theCollection[$row][5] = (isset($list->course_name) && !empty($list->course_name) ? $list->course_name : '');
                $theCollection[$row][6] = $list->ssn_no;
                $theCollection[$row][7] = (isset($list->reference) && !empty($list->reference) ? $list->reference : '');
                $theCollection[$row][8] = $list->status;
                $theCollection[$row][9] = $list->mobile;
                $theCollection[$row][10] = $list->institutional_email;
                $theCollection[$row][11] = $list->group_name;
                $theCollection[$row][12] = $list->P;
                $theCollection[$row][13] = $list->O;
                $theCollection[$row][14] = $list->A;
                $theCollection[$row][15] = $list->E;
                $theCollection[$row][16] = $list->M;
                $theCollection[$row][17] = $list->H;
                $theCollection[$row][18] = $list->L;
                $theCollection[$row][19] = $list->LE;
                $theCollection[$row][20] = $list->TOTAL;
                $theCollection[$row][21] = (isset($list->percentage_withexcuse) && !empty($list->percentage_withexcuse) ? number_format($list->percentage_withexcuse, 2) : '0');
                $theCollection[$row][22] = (isset($list->percentage_withoutexcuse) && !empty($list->percentage_withoutexcuse) ? number_format($list->percentage_withoutexcuse, 2) : '0');
                $row += 1;
            endforeach;
        endif;

        // if (Storage::disk('local')->exists('public/Student_Attendance_Reports.xlsx')):
        //     Storage::disk('local')->delete('public/Student_Attendance_Reports.xlsx');
        // endif;
        // Excel::store(new ArrayCollectionExport($theCollection), 'public/Student_Attendance_Reports.xlsx', 'local');
        // return response()->json(['url' => url('storage/Student_Attendance_Reports.xlsx')], 200);

        return Excel::download(new ArrayCollectionExport($theCollection), 'Student_Attendance_Reports.xlsx');
    }
}
