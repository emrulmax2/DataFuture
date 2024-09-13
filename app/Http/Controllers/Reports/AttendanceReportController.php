<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Group;
use App\Models\Semester;
use App\Models\Status;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function index(Request $request){
        $params = (isset($request->params) && !empty($request->params) ? $request->params : []);

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
            'params' => $params,
            //'reports' => ($semester_id > 0 ? $this->generateReports($semester_id) : false)
        ]);
    }
}
