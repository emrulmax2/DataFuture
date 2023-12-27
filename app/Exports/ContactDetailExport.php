<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Address;
use App\Models\EmployeeEmergencyContact;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ContactDetailExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $query = Employee::where('status', '=', 1)->get();

        $i = 0;

        $dataList =[];

        foreach($query as $item) {
            $address = Address::find($item->address_id);
            $emergencyContact= EmployeeEmergencyContact::find($item->id);
            $dataList[$i++] = [
                'name' => $item->first_name.' '.$item->last_name,
                'address' => $address->address_line_1.','.$address->address_line_2,
                'post_code' => $item->post_code,
                'telephone' => isset($item->telephone) ? $item->telephone : '',
                'mobile' => isset($item->mobile) ? $item->mobile : '',
                'email' => isset($item->email) ? $item->email : '',
                'emergency_telephone' => isset($emergencyContact->emergency_contact_telephone) ? $emergencyContact->emergency_contact_telephone : '',
                'emergency_mobile' => isset($emergencyContact->emergency_contact_mobile) ? $emergencyContact->emergency_contact_mobile : '',
                'emergency_email' => isset($emergencyContact->emergency_contact_email) ? $emergencyContact->emergency_contact_email : ''
            ];
        }

        return view('pages.hr.portal.reports.excel.contactexcel', [
            'dataList' => $dataList
        ]);
    }
}
