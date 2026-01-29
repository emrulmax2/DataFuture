<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeEmergencyContact;
use App\Models\HrVacancy;
use App\Models\PaySlipUploadSync;
use App\Models\User;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function index(){
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $employeeId = $employee->id;
        return view('pages.users.my-account.index', [
            'title' => 'My HR - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'My HR', 'href' => 'javascript:void(0);']
            ],
            'user' => User::find(auth()->user()->id),
            'employee' => Employee::where('user_id', auth()->user()->id)->get()->first(),
            "emergencyContacts" => EmployeeEmergencyContact::where("employee_id",$employeeId)->get()->first(),
            'vacanties' => HrVacancy::where('active', 1)->get()->count()
        ]);
    }
    public function extraBenefit(){
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $employeeId = $employee->id;

        return view('pages.users.my-account.extrabenefit', [
            'title' => 'My HR - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'My HR', 'href' => 'javascript:void(0);']
            ],
            'user' => User::find(auth()->user()->id),
            'employee' => Employee::where('user_id', auth()->user()->id)->get()->first(),
            "emergencyContacts" => EmployeeEmergencyContact::where("employee_id",$employeeId)->get()->first(),
        ]);
    }

    public function payrollSyncShow(){
        
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $employeeId = $employee->id;
        $paySlipUploadSync = PaySlipUploadSync::with('holidayYear')->where('employee_id', $employeeId)->where('file_transffered_at', '!=', null)->orderBy('holiday_year_id', 'desc')->orderBy('created_at', 'desc')->get();
        $holidayYearIds = PaySlipUploadSync::with('holidayYear')->where('employee_id', $employeeId)->where('file_transffered_at', '!=', null)->orderBy('holiday_year_id', 'desc')->pluck('holiday_year_id')->unique()->toArray();
        
        if(!$paySlipUploadSync){
            $paySlipUploadSync = [];
        }

        return view('pages.users.my-account.payslip', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Monthly Attendance', 'href' => route('hr.attendance')],
                ['label' => 'Payroll Sync', 'href' => 'javascript:void(0);']
            ],
            'employee'=> Employee::find($id),
            'paySlipUploadSync' => $paySlipUploadSync,
            'holidayYearIds' => $holidayYearIds ?? [],
        ]);
    }
    
}
