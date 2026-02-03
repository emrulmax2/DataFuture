<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Applicant;
use App\Models\ApplicantCriminalConviction;
use App\Models\ApplicantResidency;
use App\Models\ApplicantUser;
use App\Models\Student;
use App\Models\StudentCriminalConviction;
use App\Models\StudentResidency;
use App\Models\StudentUser;

class ProcessStudentResidencyAndCriminalConviction implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $applicant;

    /**
     * Create a new job instance.
     */
    public function __construct(Applicant $applicant)
    {
        $this->applicant = $applicant;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $ApplicantUser = ApplicantUser::find($this->applicant->applicant_user_id);
        $user = StudentUser::where(["email" => $ApplicantUser->email])->get()->first();
        $student = Student::where(["student_user_id" => $user->id])->get()->first();

        if (!$student) {
            return;
        }

        $applicantResidency = ApplicantResidency::where('applicant_id', $this->applicant->id)->first();
        if ($applicantResidency) {
            StudentResidency::updateOrCreate([
                'student_id' => $student->id,
            ], [
                'student_id' => $student->id,
                'residency_status_id' => $applicantResidency->residency_status_id,
                'created_by' => ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
                'updated_by' => ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ]);
        }

        $applicantCriminalConviction = ApplicantCriminalConviction::where('applicant_id', $this->applicant->id)->first();
        if ($applicantCriminalConviction) {
            StudentCriminalConviction::updateOrCreate([
                'student_id' => $student->id,
            ], [
                'student_id' => $student->id,
                'have_you_been_convicted' => (int) $applicantCriminalConviction->have_you_been_convicted,
                'criminal_conviction_details' => $applicantCriminalConviction->criminal_conviction_details,
                'criminal_declaration' => (int) ($applicantCriminalConviction->criminal_declaration ?? 0),
                'created_by' => ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
                'updated_by' => ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ]);
        }
    }
}
