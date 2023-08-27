<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\User;
use App\Models\ApplicantNote;
use App\Models\ApplicantDocument;
use App\Models\DocumentSettings;
use App\Models\ApplicantEmployment;
use App\Models\EmploymentReference;
use App\Models\ApplicantTask;
use App\Models\ApplicantEmail;
use App\Models\ComonSmtp;
use App\Models\ApplicantSms;
use PDF;

class ApplicantProfilePrintController extends Controller
{
    public function generatePDF($applicantId)
    {
        $applicant = Applicant::find($applicantId)->first();
        $applicantPendingTask = ApplicantTask::where('applicant_id', $applicantId)->where('status', 'Pending')->get();
        $applicantCompletedTask = ApplicantTask::where('applicant_id', $applicantId)->where('status', 'Completed')->get();
        $applicant->load(['title','notes','quals','employment','emails','sms']);
 
        $notes = $applicant->notes;

        $i = 0;
        $noteList =[];

        foreach($notes as $data) {  
            $applicantDocument = ApplicantDocument::find($data->applicant_document_id);
            //dd($applicantDocument); 
            $documents = DocumentSettings::find($applicantDocument->document_setting_id);     
            $applicantUser = User::find($data->created_by);
            $uploadedBy = User::find($applicantDocument->created_by);
            
            $quals = $applicant->quals;
            
            $employment = $applicant->employment;
            $emails = $applicant->emails;
            $emailsender = User::find($emails[0]['created_by']);
            
            $sms = $applicant->sms;
            $smsby = User::find($sms[0]['created_by']);
            $smtpUser = ComonSmtp::find($emails[0]['comon_smtp_id']);
  
            $employeeReference = EmploymentReference::find($employment[0]['id']);

            $noteList[$i++] = [
                'id'=>$data->id,
                'note'=>$data->note,
           	    'created_at'=>date("d/m/Y",strtotime($data->created_at)),
                'created_by' => $applicantUser->name,
                'hard_copy_check' => $applicantDocument->hard_copy_check,
                'uploaded_by' => $uploadedBy,
                'document_id' => $documents->id,
                'document_name' => $documents->name,
                'document_link' => $applicantDocument->current_file_name,
                'applicant_id' => $applicantDocument->applicant_id,
                'qual_id' => $quals[0]['id'],
                'awarding_body' => $quals[0]['awarding_body'],
                'highest_academic' => $quals[0]['highest_academic'],
                'subjects' => $quals[0]['subjects'],
                'result' => $quals[0]['result'],
                'degree_award_date' => $quals[0]['degree_award_date'],
                'employment_id' => $employment[0]['id'],
                'company_name' => $employment[0]['company_name'],
                'company_phone' => $employment[0]['company_phone'],
                'position' => $employment[0]['position'],
                'start_date' => ($employment[0]['start_date']),
                'end_date' => ($employment[0]['end_date']),
                'address' => $employment[0]['address_line_1'].','.$employment[0]['address_line_2'].','.$employment[0]['state'].','.$employment[0]['post_code'].','.$employment[0]['city'].','.$employment[0]['country'],
                'reference' => $employeeReference['name'],
                'reference_position' => $employeeReference['position'],
                'reference_phone' => $employeeReference['phone'],
                'email_subject' => $emails[0]['subject'],
                'email_by' => $emailsender->name,
                'smtp_user' => $smtpUser->smtp_user,
                'sms_subject' => $sms[0]['subject'],
                'sms_by' => $smsby->name,
            ];
        }

        $pdf = PDF::loadView(
                'pages.students.admission.applicantprofile', 
                compact('applicant','noteList','applicantPendingTask','applicantCompletedTask')
            )->setPaper('a4', 'portrait')
            ->setWarnings(false);
            
        return $pdf->download("$applicant->first_name $applicant->last_name profile.pdf");
        //return view('pages.students.admission.applicantprofile', compact('applicant','noteList','applicantPendingTask','applicantCompletedTask'));
    }
}
