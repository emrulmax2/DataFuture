<?php 
namespace App\Traits;

use App\Models\Applicant;
use App\Models\LetterHeaderFooter;
use App\Models\Option;
use App\Models\Signatory;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

trait GenerateStudentLetterTrait{
    public function generateLetter($student_id, $letter_title, $letter_content, $issued_date, $pin, $signatory = 0){
        $student = Student::find($student_id);
        $issued_date = (!empty($issued_date) ? date('d/m/Y', strtotime($issued_date)) : date('d/m/Y'));
        $signature = Signatory::find($signatory);
        $data_table_arr = $this->perseLetterData($letter_content);
        $letter_content = stripslashes($letter_content);

        if(!empty($data_table_arr)):
            foreach ($data_table_arr as $k => $v):
                $table = $v[0];
                $field = $v[1];

                if($table == 'titles'):
                    $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->title->name) ? $student->title->name : ''), $letter_content);
                elseif($table == 'students'):
                    if($field == 'full_name'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", $student->full_name, $letter_content);
                    elseif($field == 'first_name'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", $student->first_name, $letter_content);
                    elseif($field == 'last_name'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", $student->last_name, $letter_content);
                    elseif($field == 'application_no'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", $student->application_no, $letter_content);
                    elseif($field == 'registration_no'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", $student->registration_no, $letter_content);
                    elseif($field == 'date_of_birth'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", $student->date_of_birth, $letter_content);
                    endif;
                elseif($table == 'student_contacts'):
                    if($field == 'term_address'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->termaddress->full_address) ? $student->contact->termaddress->full_address : ''), $letter_content);
                    elseif($field == 'term_address_line_1'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->termaddress->address_line_1) ? $student->contact->termaddress->address_line_1 : ''), $letter_content);
                    elseif($field == 'term_address_line_2'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->termaddress->address_line_2) ? $student->contact->termaddress->address_line_2 : ''), $letter_content);
                    elseif($field == 'term_city'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->termaddress->city) ? $student->contact->termaddress->city : ''), $letter_content);
                    elseif($field == 'term_post_code'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->termaddress->post_code) ? $student->contact->termaddress->post_code : ''), $letter_content);
                    elseif($field == 'term_country'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->termaddress->country) ? $student->contact->termaddress->country : ''), $letter_content);
                    elseif($field == 'permanent_address'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->permaddress->full_address) ? $student->contact->permaddress->full_address : ''), $letter_content);
                    elseif($field == 'permanent_address_line_1'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->permaddress->address_line_1) ? $student->contact->permaddress->address_line_1 : ''), $letter_content);
                    elseif($field == 'permanent_address_line_2'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->permaddress->address_line_2) ? $student->contact->permaddress->address_line_2 : ''), $letter_content);
                    elseif($field == 'permanent_city'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->permaddress->city) ? $student->contact->permaddress->city : ''), $letter_content);
                    elseif($field == 'permanent_post_code'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->permaddress->post_code) ? $student->contact->permaddress->post_code : ''), $letter_content);
                    elseif($field == 'permanent_country'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->permaddress->country) ? $student->contact->permaddress->country : ''), $letter_content);
                    elseif($field == 'mobile'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->mobile) ? $student->contact->mobile : ''), $letter_content);
                    elseif($field == 'personal_email'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->personal_email) ? $student->contact->personal_email : ''), $letter_content);
                    elseif($field == 'institutional_email'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->contact->institutional_email) ? $student->contact->institutional_email : ''), $letter_content);
                    endif;
                elseif($table == 'student_kins'):
                    if($field == 'name'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->kin->name) ? $student->kin->name : ''), $letter_content);
                    elseif($field == 'mobile'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->kin->mobile) ? $student->kin->mobile : ''), $letter_content);
                    endif;
                elseif($table == 'other_details'):
                    if($field == 'mode'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->other->mode->name) ? $student->other->mode->name : ''), $letter_content);
                    endif;
                elseif($table == 'letter_issuing'):
                    $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", $issued_date, $letter_content);
                elseif($table == 'courses'):
                    $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->activeCR->creation->course->name) ? $student->activeCR->creation->course->name : ''), $letter_content);
                elseif($table == 'semesters'):
                    $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->activeCR->creation->semester->name) ? $student->activeCR->creation->semester->name : ''), $letter_content);
                elseif($table == 'student_proposed_courses'):
                    if($field == 'evening_and_weekends'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->activeCR->propose->full_time) && $student->activeCR->propose->full_time == 1 ? 'Yes' : 'No'), $letter_content);
                    elseif($field == 'course_start_date'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->activeCR->creation->availability[0]->course_start_date) && !empty($student->activeCR->creation->availability[0]->course_start_date) ? date('d-m-Y', strtotime($student->activeCR->creation->availability[0]->course_start_date)) : ''), $letter_content);
                    elseif($field == 'course_end_date'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->activeCR->creation->availability[0]->course_end_date) && !empty($student->activeCR->creation->availability[0]->course_end_date) ? date('d-m-Y', strtotime($student->activeCR->creation->availability[0]->course_end_date))  : ''), $letter_content);
                    elseif($field == 'class_startdate'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->activeCR->creation->availability[0]->course_start_date) && !empty($student->activeCR->creation->availability[0]->course_start_date) ? date('d-m-Y', strtotime($student->activeCR->creation->availability[0]->course_start_date)) : ''), $letter_content);
                    elseif($field == 'class_enddate'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->activeCR->creation->availability[0]->course_end_date) && !empty($student->activeCR->creation->availability[0]->course_end_date) ? date('d-m-Y', strtotime($student->activeCR->creation->availability[0]->course_end_date))  : ''), $letter_content);
                    elseif($field == 'fees'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->activeCR->creation->fees) && !empty($student->activeCR->creation->fees) ? '£'.number_format($student->activeCR->creation->fees, 2)  : '£0.00'), $letter_content);
                    elseif($field == 'venue_name'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->activeCR->propose->venue->name) && !empty($student->activeCR->propose->venue->name) ? $student->activeCR->propose->venue->name  : ''), $letter_content);
                    elseif($field == 'awarding_body'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($student->activeCR->creation->course->body->name) && !empty($student->activeCR->creation->course->body->name) ? $student->activeCR->creation->course->body->name  : ''), $letter_content);
                    endif;
                elseif($table == 'signatories'):
                    if($field == 'sign_url'):
                        $signatureImg = '';
                        if(isset($signature->signature) && !empty($signature->signature) && Storage::disk('local')->exists('public/signatories/'.$signature->signature)):
                            $signatureImg = url('storage/signatories/'.$signature->signature);
                        endif;
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (!empty($signatureImg) ? "<img src=\"" .$signatureImg. "\" style=\"width:150px; height: auto;\" />" : ''), $letter_content);
                    elseif($field == 'name'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($signature->signatory_name) && !empty($signature->signatory_name) ? $signature->signatory_name : ''), $letter_content);
                    elseif($field == 'post'):
                        $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", (isset($signature->signatory_post) && !empty($signature->signatory_post) ? $signature->signatory_post : ''), $letter_content);
                    endif;
                elseif($table == 'today_date'):
                    $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", date('d/m/Y'), $letter_content);
                elseif($table == 'page_break'):
                    $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", '<div class="pageBreak"></div>', $letter_content);
                else:
                    $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", '', $letter_content);
                endif;
            endforeach;
        endif;

        /* Generate PDF Start */
        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();
        $LetterHeader = LetterHeaderFooter::where('for_letter', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
        $LetterFooters = LetterHeaderFooter::where('for_letter', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get()->first();
        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>'.$letter_title.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: rgb(30, 41, 59); padding-top: 10px;}
                                table{margin-left: 0px; width: 100%;}
                                figure{margin: 0;}
                                @page{margin-top: 110px;margin-left: 85px !important;margin-right:85px !important;margin-bottom: 95px;}
                                header{position: fixed;left: 0px;right: 0px;height: 80px;margin-top: -90px;}
                                footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px;margin-bottom: -120px;}
                                .pageCounter{position: relative;}
                                .pageCounter:before{content: counter(page);position: relative;display: inline-block;}
                                .pinRow td{border-bottom: 1px solid gray;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}
                                @media print{ .pageBreak{page-break-after: always;} }
                                .pageBreak{page-break-after: always;}
                            </style>';
            $PDFHTML .= '</head>';
            $PDFHTML .= '<body>';
                if(isset($LetterHeader->current_file_name) && !empty($LetterHeader->current_file_name) && Storage::disk('local')->exists('public/letterheaderfooter/header/'.$LetterHeader->current_file_name)):
                    $headerImageURL = url('storage/letterheaderfooter/header/'.$LetterHeader->current_file_name);
                    $PDFHTML .= '<header>';
                        $PDFHTML .= '<img alt="'.$LetterHeader->current_file_name.'" style="width: 100%; height: auto;" src="'.$headerImageURL.'"/>';
                    $PDFHTML .= '</header>';
                endif;

                $PDFHTML .= '<footer>';
                    $PDFHTML .= '<table style="width: 100%; border: none; margin: 0; vertical-align: middle !important; font-family: serif; 
                                font-size: 8pt; color: #000000; font-weight: bold; font-style: italic;border-spacing: 0;border-collapse: collapse;">';
                        if(isset($LetterFooters->current_file_name) && !empty($LetterFooters->current_file_name) && Storage::disk('local')->exists('public/letterheaderfooter/footer/'.$LetterFooters->current_file_name)):
                            $footerImageURL = url('storage/letterheaderfooter/footer/'.$LetterFooters->current_file_name);
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td colspan="2" class="footerPartners" style="text-align: center; vertical-align: middle; padding-bottom: 5px;">';
                                    $PDFHTML .= '<img style=" width: 100%; height: auto; margin-left:0; margin-right:0;" src="'.$footerImageURL.'" alt="'.$LetterFooters->name.'"/>';
                                $PDFHTML .= '</td>';
                            $PDFHTML .= '</tr>';
                        endif;
                        $PDFHTML .= '<tr class="pinRow">';
                            $PDFHTML .= '<td style="padding-bottom: 3px;">';
                                $PDFHTML .= '<span class="pageCounter text-left"></span>';
                            $PDFHTML .= '</td>';
                            $PDFHTML .= '<td class="pinNumber text-right" style="padding-bottom: 3px;">';
                                $PDFHTML .= 'pin - '.$pin;
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';

                        if(!empty($regNo) || !empty($regAt)):
                        $PDFHTML .= '<tr class="regInfoRow">';
                            $PDFHTML .= '<td colspan="2" class="text-center" style="padding-top: 3px;">';
                                $PDFHTML .= (!empty($regNo) ? 'Company Reg. No. '.$regNo->value : '');
                                $PDFHTML .= (!empty($regAt) ? (!empty($regNo) ? ', ' : '').$regAt->value : '');
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                        endif;
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</footer>';

                $PDFHTML .= $letter_content;
            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $letterTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $letter_title);
        $fileName = time().'_'.$student_id.'_'.$letterTitle.'.pdf';
        $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        $content = $pdf->output();
        Storage::disk('s3')->put('public/students/'.$student_id.'/'.$fileName, $content );

        return ['path' => Storage::disk('s3')->url('public/students/'.$student_id.'/'.$fileName), 'filename' => $fileName];
    }
    
    public function perseLetterData($content){
        preg_match_all('/\[DATA=(.*?)\](.*?)\[\/DATA\]/', $content, $matches, PREG_SET_ORDER);

        $lists = [];
        $i = 0;
        foreach ($matches as $val):

            if (!isset($lists[$i])):
                $lists[$i] = array();
                $lists[$i] = array_merge($lists[$i], array($val[1], $val[2]));
            else:
                $lists[$i] = array_merge($lists[$i], array($val[1], $val[2]));
            endif;
            $i++;
        endforeach;

        return (!empty($lists) && count($lists) ? $lists : false);
    }
}