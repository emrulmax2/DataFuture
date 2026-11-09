<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A notification sent about an applicant (admission_notifications), e.g. by the DocuSeal workflow. */
class AdmissionNotification extends Model
{
    const TYPE_OFFER_ACCEPTED = 'offer_accepted';
    const TYPE_CONVERSION_FAILED = 'conversion_failed';
    const TYPE_PROOF_MISSING = 'proof_of_id_missing';
    const TYPE_OFFER_REJECTED = 'offer_rejected';

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_NOT_SENT = 'not_sent';

    protected $fillable = [
        'applicant_id',
        'user_id',
        'source',
        'docuseal_submission_id',
        'type',
        'channel',
        'recipients',
        'subject',
        'body',
        'data',
        'status',
        'error',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function docusealSubmission()
    {
        return $this->belongsTo(ApplicantDocusealSubmission::class, 'docuseal_submission_id');
    }
}
