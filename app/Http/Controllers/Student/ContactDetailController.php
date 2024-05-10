<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\StudentContactDetailsRequest;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentContact;
use App\Models\StudentUser;

class ContactDetailController extends Controller
{
    public function update(StudentContactDetailsRequest $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $contactOldRow = StudentContact::find($request->id);
        $email = $request->email;

        $request->request->remove('email');
        $contact = StudentContact::find($request->id);
        $contact->fill([
            'home' => $request->phone,
            'mobile' => $request->mobile,
            'personal_email_verification' => $request->personal_email_verification,
            'personal_email' => $request->personal_email,
            'institutional_email' => $request->org_email,
            'term_time_address_id' => (isset($request->term_time_address_id) && $request->term_time_address_id > 0 ? $request->term_time_address_id : null),
            'term_time_accommodation_type_id' => (isset($request->term_time_accommodation_type_id) && $request->term_time_accommodation_type_id > 0 ? $request->term_time_accommodation_type_id : null),
            'term_time_post_code' => (isset($request->term_time_post_code) && !empty($request->term_time_post_code) ? $request->term_time_post_code : null),
            'permanent_address_id' => (isset($request->permanent_address_id) && $request->permanent_address_id > 0 ? $request->permanent_address_id : null),
            'permanent_country_id' => (isset($request->permanent_country_id) && $request->permanent_country_id > 0 ? $request->permanent_country_id : null),
            'permanent_post_code' => (isset($request->permanent_post_code) && !empty($request->permanent_post_code) ? $request->permanent_post_code : null),
            'updated_by' => auth()->user()->id
        ]);
        $changes = $contact->getDirty();
        $contact->save();

        if(isset($student->users->email) && $student->users->email != $request->org_email && $student->student_user_id > 0):
            StudentUser::where('id', $student->student_user_id)->update(['email' => $request->org_email]);
        endif;

        if($contact->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'student_contacts';
                $data['field_name'] = $field;
                $data['field_value'] = $contactOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Contact Details Successfully Updated.'], 200);
    }
}
