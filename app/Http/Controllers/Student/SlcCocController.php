<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlcCocUpdateRequest;
use App\Models\SlcCoc;
use App\Models\SlcCocDocument;
use App\Models\SlcRegistration;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SlcCocController extends Controller
{

    public function store(SlcCocUpdateRequest $request){
        $studen_id = $request->studen_id;
        $student = Student::find($studen_id);

        $slc_registration_id = $request->slc_registration_id;
        $slcRegistration = SlcRegistration::find($slc_registration_id);
        $slc_attendance_id = $request->slc_attendance_id;

        $cocData = [
            'student_id' => $studen_id,
            'student_course_relation_id' => $slcRegistration->student_course_relation_id,
            'course_creation_instance_id' => $slcRegistration->course_creation_instance_id,
            'slc_registration_id' => $slc_registration_id,
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
                $documentName = 'COC_'.$student->applicant_id.'_'.time().'.'.$file->extension();
                $path = $file->storeAs('public/applicants/'.$student->applicant_id, $documentName, 's3');

                $data = [];
                $data['student_id'] = $studen_id;
                $data['hard_copy_check'] = 0;
                $data['doc_type'] = $file->getClientOriginalExtension();
                $data['path'] = Storage::disk('s3')->url($path);
                $data['display_file_name'] = $documentName;
                $data['current_file_name'] = $documentName;
                $data['created_by'] = auth()->user()->id;
                $studentDocument = StudentDocument::create($data);

                if($studentDocument):
                    $cocDocument = SlcCocDocument::create([
                        'student_id' => $studen_id,
                        'slc_coc_id' => $slcCoc->id,
                        'student_document_id' => $studentDocument->id,
                        'created_by' => auth()->user()->id
                    ]);
                endif;
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

        $slcCoc = SlcCoc::find($slc_coc_id);
        $cocData = [
            'confirmation_date' => (isset($request->confirmation_date) && !empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null),
            'coc_type' => $request->coc_type,
            'reason' => $request->reason,
            'actioned' => $request->actioned,
            'updated_by' => auth()->user()->id,
        ];
        $slcCoc->fill($cocData);
        $slcCoc->save();

        if($request->hasFile('document')):
            foreach($request->file('document') as $file):
                $documentName = 'COC_'.$student->applicant_id.'_'.time().'.'.$file->extension();
                $path = $file->storeAs('public/applicants/'.$student->applicant_id, $documentName, 's3');

                $data = [];
                $data['student_id'] = $studen_id;
                $data['hard_copy_check'] = 0;
                $data['doc_type'] = $file->getClientOriginalExtension();
                $data['path'] = Storage::disk('s3')->url($path);
                $data['display_file_name'] = $documentName;
                $data['current_file_name'] = $documentName;
                $data['created_by'] = auth()->user()->id;
                $studentDocument = StudentDocument::create($data);

                if($studentDocument):
                    $cocDocument = SlcCocDocument::create([
                        'student_id' => $studen_id,
                        'slc_coc_id' => $slc_coc_id,
                        'student_document_id' => $studentDocument->id,
                        'created_by' => auth()->user()->id
                    ]);
                endif;
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
        $doc = StudentDocument::find($document_id);

        $slcDocuments = SlcCocDocument::where('student_id', $student_id)->where('student_document_id', $coc_id)->where('student_document_id', $document_id)->forceDelete();
        if(isset($doc->id) && $doc->id > 0):
            if(isset($doc->current_file_name) && !empty($doc->current_file_name) && Storage::disk('s3')->exists('public/applicants/'.$student->applicant_id.'/'.$doc->current_file_name)):
                Storage::disk('s3')->delete('public/applicants/'.$student->applicant_id.'/'.$doc->current_file_name);
            endif;
            StudentDocument::where('id', $document_id)->forceDelete();
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
}
