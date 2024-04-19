<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\AccBank;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function index($bank){
        return view('pages.accounts.storage.index', [
            'title' => 'Accounts Storage - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Accounts Summary', 'href' => route('accounts')],
                ['label' => 'Storage', 'href' => 'javascript:void(0);']
            ],
            'banks' => AccBank::where('status', 1)->orderBy('bank_name', 'ASC')->get(),
            'bank' => AccBank::find($bank)
        ]);
    }
}
