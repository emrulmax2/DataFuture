<?php

namespace App\Support;

/**
 * Which statuses an applicant can be moved to from the admission portal, and
 * through which handler in admission-global.js: the record header renders
 * one hidden anchor per target status and the Change status dialog clicks
 * the chosen one.
 */
class AdmissionStatusFlow
{
    public const HELP = [
        3 => 'Moves the application back to In Progress.',
        5 => 'Accepts the application and sends the conditional offer letter, then places the offer straight away: the status moves on to Offer Placed and the offer acceptance form is sent with DocuSeal. Needs proof of ID and fee eligibility.',
        6 => 'Sends the offer acceptance form with DocuSeal. Signing it moves the applicant to Offer Accepted and on to Students; declining it moves them to Offer Rejected.',
        7 => 'Converts the applicant into a student. Needs proof of ID and fee eligibility.',
        8 => 'Needs a rejection reason.',
        9 => 'The applicant declined the offer.',
        46 => 'Withdrawn or duplicate.',
    ];

    /**
     * @param  object  $applicant  the Applicant (status_id)
     * @param  iterable<object>  $allStatuses  Status rows (id, name)
     * @param  array  $priv  auth()->user()->priv()
     * @return array<int, array{id:int,name:string,via:string}>
     */
    public static function next($applicant, iterable $allStatuses, array $priv): array
    {
        $statusId = (int) ($applicant->status_id ?? 0);
        $canRejectToggle = isset($priv['applicant_rejected']) && $priv['applicant_rejected'] == 1;
        /* Offer Placed → the DocuSeal signing decides (OfferAcceptanceWorkflow), so staff get no
           manual Accept / Reject — except to finish a signed form that was held for missing proof
           of ID, or when the automatic move is switched off (DOCUSEAL_AUTO_CONVERT=false).
           Offer Rejected → the offer can be made again (Offer Placed sends a new form). */
        $fromOfferPlaced = [];
        if ($statusId == 6) {
            $fromOfferPlaced = !self::autoAccepts() ? [7, 9] : (self::signedButHeld($applicant) ? [7] : []);
        }
        $next = [];
        foreach ($allStatuses as $sts) {
            $id = (int) $sts->id;
            $name = $sts->name === 'Awating Decision' ? 'Awaiting Decision' : $sts->name;
            if (($statusId == 4 && in_array($id, [5, 8])) || ($statusId == 5 && $id == 6) || ($statusId == 6 && in_array($id, $fromOfferPlaced)) || ($statusId == 9 && $id == 6)) {
                $next[] = ['id' => $id, 'name' => $name, 'via' => 'changeApplicantStatus'];
            } elseif ($canRejectToggle && (($statusId == 3 && $id == 8) || ($statusId == 8 && $id == 3))) {
                $next[] = ['id' => $id, 'name' => $name, 'via' => 'rejectApplicationBtn'];
            }
        }

        return $next;
    }

    /** Does a DocuSeal signing move Offer Placed on by itself? */
    public static function autoAccepts(): bool
    {
        return (bool) config('services.docuseal.auto_convert');
    }

    /** Statuses that send the offer acceptance form, so need what the conversion will use. */
    public const NEEDS_PROOF = [5, 6];

    /**
     * What is missing before the offer can go out (user, 26 Oct 2026): the proof of ID
     * type, number and expiry, and a fee eligibility. Checked when staff choose Accepted
     * (which places the offer) or Offer Placed, so a signed form is never held for it.
     *
     * @return string[] labels, empty when everything is there
     */
    public static function missingForOffer($applicant): array
    {
        $proof = $applicant->proof()->first();
        $fee = $applicant->feeeligibility()->first();
        $missing = [];
        if (empty($proof->proof_type)) $missing[] = 'Proof of ID type';
        if (empty($proof->proof_id)) $missing[] = 'ID number';
        if (empty($proof->proof_expiredate)) $missing[] = 'ID expiry date';
        if (empty($fee->fee_eligibility_id)) $missing[] = 'Fee eligibility';

        return $missing;
    }

    /** The newest DocuSeal offer acceptance form sent to the applicant, if any. */
    public static function latestOfferForm($applicant): ?\App\Models\ApplicantDocusealSubmission
    {
        return \App\Models\ApplicantDocusealSubmission::where('applicant_id', $applicant->id)->orderByDesc('sent_at')->orderByDesc('id')->first();
    }

    /** Signed on DocuSeal but held at Offer Placed because the proof of ID is incomplete. */
    public static function signedButHeld($applicant): bool
    {
        $form = self::latestOfferForm($applicant);

        return $form !== null && $form->outcome === \App\Models\ApplicantDocusealSubmission::OUTCOME_HELD;
    }

    /**
     * The student record this applicant became: the student its conversion logged, else the
     * first student of its login (how the conversion steps find it), else the newest student
     * carrying the applicant id. Null when there is none (e.g. the conversion stopped).
     */
    public static function studentFor($applicant): ?\App\Models\Student
    {
        $logged = \App\Models\StudentConversionLog::where('applicant_id', $applicant->id)->whereNotNull('student_id')->orderByDesc('id')->value('student_id');
        if ($logged && ($student = \App\Models\Student::find($logged))) {
            return $student;
        }
        $email = optional(\App\Models\ApplicantUser::find($applicant->applicant_user_id))->email;
        $userId = $email ? \App\Models\StudentUser::where('email', $email)->value('id') : null;
        if ($userId && ($student = \App\Models\Student::where('student_user_id', $userId)->first())) {
            return $student;
        }

        return \App\Models\Student::where('applicant_id', $applicant->id)->where('applicant_user_id', $applicant->applicant_user_id)->orderByDesc('id')->first();
    }
}
