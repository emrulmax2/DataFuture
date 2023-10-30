<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePaymentSettingsRequest;
use App\Http\Requests\EmployeePaymentSettingsUpdateRequest;
use App\Models\Employee;
use App\Models\EmployeeBankDetail;
use App\Models\EmployeeHolidayAuthorisedBy;
use App\Models\EmployeeHourAuthorisedBy;
use App\Models\EmployeeInfoPenssionScheme;
use App\Models\EmployeePaymentSetting;
use App\Models\EmployeePenssionScheme;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeePaymentSettingsController extends Controller
{
    public function index($id){
        $employee = Employee::find($id)->get()->first();
        $userData = User::find($employee->user_id);
        $employment = Employment::where("employee_id",$id)->get()->first();

        return view('pages.employee.profile.payment',[
            'title' => 'Welcome - LCC Data Future Managment',
            'breadcrumbs' => [],
            "user" => $userData,
            "employee" => $employee,
            "employment" => $employment,
            'schemes' => EmployeeInfoPenssionScheme::all(),
            'users' => User::all(),
            'hourAuthIds' => EmployeeHourAuthorisedBy::where('employee_id', $id)->pluck('user_id')->toArray(),
            'holidayAuthIds' => EmployeeHolidayAuthorisedBy::where('employee_id', $id)->pluck('user_id')->toArray()
        ]);
    }

    public function store(EmployeePaymentSettingsRequest $request){
        $employee_id = $request->employee_id;
        $employee_payment_setting_id = $request->employee_payment_setting_id;
        $payment_method = (isset($request->payment_method) ? $request->payment_method : null);
        $subject_to_clockin = (isset($request->subject_to_clockin) ? $request->subject_to_clockin : 0);
        $holiday_entitled = (isset($request->holiday_entitled) ? $request->holiday_entitled : 0);
        $pension_enrolled = (isset($request->pension_enrolled) ? $request->pension_enrolled : 0);

        $data = [];
        $data['employee_id'] = $employee_id;
        $data['pay_frequency'] = (isset($request->pay_frequency) ? $request->pay_frequency : null);
        $data['tax_code'] = (isset($request->tax_code) ? $request->tax_code : null);
        $data['payment_method'] = (isset($request->payment_method) ? $request->payment_method : null);
        $data['created_by'] = auth()->user()->id;
        $data['updated_by'] = auth()->user()->id;

        if($payment_method == 'Bank Transfer'):
            $bdata = [];
            $bdata['employee_id'] = $employee_id;
            $bdata['beneficiary'] = (isset($request->beneficiary) ? $request->beneficiary : '');
            $bdata['sort_code'] = (isset($request->sort_code) ? $request->sort_code : '');
            $bdata['ac_no'] = (isset($request->ac_no) ? $request->ac_no : '');
            $bdata['active'] = (isset($request->active) ? $request->active : 1);
            $bdata['created_by'] = auth()->user()->id;

            EmployeeBankDetail::create($bdata);
        endif;

        if($subject_to_clockin == 1):
            $data['subject_to_clockin'] = 'Yes';
            $hour_authorised_by = (isset($request->hour_authorised_by) && !empty($request->hour_authorised_by) ? $request->hour_authorised_by : []);
            if(!empty($hour_authorised_by)):
                foreach($hour_authorised_by as $authBy):
                    $adata = [];
                    $adata['employee_id'] = $employee_id;
                    $adata['user_id'] = $authBy;
                    $adata['created_by'] = auth()->user()->id;

                    EmployeeHourAuthorisedBy::create($adata);
                endforeach;
            endif;
        else:
            $data['subject_to_clockin'] = 'No';
        endif;

        if($holiday_entitled == 1):
            $data['holiday_entitled'] = 'Yes';
            $data['holiday_base'] = (isset($request->holiday_base) && !empty($request->holiday_base) ? $request->holiday_base : null);
            $data['bank_holiday_auto_book'] = (isset($request->bank_holiday_auto_book) && $request->bank_holiday_auto_book == 1 ? 'Yes': 'No');
            
            $holiday_authorised_by = (isset($request->holiday_authorised_by) && !empty($request->holiday_authorised_by) ? $request->holiday_authorised_by : []);
            if(!empty($holiday_authorised_by)):
                foreach($holiday_authorised_by as $authBy):
                    $adata = [];
                    $adata['employee_id'] = $employee_id;
                    $adata['user_id'] = $authBy;
                    $adata['created_by'] = auth()->user()->id;

                    EmployeeHolidayAuthorisedBy::create($adata);
                endforeach;
            endif;
        else:
            $data['holiday_entitled'] = 'No';
        endif;

        if($pension_enrolled == 1):
            $data['pension_enrolled'] = 'Yes';

            $bdata = [];
            $bdata['employee_id'] = $employee_id;
            $adata['employee_info_penssion_scheme_id'] = (isset($request->employee_info_penssion_scheme_id) && !empty($request->employee_info_penssion_scheme_id) ? $request->employee_info_penssion_scheme_id : null);
            $adata['joining_date'] = (isset($request->joining_date) && !empty($request->joining_date) ? date('Y-m-d', strtotime($request->joining_date)) : null);
            $adata['date_left'] = (isset($request->date_left) && !empty($request->date_left) ? date('Y-m-d', strtotime($request->date_left)) : null);
            $adata['created_by'] = auth()->user()->id;

            EmployeePenssionScheme::create($adata);
        else:
            $data['pension_enrolled'] = 'No';
        endif;

        $paymentSetting = EmployeePaymentSetting::updateOrCreate([ 'employee_id' => $employee_id, 'id' => $employee_payment_setting_id ], $data);

        return response()->json(['msg' => 'Payment Settings Successfully updated.'], 200);
    }

    public function update(EmployeePaymentSettingsUpdateRequest $request){
        $employee_id = $request->employee_id;
        $employee_payment_setting_id = $request->employee_payment_setting_id;

        $payment_method = (isset($request->payment_method) ? $request->payment_method : null);
        $subject_to_clockin = (isset($request->subject_to_clockin) ? $request->subject_to_clockin : 0);
        $holiday_entitled = (isset($request->holiday_entitled) ? $request->holiday_entitled : 0);
        $pension_enrolled = (isset($request->pension_enrolled) ? $request->pension_enrolled : 0);

        $data = [];
        $data['employee_id'] = $employee_id;
        $data['pay_frequency'] = (isset($request->pay_frequency) ? $request->pay_frequency : null);
        $data['tax_code'] = (isset($request->tax_code) ? $request->tax_code : null);
        $data['payment_method'] = (isset($request->payment_method) ? $request->payment_method : null);
        $data['updated_by'] = auth()->user()->id;

        
        if($payment_method == 'Bank Transfer'):
            EmployeeBankDetail::where('employee_id', $employee_id)->withTrashed()->restore();
        else:
            EmployeeBankDetail::where('employee_id', $employee_id)->delete();
        endif;


        if($subject_to_clockin == 1):
            EmployeeHourAuthorisedBy::where('employee_id', $employee_id)->forceDelete();

            $data['subject_to_clockin'] = 'Yes';
            $hour_authorised_by = (isset($request->hour_authorised_by) && !empty($request->hour_authorised_by) ? $request->hour_authorised_by : []);
            if(!empty($hour_authorised_by)):
                foreach($hour_authorised_by as $authBy):
                    $adata = [];
                    $adata['employee_id'] = $employee_id;
                    $adata['user_id'] = $authBy;
                    $adata['created_by'] = auth()->user()->id;

                    EmployeeHourAuthorisedBy::create($adata);
                endforeach;
            endif;
        else:
            EmployeeHourAuthorisedBy::where('employee_id', $employee_id)->forceDelete();
            $data['subject_to_clockin'] = 'No';
        endif;

        if($holiday_entitled == 1):
            EmployeeHolidayAuthorisedBy::where('employee_id', $employee_id)->forceDelete();
            $data['holiday_entitled'] = 'Yes';
            $data['holiday_base'] = (isset($request->holiday_base) && !empty($request->holiday_base) ? $request->holiday_base : null);
            $data['bank_holiday_auto_book'] = (isset($request->bank_holiday_auto_book) && $request->bank_holiday_auto_book == 1 ? 'Yes': 'No');
            
            $holiday_authorised_by = (isset($request->holiday_authorised_by) && !empty($request->holiday_authorised_by) ? $request->holiday_authorised_by : []);
            if(!empty($holiday_authorised_by)):
                foreach($holiday_authorised_by as $authBy):
                    $adata = [];
                    $adata['employee_id'] = $employee_id;
                    $adata['user_id'] = $authBy;
                    $adata['created_by'] = auth()->user()->id;

                    EmployeeHolidayAuthorisedBy::create($adata);
                endforeach;
            endif;
        else:
            EmployeeHolidayAuthorisedBy::where('employee_id', $employee_id)->forceDelete();
            $data['holiday_entitled'] = 'No';
        endif;

        if($pension_enrolled == 1):
            EmployeePenssionScheme::where('employee_id', $employee_id)->withTrashed()->restore();
            $data['pension_enrolled'] = 'Yes';
        else:
            EmployeePenssionScheme::where('employee_id', $employee_id)->delete();
            $data['pension_enrolled'] = 'No';
        endif;

        $paymentSetting = EmployeePaymentSetting::where('employee_id', $employee_id)->where('id', $employee_payment_setting_id)->update($data);

        return response()->json(['msg' => 'Payment Settings Successfully updated.'], 200);
    }
}
