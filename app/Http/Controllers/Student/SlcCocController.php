<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlcCocUpdateRequest;
use App\Models\SlcAttendance;
use App\Models\SlcCoc;
use App\Models\SlcCocDocument;
use App\Models\SlcRegistration;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\StudentArchive;

class SlcCocController extends Controller
{

    public function store(SlcCocUpdateRequest $request){
        $studen_id = $request->studen_id;
        $student = Student::find($studen_id);

        $slc_registration_id = (isset($request->slc_registration_id) && $request->slc_registration_id > 0 ? $request->slc_registration_id : 0);
        $slcRegistration = SlcRegistration::find($slc_registration_id);
        $slc_attendance_id = $request->slc_attendance_id;

        $cocData = [
            'student_id' => $studen_id,
            'student_course_relation_id' => (isset($slcRegistration->student_course_relation_id) && $slcRegistration->student_course_relation_id > 0 ? $slcRegistration->student_course_relation_id : (isset($student->activeCR->id) && $student->activeCR->id > 0 ? $student->activeCR->id : null)),
            'course_creation_instance_id' => (isset($slcRegistration->course_creation_instance_id) && $slcRegistration->course_creation_instance_id > 0 ? $slcRegistration->course_creation_instance_id : null),
            'slc_registration_id' => ($slc_registration_id > 0 ? $slc_registration_id : null),
            'slc_attendance_id' => ($slc_attendance_id > 0 ? $slc_attendance_id : null),
            'confirmation_date' => (isset($request->confirmation_date) && !empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null),
            'coc_type' => $request->coc_type,
            'reason' => $request->reason,
            'actioned' => $request->actioned,
            'created_by' => auth()->user()->id,
        ];
        $slcCoc = SlcCoc::create($cocData);

        if($slcCoc && $request->hasFile('document')):
            foreach($request->file('document') as $file):
                $documentName = 'COC_'.$studen_id.'_'.time().'.'.$file->extension();
                $path = $file->storeAs('public/students/'.$studen_id, $documentName, 's3');

                $data = [];
                $data['student_id'] = $studen_id;
                $data['slc_coc_id'] = $slcCoc->id;
                $data['hard_copy_check'] = 0;
                $data['doc_type'] = $file->getClientOriginalExtension();
                $data['path'] = Storage::disk('s3')->url($path);
                $data['display_file_name'] = $documentName;
                $data['current_file_name'] = $documentName;
                $data['created_by'] = auth()->user()->id;
                $SlcCocDocument = SlcCocDocument::create($data);
            endforeach;
        endif;

        return response()->json(['res' => 'Success'], 200);
    }


    public function edit(Request $request){
        $coc_id = $request->coc_id;
        $coc = SlcCoc::find($coc_id);

        return response()->json(['res' => $coc], 200);
    }

    public function update(SlcCocUpdateRequest $request){
        $studen_id = $request->studen_id;
        $student = Student::find($studen_id);

        $slc_coc_id = $request->slc_coc_id;

        $cocOldRow = SlcCoc::find($slc_coc_id);
        
        $slcCoc = SlcCoc::find($slc_coc_id);
        $cocData = [
            'confirmation_date' => (isset($request->confirmation_date) && !empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null),
            'coc_type' => $request->coc_type,
            'reason' => $request->reason,
            'actioned' => $request->actioned,
            'updated_by' => auth()->user()->id,
        ];
        $slcCoc->fill($cocData);
        $changes = $slcCoc->getDirty();
        $slcCoc->save();

        if($slcCoc->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                StudentArchive::create([
                    'student_id' => $studen_id,
                    'table' => 'slc_cocs',
                    'field_name' => $field,
                    'field_value' => $cocOldRow->$field,
                    'field_new_value' => $value,
                    'created_by' => auth()->user()->id
                ]);
            endforeach;
        endif;

        if($request->hasFile('document')):
            foreach($request->file('document') as $file):
                $documentName = 'COC_'.$studen_id.'_'.time().'.'.$file->extension();
                $path = $file->storeAs('public/students/'.$studen_id, $documentName, 's3');

                $data = [];
                $data['student_id'] = $studen_id;
                $data['slc_coc_id'] = $slc_coc_id;
                $data['hard_copy_check'] = 0;
                $data['doc_type'] = $file->getClientOriginalExtension();
                $data['path'] = Storage::disk('s3')->url($path);
                $data['display_file_name'] = $documentName;
                $data['current_file_name'] = $documentName;
                $data['created_by'] = auth()->user()->id;
                $SlcCocDocument = SlcCocDocument::create($data);
            endforeach;
        endif;

        return response()->json(['res' => 'Success'], 200);
    }

    public function destroyCocDocument(Request $request){
        $student_id = $request->student;
        $student = Student::find($student_id);
        $theids = explode('_', $request->recordid);
        $coc_id = $theids[0];
        $document_id = $theids[1];
        $doc = SlcCocDocument::find($document_id);

        if(isset($doc->id) && $doc->id > 0):
            if(isset($doc->current_file_name) && !empty($doc->current_file_name) && Storage::disk('s3')->exists('public/students/'.$student->id.'/'.$doc->current_file_name)):
                Storage::disk('s3')->delete('public/students/'.$student->id.'/'.$doc->current_file_name);
            endif;
            SlcCocDocument::where('id', $doc->id)->delete();
        endif;

        return response()->json(['res' => 'Success'], 200);
    }

    public function destroy(Request $request){
        $student_id = $request->student;
        $coc_id = $request->recordid;

        SlcCocDocument::where('student_id', $student_id)->where('slc_coc_id', $coc_id)->delete();
        SlcCoc::where('student_id', $student_id)->where('id', $coc_id)->delete();

        return response()->json(['res' => 'Success'], 200);
    }

    public function syncCocToAttendance(Request $request){
        $student = $request->student;
        $ids = (isset($request->recordid) && !empty($request->recordid) ? explode('_', $request->recordid) : []);
        if(!empty($ids) && count($ids)):
            $atn_id = (isset($ids[0]) && $ids[0] > 0 ? $ids[0] : 0);
            $coc_id = (isset($ids[1]) && $ids[1] > 0 ? $ids[1] : 0);
            if($atn_id > 0 && $coc_id > 0):
                $attendance = SlcAttendance::find($atn_id);
                $slc_registration_id = (isset($attendance->slc_registration_id) && $attendance->slc_registration_id > 0 ? $attendance->slc_registration_id : null);

                SlcCoc::where('id', $coc_id)->update(['slc_registration_id' => $slc_registration_id, 'slc_attendance_id' => $atn_id]);
                return response()->json(['res' => 'Error'], 200);
            else:
                return response()->json(['res' => 'Error'], 422);
            endif;
        else:
            return response()->json(['res' => 'Error'], 422);
        endif;
    }
}
