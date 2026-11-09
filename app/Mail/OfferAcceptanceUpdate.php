<?php

namespace App\Mail;

use App\Models\Applicant;
use App\Models\Option;
use App\Support\EmailLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Admin notice about a DocuSeal offer acceptance form (OfferAcceptanceWorkflow):
 * converted · conversion_failed · held (proof of ID missing) · rejected.
 * Sent through the default mailer, so outside production it goes to MAIL_REDIRECT_TO.
 */
class OfferAcceptanceUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public array $copy;

    public function __construct(public string $kind, public Applicant $applicant, public array $details = [])
    {
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? ''));
        $ref = $applicant->application_no;
        $this->copy = match ($kind) {
            'converted' => [
                'subject' => 'Offer accepted: '.$name.' ('.$ref.') has been moved to Students',
                'eyebrow' => 'Offer accepted',
                'tone' => '#1f8a5f',
                'paragraphs' => [
                    $name.' has accepted the offer from London Churchill College and signed the offer acceptance form on DocuSeal.',
                    'The application status has been updated to Offer Accepted and the applicant has been moved to Students. The signed offer acceptance form and its audit trail are saved in the applicant\'s uploaded files and have been carried over to the student record.',
                    'Please open the student record and check that everything has come across correctly, in particular the personal and contact details, course, proof of ID, fee eligibility and documents. If anything is missing or incorrect, please update it on the student record.',
                ],
            ],
            'conversion_failed' => [
                'subject' => 'Offer accepted, conversion stopped: '.$name.' ('.$ref.')',
                'eyebrow' => 'Conversion stopped',
                'tone' => '#b3212c',
                'paragraphs' => [
                    $name.' has accepted the offer and signed the offer acceptance form on DocuSeal, and the application status has been updated to Offer Accepted.',
                    'Moving the applicant to Students stopped before it finished, so the student record is incomplete. Please open the conversion log to see which step failed, resolve the cause and run the conversion again.',
                ],
            ],
            'held' => [
                'subject' => 'Offer acceptance signed, proof of ID needed: '.$name.' ('.$ref.')',
                'eyebrow' => 'Signed — action needed',
                'tone' => '#b06a0b',
                'paragraphs' => [
                    $name.' has signed the offer acceptance form on DocuSeal, but the applicant cannot be moved to Students until the proof of ID details are complete.',
                    'The status is still Offer Placed. Please add the missing details, then set the status to Offer Accepted from Change status.',
                ],
            ],
            default => [
                'subject' => 'Offer rejected: '.$name.' ('.$ref.')',
                'eyebrow' => 'Offer rejected',
                'tone' => '#b3212c',
                'paragraphs' => [
                    // 'The applicant declined to sign the offer acceptance form on DocuSeal, so the status was set to Offer Rejected.',
                    ($details['via'] ?? 'docuseal') === 'email_link'
                        ? 'The applicant declined the offer with the Decline button in the offer acceptance email, so the status was set to Offer Rejected.'.(($details['withdrawn'] ?? true) ? ' The DocuSeal form has been withdrawn, so its signing link no longer works.' : '')
                        : 'The applicant declined to sign the offer acceptance form on DocuSeal, so the status was set to Offer Rejected.',
                    !empty($applicant->users->email)
                        ? 'The applicant has been sent a confirmation'.(!empty($applicant->agent_user_id) ? ', and their agent has been told' : '').'.'
                        : (!empty($applicant->agent_user_id) ? 'Their agent has been told.' : 'The applicant has no email address, so no confirmation was sent.'),
                ],
            ],
        };

    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->copy['subject']);
    }

    public function content(): Content
    {
        $applicant = $this->applicant->loadMissing(['course.creation.course', 'course.semester', 'course.venue', 'users', 'contact', 'title', 'agent']);

        $site = Option::where('category', 'SITE_SETTINGS')->whereIn('name', ['site_logo', 'company_name', 'company_address', 'company_phone'])->pluck('value', 'name');

        return new Content(view: 'emails.admission.offer-acceptance-update', with: [
            // Site Settings logo: embedded in the mail (EmailLogo keeps a 420px copy); the hosted
            // file when rendered outside a mail (the copy kept in admission_notifications).
            'logoPath' => EmailLogo::path(),
            'logoUrl' => !empty($site['site_logo']) ? url('storage/'.$site['site_logo']) : null,
            'companyName' => $site['company_name'] ?? 'London Churchill College',
            'companyAddress' => $site['company_address'] ?? 'Barclay Hall, 156B Green Street, London, E7 8JQ',
            'companyPhone' => self::displayPhone($site['company_phone'] ?? '02073771077'),
            'companyPhoneTel' => self::telPhone($site['company_phone'] ?? '02073771077'),
            'name' => trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')),
            'applicantRows' => $this->applicantRows($applicant),
            'course' => $applicant->course->creation->course->name ?? null,
            'semester' => $applicant->course->semester->name ?? null,
            'recordUrl' => route('admission.show', $applicant->id),
            'logUrl' => route('admission.conversion.log', $applicant->id),
            'uploadsUrl' => route('admission.uploads', $applicant->id),
        ]);
    }

    /**
     * The email as kept in admission_notifications: rendered like the sent one, but with
     * the embedded logo (a base64 data URI outside a real send) pointing at the hosted
     * file, so a row is a few KB rather than ~80.
     */
    public function renderForRecord(): string
    {
        $html = $this->render();
        $logo = Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');

        return (string) preg_replace('#src="data:image/[^"]+"#', 'src="'.($logo ? url('storage/'.$logo) : '').'"', $html);
    }

    /** The applicant's basic details (shown on the offer rejected email); empty values are left out. */
    private function applicantRows(Applicant $applicant): array
    {
        $date = function ($value) {
            if (empty($value)) {
                return null;
            }
            try {
                return \Illuminate\Support\Carbon::createFromFormat('d-m-Y', (string) $value)->format('j M Y');
            } catch (\Throwable $e) {
                try {
                    return \Illuminate\Support\Carbon::parse($value)->format('j M Y');
                } catch (\Throwable $e) {
                    return (string) $value;
                }
            }
        };
        $course = $applicant->course;
        $studyMode = $course && $course->full_time !== null && $course->full_time !== '' ? ((int) $course->full_time === 1 ? 'Full time' : 'Part time') : null;

        return array_filter([
            'Name' => trim(implode(' ', array_filter([$applicant->title->name ?? null, $applicant->first_name, $applicant->last_name]))),
            'Application ref no.' => $applicant->application_no,
            'Email' => $applicant->users->email ?? null,
            'Mobile' => $applicant->contact->mobile ?? null,
            'Date of birth' => $date($applicant->date_of_birth),
            'Course' => $course->creation->course->name ?? null,
            'Semester' => $course->semester->name ?? null,
            'Venue' => $course->venue->name ?? null,
            'Study mode' => $studyMode,
            'Funding' => $course->student_loan ?? null,
            'Applied on' => $date($applicant->submission_date),
            'Referral code' => $applicant->referral_code ?? null,
            'Agent' => $applicant->agent->email ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /** "02073771077" → "+44 (0) 207 377 1077", as the other college emails print it. */
    public static function displayPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) === 11 && $digits[0] === '0') {
            return '+44 (0) '.substr($digits, 1, 3).' '.substr($digits, 4, 3).' '.substr($digits, 7);
        }

        return $phone;
    }

    public static function telPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        return strlen($digits) === 11 && $digits[0] === '0' ? '+44'.substr($digits, 1) : $digits;
    }
}
