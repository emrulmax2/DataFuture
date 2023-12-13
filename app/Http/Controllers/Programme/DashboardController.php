<?php

namespace App\Http\Controllers\Programme;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        return view('pages.programme.dashboard.index', [
            'title' => 'Programme Dashboard - LCC Data Future Managment',
            'breadcrumbs' => []
        ]);
    }
}
