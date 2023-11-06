<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeEmergencyContact;
use App\Models\User;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function index(){
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $employeeId = $employee->id;
        return view('pages.users.my-account.index', [
            'title' => 'My Account - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'My Account', 'href' => 'javascript:void(0);']
            ],
            'user' => User::find(auth()->user()->id),
            'employee' => Employee::where('user_id', auth()->user()->id)->get()->first(),
            "emergencyContacts" => EmployeeEmergencyContact::where("employee_id",$employeeId)->get()->first(),
        ]);
    }
}
