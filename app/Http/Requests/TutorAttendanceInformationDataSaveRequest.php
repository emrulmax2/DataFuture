<?php

namespace App\Http\Requests;

use App\Models\AttendanceInformation;
use App\Models\PlansDateList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TutorAttendanceInformationDataSaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        if (!empty($this->plan_date_list_id)) {
            $planDateList = PlansDateList::find($this->plan_date_list_id);
            $attendanceInformation = AttendanceInformation::where('plans_date_list_id',$this->plan_date_list_id)->get()->first();
            if(!$attendanceInformation)
                if($planDateList->plan->tutor_id != Auth::user()->id) {
                    return [
                        "note" =>  'required'
                    ];
                }
        }
        return [
            
        ];
    }
}
