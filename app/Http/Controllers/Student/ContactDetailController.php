<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\AdmissionContactDetailsRequest;


class ContactDetailController extends Controller
{
    public function update(AdmissionContactDetailsRequest $request){
        $applicant_id = $request->applicant_id;
        $applicant = Applicant::find($applicant_id);
        $contactOldRow = ApplicantContact::find($request->id);
        $email = $request->email;

        $request->request->remove('email');
        $contact = ApplicantContact::find($request->id);
        $contact->fill([
            'home' => $request->phone,
            'mobile' => $request->mobile,
            'address_line_1' => (isset($request->applicant_address_line_1) && !empty($request->applicant_address_line_1) ? $request->applicant_address_line_1 : null),
            'address_line_2' => (isset($request->applicant_address_line_2) && !empty($request->applicant_address_line_2) ? $request->applicant_address_line_2 : null),
            'state' => (isset($request->applicant_address_state) && !empty($request->applicant_address_state) ? $request->applicant_address_state : null),
            'post_code' => (isset($request->applicant_address_postal_zip_code) && !empty($request->applicant_address_postal_zip_code) ? $request->applicant_address_postal_zip_code : null),
            'city' => (isset($request->applicant_address_city) && !empty($request->applicant_address_city) ? $request->applicant_address_city : null),
            'country' => (isset($request->applicant_address_country) && !empty($request->applicant_address_country) ? $request->applicant_address_country : null),
            'updated_by' => auth()->user()->id
        ]);
        $changes = $contact->getDirty();
        $contact->save();

        if($applicant->users->email != $email):
            $tempEmail = ApplicantTemporaryEmail::create([
                'applicant_id' => $applicant_id,
                'email' => $email,
                'status' => 'Pending',
                'created_by' => auth()->user()->id
            ]);
            if($tempEmail):
                $applicantName = $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name;
                $url = route('varify.temp.email', $applicant_id);
                Mail::to($email)->send(new ApplicantTempEmailVerification($applicantName, $applicant->users->email, $email, $url));
            endif;
        endif;

        if($contact->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_contacts';
                $data['field_name'] = $field;
                $data['field_value'] = $contactOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Contact Details Successfully Updated.'], 200);
    }
}
