<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentOptionController extends Controller
{
    public function index()
    {
        return view('pages.settings.studentoption.index', [
            'title' => 'Student Option Values - London Churchill College',
            'subtitle' => 'Student Option Values',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Student Option Values', 'href' => 'javascript:void(0);']
            ],
        ]);
    }
}
