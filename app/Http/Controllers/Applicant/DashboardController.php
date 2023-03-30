<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userData = \Auth::guard('applicant')->user();
        
        return view('pages.applicant.index', [
            'layout' => 'side-menu',
            'user' => $userData
        ]);
    }
}
