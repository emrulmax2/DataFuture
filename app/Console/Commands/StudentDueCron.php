<?php

namespace App\Console\Commands;

use App\Models\CourseCreation;
use App\Models\SlcAgreement;
use App\Models\SlcInstallment;
use App\Models\SlcMoneyReceipt;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use Illuminate\Console\Command;

class StudentDueCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'studentdue:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Student Due Indicator Update Cron';
  
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
        $today = date('Y-m-d');
        $status_ids = [23,26,27,28,29,30];
        $semester_id = 121;

        $creation_ids = CourseCreation::orderBy('id', 'ASC')->where('semester_id', '>=', $semester_id)->pluck('id')->unique()->toArray();
        $students = StudentCourseRelation::whereIn('course_creation_id', $creation_ids)->where('active', 1)->pluck('student_id')->unique()->toArray();
                    // ->whereHas('student', function($q) use($status_ids){
                    //     $q->where('status_id', $status_ids);
                    // })
                    
        if(!empty($students)):
            foreach($students as $student_id):
                $student = Student::with('activeCR')->where('id', $student_id)->get()->first();
                $studentCourseRelation = $student->activeCR->id;
                $slcAgreement = SlcAgreement::where('student_course_relation_id', $studentCourseRelation)->where('student_id', $student_id)->where('date', '<=', $today)->orderBy('id', 'ASC')->get();
                $dueCount = 0;
                if($slcAgreement->count() > 0):
                    foreach($slcAgreement as $agreement):
                        $installment = SlcInstallment::where('slc_agreement_id', $agreement->id)->where('student_id', $student_id)->where('installment_date', '<=', $today)->sum('amount');
                        $totalReceived = SlcMoneyReceipt::where('slc_agreement_id', $agreement->id)->where('student_id', $student_id)->where('payment_date', '<=', $today)->where('payment_type', '!=', 'Refund')->sum('amount');
                        $totalRefund = SlcMoneyReceipt::where('slc_agreement_id', $agreement->id)->where('student_id', $student_id)->where('payment_date', '<=', $today)->where('payment_type', '=', 'Refund')->sum('amount');
                        $due = $installment - $totalReceived + $totalRefund;
                        $hasDue = 0;
                        if($due > 0):
                            $dueCount += 1;
                            $hasDue = 1;
                        endif;
                        SlcAgreement::where('id', $agreement->id)->update(['has_due' => $hasDue]);
                    endforeach;
                endif;
                Student::where('id', $student_id)->update(['has_due' => ($dueCount > 0 ? 1 : 0)]);
            endforeach;
        endif;

        return 0;
    }
}
