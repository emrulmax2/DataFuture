<?php

namespace App\Http\Controllers\Student\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudetnAttendanceExcuseRequest;
use App\Models\AttendanceExcuse;
use App\Models\AttendanceExcuseDay;
use App\Models\AttendanceExcuseDocument;
use App\Models\StudentTask;
use App\Models\TaskList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceExcuseController extends Controller
{
    public function store(StudetnAttendanceExcuseRequest $request){
        $student_id = $request->student_id;
        $excuses = (isset($request->excuses) && !empty($request->excuses) ? $request->excuses : []);
        $reason = (isset($request->reason) && !empty($request->reason) ? $request->reason : '');

        if(!empty($excuses)):
            $data = [];
            $data['student_id'] = $student_id;
            $data['reason'] = $reason;
            $data['status'] = 'Pending';
            $data['created_by'] = auth('student')->user()->id;

            $attnExcuse = AttendanceExcuse::create($data);
            if($attnExcuse):
                foreach($excuses as $plan_id => $plan_dates):
                    if(!empty($plan_dates)):
                        foreach($plan_dates as $plan_date_id):
                            $data = [];
                            $data['attendance_excuse_id'] = $attnExcuse->id;
                            $data['plan_id'] = $plan_id;
                            $data['plans_date_list_id'] = $plan_date_id;
                            $data['created_by'] = auth('student')->user()->id;

                            AttendanceExcuseDay::create($data);
                        endforeach;
                    endif;
                endforeach;

                if($request->hasFile('document')):
                    foreach($request->file('document') as $file):
                        $documentName = 'EXC_'.$student_id.'_'.time().'.'.$file->extension();
                        $path = $file->storeAs('public/students/'.$student_id, $documentName, 's3');
        
                        $data = [];
                        $data['attendance_excuse_id'] = $attnExcuse->id;
                        $data['hard_copy_check'] = 0;
                        $data['doc_type'] = $file->getClientOriginalExtension();
                        $data['path'] = Storage::disk('s3')->url($path);
                        $data['display_file_name'] = str_replace('.'.$file->extension(), '', $file->getClientOriginalName());
                        $data['current_file_name'] = $documentName;
                        $data['created_by'] = auth('student')->user()->id;
                        AttendanceExcuseDocument::create($data);
                    endforeach;
                endif;

                $excuseTask = TaskList::where('attendance_excuses', 'Yes')->orderBy('id', 'desc')->get()->first();
                if(isset($excuseTask->id) && $excuseTask->id > 0):
                    $studentTask = StudentTask::create([
                        'student_id' => $student_id,
                        'task_list_id' => $excuseTask->id,
                        'status' => 'Pending',
                        'created_by' => 1,
                    ]);
                    if($studentTask):
                        AttendanceExcuse::where('id', $attnExcuse->id)->update(['student_task_id' => $studentTask->id]);
                    endif;
                endif;

                return response()->json(['message' => 'Attendance excuse successfully submitted for review.'], 200);
            else:
                return response()->json(['message' => 'Something went wrong!'], 400);
            endif;
        else:
            return response()->json(['message' => 'Excuse not found'], 422);
        endif;
    }
}
