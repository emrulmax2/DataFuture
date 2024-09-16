<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
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
}
