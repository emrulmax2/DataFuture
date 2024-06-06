<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeWorkPatternRequest;
use App\Http\Requests\EmployeeWorkPatterUpdateRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\Employee;
use App\Models\EmployeeArchive;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\EmployeeWorkingPatternPay;
use Illuminate\Http\Request;

class EmployeeWorkingPatternController extends Controller
{
    public function list(Request $request){
        $status = (isset($request->status) ? $request->status : 1);
        $employee_id = (isset($request->employee_id) && $request->employee_id > 0 ? $request->employee_id : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeWorkingPattern::orderByRaw(implode(',', $sorts))->where('employee_id', $employee_id);
        if($status == 2):
            $query->onlyTrashed();
        else:
            $query->where('active', $status);
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'effective_from' => date('jS M, Y', strtotime($list->effective_from)),
                    'end_to' => (!empty($list->end_to) ? date('jS M, Y', strtotime($list->end_to)) : ''),
                    'contracted_hour' => $list->contracted_hour,
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at,
                    'has_days' => (isset($list->patterns) ? $list->patterns->count() : 0),
                    'has_pays' => (isset($list->pays) ? $list->pays->count() : 0)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(EmployeeWorkPatternRequest $request){
        $employee_id = $request->employee_id;
        $employee = Employee::find($employee_id);
        $oldPattern = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)->orderBy('id', 'DESC')->get()->first();

        $active = (isset($request->active) && $request->active > 0 ? $request->active : 0);
        $salary = (isset($request->salary) ? $request->salary : 0);
        $hourlyRate = (isset($request->hourly_rate) ? $request->hourly_rate : 0);
        $effectiveFrom = (isset($request->effective_from) && !empty($request->effective_from) ? date('Y-m-d', strtotime($request->effective_from)) : null);
        $endTo = (isset($request->end_to) && !empty($request->end_to) ? date('Y-m-d', strtotime($request->end_to)) : NULL);

        $data = [];
        $data['employee_id'] = $employee_id;
        $data['effective_from'] = $effectiveFrom;
        $data['end_to'] = $endTo;
        $data['contracted_hour'] = (isset($request->contracted_hour) ? $request->contracted_hour : null);
        $data['active'] = $active;
        $data['created_by'] = auth()->user()->id;

        $pattern = EmployeeWorkingPattern::create($data);
        if($pattern):
            $data = [];
            $data['employee_working_pattern_id'] = $pattern->id;
            $data['effective_from'] = $effectiveFrom;
            $data['end_to'] = $endTo;
            $data['salary'] = $salary;
            $data['hourly_rate'] = $hourlyRate;
            $data['active'] = $active;
            $data['created_by'] = auth()->user()->id;

            EmployeeWorkingPatternPay::create($data);
            if($active == 1):
                if(isset($oldPattern->id) && $oldPattern->id > 0):
                    $old_pattern_id = $oldPattern->id;
                    $leave_ids = EmployeeLeaveDay::where('status', 'Active')->where('leave_date', '>=', $effectiveFrom)
                                ->whereHas('leave', function($q) use($employee_id, $old_pattern_id){
                                    $q->where('employee_working_pattern_id', $old_pattern_id)->where('employee_id', $employee_id)->whereIn('status', ['Pending', 'Approved']);
                                })->pluck('employee_leave_id')->unique()->toArray();
                    if(!empty($leave_ids)):
                        $commonSmtp = ComonSmtp::where('smtp_user', 'internal@lcc.ac.uk')->get()->first();
                        $configuration = [
                            'smtp_host'         => $commonSmtp->smtp_host,
                            'smtp_port'         => $commonSmtp->smtp_port,
                            'smtp_username'     => $commonSmtp->smtp_user,
                            'smtp_password'     => $commonSmtp->smtp_pass,
                            'smtp_encryption'   => $commonSmtp->smtp_encryption,

                            'from_email'         => $commonSmtp->smtp_user,
                            'from_name'         => 'HR Department London Churchill College',
                        ];

                        $days = EmployeeLeaveDay::whereIn('employee_leave_id', $leave_ids)->where('status', 'Active')->where('leave_date', '>=', $effectiveFrom)
                                ->whereHas('leave', function($q) use($employee_id, $old_pattern_id){
                                    $q->where('employee_working_pattern_id', $old_pattern_id)->where('employee_id', $employee_id)->whereIn('status', ['Pending', 'Approved']);
                                })->orderBy('leave_date', 'ASC')->get();
                        EmployeeLeave::whereIn('id', $leave_ids)->update(['status' => 'Canceled']);
                        

                        $empMessage = 'Hi '.$employee->full_name.', <br/><br/>';
                        $empMessage .= 'Some of your leave days have been canceled due to new working pattern creation. Here are the details:';
                        $empMessage .= '<ul>';
                            foreach($days as $day):
                                EmployeeLeaveDay::where('id', $day->id)->update(['status' => 'In Active']);
                                $empMessage .= '<li>'.date('jS F, Y', strtotime($day->leave_date)).'</li>';
                            endforeach;
                        $empMessage .= '</ul>';
                        $empMessage .= 'Please contact with the HR Manager to resolve this issue.<br/><br/>';
                        $empMessage .= 'Thanks & Regards<br/>';
                        $empMessage .= 'HR London Churchill College';

                        $configuration['from_email'] = 'hr@lcc.ac.uk';
                        UserMailerJob::dispatch($configuration, [$employee->employment->email], new CommunicationSendMail('Canceled Leave Days', $empMessage, []));

                        $hrMessage = 'Hi Dear, <br/><br/>';
                        $hrMessage .= "Some of ".$employee->full_name."'s leave days have been canceled due to new working pattern creation. Here are the details:";
                        $hrMessage .= '<ul>';
                            foreach($days as $day):
                                $hrMessage .= '<li>'.date('jS F, Y', strtotime($day->leave_date)).'</li>';
                            endforeach;
                        $hrMessage .= '</ul>';
                        $hrMessage .= 'Please contact with '.$employee->full_name.' and resolve this issue.<br/><br/>';
                        $hrMessage .= 'Thanks & Regards<br/>';
                        $hrMessage .= 'HR London Churchill College';
                        
                        $configuration['from_email'] = 'internal@lcc.ac.uk';
                        UserMailerJob::dispatch($configuration, ['hr@lcc.ac.uk'], new CommunicationSendMail('Canceled Leave Days', $hrMessage, []));
                    endif;
                endif;
                EmployeeWorkingPattern::where('employee_id', $employee_id)->where('id', '!=', $pattern->id)->where('active', 1)->update(['active' => 0]);
            endif;
        endif;

        return response()->json(['msg' => 'Data successfully inserted.'], 200);
    }

    public function edit(Request $request){
        $id = $request->editId;
        $pattern = EmployeeWorkingPattern::find($id);
        $pattern['efffected_from_modified'] = (isset($pattern->effective_from) && !empty($pattern->effective_from) ? date('Y-m-d', strtotime($pattern->effective_from)) : '');

        return response()->json(['res' => $pattern], 200);
    }


    public function update(EmployeeWorkPatterUpdateRequest $request){
        $employee_id = $request->employee_id;
        $id = $request->id;
        $employeeWorkingPatternOld = EmployeeWorkingPattern::find($id);

        $active = (isset($request->active) && $request->active > 0 ? $request->active : 0);
        $end_to = (isset($request->end_to) && !empty($request->end_to) ? date('Y-m-d', strtotime($request->end_to)) : Null);
        $active = (!empty($end_to) && $end_to < date('Y-m-d') ? 0 : $active);

        $data = [];
        $data['employee_id'] = $employee_id;
        $data['effective_from'] = (isset($request->effective_from) && !empty($request->effective_from) ? date('Y-m-d', strtotime($request->effective_from)) : null);
        $data['end_to'] = (isset($request->end_to) && !empty($request->end_to) ? date('Y-m-d', strtotime($request->end_to)) : NULL);
        $data['contracted_hour'] = (isset($request->contracted_hour) ? $request->contracted_hour : null);
        $data['active'] = $active;
        $data['updated_by'] = auth()->user()->id;

        $employeeWorkingPattern = EmployeeWorkingPattern::find($id);
        $employeeWorkingPattern->fill($request->input());
        $changes = $employeeWorkingPattern->getDirty();
        $employeeWorkingPattern->save();

        if($employeeWorkingPattern->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['employee_id'] = $employee_id;
                $data['table'] = 'employee_bank_details';
                $data['row_id'] = $id;
                $data['field_name'] = $field;
                $data['field_value'] = $employeeWorkingPatternOld->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                EmployeeArchive::create($data);
            endforeach;
        endif;

        if($active == 1):
            EmployeeWorkingPattern::where('employee_id', $employee_id)->where('id', '!=', $id)->where('active', 1)->update(['active' => 0]);
        endif;

        return response()->json(['msg' => 'Data successfully inserted.'], 200);
    }

    public function destroy($id){
        EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $id)->delete();
        $data = EmployeeWorkingPattern::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $id)->withTrashed()->restore();
        $data = EmployeeWorkingPattern::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
