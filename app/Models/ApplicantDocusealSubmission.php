<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A DocuSeal offer acceptance form sent to an applicant (Offer Placed). The
 * collector (App\Services\DocuSeal\OfferAcceptanceDocuments) checks the rows in
 * STATE_WAITING / STATE_FAILED and, once DocuSeal reports the submission
 * completed, stores the signed form and the audit trail as applicant documents.
 */
class ApplicantDocusealSubmission extends Model
{
    const STATE_WAITING = 'waiting';
    const STATE_STORED = 'stored';
    const STATE_SKIPPED = 'skipped';
    const STATE_DECLINED = 'declined';
    const STATE_EXPIRED = 'expired';
    const STATE_FAILED = 'failed';

    /** A failing row is retried on every run until it has failed this many times in a row. */
    const MAX_FAILURES = 10;

    /* What the signing did to the application (OfferAcceptanceWorkflow). */
    const OUTCOME_CONVERTING = 'converting';
    const OUTCOME_CONVERTED = 'converted';
    const OUTCOME_CONVERSION_FAILED = 'conversion_failed';
    const OUTCOME_HELD = 'held_missing_proof';
    const OUTCOME_REJECTED = 'rejected';
    const OUTCOME_UNCHANGED = 'status_unchanged';

    protected $fillable = [
        'applicant_id',
        'submission_id',
        'template_id',
        'applicant_email',
        'signing_url',
        'state',
        'docuseal_status',
        'sent_by',
        'sent_at',
        'opened_at',
        'completed_at',
        'decline_reason',
        'stored_at',
        'offer_document_id',
        'audit_document_id',
        'outcome',
        'outcome_note',
        'outcome_at',
        'batch_id',
        'last_checked_at',
        'checks',
        'failures',
        'last_error',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'completed_at' => 'datetime',
        'outcome_at' => 'datetime',
        'stored_at' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    /** Rows the scheduled check still looks at. */
    public function scopeOpen($query)
    {
        return $query->where(function ($q) {
            $q->where('state', self::STATE_WAITING)
                ->orWhere(fn ($f) => $f->where('state', self::STATE_FAILED)->where('failures', '<', self::MAX_FAILURES));
        });
    }

    /**
     * Rows the scheduled backstop still has work for: not signed yet, a failed
     * download, or signed / declined with the status step not applied yet.
     */
    public function scopeNeedsWork($query)
    {
        return $query->where(function ($q) {
            $q->open()->orWhere(fn ($f) => $f->whereNull('outcome')->whereIn('state', [self::STATE_STORED, self::STATE_SKIPPED, self::STATE_DECLINED]));
        });
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function offerDocument()
    {
        return $this->belongsTo(ApplicantDocument::class, 'offer_document_id');
    }

    public function auditDocument()
    {
        return $this->belongsTo(ApplicantDocument::class, 'audit_document_id');
    }
}
