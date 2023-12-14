<?php

namespace App\Http\Controllers\Personal_Tutor;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index(){
        //$plans = Plan::where("personal_tutor_id", $id)->groupBy("term_declartion_id")->get();
        return view('pages.personal-tutor.dashboard.index', [
            'title' => 'Personal Tutor Dashboard - LCC Data Future Managment',
            'breadcrumbs' => []
        ]);
    }
}
