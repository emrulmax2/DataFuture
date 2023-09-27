<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentOptionController extends Controller
{
    public function index()
    {
        return view('pages/studentoption/index', [
            'title' => 'Student Option Values - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Student Option Values', 'href' => 'javascript:void(0);']
            ],
        ]);
    }
}
