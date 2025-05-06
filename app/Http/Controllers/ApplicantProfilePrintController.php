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
use Illuminate\Support\Facades\Storage;

class ApplicantProfilePrintController extends Controller
{
    public function generatePDF($applicantId)
    {
        $applicant = Applicant::find($applicantId);
        $applicantPendingTask = ApplicantTask::where('applicant_id', $applicantId)->where('status', 'Pending')->get();
        $applicantCompletedTask = ApplicantTask::where('applicant_id', $applicantId)->where('status', 'Completed')->get();
        $applicant->load(['title', 'notes', 'quals', 'employment', 'emails', 'sms']);

        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>Application of '.$applicant->first_name.' '.$applicant->last_name.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: rgb(30, 41, 59);}
                                table{margin-left: 0px; border-collapse: collapse; width: 100%;}
                                figure{margin: 0;}
                                @page{margin-top: 115px;margin-left: 30px;margin-right: 30px;margin-bottom: 30px;}
                                header{position: fixed;left: 0px;right: 0px;height: 90px;margin-top: -90px;}
                                
                                .regInfoRow td{border-top: 1px solid gray;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}
                                .btn{display: inline-block; font-size: 10px; line-height: normal; font-weight: bold; color: #FFF; background: rgb(22 78 99); padding: 2px 5px; text-align: center;}
                                .btn-success{background: rgb(13 148 13);}
                                .btn-danger{background: rgb(185 28 28);}

                                .bodyContainer{font-size: 13px; line-height: normal; padding: 0 30px;}
                                .tableTitle{font-size: 22px; font-weight: bold; color: #000; line-height: 22px; margin: 0;}
                                .employeeInfo{line-height: normal;}
                                .mb-30{margin-bottom: 30px;}
                                .mb-20{margin-bottom: 20px;}
                                .mb-15{margin-bottom: 15px;}
                                .mb-10{margin-bottom: 10px;}
                                .text-justify{text-align: justify;}
                            
                                .table {width: 100%; text-align: left; text-indent: 0; border-color: inherit; border-collapse: collapse;}
                                .table th {border-style: solid;border-color: #e5e7eb;border-bottom-width: 2px;padding-left: 1.25rem;padding-right: 1.25rem;padding-top: 0.75rem;padding-bottom: 0.75rem;font-weight: 500;}
                                .table td {border-style: solid;border-color: #e5e7eb; border-bottom-width: 1px;padding-left: 1.25rem;padding-right: 1.25rem;padding-top: 0.75rem;padding-bottom: 0.75rem;}

                                .table.table-bordered th, .table.table-bordered td {border-left-width: 1px;border-right-width: 1px;border-top-width: 1px;}

                                .table.table-sm th {padding-left: 1rem;padding-right: 1rem;padding-top: 0.5rem;padding-bottom: 0.5rem;}
                                .table.table-sm td {padding-left: 1rem;padding-right: 1rem;padding-top: 0.5rem;padding-bottom: 0.5rem;}

                                .barTitle{padding: 5px 10px; background: rgb(226, 232, 240); font-size: 14px; font-weight: bold; line-height: normal;}
                                .spacer{padding: 5px 0 6px;}
                                .theLabel{vertical-align: top; padding: 0 10px 15px; width: 20%; font-weight: medium; font-size: 13px; color: rgb(100, 116, 139); line-height: normal;}
                                .theValue{vertical-align: top; padding: 0 10px 15px; width: 30%; font-weight: medium; font-size: 13px; color: rgb(30, 41, 59); line-height: normal;}
                                .theValue.tv-large{width: 80%;}

                                .pdfList{margin: 0; padding: 0 0 0 10px; }
                                .pdfList li{margin: 0 0 3px; font-size: 12px; line-height: normal; color: rgb(100, 116, 139);}
                            </style>';
            $PDFHTML .= '</head>';
            $PDFHTML .= '<body>';

            $PDFHTML .= '<header>';
                $PDFHTML .= '<table>';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td>';
                            $PDFHTML .= '<img style="height: 60px; width: atuo;" src="https://datafuture2.lcc.ac.uk/limon/LCC-Logo-01-croped.png"/>';
                        $PDFHTML .= '</td>';
                        $PDFHTML .= '<td class="text-right">';//'https://datafuture2.lcc.ac.uk/limon/avatar.png'
                            $PDFHTML .= '<img style="height: 60px; width: auto;" alt="'.$applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name.'" src="'.(isset($applicant->photo) && !empty($applicant->photo) && Storage::disk('local')->exists('public/applicants/'.$applicant->id.'/'.$applicant->photo) ? public_path('storage/applicants/'.$applicant->id.'/'.$applicant->photo) : asset('build/assets/images/placeholders/200x200.jpg')).'">';
                            $PDFHTML .= '<span style="font-size: 10px; padding: 3px 0 0; font-weight: 700; display: block;">'.(!empty($applicant->application_no) ? $applicant->application_no : '').'</span>';
                        $PDFHTML .= '</td>';
                    $PDFHTML .= '</tr>';
                $PDFHTML .= '</table>';
            $PDFHTML .= '</header>';

            /*PDF Body Start Here*/
            $PDFHTML .= '<table class="mb-10">';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="barTitle text-center">UK/EU Student Application</td>';
                $PDFHTML .= '</tr>';
            $PDFHTML .= '</table>';

            $PDFHTML .= '<table class="mb-10">';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td colspan="4" class="barTitle text-left">Personal Details</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr><td class="spacer" colspan="4"></td></tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel">Name</td>';
                    $PDFHTML .= '<td class="theValue">'.$applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name.'</td>';
                
                    $PDFHTML .= '<td class="theLabel">Date of Birth</td>';
                    $PDFHTML .= '<td class="theValue">'.date('jS F, Y', strtotime($applicant->date_of_birth)).'</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel">Gender</td>';
                    $PDFHTML .= '<td class="theValue">'.(isset($applicant->sexid->name) && !empty($applicant->sexid->name) ? $applicant->sexid->name : '').'</td>';
                    $PDFHTML .= '<td class="theLabel">Nationality</td>';
                    $PDFHTML .= '<td class="theValue">'.$applicant->nation->name.'</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel">Country of Birth</td>';
                    $PDFHTML .= '<td class="theValue">'.$applicant->country->name.'</td>';
                    $PDFHTML .= '<td class="theLabel">Ethnicity</td>';
                    $PDFHTML .= '<td class="theValue">'.$applicant->other->ethnicity->name.'</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel">Disability Status</td>';
                    $PDFHTML .= '<td class="theValue">'.(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1 ? '<span class="btn btn-success">Yes</span>' : '<span class="btn btn-danger">No</span>').'</td>';
                    if(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1):
                        $PDFHTML .= '<td class="theLabel">Allowance Claimed?</td>';
                        $PDFHTML .= '<td class="theValue">'.(isset($applicant->other->disabilty_allowance) && $applicant->other->disabilty_allowance == 1 ? '<span class="btn btn-success">Yes</span>' : '<span class="btn btn-danger">No</span>').'</td>';
                    endif;
                $PDFHTML .= '</tr>';
                if(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1):
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="theLabel">Disabilities</td>';
                        $PDFHTML .= '<td class="theValue tv-large" colspan="3">';
                            if(isset($applicant->disability) && !empty($applicant->disability)):
                                $PDFHTML .= '<ul class="pdfList">';
                                    foreach($applicant->disability as $dis):
                                        $PDFHTML .= '<li><span></span>'.$dis->disabilities->name.'</li>';
                                    endforeach;
                                $PDFHTML .= '</ul>';
                            endif;
                        $PDFHTML .= '</td>';
                    $PDFHTML .= '</tr>';
                endif;

                /* Contact Details */
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td colspan="4" class="barTitle text-left">Contact Details</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr><td class="spacer" colspan="4"></td></tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel">Email</td>';
                    $PDFHTML .= '<td class="theValue">'.$applicant->users->email.'</td>';
                    $PDFHTML .= '<td class="theLabel">Home Phone</td>';
                    $PDFHTML .= '<td class="theValue">'.$applicant->contact->home.'</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel">Mobile</td>';
                    $PDFHTML .= '<td class="theValue">'.$applicant->contact->mobile.'</td>';
                    $PDFHTML .= '<td class="theLabel">Address</td>';
                    $PDFHTML .= '<td class="theValue">';
                        if(isset($applicant->contact->address_line_1) && !empty($applicant->contact->address_line_1)):
                            $PDFHTML .= $applicant->contact->address_line_1.'<br/>';
                        endif;
                        if(isset($applicant->contact->address_line_2) && !empty($applicant->contact->address_line_2)):
                            $PDFHTML .= $applicant->contact->address_line_2.'<br/>';
                        endif;
                        if(isset($applicant->contact->city) && !empty($applicant->contact->city)):
                            $PDFHTML .= $applicant->contact->city.', ';
                        endif;
                        if(isset($applicant->contact->state) && !empty($applicant->contact->state)):
                            $PDFHTML .= $applicant->contact->state.', <br/>';
                        endif;
                        if(isset($applicant->contact->post_code) && !empty($applicant->contact->post_code)):
                            $PDFHTML .= $applicant->contact->post_code.',';
                        endif;
                        if(isset($applicant->contact->country) && !empty($applicant->contact->country)):
                            $PDFHTML .= $applicant->contact->country;
                        endif;
                    $PDFHTML .= '</td>';
                $PDFHTML .= '</tr>';

                /* Next of Kin */
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td colspan="4" class="barTitle text-left">Next of Kin</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr><td class="spacer" colspan="4"></td></tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel">Name</td>';
                    $PDFHTML .= '<td class="theValue">'.$applicant->kin->name.'</td>';
                    $PDFHTML .= '<td class="theLabel">Relation</td>';
                    $PDFHTML .= '<td class="theValue">'.(isset($applicant->kin->relation->name) ? $applicant->kin->relation->name : '').'</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel">Mobile</td>';
                    $PDFHTML .= '<td class="theValue">'.$applicant->kin->mobile.'</td>';
                    $PDFHTML .= '<td class="theLabel">Email</td>';
                    $PDFHTML .= '<td class="theValue">'.(isset($applicant->kin->email) && !empty($applicant->kin->email) ? $applicant->kin->email : '---').'</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel">Address</td>';
                    $PDFHTML .= '<td class="theValue">';
                        if(isset($applicant->kin->address_line_1) && !empty($applicant->kin->address_line_1)):
                            $PDFHTML .= $applicant->kin->address_line_1.'<br/>';
                        endif;
                        if(isset($applicant->kin->address_line_2) && !empty($applicant->kin->address_line_2)):
                            $PDFHTML .= $applicant->kin->address_line_2.'<br/>';
                        endif;
                        if(isset($applicant->kin->city) && !empty($applicant->kin->city)):
                            $PDFHTML .= $applicant->kin->city.', ';
                        endif;
                        if(isset($applicant->kin->state) && !empty($applicant->kin->state)):
                            $PDFHTML .= $applicant->kin->state.', <br/>';
                        endif;
                        if(isset($applicant->kin->post_code) && !empty($applicant->kin->post_code)):
                            $PDFHTML .= $applicant->kin->post_code.',';
                        endif;
                        if(isset($applicant->kin->country) && !empty($applicant->kin->country)):
                            $PDFHTML .= $applicant->kin->country;
                        endif;
                    $PDFHTML .= '</td>';
                    $PDFHTML .= '<td class="theLabel"></td>';
                    $PDFHTML .= '<td class="theValue"></td>';
                $PDFHTML .= '</tr>';

                /* Proposed Course */
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td colspan="4" class="barTitle text-left">Proposed Course</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr><td class="spacer" colspan="4"></td></tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">When would you like to start your course?</td>';
                    $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.$applicant->course->semester->name.'</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">Which course do you propose to take?</td>';
                    $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.$applicant->course->creation->course->name.'</td>';
                $PDFHTML .= '</tr>';
                if(isset($applicant->course->venue) && !empty($applicant->course->venue)):
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">Which venue do you want to study?</td>';
                    $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'. $applicant->course->venue->name.'</td>';
                $PDFHTML .= '</tr>';
                endif;
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">How are you funding your education at London Churchill College?</td>';
                    $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.$applicant->course->student_loan.'</td>';
                $PDFHTML .= '</tr>';
                if($applicant->course->student_loan == 'Student Loan'):
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">If your funding is through Student Finance England, please choose from the following. Have you applied for the proposed course?</td>';
                        $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1 ? '<span class="btn btn-success">Yes</span>' : '<span class="btn btn-danger">No</span>').'</td>';
                    $PDFHTML .= '</tr>';
                    if(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1):
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">Are you already in receipt of funds?</td>';
                            $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.(isset($applicant->course->fund_receipt) && $applicant->course->fund_receipt == 1 ? '<span class="btn btn-success">Yes</span>' : '<span class="btn btn-danger">No</span>').'</td>';
                        $PDFHTML .= '</tr>';
                    endif;
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">Have you ever apply/Received any fund/Loan from SLC/government Loan for any other programme/institution?</td>';
                        $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.(isset($applicant->course->applied_received_fund) && $applicant->course->applied_received_fund == 1 ? '<span class="btn btn-success">Yes</span>' : '<span class="btn btn-danger">No</span>').'</td>';
                    $PDFHTML .= '</tr>';
                elseif($applicant->course->student_loan == 'Others'):
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">Other Funding</td>';
                        $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.(isset($applicant->course->other_funding) && $applicant->course->other_funding != '' ? $applicant->course->other_funding : '').'</td>';
                    $PDFHTML .= '</tr>';
                endif;
                if(isset($applicant->course->creation->has_evening_and_weekend) && $applicant->course->creation->has_evening_and_weekend == 1):
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">Are you applying for evening and weekend classes (Full Time)</td>';
                        $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.(isset($applicant->course->full_time) && $applicant->course->full_time == 1 ? '<span class="btn btn-success">Yes</span>' : '<span class="btn btn-danger">No</span>').'</td>';
                    $PDFHTML .= '</tr>';
                endif;
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">Fee Eligibility</td>';
                    $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.(isset($applicant->feeeligibility->elegibility->name) && isset($applicant->feeeligibility->fee_eligibility_id) && $applicant->feeeligibility->fee_eligibility_id > 0 ? $applicant->feeeligibility->elegibility->name : '---').'</td>';
                $PDFHTML .= '</tr>';

                /* Educational Qualification */
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td colspan="4" class="barTitle text-left">Educational Qualification</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr><td class="spacer" colspan="4"></td></tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">Do you have any formal academic qualification?</td>';
                    $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.(isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? '<span class="btn btn-success">Yes</span>' : '<span class="btn btn-danger">No</span>').'</td>';
                $PDFHTML .= '</tr>';
                if(isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1):
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td colspan="4" style="padding: 3px 0 0;">';
                            $PDFHTML .= '<table class="table table-bordered table-sm mb-15">';
                                $PDFHTML .= '<thead>';
                                    $PDFHTML .= '<tr>';
                                        $PDFHTML .= '<th>Awarding Body</th>';
                                        $PDFHTML .= '<th>Highest Academic Qualification</th>';
                                        $PDFHTML .= '<th>Subjects</th>';
                                        $PDFHTML .= '<th>Result</th>';
                                        $PDFHTML .= '<th>Award Date</th>';
                                    $PDFHTML .= '</tr>';
                                $PDFHTML .= '</thead>';
                                $PDFHTML .= '<tbody>';
                                    if(isset($applicant->quals) && $applicant->quals->count() > 0):
                                        foreach($applicant->quals as $qual):
                                            $PDFHTML .= '<tr>';
                                                $PDFHTML .= '<td>'.$qual->awarding_body.'</td>';
                                                $PDFHTML .= '<td>'.$qual->highest_academic.'</td>';
                                                $PDFHTML .= '<td>'.$qual->subjects.'</td>';
                                                $PDFHTML .= '<td>'.$qual->result.'</td>';
                                                $PDFHTML .= '<td>'.date('F, Y', strtotime($qual->degree_award_date)).'</td>';
                                            $PDFHTML .= '</tr>';
                                        endforeach;
                                    else:
                                        $PDFHTML .= '<tr><td colspan="5" class="text-center">No data found!</td></tr>';
                                    endif;
                                $PDFHTML .= '</tbody>';
                            $PDFHTML .= '</table>';
                        $PDFHTML .= '</td>';
                    $PDFHTML .= '</tr>';
                endif;

                /* Employment History */
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td colspan="4" class="barTitle text-left">Employment History</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr><td class="spacer" colspan="4"></td></tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">What is your current employment status?</td>';
                    $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.(isset($applicant->other->employment_status) && $applicant->other->employment_status != '' ? $applicant->other->employment_status : '---').'</td>';
                $PDFHTML .= '</tr>';
                if(!isset($applicant->other->employment_status) || ($applicant->other->employment_status == 'Unemployed' || $applicant->other->employment_status == 'Contractor' || $applicant->other->employment_status == 'Consultant' || $applicant->other->employment_status == 'Office Holder')):
                    $emptStatus = false;
                else:
                    $emptStatus = true;
                endif;
                if($emptStatus):
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td colspan="4" style="padding: 3px 0 5px;">';
                            if(isset($applicant->employment) && $applicant->employment->count() > 0):
                                foreach($applicant->employment as $empt):
                                    $address = '';
                                    $address .= $empt->address_line_1.'<br/>';
                                    $address .= ($empt->address_line_2 != '' ? $empt->address_line_2.'<br/>' : '');
                                    $address .= ($empt->city != '' ? $empt->city.', ' : '');
                                    $address .= ($empt->state != '' ? $empt->state.', ' : '');
                                    $address .= ($empt->post_code != '' ? $empt->post_code.', ' : '');
                                    $address .= ($empt->country != '' ? '<br/>'.$empt->country : '');

                                    $PDFHTML .= '<div style="border: 1px solid rgb(226, 232, 240); padding: 15px 0 0; display: block;" class="mb-10">';
                                        $PDFHTML .= '<table>';
                                            $PDFHTML .= '<tr>';
                                                $PDFHTML .= '<td class="theLabel">Organization</td>';
                                                $PDFHTML .= '<td class="theValue">'.$empt->company_name.'</td>';
                                                $PDFHTML .= '<td class="theLabel">Phone</td>';
                                                $PDFHTML .= '<td class="theValue">'.$empt->company_phone.'</td>';
                                            $PDFHTML .= '</tr>';
                                            $PDFHTML .= '<tr>';
                                                $PDFHTML .= '<td class="theLabel">Position</td>';
                                                $PDFHTML .= '<td class="theValue">'.$empt->position.'</td>';
                                                $PDFHTML .= '<td class="theLabel">Start</td>';
                                                $PDFHTML .= '<td class="theValue">'.(!empty($empt->start_date) ? date('F, Y', strtotime('01-'.$empt->start_date)) : '').'</td>';
                                            $PDFHTML .= '</tr>';
                                            $PDFHTML .= '<tr>';
                                                $PDFHTML .= '<td class="theLabel">End</td>';
                                                $PDFHTML .= '<td class="theValue">'.(!empty($empt->end_date) ? date('F, Y', strtotime('01-'.$empt->end_date)) : '').'</td>';
                                                $PDFHTML .= '<td class="theLabel">Address</td>';
                                                $PDFHTML .= '<td class="theValue">'.$address.'</td>';
                                            $PDFHTML .= '</tr>';
                                            $PDFHTML .= '<tr>';
                                                $PDFHTML .= '<td class="theLabel">Contact Person</td>';
                                                $PDFHTML .= '<td class="theValue">'.$empt->reference[0]->name.'</td>';
                                                $PDFHTML .= '<td class="theLabel">Position</td>';
                                                $PDFHTML .= '<td class="theValue">'.$empt->reference[0]->position.'</td>';
                                            $PDFHTML .= '</tr>';
                                            $PDFHTML .= '<tr>';
                                                $PDFHTML .= '<td class="theLabel">Phone</td>';
                                                $PDFHTML .= '<td class="theValue">'.$empt->reference[0]->phone.'</td>';
                                                $PDFHTML .= '<td class="theLabel"></td>';
                                                $PDFHTML .= '<td class="theValue"></td>';
                                            $PDFHTML .= '</tr>';
                                        $PDFHTML .= '</table>';
                                    $PDFHTML .= '</div>';
                                endforeach;
                            endif;
                        $PDFHTML .= '</td>';
                    $PDFHTML .= '</tr>';
                endif;


                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td colspan="4" class="barTitle text-left">Others</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr><td class="spacer" colspan="4"></td></tr>';
                if(isset($applicant->referral_code) && $applicant->referral_code != ''):
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="theLabel" style="width: 50%;" colspan="2">If you referred by Somone/ Agent, Please enter the Referral Code.</td>';
                        $PDFHTML .= '<td class="theValue" style="width: 50%;" colspan="2">'.(isset($applicant->referral_code) && $applicant->referral_code != '' ? $applicant->referral_code : '').'</td>';
                    $PDFHTML .= '</tr>';
                endif;
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel" style="width: 20%;">Video Consent:</td>';
                    $PDFHTML .= '<td class="theValue" style="width: 80%;" colspan="3">';
                        $PDFHTML .= '<div style="font-size: 13px; line-height: normal; position: relative; padding: 0 0 0 35px;">';
                            $PDFHTML .= '<span style="position: absolute; left: 0; top: 3px; width:20px; height: 20px; background: transparent; border: 1px solid #d2d4d6; font-size: 20px; text-align: center; color: #FFF; font-family: DejaVu Sans, sans-serif;"></span>';
                            $PDFHTML .= 'I hereby authorize the filming and utilization of recordings featuring my person, conducted by members or staff of
                                         London Churchill College, exclusively for admission purposes.';
                        $PDFHTML .= '</div>';
                    $PDFHTML .= '</td>';
                $PDFHTML .= '</tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="theLabel" style="width: 20%;">Declaration:</td>';
                    $PDFHTML .= '<td class="theValue" style="width: 80%;" colspan="3">';
                        $PDFHTML .= '<div style="font-size: 13px; line-height: normal; position: relative; padding: 0 0 0 35px;">';
                            $PDFHTML .= '<span style="position: absolute; left: 0; top: 3px; width:20px; height: 20px; background: rgb(22, 78, 99); font-size: 20px; text-align: center; color: #FFF; font-family: DejaVu Sans, sans-serif;"><i style="position: relative; top: -8px;">&check;</i></span>';
                            $PDFHTML .= 'I hereby verify the accuracy and truthfulness of the information provided in this form to the best 
                                        of my knowledge. It is my responsibility to stay informed about the terms and conditions as well as 
                                        the policies of the college, and I commit to comply with them. I have thoroughly reviewed the 
                                        college\'s terms and conditions and student privacy policy and pledge to adhere to them throughout 
                                        my entire course of study.';
                        $PDFHTML .= '</div>';
                    $PDFHTML .= '</td>';
                $PDFHTML .= '</tr>';

                /* Signature Area */
                $PDFHTML .= '<tr><td style="width: 100%; padding: 30px 0 0;" colspan="4"></td></tr>';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td colspan="2" style="width: 50%;">'; 
                        $PDFHTML .= '<span style="font-weight: bold; font-size: 12px; line-height: normal; margin: 0 0 5px; display: block;">Student\'s Signature:</span>';
                        $PDFHTML .= '<span style="height:40px; width:200px; border:1px solid #d2d4d6; display: inline-block;"></span>';
                    $PDFHTML .= '</td>';
                    $PDFHTML .= '<td colspan="2" style="width: 50%;" class="text-right">'; 
                        $PDFHTML .= '<span style="text-align: right; font-weight: bold; font-size: 12px; line-height: normal; margin: 0 0 5px; display: block;">Date:</span>';
                        $PDFHTML .= '<span style="height:40px; width:200px; border:1px solid #d2d4d6; display: inline-block;"></span>';
                    $PDFHTML .= '</td>';
                $PDFHTML .= '</tr>';

            $PDFHTML .= '</table>';
            /*PDF Body End Here*/

            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = 'Application_of_'.$applicant->first_name.'_'.$applicant->last_name.'.pdf';
        $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        return $pdf->download($fileName);
 
    }
}
