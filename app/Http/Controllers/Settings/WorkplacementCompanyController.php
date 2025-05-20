<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WorkplacementCompanyController extends Controller
{
    public function wp_companies()
    {
        return view('pages.settings.workplacement.wp-companies', [
            'title' => 'Workplacement Companies - London Churchill College',
            'subtitle' => 'Workplacement Companies',
            'breadcrumbs' => [
                ['label' => 'Workplacement Companies', 'href' => 'javascript:void(0);']
            ],
            'companies' => Company::orderBy('name')->get()
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        
        $companies = Company::where(function($query) use ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhereHas('supervisors', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                  });
        })
        ->orderBy('name')
        ->get();

        if ($request->ajax()) {
            $html = view('pages.settings.workplacement.partials.company-list', [
                'companies' => $companies
            ])->render();

            return response()->json(['html' => $html]);
        }

        return back();
    }
}
