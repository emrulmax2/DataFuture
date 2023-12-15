<?php

namespace App\Http\Controllers\Personal_Tutor;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index(){

        return view('pages.personal-tutor.dashboard.index', [
            'title' => 'Personal Tutor Dashboard - LCC Data Future Managment',
            'breadcrumbs' => []
        ]);
    }
}
