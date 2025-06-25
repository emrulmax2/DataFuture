<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentAwardStoreRequest;
use App\Models\StudentAward;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    public function store(StudentAwardStoreRequest $request){
        $student_id = $request->student_id;
        $student_course_relation_id = $request->student_course_relation_id;
        $id = (isset($request->id) && $request->id > 0 ? $request->id : 0);

        $certificate_requested = (isset($request->certificate_requested) && !empty($request->certificate_requested) ? $request->certificate_requested : 'No');
        $certificate_received = (isset($request->certificate_received) && !empty($request->certificate_received) ? $request->certificate_received : 'No');
        $certificate_released = (isset($request->certificate_released) && !empty($request->certificate_released) ? $request->certificate_released : 'No');

        $data = [
            'date_of_award' => (!empty($request->date_of_award) ? date('Y-m-d', strtotime($request->date_of_award)) : null),
            'qual_award_type' => ($request->qual_award_type > 0 ? $request->qual_award_type : null),
            'qual_award_result_id' => ($request->qual_award_result_id > 0 ? $request->qual_award_result_id : null),
            'certificate_requested' => $certificate_requested,
            'date_of_certificate_requested' => ($certificate_requested == 'Yes' && !empty($request->date_of_certificate_requested) ? date('Y-m-d', strtotime($request->date_of_certificate_requested)) : null),
            'certificate_requested_by' => ($certificate_requested == 'Yes' && $request->certificate_requested_by > 0 ? $request->certificate_requested_by : null),
            'certificate_received' => $certificate_received,
            'date_of_certificate_received' => ($certificate_received == 'Yes' && !empty($request->date_of_certificate_received) ? date('Y-m-d', strtotime($request->date_of_certificate_received)) : null),
            'certificate_released' => $certificate_released,
            'date_of_certificate_released' => ($certificate_released == 'Yes' && !empty($request->date_of_certificate_released) ? date('Y-m-d', strtotime($request->date_of_certificate_released)) : null),
            'certificate_released_by' => ($certificate_released == 'Yes' && $request->certificate_released_by > 0 ? $request->certificate_released_by : null),
        ];
        if($id > 0):
            $data['updated_by'] = auth()->user()->id;
        
            $studentAward = StudentAward::where('id', $id)->update($data);
            return response()->json(['msg' => 'Award details successfully updated.'], 200);
        else:
            $data['student_id'] = $student_id;
            $data['student_course_relation_id'] = $student_course_relation_id;
            $data['created_by'] = auth()->user()->id;

            $studentAward = StudentAward::create($data);
            return response()->json(['msg' => 'Award details successfully stored.'], 200);
        endif;
    }

    public function edit(Request $request){
        $student_id = $request->student_id;
        $award_id = $request->award_id;

        $award = StudentAward::where('student_id', $student_id)->where('id', $award_id)->get()->first();
        return response()->json(['row' => $award], 200);
    }
}
