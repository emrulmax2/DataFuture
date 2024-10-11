<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;

class ClassStatusByTermController extends Controller
{
    public function index(){

        return view('pages.reports.class-status.index', [
            'title' => 'Status Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Class Status Reports', 'href' => 'javascript:void(0);']
            ],
         
            'terms' => TermDeclaration::all()->sortByDesc('id'),
          

        ]);
    }
    
}
