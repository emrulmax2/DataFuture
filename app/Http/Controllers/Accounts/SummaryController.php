<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function index(){
        return view('pages.accounts.summary', [
            'title' => 'Accounts - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Accounts Summary', 'href' => 'javascript:void(0);']
            ]
        ]);
    }
}
