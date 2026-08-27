<?php

namespace App\Models;

use App\Jobs\ProcessNewStudentToUser;
use App\Jobs\ProcessStudents;
use App\Jobs\ProcessStudentConsent;
use App\Jobs\ProcessStudentContact;
use App\Jobs\ProcessStudentDisability;
use App\Jobs\ProcessStudentDocuments;
use App\Jobs\ProcessStudentEmail;
use App\Jobs\ProcessStudentEmployement;
use App\Jobs\ProcessStudentFeeEligibility;
use App\Jobs\ProcessStudentInterview;
use App\Jobs\ProcessStudentKinDetail;
use App\Jobs\ProcessStudentLetter;
use App\Jobs\ProcessStudentNoteDetails;
use App\Jobs\ProcessStudentOtherDetails;
use App\Jobs\ProcessStudentProofOfId;
use App\Jobs\ProcessStudentProposedCourse;
use App\Jobs\ProcessStudentQualification;
use App\Jobs\ProcessStudentResidencyAndCriminalConviction;
use App\Jobs\ProcessStudentSms;
use App\Jobs\ProcessStudentTask;
use App\Jobs\ProcessStudentTaskDocument;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentConversionLog extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_QUEUED = 'queued';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * The conversion jobs in strict chain order. Must mirror the Bus::batch
     * chain in AdmissionController::admissionStudentUpdateStatus — a job added
     * to that chain only gets logged once it is added here too.
     */
    const JOB_SEQUENCE = [
        ProcessNewStudentToUser::class,
        ProcessStudents::class,
        ProcessStudentNoteDetails::class,
        ProcessStudentTask::class,
        ProcessStudentTaskDocument::class,
        ProcessStudentQualification::class,
        ProcessStudentContact::class,
        ProcessStudentDisability::class,
        ProcessStudentEmployement::class,
        ProcessStudentProposedCourse::class,
        ProcessStudentKinDetail::class,
        ProcessStudentOtherDetails::class,
        ProcessStudentResidencyAndCriminalConviction::class,
        ProcessStudentProofOfId::class,
        ProcessStudentFeeEligibility::class,
        ProcessStudentSms::class,
        ProcessStudentLetter::class,
        ProcessStudentInterview::class,
        ProcessStudentEmail::class,
        ProcessStudentConsent::class,
        ProcessStudentDocuments::class,
    ];

    const JOB_LABELS = [
        ProcessNewStudentToUser::class => 'Create Student Login Account',
        ProcessStudents::class => 'Create Student Record',
        ProcessStudentNoteDetails::class => 'Copy Notes',
        ProcessStudentTask::class => 'Copy Tasks',
        ProcessStudentTaskDocument::class => 'Copy Task Documents',
        ProcessStudentQualification::class => 'Copy Qualifications',
        ProcessStudentContact::class => 'Copy Contact Details',
        ProcessStudentDisability::class => 'Copy Disabilities',
        ProcessStudentEmployement::class => 'Copy Employment History',
        ProcessStudentProposedCourse::class => 'Create Course Relation',
        ProcessStudentKinDetail::class => 'Copy Next of Kin',
        ProcessStudentOtherDetails::class => 'Copy Other Details',
        ProcessStudentResidencyAndCriminalConviction::class => 'Copy Residency & Criminal Conviction',
        ProcessStudentProofOfId::class => 'Copy Proof of ID',
        ProcessStudentFeeEligibility::class => 'Copy Fee Eligibility',
        ProcessStudentSms::class => 'Copy SMS History',
        ProcessStudentLetter::class => 'Copy Letters',
        ProcessStudentInterview::class => 'Copy Interviews',
        ProcessStudentEmail::class => 'Copy Emails',
        ProcessStudentConsent::class => 'Create Consents',
        ProcessStudentDocuments::class => 'Copy Documents',
    ];

    protected $fillable = [
        'applicant_id',
        'student_id',
        'batch_id',
        'job_class',
        'job_name',
        'status',
        'attempts',
        'message',
        'exception',
        'started_at',
        'finished_at',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public static function jobLabel($jobClass){
        return self::JOB_LABELS[$jobClass] ?? class_basename($jobClass);
    }

    public function applicant(){
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
