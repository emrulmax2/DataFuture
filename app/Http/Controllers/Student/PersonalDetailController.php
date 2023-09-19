<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AdmissionPersonalDetailsRequest;

use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantOtherDetail;
use App\Models\ApplicantDisability;


class PersonalDetailController extends Controller
{
    public function update(AdmissionPersonalDetailsRequest $request){
        $applicant_id = $request->id;
        $applicantOldRow = Applicant::find($applicant_id);
        $otherDetailsOldRow = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();

        $ethnicity_id = $request->ethnicity_id;
        $disability_status = (isset($request->disability_status) && $request->disability_status > 0 ? $request->disability_status : 0);
        $disability_id = ($disability_status == 1 && isset($request->disability_id) && !empty($request->disability_id) ? $request->disability_id : []);
        $disabilty_allowance = ($disability_status == 1 && !empty($disability_id) && (isset($request->disabilty_allowance) && $request->disabilty_allowance > 0) ? $request->disabilty_allowance : 0);

        $request->request->remove('ethnicity_id');
        $request->request->remove('disability_status');
        $request->request->remove('disability_id');
        $request->request->remove('disabilty_allowance');

        $applicant = Applicant::find($applicant_id);
        $applicant->fill($request->input());
        $changes = $applicant->getDirty();
        $applicant->save();

        if($applicant->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicants';
                $data['field_name'] = $field;
                $data['field_value'] = $applicantOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;
        $request->request->remove('id');

        $otherDetails = ApplicantOtherDetail::where('applicant_id', $applicant_id)->first();
        $otherDetails->fill([
            'ethnicity_id' => $ethnicity_id,
            'disability_status' => $disability_status,
            'disability_status' => $disability_status,
            'disabilty_allowance' => $disabilty_allowance,
        ]);
        $changes = $otherDetails->getDirty();
        $otherDetails->save();

        if($otherDetails->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_other_details';
                $data['field_name'] = $field;
                $data['field_value'] = $otherDetailsOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endforeach;
        endif;
        $applicantDisablities = ApplicantDisability::where('applicant_id', $applicant_id)->get();
        $existingIds = [];
        if(!empty($applicantDisablities)):
            foreach($applicantDisablities as $dis):
                $existingIds[] = $dis->disabilitiy_id;
            endforeach;
        endif;
        if($disability_status == 1 && !empty($disability_id)):
            $applicantDisablityDel = ApplicantDisability::where('applicant_id', $applicant_id)->forceDelete();
            foreach($disability_id as $disabilityID):
                $applicantDisabilitiesCr = ApplicantDisability::create([
                    'applicant_id' => $applicant_id,
                    'disabilitiy_id' => $disabilityID,
                    'created_by' => auth()->user()->id,
                ]);
            endforeach;

            $data = [];
            $data['applicant_id'] = $applicant_id;
            $data['table'] = 'applicant_disabilities';
            $data['field_name'] = 'disabilitiy_id';
            $data['field_value'] = implode(',', $existingIds);
            $data['field_new_value'] = implode(',', $disability_id);
            $data['created_by'] = auth()->user()->id;

            ApplicantArchive::create($data);
        else:
            if(!empty($existingIds)):
                $applicantDisablityDel = ApplicantDisability::where('applicant_id', $applicant_id)->forceDelete();
                $data = [];
                $data['applicant_id'] = $applicant_id;
                $data['table'] = 'applicant_disabilities';
                $data['field_name'] = 'disabilitiy_id';
                $data['field_value'] = implode(',', $existingIds);
                $data['field_new_value'] = implode(',', $disability_id);
                $data['created_by'] = auth()->user()->id;

                ApplicantArchive::create($data);
            endif;
        endif;


        return response()->json(['msg' => 'Personal Data Successfully Updated.'], 200);
    }
}
