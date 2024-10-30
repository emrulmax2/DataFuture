<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAppraisal;
use App\Models\EmployeeEligibilites;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\Option;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LibraryManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $expireDate = Carbon::now()->addDays(60)->format('Y-m-d');

        return view('pages.library.index', [
            'title' => 'Library Managemnet - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => 'javascript:void(0);']
            ],
            'pendingLeaves' => EmployeeLeave::where('status', 'Pending')->orderBy('id', 'DESC')->skip(0)->take(5)->get(),
            'absentToday' => [],
            'holidays' => EmployeeLeaveDay::where('leave_date', date('Y-m-d'))->where('status', 'Active')->whereHas('leave', function($query){
                              $query->where('status', 'Approved')->where('leave_type', 1);
                          })->skip(0)->limit(5)->get(),
            'passExpiry' => EmployeeEligibilites::where('document_type', 1)->where('doc_expire', '<=', $expireDate)
                            ->whereHas('employee', function($q){
                                $q->where('status', 1);
                            })->orderBy('doc_expire', 'ASC')->skip(0)->limit(5)->get(),
            'visaExpiry' => EmployeeEligibilites::where('eligible_to_work', 'Yes')->where('employee_work_permit_type_id', 3)
                            ->whereDate('workpermit_expire', '<=', $expireDate)
                            ->whereHas('employee', function($q){
                                $q->where('status', 1);
                            })->orderBy('workpermit_expire', 'ASC')->skip(0)->limit(5)->get(),
            'appraisal' => EmployeeAppraisal::where('due_on', '<=', $expireDate)->whereNull('completed_on')
                           ->whereHas('employee', function($q){
                                $q->where('status', 1);
                           })->orderBy('due_on', 'ASC')->skip(0)->limit(5)->get()
        ]);
    }


    public function settings()
    {
        return view('pages.library.settings', [
            'title' => 'Library Settings - London Churchill College',
            'subtitle' => 'Library Settings',
            'breadcrumbs' => [
                ['label' => 'Library Settings', 'href' => 'javascript:void(0);']
            ],
            'opt' => Option::where('category', 'SITE_SETTINGS')->pluck('value', 'name')->toArray(),
        ]);
    }
}
