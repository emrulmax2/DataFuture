<?php

namespace App\Http\Controllers\Student\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentContactDetailsRequest;
use App\Http\Requests\StudentFrontendContactDetailsRequest;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentConsent;
use App\Models\StudentContact;
use Illuminate\Http\Request;

class ContactDetailController extends Controller
{
    public function update(StudentFrontendContactDetailsRequest $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $contactOldRow = StudentContact::find($request->id);
        $email = $request->email;

        $request->request->remove('email');
        $contact = StudentContact::find($request->id);
        $contact->fill([
            'home' => $request->phone,
            'term_time_address_id' => (isset($request->term_time_address_id) && $request->term_time_address_id > 0 ? $request->term_time_address_id : null),
            'term_time_accommodation_type_id' => (isset($request->term_time_accommodation_type_id) && $request->term_time_accommodation_type_id > 0 ? $request->term_time_accommodation_type_id : null),
            'permanent_address_id' => (isset($request->permanent_address_id) && $request->permanent_address_id > 0 ? $request->permanent_address_id : null),
            'updated_by' => auth('student')->user()->id
        ]);
        $changes = $contact->getDirty();
        $contact->save();

        if($contact->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'student_contacts';
                $data['field_name'] = $field;
                $data['field_value'] = $contactOldRow->$field;
                $data['field_new_value'] = $value;
                $data['student_user_id'] = auth('student')->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Contact Details Successfully Updated.'], 200);
    }
}
