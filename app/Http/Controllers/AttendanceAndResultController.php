<?php

namespace App\Http\Controllers;

use App\Models\InstanceTerm;
use Illuminate\Http\Request;

class AttendanceAndResultController extends Controller
{
    public function index()
    {
        return view('pages.attendance-and-result.index', [
            'title' => 'Attendance & Result - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Attendance & Result', 'href' => 'javascript:void(0);']
            ],
            'terms' => InstanceTerm::orderBy('id', 'desc')->get()
        ]);
    }
}
