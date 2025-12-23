<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\LetterSet;
use App\Models\Signatory;
use App\Models\Student;
use App\Models\StudentLetter;
use App\Models\StudentLettersDocument;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StudentBulkEmailCreationMailSendCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'studentbulkemailcreationmailsend:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Student Bulk Email Creation E-Mail Sending';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $letterSet = LetterSet::find(116);

        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        $configuration = [
            'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
            'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
            'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
            'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
            'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
            'from_email'    => 'no-reply@lcc.ac.uk',
            'from_name'    =>  'London Churchill College',
            
        ];

        // Concurrency-safe processing: claim records before processing
        $documents = StudentLettersDocument::where('mail_sent_status', 1)
            ->whereNull('email_sent_at')
            ->limit(10)
            ->get();

        foreach ($documents as $document) {
            // Try to claim the document for processing (set status to 9)
            $updated = StudentLettersDocument::where('id', $document->id)
                ->where('mail_sent_status', 1)
                ->whereNull('email_sent_at')
                ->update(['mail_sent_status' => 9]);

            if ($updated === 0) {
                // Another process has already claimed this document
                continue;
            }

            try {
                $subject = 'Welcome to London Churchill College';
                $studentLetter = StudentLetter::find($document->student_letter_id);
                if (!$studentLetter) {
                    // Release the claim if student letter is missing
                    $document->mail_sent_status = 3; // mark as error
                    $document->save();
                    continue;
                }

                $student = Student::find($studentLetter->student_id);
                if (!$student) {
                    $document->mail_sent_status = 3; // mark as error
                    $document->save();
                    continue;
                }
                $orgEmail = strtolower($student->registration_no).'@lcc.ac.uk';
                $studentUserEmail = $orgEmail;
                $mailTo = [];
                $mailTo[] = $studentUserEmail;
                if(isset($student->contact->personal_email) && !empty($student->contact->personal_email)) {
                    $mailTo[] = $student->contact->personal_email;
                }

                $MSGBODY = $this->parseLetterContent($studentLetter->student_id, $document->display_file_name, $letterSet->description, $studentLetter->issued_date, 23);
                UserMailerJob::dispatch($configuration, $mailTo, new CommunicationSendMail($subject, $MSGBODY, []));

                // After sending email, update the mail_sent_status
                $document->mail_sent_status = 2;
                $document->email_sent_at = now();
                $document->save();
            } catch (\Exception $e) {
                // On error, mark as failed
                $document->mail_sent_status = 3;
                $document->save();
                // Optionally log the error
                Log::error('StudentBulkEmailCreationMailSendCron error: ' . $e->getMessage());
            }
        }
    }

    
    private function parseLetterContent($student_id, $letter_title, $letter_content, $issued_date, $signatory = 0){
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
                elseif($table == 'result'):
                    $theResultContent = $this->examResultHtml($student_id, $field);
                    $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", $theResultContent, $letter_content);
                else:
                    $letter_content = str_replace("[DATA=" . $table . "]" . $field . "[/DATA]", '', $letter_content);
                endif;
            endforeach;
        endif;

        return $letter_content;
    }
    
}
