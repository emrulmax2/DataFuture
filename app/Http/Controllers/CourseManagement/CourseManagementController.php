<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Group;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\Semester;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;

class CourseManagementController extends Controller
{
    public function index()
    {
        return view('pages.course-management.index', [
            'title' => 'Course Management - London Churchill College',
            'subtitle' => 'Dashboard',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => 'javascript:void(0);']
            ],
            'semesters' => Semester::all()->count(),
            'courses' => Course::all()->count(),
            'termdecs' => TermDeclaration::all()->count(),
            'modcreations' => ModuleCreation::all()->count(),
            'groups' => Group::all()->count(),
            'plans' => Plan::all()->count(),
        ]);
    }
}
