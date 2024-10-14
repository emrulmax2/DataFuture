<?php

namespace App\Console\Commands;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\Course;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Console\Command;

class CourseContentMissingTutorNotificationCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coursecontentmissingtutornotification:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Course Content Module Document Missing Notification Send to tutors.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $latest_term_declaration = Plan::orderBy('term_declaration_id', 'DESC')->get()->first();
        $term_declaration_id = $latest_term_declaration->term_declaration_id;

        $plans = Plan::where('term_declaration_id', $term_declaration_id)->where('class_type', 'Theory')->whereHas('tasks', function($q){
            $q->has('uploads', '=', 0);
        })->orderBy('id', 'ASC')->orderBy('course_id', 'ASC')->orderBy('tutor_id', 'ASC')->get();
        $plan_ids = $plans->pluck('id')->unique()->toArray();

        $res = [];
        if(!empty($plans)):
            $i = 1;
            foreach($plans as $pln):
                $empUploads = [];
                foreach($pln->tasks as $tsk):
                    if($tsk->uploads->count() == 0):
                        if($tsk->last_date && $tsk->last_date <= date('Y-m-d')):
                            $empUploads[] = $tsk->eLearn->short_code;
                        endif;
                    endif;
                endforeach;
                if(!empty($empUploads) && count($empUploads) > 0):
                    $res[$pln->course_id][$pln->tutor_id][$i]['group'] = $pln->group->name;
                    $res[$pln->course_id][$pln->tutor_id][$i]['module'] = $pln->creations->module_name;
                    $res[$pln->course_id][$pln->tutor_id][$i]['tsks'] = implode(', ', $empUploads);
                endif;
                $i++;
            endforeach;
        endif;

        if(!empty($res)):
            $i = 1;
            $subject = 'Missing Course Content - Request for Attention';
            $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
            $configuration = [
                'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                
                'from_email'    => $commonSmtp->smtp_user,
                'from_name'    =>  'Monitoring Team',
            ];
            foreach($res as $course_id => $tutorContent):
                $course = Course::find($course_id);
                $team = (isset($course->team->email) && !empty($course->team->email) ? $course->team->email : '');
                if(!empty($team)):
                    $configuration['from_email'] = $team;
                endif;

                foreach($tutorContent as $tutor_id => $modules):
                    //if($i > 1): break; endif;
                    $tutor = User::find($tutor_id);
                    $tutorName = (isset($tutor->employee->full_name) && !empty($tutor->employee->full_name) ? $tutor->employee->full_name : $tutor->name);
                    $mailTo = [$tutor->email];
                    if(isset($tutor->employee->email) && !empty($tutor->employee->email)):
                        $mailTo[] = $tutor->employee->email;
                    endif;
                    $MAILBODY = '';
                    if(!empty($modules)):
                        $MAILBODY .= 'Dear '.$tutorName.',<br><br/>';
                        $MAILBODY .= '<p>I hope this message finds you well. I’m reaching out to inform you that certain course materials appear to be missing from the following:</p>';
                        $MAILBODY .= '<table style="border: 1px solid #ddd; width: 100%; border-spacing: 0; border-collapse: collapse; text-align:left">';
                            $MAILBODY .= '<thead>';
                                $MAILBODY .= '<tr>';
                                    $MAILBODY .= '<th style="padding: 5px; width: 40px; text-align:center; border: 1px solid #ddd;">#</th>';
                                    $MAILBODY .= '<th style="padding: 5px; border: 1px solid #ddd;">Course</th>';
                                    $MAILBODY .= '<th style="padding: 5px; border: 1px solid #ddd;">Module</th>';
                                    $MAILBODY .= '<th style="padding: 5px; border: 1px solid #ddd;">Group</th>';
                                    $MAILBODY .= '<th style="padding: 5px; border: 1px solid #ddd;">Document</th>';
                                $MAILBODY .= '</tr>';
                            $MAILBODY .= '</thead>';
                            $MAILBODY .= '<tbody>';
                                $sl = 1;
                                foreach($modules as $module):
                                    $MAILBODY .= '<tr>';
                                        $MAILBODY .= '<td style="padding: 5px; width: 40px; text-align:center; border: 1px solid #ddd;">'.$sl.'</td>';
                                        $MAILBODY .= '<td style="padding: 5px; border: 1px solid #ddd;">'.$course->name.'</td>';
                                        $MAILBODY .= '<td style="padding: 5px; border: 1px solid #ddd;">'.$module['module'].'</td>';
                                        $MAILBODY .= '<td style="padding: 5px; border: 1px solid #ddd;">'.$module['group'].'</td>';
                                        $MAILBODY .= '<td style="padding: 5px; border: 1px solid #ddd;">'.$module['tsks'].'</td>';
                                    $MAILBODY .= '</tr>';
                                    $sl += 1;
                                endforeach;
                            $MAILBODY .= '</tbody>';
                        $MAILBODY .= '</table>';
                        $MAILBODY .= '<p>These resources are important for us to engage with the coursework fully and meet the learning objectives.</p>';
                        $MAILBODY .= '<p>We would greatly appreciate it if you could look into this and inform us of when the missing content will be made available.</p>';
                        $MAILBODY .= '<p>Thank you for your attention to this matter.</p>';
                        $MAILBODY .= 'Best regards,<br/>'; 
                        $MAILBODY .= 'Monitoring Team<br/>';
                        $MAILBODY .= 'London Churchill College';

                        UserMailerJob::dispatch($configuration, $mailTo, new CommunicationSendMail($subject, $MAILBODY, []));
                        $i++;
                    endif;
                endforeach;
            endforeach;
        endif;

        return 0;
    }
}
