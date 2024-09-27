<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\AccTransaction;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Status;
use Illuminate\Http\Request;

class SystemReportController extends Controller
{
    public function index(){

        return view('pages.reports.index', [
            'title' => 'Site Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => 'javascript:void(0);']
            ]
        ]);
    }
    
    public function accountsReports(){
        return view('pages.reports.accounts.index', [
            'title' => 'Student Accounts Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'Account Reports', 'href' => 'javascript:void(0);']
            ],
            'semester' => Semester::orderBy('id', 'DESC')->get(),
            'courses' => Course::where('active', 1)->orderBy('name', 'ASC')->get(),
            'status' => Status::where('type', 'Student')->orderBy('name', 'ASC')->get()
        ]);
    }

    public function intakePerformance(){
        return view('pages.reports.intake-performance.index', [
            'title' => 'Site Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'Intake Performance Reports', 'href' => 'javascript:void(0);']
            ],
            'semester' => Semester::orderBy('id', 'DESC')->get(),
        ]);
    }
}
